<?php
$page_title = 'Borrowing Slip & Details';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('danger', 'Invalid borrowing record ID requested.');
    header('Location: borrowings.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM equipment_borrowings WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        setFlash('danger', 'Record not found.');
        header('Location: borrowings.php');
        exit;
    }
} catch (PDOException $e) {
    setFlash('danger', 'Database error: ' . $e->getMessage());
    header('Location: borrowings.php');
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <!-- Action Buttons (Hidden on Print) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="borrowings.php" class="text-decoration-none">Borrow Records</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Record #<?= $id ?></li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 text-dark">Equipment Loan Slip</h3>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Print Slip
                </button>
                <a href="edit.php?id=<?= $id ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="borrowings.php" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <!-- Printable Slip Card -->
        <div class="receipt-card mb-5">
            <!-- Header of Slip -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-display-fill fs-3 text-primary"></i>
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars(APP_NAME) ?></h4>
                    </div>
                    <p class="text-muted small mb-0">Audio-Visual & Facilities Management Department | Property Office</p>
                    <p class="text-muted small mb-0">Official Equipment Loan Receipt & Room Gate Pass</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark border fs-6 px-3 py-2">SLIP #EQ-<?= str_pad($record['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    <div class="small text-muted mt-1">Status: <?= renderStatusBadge($record['status']) ?></div>
                </div>
            </div>

            <!-- Borrower Info & Room Assignment Grid -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6">
                    <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="bi bi-person-fill text-primary me-1"></i> Borrower Information</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0" style="width: 130px;">Full Name:</td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($record['borrower_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">ID Number:</td>
                            <td><?= htmlspecialchars($record['borrower_id_number']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Email:</td>
                            <td><?= htmlspecialchars($record['borrower_email']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Phone:</td>
                            <td><?= htmlspecialchars($record['borrower_contact']) ?></td>
                        </tr>
                    </table>
                </div>

                <div class="col-sm-6">
                    <h6 class="text-uppercase text-muted fw-bold small mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Destination & Purpose</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0" style="width: 130px;">Assigned Room:</td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($record['room_assigned']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Purpose:</td>
                            <td class="text-dark"><?= htmlspecialchars($record['purpose']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Release Condition:</td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($record['condition_on_borrow']) ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Equipment Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered border-light-subtle">
                    <thead class="table-light">
                        <tr>
                            <th>Item Description</th>
                            <th class="text-center" style="width: 100px;">Quantity</th>
                            <th style="width: 160px;">Borrow Date & Time</th>
                            <th style="width: 160px;">Expected Return</th>
                            <th style="width: 160px;">Actual Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($record['equipment_name']) ?></strong>
                                <?php if (!empty($record['remarks'])): ?>
                                    <div class="small text-muted mt-1">
                                        <em>Accessories: <?= nl2br(htmlspecialchars($record['remarks'])) ?></em>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold"><?= (int)$record['quantity'] ?></td>
                            <td>
                                <?= formatDateFriendly($record['borrow_date']) ?><br>
                                <small class="text-muted"><?= formatTimeFriendly($record['borrow_time']) ?></small>
                            </td>
                            <td>
                                <?= formatDateFriendly($record['expected_return_date']) ?>
                            </td>
                            <td>
                                <?= formatDateFriendly($record['actual_return_date']) ?>
                                <?php if (!empty($record['condition_on_return'])): ?>
                                    <div class="small text-muted">(Condition: <?= htmlspecialchars($record['condition_on_return']) ?>)</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Terms & Policy Notice -->
            <div class="alert alert-light border small text-muted mb-4">
                <strong>Borrower Policy Terms:</strong> The borrower named herein assumes full physical and legal responsibility for the safe custody and operation of the above equipment in the specified room/venue. Any lost accessories, damaged components, or unreturned items will incur replacement costs as per university/office policy.
            </div>

            <!-- Signature Lines -->
            <div class="row pt-4 mt-4 g-4 text-center">
                <div class="col-6">
                    <div class="border-top border-dark mx-auto pt-2" style="max-width: 260px;">
                        <div class="fw-bold text-dark"><?= htmlspecialchars($record['borrower_name']) ?></div>
                        <div class="small text-muted">Borrower's Signature Over Printed Name</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark mx-auto pt-2" style="max-width: 260px;">
                        <div class="fw-bold text-dark">Custodian On Duty</div>
                        <div class="small text-muted">Property & AVR Management Officer</div>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <div class="text-center text-muted small mt-4 pt-3 border-top">
                Date Printed: <?= date('F d, Y h:i A') ?> • Generated by <?= htmlspecialchars(APP_NAME) ?>
            </div>

        </div>

        <!-- Quick Return Bar (if still active) -->
        <?php if ($record['status'] === 'Borrowed' || $record['status'] === 'Overdue'): ?>
            <div class="card card-standard bg-light border p-4 mb-5 no-print">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Ready to return this equipment?</h5>
                        <p class="text-muted small mb-0">Record inspection condition and log actual return timestamp.</p>
                    </div>
                    <form action="update_status.php" method="POST" class="d-flex gap-2">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="redirect" value="view.php">
                        <input type="hidden" name="new_status" value="Returned">
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i> Mark as Returned Now
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
