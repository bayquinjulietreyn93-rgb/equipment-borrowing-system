<?php
$page_title = 'Borrow Equipment (New Request)';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Fetch available equipment inventory for dropdown
$inventoryList = [];
try {
    $stmtEquip = $pdo->query("SELECT * FROM equipment_inventory WHERE status = 'Available' ORDER BY item_name ASC");
    $inventoryList = $stmtEquip->fetchAll();
} catch (PDOException $e) {
    // If table doesn't exist yet, handle gracefully
}

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
$formData = [
    'borrower_name'        => '',
    'borrower_id_number'   => '',
    'borrower_email'       => '',
    'borrower_contact'     => '',
    'equipment_id'         => '',
    'equipment_name'       => '',
    'room_assigned'        => '',
    'quantity'             => 1,
    'borrow_date'          => date('Y-m-d'),
    'borrow_time'          => date('H:i'),
    'expected_return_date' => date('Y-m-d'),
    'status'               => 'Borrowed',
    'purpose'              => '',
    'condition_on_borrow'  => 'Good / Functional',
    'remarks'              => ''
];

// Handle Form Submission (INSERT operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitize input
    $formData['borrower_name']        = trim($_POST['borrower_name'] ?? '');
    $formData['borrower_id_number']   = trim($_POST['borrower_id_number'] ?? '');
    $formData['borrower_email']       = trim($_POST['borrower_email'] ?? '');
    $formData['borrower_contact']     = trim($_POST['borrower_contact'] ?? '');
    $formData['equipment_id']         = !empty($_POST['equipment_id']) ? (int)$_POST['equipment_id'] : null;
    $formData['equipment_name']       = trim($_POST['equipment_name'] ?? '');
    $formData['room_assigned']        = trim($_POST['room_assigned'] ?? '');
    $formData['quantity']             = max(1, (int)($_POST['quantity'] ?? 1));
    $formData['borrow_date']          = trim($_POST['borrow_date'] ?? date('Y-m-d'));
    $formData['borrow_time']          = trim($_POST['borrow_time'] ?? date('H:i:s'));
    $formData['expected_return_date'] = trim($_POST['expected_return_date'] ?? date('Y-m-d'));
    $formData['status']               = trim($_POST['status'] ?? 'Borrowed');
    $formData['purpose']              = trim($_POST['purpose'] ?? '');
    $formData['condition_on_borrow']  = trim($_POST['condition_on_borrow'] ?? 'Good / Functional');
    $formData['remarks']              = trim($_POST['remarks'] ?? '');

    // If an inventory item was picked and equipment_name is empty, pull name from DB
    if ($formData['equipment_id'] && empty($formData['equipment_name'])) {
        foreach ($inventoryList as $item) {
            if ($item['id'] == $formData['equipment_id']) {
                $formData['equipment_name'] = $item['item_name'];
                break;
            }
        }
    }

    // Server-side Validation
    if (empty($formData['borrower_name'])) {
        $errors[] = 'Borrower full name is required.';
    }
    if (empty($formData['borrower_id_number'])) {
        $errors[] = 'Borrower ID number (Student/Faculty ID) is required.';
    }
    if (empty($formData['borrower_email']) || !filter_var($formData['borrower_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (empty($formData['borrower_contact'])) {
        $errors[] = 'Borrower contact phone number is required.';
    }
    if (empty($formData['equipment_name'])) {
        $errors[] = 'Please select or enter the equipment name.';
    }
    if (empty($formData['room_assigned'])) {
        $errors[] = 'Please specify the room or venue where equipment will be used.';
    }
    if (empty($formData['purpose'])) {
        $errors[] = 'Please describe the purpose or event for borrowing this equipment.';
    }
    if ($formData['expected_return_date'] < $formData['borrow_date']) {
        $errors[] = 'Expected return date cannot be earlier than the borrow date.';
    }

    // Execute INSERT if no validation errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $insertSql = "
                INSERT INTO equipment_borrowings (
                    borrower_name, borrower_id_number, borrower_email, borrower_contact,
                    equipment_id, equipment_name, room_assigned, quantity,
                    borrow_date, borrow_time, expected_return_date, status,
                    purpose, condition_on_borrow, remarks
                ) VALUES (
                    :borrower_name, :borrower_id_number, :borrower_email, :borrower_contact,
                    :equipment_id, :equipment_name, :room_assigned, :quantity,
                    :borrow_date, :borrow_time, :expected_return_date, :status,
                    :purpose, :condition_on_borrow, :remarks
                )
            ";

            $stmtInsert = $pdo->prepare($insertSql);
            $stmtInsert->execute([
                ':borrower_name'        => $formData['borrower_name'],
                ':borrower_id_number'   => $formData['borrower_id_number'],
                ':borrower_email'       => $formData['borrower_email'],
                ':borrower_contact'     => $formData['borrower_contact'],
                ':equipment_id'         => $formData['equipment_id'],
                ':equipment_name'       => $formData['equipment_name'],
                ':room_assigned'        => $formData['room_assigned'],
                ':quantity'             => $formData['quantity'],
                ':borrow_date'          => $formData['borrow_date'],
                ':borrow_time'          => $formData['borrow_time'],
                ':expected_return_date' => $formData['expected_return_date'],
                ':status'               => $formData['status'],
                ':purpose'              => $formData['purpose'],
                ':condition_on_borrow'  => $formData['condition_on_borrow'],
                ':remarks'              => $formData['remarks']
            ]);

            $newRecordId = $pdo->lastInsertId();

            // Deduct available quantity in equipment_inventory if applicable
            if ($formData['equipment_id']) {
                $updateStockSql = "UPDATE equipment_inventory SET available_quantity = GREATEST(0, available_quantity - :qty) WHERE id = :equip_id";
                $stmtStock = $pdo->prepare($updateStockSql);
                $stmtStock->execute([
                    ':qty'      => $formData['quantity'],
                    ':equip_id' => $formData['equipment_id']
                ]);
            }

            $pdo->commit();

            setFlash('success', 'Equipment borrowing record #' . $newRecordId . ' for "' . $formData['borrower_name'] . '" was successfully created!');
            header('Location: view.php?id=' . $newRecordId);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="borrowings.php" class="text-decoration-none">Borrow Records</a></li>
                        <li class="breadcrumb-item active" aria-current="page">New Request</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 text-dark">Equipment Borrowing Form</h2>
                <p class="text-muted small mb-0">Fill in the borrower information, equipment selection, and room assignment details.</p>
            </div>
            <a href="borrowings.php" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <!-- Validation Error Alert -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i>Please correct the following errors:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Main Form Card -->
        <div class="card form-card shadow-sm border-0 mb-5">
            <div class="card-header bg-primary text-white py-3 px-4">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard2-plus me-2"></i>Equipment Loan & Room Reservation Application</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                <form action="create.php" method="POST" class="needs-validation" novalidate>
                    
                    <!-- Section 1: Borrower Details -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">1</span>
                        <h5 class="fw-bold mb-0 text-dark">Borrower Information</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="borrower_name" class="form-label">Borrower Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="borrower_name" id="borrower_name" class="form-control" required placeholder="e.g. Prof. Juan Dela Cruz" value="<?= htmlspecialchars($formData['borrower_name']) ?>">
                                <div class="invalid-feedback">Please enter the borrower's full name.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_id_number" class="form-label">ID Number (Faculty / Student / Employee) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-heading text-muted"></i></span>
                                <input type="text" name="borrower_id_number" id="borrower_id_number" class="form-control" required placeholder="e.g. 2024-FAC-0145" value="<?= htmlspecialchars($formData['borrower_id_number']) ?>">
                                <div class="invalid-feedback">Please enter a valid identification number.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="borrower_email" id="borrower_email" class="form-control" required placeholder="e.g. borrower@university.edu" value="<?= htmlspecialchars($formData['borrower_email']) ?>">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="borrower_contact" class="form-label">Contact / Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="text" name="borrower_contact" id="borrower_contact" class="form-control" required placeholder="e.g. +63 917 123 4567" value="<?= htmlspecialchars($formData['borrower_contact']) ?>">
                                <div class="invalid-feedback">Please enter a contact number.</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Section 2: Equipment & Room Assignment -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">2</span>
                        <h5 class="fw-bold mb-0 text-dark">Equipment & Room Assignment</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label for="equipment_id" class="form-label">Select Equipment from Inventory</label>
                            <select name="equipment_id" id="equipment_id" class="form-select">
                                <option value="">-- Choose from available equipment or type below --</option>
                                <?php foreach ($inventoryList as $item): ?>
                                    <option value="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['item_name']) ?>" <?= ($formData['equipment_id'] == $item['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['item_name']) ?> (Available: <?= $item['available_quantity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Choose an existing inventory item to automatically track quantities.</div>
                        </div>

                        <div class="col-md-5">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-hash text-muted"></i></span>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="50" required value="<?= (int)$formData['quantity'] ?>">
                                <div class="invalid-feedback">Please enter a quantity of 1 or more.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="equipment_name" class="form-label">Equipment Name / Description <span class="text-danger">*</span></label>
                            <input type="text" name="equipment_name" id="equipment_name" class="form-control" required placeholder="e.g. Epson EB-X06 XGA 3600lm Projector" value="<?= htmlspecialchars($formData['equipment_name']) ?>">
                            <div class="invalid-feedback">Please provide the name of the equipment.</div>
                        </div>

                        <div class="col-md-7">
                            <label for="room_assigned" class="form-label">Room / Laboratory / Venue <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input list="roomOptions" name="room_assigned" id="room_assigned" class="form-control" required placeholder="e.g. Room 304 - Multimedia Room" value="<?= htmlspecialchars($formData['room_assigned']) ?>">
                                <datalist id="roomOptions">
                                    <?php foreach ($commonRooms as $room): ?>
                                        <option value="<?= htmlspecialchars($room) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="invalid-feedback">Please specify the destination room or facility.</div>
                            </div>
                            <div class="form-text">Type to search existing rooms or enter a new room number.</div>
                        </div>

                        <div class="col-md-5">
                            <label for="condition_on_borrow" class="form-label">Condition on Borrow</label>
                            <select name="condition_on_borrow" id="condition_on_borrow" class="form-select">
                                <option value="Good / Functional" <?= ($formData['condition_on_borrow'] === 'Good / Functional') ? 'selected' : '' ?>>Good / Fully Functional</option>
                                <option value="Minor Cosmetic Scratches" <?= ($formData['condition_on_borrow'] === 'Minor Cosmetic Scratches') ? 'selected' : '' ?>>Minor Cosmetic Scratches</option>
                                <option value="Brand New / Boxed" <?= ($formData['condition_on_borrow'] === 'Brand New / Boxed') ? 'selected' : '' ?>>Brand New / Boxed</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Section 3: Schedule, Purpose & Status -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary rounded-circle p-2 me-2">3</span>
                        <h5 class="fw-bold mb-0 text-dark">Schedule & Loan Terms</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="borrow_date" class="form-label">Borrow Date <span class="text-danger">*</span></label>
                            <input type="date" name="borrow_date" id="borrow_date" class="form-control" required value="<?= htmlspecialchars($formData['borrow_date']) ?>">
                            <div class="invalid-feedback">Borrow date is required.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="borrow_time" class="form-label">Borrow Time <span class="text-danger">*</span></label>
                            <input type="time" name="borrow_time" id="borrow_time" class="form-control" required value="<?= htmlspecialchars($formData['borrow_time']) ?>">
                            <div class="invalid-feedback">Borrow time is required.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="expected_return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="expected_return_date" id="expected_return_date" class="form-control" required value="<?= htmlspecialchars($formData['expected_return_date']) ?>">
                            <div class="invalid-feedback">Expected return date is required.</div>
                        </div>

                        <div class="col-md-8">
                            <label for="purpose" class="form-label">Purpose of Borrowing <span class="text-danger">*</span></label>
                            <input type="text" name="purpose" id="purpose" class="form-control" required placeholder="e.g. Undergraduate Thesis Defense / Class Lecture / Faculty Meeting" value="<?= htmlspecialchars($formData['purpose']) ?>">
                            <div class="invalid-feedback">Please indicate the purpose.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label">Initial Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Borrowed" <?= ($formData['status'] === 'Borrowed') ? 'selected' : '' ?>>Borrowed (Released)</option>
                                <option value="Pending" <?= ($formData['status'] === 'Pending') ? 'selected' : '' ?>>Pending Approval</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="remarks" class="form-label">Accessories Included / Remarks (Optional)</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="e.g. Includes HDMI Cable, Power Brick, Remote Control, Carrying Case..."><?= htmlspecialchars($formData['remarks']) ?></textarea>
                        </div>
                    </div>

                    <!-- Submit & Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="borrowings.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-semibold">
                            <i class="bi bi-check-circle-fill me-2"></i> Save & Record Loan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
