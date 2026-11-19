<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Fetch statistics
try {
    $statTotalBorrowed = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Borrowed'")->fetchColumn();
    $statTotalReturned = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Returned'")->fetchColumn();
    $statTotalOverdue = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Overdue'")->fetchColumn();
    $statTotalPending = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Pending'")->fetchColumn();
    $statTotalInventory = $pdo->query("SELECT COUNT(*) FROM equipment_inventory")->fetchColumn();

    // Fetch recent 7 transactions
    $stmtRecent = $pdo->query("
        SELECT * FROM equipment_borrowings 
        ORDER BY id DESC 
        LIMIT 7
    ");
    $recentRecords = $stmtRecent->fetchAll();

    // Fetch equipment inventory preview
    $stmtEquip = $pdo->query("
        SELECT * FROM equipment_inventory 
        ORDER BY available_quantity ASC 
        LIMIT 5
    ");
    $inventoryItems = $stmtEquip->fetchAll();

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error retrieving dashboard data: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $recentRecords = [];
    $inventoryItems = [];
}
?>

<!-- Hero Welcome Section -->
<div class="hero-banner mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-white text-primary mb-2 px-3 py-1 fw-bold text-uppercase">Room & Facilities Management</span>
            <h1 class="display-6 fw-bold mb-2">Room Equipment Borrowing System</h1>
            <p class="lead mb-3 text-white-50 fs-6">
                Effortlessly monitor, issue, return, and manage audiovisual and multimedia equipment assigned to classrooms, conference rooms, and laboratory facilities.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="create.php" class="btn btn-warning px-4 py-2 fw-semibold btn-rounded shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Borrow Equipment
                </a>
                <a href="borrowings.php" class="btn btn-outline-light px-4 py-2 fw-semibold btn-rounded">
                    <i class="bi bi-list-check me-1"></i> View All Records
                </a>
            </div>
        </div>
        <div class="col-lg-4 d-none d-lg-block text-end pe-4">
            <i class="bi bi-building-check display-1 opacity-75"></i>
        </div>
    </div>
</div>

<!-- Key Performance Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase mb-1 small fw-bold">Active Borrowed</h6>
                    <h3 class="mb-0 fw-bold text-dark"><?= number_format($statTotalBorrowed) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-stat p-3">
            <div class="stat-icon bg-success bg-opacity-10 text-success me-3 float-start">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Returned Items</h6>
                <h3 class="mb-0 fw-bold text-dark"><?= number_format($statTotalReturned) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-stat p-3">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3 float-start">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
            <div>
                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Overdue / Late</h6>
                <h3 class="mb-0 fw-bold text-dark"><?= number_format($statTotalOverdue) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-stat p-3">
            <div class="stat-icon bg-info bg-opacity-10 text-info me-3 float-start">
                <i class="bi bi-hdd-stack"></i>
            </div>
            <div>
                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Inventory Items</h6>
                <h3 class="mb-0 fw-bold text-dark"><?= number_format($statTotalInventory) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Records & Equipment Availability -->
<div class="row g-4">
    <!-- Recent Borrowing Transactions -->
    <div class="col-lg-8">
        <div class="card card-standard h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Recent Borrowing Transactions
                </h5>
                <a href="borrowings.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table custom-table align-middle">
                        <thead>
                            <tr>
                                <th>Borrower & ID</th>
                                <th>Equipment & Room</th>
                                <th>Borrow Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentRecords)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No borrowing transactions recorded yet. <a href="create.php">Create the first one!</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentRecords as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($row['borrower_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($row['borrower_id_number']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars($row['equipment_name']) ?>
                                            </div>
                                            <small class="text-primary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($row['room_assigned']) ?></small>
                                        </td>
                                        <td>
                                            <div><?= formatDateFriendly($row['borrow_date']) ?></div>
                                            <small class="text-muted"><?= formatTimeFriendly($row['borrow_time']) ?></small>
                                        </td>
                                        <td>
                                            <?= renderStatusBadge($row['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-light text-secondary" title="View Slip / Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-light text-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($row['status'] === 'Borrowed' || $row['status'] === 'Overdue'): ?>
                                                    <button type="button" class="btn btn-light text-success" title="Quick Return"
                                                            data-bs-toggle="modal" data-bs-target="#returnModal"
                                                            data-id="<?= $row['id'] ?>"
                                                            data-borrower="<?= htmlspecialchars($row['borrower_name']) ?>"
                                                            data-equipment="<?= htmlspecialchars($row['equipment_name']) ?>">
                                                        <i class="bi bi-check2-square"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-light text-danger" title="Delete"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-borrower="<?= htmlspecialchars($row['borrower_name']) ?>"
                                                        data-equipment="<?= htmlspecialchars($row['equipment_name']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Equipment Availability & Quick Help -->
    <div class="col-lg-4">
        <!-- Stock Watch Card -->
        <div class="card card-standard mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-boxes text-info me-2"></i>Stock Watch
                </h5>
                <a href="inventory.php" class="btn btn-sm btn-link text-decoration-none">Manage</a>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-3">Live equipment quantity availability for classroom/laboratory assignments:</p>
                <div class="list-group list-group-flush">
                    <?php if (empty($inventoryItems)): ?>
                        <div class="text-muted small py-2">No inventory data available.</div>
                    <?php else: ?>
                        <?php foreach ($inventoryItems as $item): ?>
                            <div class="list-group-item px-0 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <div class="me-2 text-truncate">
                                    <div class="fw-semibold small text-dark text-truncate" style="max-width: 180px;">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border small py-0"><?= htmlspecialchars($item['category']) ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= ($item['available_quantity'] > 0) ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                        <?= $item['available_quantity'] ?> / <?= $item['total_quantity'] ?> left
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Tips Card -->
        <div class="card card-standard bg-primary bg-opacity-10 border border-primary border-opacity-25">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-info-circle-fill text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold mb-0 text-primary">System Fast Fact</h6>
                </div>
                <p class="small text-muted mb-3">
                    Borrowing records require at least 5 standard fields: Borrower name, equipment item, room assignment, borrow date, and return status. This system tracks 18 distinct attributes!
                </p>
                <a href="deployment_guide.php" class="btn btn-sm btn-primary rounded-pill w-100">
                    <i class="bi bi-cloud-upload me-1"></i> InfinityFree Deployment Guide
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Quick Mark as Returned -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="update_status.php" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="returnModalLabel">
                        <i class="bi bi-check2-circle me-2"></i>Confirm Equipment Return
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="return_record_id" value="">
                    <input type="hidden" name="new_status" value="Returned">
                    <input type="hidden" name="redirect" value="index.php">

                    <p class="mb-3">
                        Mark equipment as returned for borrower: <br>
                        <strong class="text-dark fs-6" id="return_borrower_name"></strong><br>
                        <span class="text-muted small" id="return_equipment_name"></span>
                    </p>

                    <div class="mb-3">
                        <label for="condition_on_return" class="form-label">Condition on Return</label>
                        <select name="condition_on_return" id="condition_on_return" class="form-select" required>
                            <option value="Good / Functional" selected>Good / Fully Functional</option>
                            <option value="Minor Cosmetic Scratches">Minor Cosmetic Scratches</option>
                            <option value="Missing Accessory / Cable">Missing Accessory / Cable</option>
                            <option value="Damaged / Needs Repair">Damaged / Needs Repair</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="return_remarks" class="form-label">Return Remarks (Optional)</label>
                        <textarea name="remarks" id="return_remarks" class="form-control" rows="2" placeholder="e.g. Returned with all cables in good order..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-check2 me-1"></i> Confirm Return
                    </button>
                </div>
            </form>
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
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Borrowing Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="id" id="delete_record_id" value="">
                    <input type="hidden" name="redirect" value="index.php">

                    <i class="bi bi-trash3-fill text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold mb-2">Are you sure you want to delete this record?</h5>
                    <p class="text-muted mb-0">
                        Record of <strong id="delete_borrower_name"></strong> (<span id="delete_equipment_name"></span>) will be permanently removed from the database.
                    </p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash-fill me-1"></i> Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
