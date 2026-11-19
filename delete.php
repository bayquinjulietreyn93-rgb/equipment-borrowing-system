<?php
/**
 * Room Equipment Borrowing System - Delete Record Handler
 * CRUD: Delete
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_GET['redirect']) ? $_GET['redirect'] : 'borrowings.php');

// Ensure redirect target is safe
if (!in_array($redirect, ['index.php', 'borrowings.php'])) {
    $redirect = 'borrowings.php';
}

if ($id <= 0) {
    setFlash('danger', 'Invalid borrowing record ID provided for deletion.');
    header("Location: {$redirect}");
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch record first to check details
    $stmtCheck = $pdo->prepare("SELECT borrower_name, equipment_id, quantity, status FROM equipment_borrowings WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    $record = $stmtCheck->fetch();

    if (!$record) {
        setFlash('warning', 'Borrowing record not found or already deleted.');
        header("Location: {$redirect}");
        exit;
    }

    // If record was active 'Borrowed' and has equipment_id, restore inventory count
    if (($record['status'] === 'Borrowed' || $record['status'] === 'Overdue') && !empty($record['equipment_id'])) {
        $restoreStmt = $pdo->prepare("UPDATE equipment_inventory SET available_quantity = LEAST(total_quantity, available_quantity + :qty) WHERE id = :equip_id");
        $restoreStmt->execute([
            ':qty'      => $record['quantity'],
            ':equip_id' => $record['equipment_id']
        ]);
    }

    // Delete borrowing record
    $stmtDelete = $pdo->prepare("DELETE FROM equipment_borrowings WHERE id = :id");
    $stmtDelete->execute([':id' => $id]);

    $pdo->commit();

    setFlash('success', 'Borrowing record #' . $id . ' (' . htmlspecialchars($record['borrower_name']) . ') has been permanently deleted.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('danger', 'Failed to delete record: ' . $e->getMessage());
}

header("Location: {$redirect}");
exit;
