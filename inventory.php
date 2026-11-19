<?php
$page_title = 'Equipment Inventory';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

$actionError = null;

// Handle Add New Equipment (INSERT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_equipment') {
    $itemCode    = trim($_POST['item_code'] ?? '');
    $itemName    = trim($_POST['item_name'] ?? '');
    $category    = trim($_POST['category'] ?? 'Audio/Visual');
    $totalQty    = max(1, (int)($_POST['total_quantity'] ?? 1));
    $storageRoom = trim($_POST['storage_room'] ?? 'IT Storage');
    $status      = trim($_POST['status'] ?? 'Available');

    if (empty($itemCode) || empty($itemName)) {
        setFlash('danger', 'Item Code and Equipment Name are required.');
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO equipment_inventory (item_code, item_name, category, total_quantity, available_quantity, storage_room, status)
                VALUES (:item_code, :item_name, :category, :total_quantity, :available_quantity, :storage_room, :status)
            ");
            $stmt->execute([
                ':item_code'          => $itemCode,
                ':item_name'          => $itemName,
                ':category'           => $category,
                ':total_quantity'     => $totalQty,
                ':available_quantity' => $totalQty,
                ':storage_room'       => $storageRoom,
                ':status'             => $status
            ]);
            setFlash('success', 'Equipment item "' . htmlspecialchars($itemName) . '" was added to inventory!');
            header('Location: inventory.php');
            exit;
        } catch (PDOException $e) {
            setFlash('danger', 'Error adding item: ' . $e->getMessage());
        }
    }
}

// Handle Update Equipment (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_equipment') {
    $equipId     = (int)($_POST['id'] ?? 0);
    $itemCode    = trim($_POST['item_code'] ?? '');
    $itemName    = trim($_POST['item_name'] ?? '');
    $category    = trim($_POST['category'] ?? 'Audio/Visual');
    $totalQty    = max(1, (int)($_POST['total_quantity'] ?? 1));
    $availQty    = max(0, (int)($_POST['available_quantity'] ?? 1));
    $storageRoom = trim($_POST['storage_room'] ?? 'IT Storage');
    $status      = trim($_POST['status'] ?? 'Available');

    if ($equipId > 0 && !empty($itemName)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE equipment_inventory SET
                    item_code = :item_code,
                    item_name = :item_name,
                    category = :category,
                    total_quantity = :total_quantity,
                    available_quantity = :available_quantity,
                    storage_room = :storage_room,
                    status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                ':item_code'          => $itemCode,
                ':item_name'          => $itemName,
                ':category'           => $category,
                ':total_quantity'     => $totalQty,
                ':available_quantity' => $availQty,
                ':storage_room'       => $storageRoom,
                ':status'             => $status,
                ':id'                 => $equipId
            ]);
            setFlash('success', 'Equipment item updated successfully!');
            header('Location: inventory.php');
            exit;
        } catch (PDOException $e) {
            setFlash('danger', 'Error updating item: ' . $e->getMessage());
        }
    }
}

// Fetch all equipment items
try {
    $items = $pdo->query("SELECT * FROM equipment_inventory ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    $items = [];
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Equipment Inventory</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0 text-dark">Equipment Catalog & Stock Inventory</h2>
        <p class="text-muted small mb-0">Manage hardware, projectors, microphones, audio systems, and classroom accessories.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
            <i class="bi bi-plus-lg me-1"></i> Add Equipment Item
        </button>
    </div>
</div>

<div class="card card-standard">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-boxes text-primary me-2"></i>Catalog Items (<?= count($items) ?> Total)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Item Name & Category</th>
                        <th>Storage Location</th>
                        <th class="text-center">Available / Total</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No equipment found in inventory. Add your first item!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $row): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($row['item_code']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['item_name']) ?></div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($row['category']) ?></span>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-muted me-1"></i><?= htmlspecialchars($row['storage_room']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= ($row['available_quantity'] > 0) ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-1">
                                        <?= (int)$row['available_quantity'] ?> / <?= (int)$row['total_quantity'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Available'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Available</span>
                                    <?php elseif ($row['status'] === 'Maintenance'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-2 py-1">Maintenance</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><?= htmlspecialchars($row['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#editEquipModal<?= $row['id'] ?>">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Equipment Modal -->
                            <div class="modal fade" id="editEquipModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="inventory.php" method="POST">
                                            <input type="hidden" name="action" value="update_equipment">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title fw-bold">Edit Equipment #<?= $row['id'] ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4 text-start">
                                                <div class="mb-3">
                                                    <label class="form-label">Item Code</label>
                                                    <input type="text" name="item_code" class="form-control" required value="<?= htmlspecialchars($row['item_code']) ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Item Name</label>
                                                    <input type="text" name="item_name" class="form-control" required value="<?= htmlspecialchars($row['item_name']) ?>">
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Category</label>
                                                        <select name="category" class="form-select">
                                                            <option value="Audio/Visual" <?= ($row['category'] === 'Audio/Visual') ? 'selected' : '' ?>>Audio/Visual</option>
                                                            <option value="Computing" <?= ($row['category'] === 'Computing') ? 'selected' : '' ?>>Computing</option>
                                                            <option value="Cables & Adapters" <?= ($row['category'] === 'Cables & Adapters') ? 'selected' : '' ?>>Cables & Adapters</option>
                                                            <option value="Peripherals" <?= ($row['category'] === 'Peripherals') ? 'selected' : '' ?>>Peripherals</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="Available" <?= ($row['status'] === 'Available') ? 'selected' : '' ?>>Available</option>
                                                            <option value="Maintenance" <?= ($row['status'] === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
                                                            <option value="Decommissioned" <?= ($row['status'] === 'Decommissioned') ? 'selected' : '' ?>>Decommissioned</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Total Quantity</label>
                                                        <input type="number" name="total_quantity" class="form-control" min="1" value="<?= (int)$row['total_quantity'] ?>">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Available Quantity</label>
                                                        <input type="number" name="available_quantity" class="form-control" min="0" value="<?= (int)$row['available_quantity'] ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Storage Room</label>
                                                    <input type="text" name="storage_room" class="form-control" value="<?= htmlspecialchars($row['storage_room']) ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">Update Item</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Equipment -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="inventory.php" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_equipment">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add Equipment Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Item Code <span class="text-danger">*</span></label>
                        <input type="text" name="item_code" class="form-control" required placeholder="e.g. EQ-PROJ-05">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required placeholder="e.g. Sony Bravia 55 inch Smart TV">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Audio/Visual">Audio/Visual</option>
                                <option value="Computing">Computing</option>
                                <option value="Cables & Adapters">Cables & Adapters</option>
                                <option value="Peripherals">Peripherals</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="total_quantity" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Storage Room / Office</label>
                        <input type="text" name="storage_room" class="form-control" value="AVR Media Center">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="Available" selected>Available</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save to Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
