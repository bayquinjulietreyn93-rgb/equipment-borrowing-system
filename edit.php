<?php
$page_title = 'Edit Borrowing Record';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', 'Invalid borrowing record ID requested.');
    header('Location: borrowings.php');
    exit;
}

// Fetch existing record
try {
    $stmt = $pdo->prepare("SELECT * FROM equipment_borrowings WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        setFlash('danger', 'The requested borrowing record does not exist.');
        header('Location: borrowings.php');
        exit;
    }
} catch (PDOException $e) {
    setFlash('danger', 'Database error: ' . $e->getMessage());
    header('Location: borrowings.php');
    exit;
}

// Fetch available equipment inventory for dropdown
$inventoryList = [];
try {
    $stmtEquip = $pdo->query("SELECT * FROM equipment_inventory ORDER BY item_name ASC");
    $inventoryList = $stmtEquip->fetchAll();
} catch (PDOException $e) {}

// Predefined room options
$commonRooms = [
    'Room 101 - Lecture Hall',
    'Room 201 - Lecture Hall',
    'Room 204 - Science Laboratory',
    'Room 304 - Multimedia Room',
    'Room 305 - Multimedia Lab',
    'Room 402 - Business Seminar Room',
    'Computer Lab 1 (Main Building)',
    'Computer Lab 3 (IT Building)',
    'Audio-Visual Room (AVR)',
    'Main Auditorium',
    'Executive Conference Room A',
    'Executive Conference Room B',
    'Faculty Lounge',
    'Student Center Hall'
];

$errors = [];

// Handle Form Update (UPDATE operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrower_name        = trim($_POST['borrower_name'] ?? '');
    $borrower_id_number   = trim($_POST['borrower_id_number'] ?? '');
    $borrower_email       = trim($_POST['borrower_email'] ?? '');
    $borrower_contact     = trim($_POST['borrower_contact'] ?? '');
    $equipment_id         = !empty($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : null;
    $equipment_name       = trim($_POST['equipment_name'] ?? '');
    $room_assigned        = trim($_POST['room_assigned'] ?? '');
    $quantity             = max(1, (int)($_POST['quantity'] ?? 1));
    $borrow_date          = trim($_POST['borrow_date'] ?? '');
    $borrow_time          = trim($_POST['borrow_time'] ?? '');
    $expected_return_date = trim($_POST['expected_return_date'] ?? '');
    $actual_return_date   = !empty($_POST['actual_return_date']) ? trim($_POST['actual_return_date']) : null;
    $status               = trim($_POST['status'] ?? 'Borrowed');
    $purpose              = trim($_POST['purpose'] ?? '');
    $condition_on_borrow  = trim($_POST['condition_on_borrow'] ?? 'Good / Functional');
    $condition_on_return  = !empty($_POST['condition_on_return']) ? trim($_POST['condition_on_return']) : null;
    $remarks              = trim($_POST['remarks'] ?? '');

    // If marked as Returned and actual_return_date is empty, default to today
    if ($status === 'Returned' && empty($actual_return_date)) {
        $actual_return_date = date('Y-m-d');
    }

    // Validation
    if (empty($borrower_name)) {
        $errors[] = 'Borrower name is required.';
    }
    if (empty($borrower_id_number)) {
        $errors[] = 'Borrower ID number is required.';
    }
    if (empty($borrower_email) || !filter_var($borrower_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($borrower_contact)) {
        $errors[] = 'Contact number is required.';
    }
    if (empty($equipment_name)) {
        $errors[] = 'Equipment name is required.';
    }
    if (empty($room_assigned)) {
        $errors[] = 'Room assigned is required.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $updateSql = "
                UPDATE equipment_borrowings SET 
                    borrower_name = :borrower_name,
                    borrower_id_number = :borrower_id_number,
                    borrower_email = :borrower_email,
                    borrower_contact = :borrower_contact,
                    equipment_id = :equipment_id,
                    equipment_name = :equipment_name,
                    room_assigned = :room_assigned,
                    quantity = :quantity,
                    borrow_date = :borrow_date,
                    borrow_time = :borrow_time,
                    expected_return_date = :expected_return_date,
                    actual_return_date = :actual_return_date,
                    status = :status,
                    purpose = :purpose,
                    condition_on_borrow = :condition_on_borrow,
                    condition_on_return = :condition_on_return,
                    remarks = :remarks
                WHERE id = :id
            ";

            $stmtUpdate = $pdo->prepare($updateSql);
            $stmtUpdate->execute([
                ':borrower_name'        => $borrower_name,
                ':borrower_id_number'   => $borrower_id_number,
                ':borrower_email'       => $borrower_email,
                ':borrower_contact'     => $borrower_contact,
                ':equipment_id'         => $equipment_id,
                ':equipment_name'       => $equipment_name,
                ':room_assigned'        => $room_assigned,
                ':quantity'             => $quantity,
                ':borrow_date'          => $borrow_date,
                ':borrow_time'          => $borrow_time,
                ':expected_return_date' => $expected_return_date,
                ':actual_return_date'   => $actual_return_date,
                ':status'               => $status,
                ':purpose'              => $purpose,
                ':condition_on_borrow'  => $condition_on_borrow,
                ':condition_on_return'  => $condition_on_return,
                ':remarks'              => $remarks,
                ':id'                   => $id
            ]);

            // If status changed from not Returned to Returned, restore quantity in inventory
            if ($record['status'] !== 'Returned' && $status === 'Returned' && $equipment_id) {
                $restoreSql = "UPDATE equipment_inventory SET available_quantity = LEAST(total_quantity, available_quantity + :qty) WHERE id = :equip_id";
                $stmtRestore = $pdo->prepare($restoreSql);
                $stmtRestore->execute([
                    ':qty'      => $quantity,
                    ':equip_id' => $equipment_id
                ]);
            }

            $pdo->commit();

            setFlash('success', 'Borrowing record #' . $id . ' has been updated successfully!');
            header('Location: view.php?id=' . $id);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Update failed: ' . $e->getMessage();
        }
    }
} else {
    // Populate form with current record
    $borrower_name        = $record['borrower_name'];
    $borrower_id_number   = $record['borrower_id_number'];
    $borrower_email       = $record['borrower_email'];
    $borrower_contact     = $record['borrower_contact'];
    $equipment_id         = $record['equipment_id'];
    $equipment_name       = $record['equipment_name'];
    $room_assigned        = $record['room_assigned'];
    $quantity             = $record['quantity'];
    $borrow_date          = $record['borrow_date'];
    $borrow_time          = $record['borrow_time'];
    $expected_return_date = $record['expected_return_date'];
    $actual_return_date   = $record['actual_return_date'];
    $status               = $record['status'];
    $purpose              = $record['purpose'];
    $condition_on_borrow  = $record['condition_on_borrow'];
    $condition_on_return  = $record['condition_on_return'];
    $remarks              = $record['remarks'];
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="borrowings.php" class="text-decoration-none">Borrow Records</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit #<?= $id ?></li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 text-dark">Edit Borrowing Record #<?= $id ?></h2>
                <p class="text-muted small mb-0">Update borrower details, return dates, status, or item condition.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="view.php?id=<?= $id ?>" class="btn btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-eye me-1"></i> View Slip
                </a>
                <a href="borrowings.php" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i>Please fix the following issues:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Form Card -->
        <div class="card form-card shadow-sm border-0 mb-5">
            <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Modify Loan Information</h5>
                <span class="badge bg-light text-dark">Record ID: #<?= $id ?></span>
            </div>
            <div class="card-body p-4 p-md-5">
                <form action="edit.php?id=<?= $id ?>" method="POST" class="needs-validation" novalidate>
                    
                    <!-- Borrower Info -->
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-person-fill text-primary me-2"></i>Borrower Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="borrower_name" class="form-label">Borrower Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_name" id="borrower_name" class="form-control" required value="<?= htmlspecialchars($borrower_name) ?>">
                            <div class="invalid-feedback">Name is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_id_number" class="form-label">ID Number <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_id_number" id="borrower_id_number" class="form-control" required value="<?= htmlspecialchars($borrower_id_number) ?>">
                            <div class="invalid-feedback">ID number is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="borrower_email" id="borrower_email" class="form-control" required value="<?= htmlspecialchars($borrower_email) ?>">
                            <div class="invalid-feedback">Valid email is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_contact" id="borrower_contact" class="form-control" required value="<?= htmlspecialchars($borrower_contact) ?>">
                            <div class="invalid-feedback">Contact number is required.</div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Equipment & Room Details -->
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-display-fill text-primary me-2"></i>Equipment & Assignment</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label for="equipment_name" class="form-label">Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" name="equipment_name" id="equipment_name" class="form-control" required value="<?= htmlspecialchars($equipment_name) ?>">
                            <input type="hidden" name="equipment_id" value="<?= htmlspecialchars($equipment_id ?? '') ?>">
                        </div>

                        <div class="col-md-5">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="50" required value="<?= (int)$quantity ?>">
                        </div>

                        <div class="col-md-7">
                            <label for="room_assigned" class="form-label">Assigned Room / Venue <span class="text-danger">*</span></label>
                            <input list="roomOptions" name="room_assigned" id="room_assigned" class="form-control" required value="<?= htmlspecialchars($room_assigned) ?>">
                            <datalist id="roomOptions">
                                <?php foreach ($commonRooms as $room): ?>
                                    <option value="<?= htmlspecialchars($room) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-5">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select fw-semibold" required>
                                <option value="Borrowed" <?= ($status === 'Borrowed') ? 'selected' : '' ?>>Borrowed (Active)</option>
                                <option value="Returned" <?= ($status === 'Returned') ? 'selected' : '' ?>>Returned (Completed)</option>
                                <option value="Overdue" <?= ($status === 'Overdue') ? 'selected' : '' ?>>Overdue / Delayed</option>
                                <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending Approval</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Schedule & Dates -->
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-calendar-check text-primary me-2"></i>Dates & Conditions</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="borrow_date" class="form-label">Borrow Date <span class="text-danger">*</span></label>
                            <input type="date" name="borrow_date" id="borrow_date" class="form-control" required value="<?= htmlspecialchars($borrow_date) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="borrow_time" class="form-label">Borrow Time <span class="text-danger">*</span></label>
                            <input type="time" name="borrow_time" id="borrow_time" class="form-control" required value="<?= htmlspecialchars($borrow_time) ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="expected_return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="expected_return_date" id="expected_return_date" class="form-control" required value="<?= htmlspecialchars($expected_return_date) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="actual_return_date" class="form-label">Actual Return Date</label>
                            <input type="date" name="actual_return_date" id="actual_return_date" class="form-control" value="<?= htmlspecialchars($actual_return_date ?? '') ?>">
                            <div class="form-text">Leave blank if the item has not yet been returned.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="condition_on_return" class="form-label">Condition on Return</label>
                            <select name="condition_on_return" id="condition_on_return" class="form-select">
                                <option value="">-- Not returned yet --</option>
                                <option value="Good / Functional" <?= ($condition_on_return === 'Good / Functional') ? 'selected' : '' ?>>Good / Fully Functional</option>
                                <option value="Minor Cosmetic Scratches" <?= ($condition_on_return === 'Minor Cosmetic Scratches') ? 'selected' : '' ?>>Minor Cosmetic Scratches</option>
                                <option value="Missing Accessory / Cable" <?= ($condition_on_return === 'Missing Accessory / Cable') ? 'selected' : '' ?>>Missing Accessory / Cable</option>
                                <option value="Damaged / Needs Repair" <?= ($condition_on_return === 'Damaged / Needs Repair') ? 'selected' : '' ?>>Damaged / Needs Repair</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="purpose" class="form-label">Purpose of Borrowing <span class="text-danger">*</span></label>
                            <input type="text" name="purpose" id="purpose" class="form-control" required value="<?= htmlspecialchars($purpose) ?>">
                        </div>

                        <div class="col-12">
                            <label for="remarks" class="form-label">Accessories & Notes</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3"><?= htmlspecialchars($remarks ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> Delete Record
                        </button>
                        <div class="d-flex gap-2">
                            <a href="borrowings.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-semibold">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirm Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="delete.php" method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Borrowing Record #<?= $id ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="redirect" value="borrowings.php">

                    <i class="bi bi-trash3-fill text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold mb-2">Are you sure you want to delete this record?</h5>
                    <p class="text-muted mb-0">
                        This action will permanently remove record #<?= $id ?> (<?= htmlspecialchars($borrower_name) ?>) from the system.
                    </p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash-fill me-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
