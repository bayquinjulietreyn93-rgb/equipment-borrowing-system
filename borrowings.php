<?php
$page_title = 'Borrowing Records';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=equipment_borrowings_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Borrower Name', 'ID Number', 'Email', 'Contact', 'Equipment', 'Room', 'Qty', 'Borrow Date', 'Borrow Time', 'Expected Return', 'Actual Return', 'Status', 'Purpose', 'Borrow Condition', 'Return Condition', 'Remarks']);
    
    $exportStmt = $pdo->query("SELECT * FROM equipment_borrowings ORDER BY id DESC");
    while ($row = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['borrower_name'],
            $row['borrower_id_number'],
            $row['borrower_email'],
            $row['borrower_contact'],
            $row['equipment_name'],
            $row['room_assigned'],
            $row['quantity'],
            $row['borrow_date'],
            $row['borrow_time'],
            $row['expected_return_date'],
            $row['actual_return_date'],
            $row['status'],
            $row['purpose'],
            $row['condition_on_borrow'],
            $row['condition_on_return'],
            $row['remarks']
        ]);
    }
    fclose($output);
    exit;
}

// Filters & Search handling
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortOrder = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

$sql = "SELECT * FROM equipment_borrowings WHERE 1=1";
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['Borrowed', 'Returned', 'Overdue', 'Pending'])) {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (borrower_name LIKE :search OR borrower_id_number LIKE :search OR equipment_name LIKE :search OR room_assigned LIKE :search OR purpose LIKE :search)";
    $params[':search'] = "%{$searchQuery}%";
}

switch ($sortOrder) {
    case 'oldest':
        $sql .= " ORDER BY id ASC";
        break;
    case 'borrow_date':
        $sql .= " ORDER BY borrow_date DESC, borrow_time DESC";
        break;
    case 'borrower':
        $sql .= " ORDER BY borrower_name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY id DESC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

    // Counts for filter pills
    $countAll = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings")->fetchColumn();
    $countBorrowed = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Borrowed'")->fetchColumn();
    $countReturned = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Returned'")->fetchColumn();
    $countOverdue = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Overdue'")->fetchColumn();
    $countPending = $pdo->query("SELECT COUNT(*) FROM equipment_borrowings WHERE status = 'Pending'")->fetchColumn();
} catch (PDOException $e) {
    $records = [];
    setFlash('danger', 'Error querying database: ' . $e->getMessage());
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Borrow Records</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Equipment Borrowing Records</h2>
        <p class="text-muted small mb-0">Browse, filter, search, print slips, and manage all room equipment loan records.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="borrowings.php?export=csv" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> New Borrowing
        </a>
    </div>
</div>

<!-- Status Navigation Tabs / Filter Pills -->
<div class="card card-standard mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-7">
                <ul class="nav nav-pills flex-nowrap overflow-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?= empty($statusFilter) ? 'active bg-primary text-white' : 'text-secondary' ?> rounded-pill px-3 py-1 small fw-semibold" href="borrowings.php<?= !empty($searchQuery) ? '?search=' . urlencode($searchQuery) : '' ?>">
                            All <span class="badge bg-light text-dark ms-1"><?= $countAll ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($statusFilter === 'Borrowed') ? 'active bg-primary text-white' : 'text-secondary' ?> rounded-pill px-3 py-1 small fw-semibold" href="borrowings.php?status=Borrowed<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>">
                            Borrowed <span class="badge bg-light text-dark ms-1"><?= $countBorrowed ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($statusFilter === 'Returned') ? 'active bg-success text-white' : 'text-secondary' ?> rounded-pill px-3 py-1 small fw-semibold" href="borrowings.php?status=Returned<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>">
                            Returned <span class="badge bg-light text-dark ms-1"><?= $countReturned ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($statusFilter === 'Overdue') ? 'active bg-danger text-white' : 'text-secondary' ?> rounded-pill px-3 py-1 small fw-semibold" href="borrowings.php?status=Overdue<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>">
                            Overdue <span class="badge bg-light text-dark ms-1"><?= $countOverdue ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($statusFilter === 'Pending') ? 'active bg-warning text-dark' : 'text-secondary' ?> rounded-pill px-3 py-1 small fw-semibold" href="borrowings.php?status=Pending<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?>">
                            Pending <span class="badge bg-light text-dark ms-1"><?= $countPending ?></span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-5">
                <form action="borrowings.php" method="GET" class="d-flex gap-2">
                    <?php if (!empty($statusFilter)): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search borrower, item, room..." value="<?= htmlspecialchars($searchQuery) ?>" id="liveTableSearch">
                    </div>
                    <?php if (!empty($searchQuery) || !empty($statusFilter)): ?>
                        <a href="borrowings.php" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary px-3">Search</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Records Table -->
<div class="card card-standard">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <span class="fw-semibold text-secondary">
            Showing <strong class="text-dark"><?= count($records) ?></strong> borrowing record(s)
        </span>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted mb-0 d-none d-sm-inline">Sort by:</label>
            <select class="form-select form-select-sm w-auto" onchange="location = this.value;">
                <option value="borrowings.php?sort=newest<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?><?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" <?= ($sortOrder === 'newest') ? 'selected' : '' ?>>Newest First</option>
                <option value="borrowings.php?sort=oldest<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?><?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" <?= ($sortOrder === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                <option value="borrowings.php?sort=borrow_date<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?><?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" <?= ($sortOrder === 'borrow_date') ? 'selected' : '' ?>>Borrow Date</option>
                <option value="borrowings.php?sort=borrower<?= !empty($statusFilter) ? '&status='.$statusFilter : '' ?><?= !empty($searchQuery) ? '&search='.urlencode($searchQuery) : '' ?>" <?= ($sortOrder === 'borrower') ? 'selected' : '' ?>>Borrower Name</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table custom-table align-middle" id="borrowingsTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Borrower Info</th>
                        <th>Equipment & Room</th>
                        <th>Qty</th>
                        <th>Schedule Dates</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-search display-6 d-block mb-3 text-secondary"></i>
                                <h5>No matching borrowing records found</h5>
                                <p class="mb-3">Try adjusting your search criteria or filter options.</p>
                                <a href="borrowings.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">Reset All Filters</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['borrower_name']) ?></div>
                                    <div class="small text-muted"><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($row['borrower_id_number']) ?></div>
                                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($row['borrower_contact']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary"><?= htmlspecialchars($row['equipment_name']) ?></div>
                                    <div class="small text-secondary"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($row['room_assigned']) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['purpose']) ?>">
                                        <em><?= htmlspecialchars($row['purpose']) ?></em>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">
                                        <?= (int)$row['quantity'] ?> unit(s)
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>Borrowed:</strong> <?= formatDateFriendly($row['borrow_date']) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <strong>Expected Return:</strong> <?= formatDateFriendly($row['expected_return_date']) ?>
                                    </div>
                                    <?php if (!empty($row['actual_return_date'])): ?>
                                        <div class="small text-success">
                                            <strong>Returned On:</strong> <?= formatDateFriendly($row['actual_return_date']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= renderStatusBadge($row['status']) ?>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <a class="dropdown-item" href="view.php?id=<?= $row['id'] ?>">
                                                    <i class="bi bi-file-earmark-text text-secondary me-2"></i> View Slip / Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="edit.php?id=<?= $row['id'] ?>">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Record
                                                </a>
                                            </li>
                                            <?php if ($row['status'] === 'Borrowed' || $row['status'] === 'Overdue'): ?>
                                                <li>
                                                    <button type="button" class="dropdown-item text-success"
                                                            data-bs-toggle="modal" data-bs-target="#returnModal"
                                                            data-id="<?= $row['id'] ?>"
                                                            data-borrower="<?= htmlspecialchars($row['borrower_name']) ?>"
                                                            data-equipment="<?= htmlspecialchars($row['equipment_name']) ?>">
                                                        <i class="bi bi-check2-circle me-2"></i> Mark as Returned
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-borrower="<?= htmlspecialchars($row['borrower_name']) ?>"
                                                        data-equipment="<?= htmlspecialchars($row['equipment_name']) ?>">
                                                    <i class="bi bi-trash3 me-2"></i> Delete Record
                                                </button>
                                            </li>
                                        </ul>
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
                    <input type="hidden" name="redirect" value="borrowings.php">

                    <p class="mb-3">
                        Mark equipment as returned for borrower: <br>
                        <strong class="text-dark fs-6" id="return_borrower_name"></strong><br>
                        <span class="text-muted small" id="return_equipment_name"></span>
                    </p>

                    <div class="mb-3">
                        <label for="modal_condition_on_return" class="form-label">Condition on Return</label>
                        <select name="condition_on_return" id="modal_condition_on_return" class="form-select" required>
                            <option value="Good / Functional" selected>Good / Fully Functional</option>
                            <option value="Minor Cosmetic Scratches">Minor Cosmetic Scratches</option>
                            <option value="Missing Accessory / Cable">Missing Accessory / Cable</option>
                            <option value="Damaged / Needs Repair">Damaged / Needs Repair</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_return_remarks" class="form-label">Return Remarks (Optional)</label>
                        <textarea name="remarks" id="modal_return_remarks" class="form-control" rows="2" placeholder="e.g. Returned with all cables in good order..."></textarea>
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
                    <input type="hidden" name="redirect" value="borrowings.php">

                    <i class="bi bi-trash3-fill text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold mb-2">Are you sure you want to delete this record?</h5>
                    <p class="text-muted mb-0">
                        Record of <strong id="delete_borrower_name"></strong> (<span id="delete_equipment_name"></span>) will be permanently deleted.
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
