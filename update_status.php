<?php
/**
 * Room Equipment Borrowing System - Quick Status Updater
 * CRUD: Quick Update for Returning Equipment
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: borrowings.php');
    exit;
}

$id                  = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$newStatus           = trim($_POST['new_status'] ?? 'Returned');
$conditionOnReturn   = trim($_POST['condition_on_return'] ?? 'Good / Functional');
$remarks             = trim($_POST['remarks'] ?? '');
$redirect            = trim($_POST['redirect'] ?? 'borrowings.php');

if (!in_array($redirect, ['index.php', 'borrowings.php', 'view.php'])) {
    $redirect = 'borrowings.php';
}

if ($id <= 0) {
    setFlash('danger', 'Invalid record ID.');
    header("Location: {$redirect}");
    exit;
}

try {
    $pdo->beginTransaction();

    // Check existing record
    $stmt = $pdo->prepare("SELECT * FROM equipment_borrowings WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();

    if (!$record) {
        setFlash('danger', 'Borrowing record not found.');
        header("Location: {$redirect}");
        exit;
    }

    $actualReturnDate = date('Y-m-d');
    
    // Append remarks if provided
    $finalRemarks = $record['remarks'];
    if (!empty($remarks)) {
        $finalRemarks .= (!empty($finalRemarks) ? "\n" : "") . "[Return Note " . date('Y-m-d H:i') . "]: " . $remarks;
    }

    $updateSql = "
        UPDATE equipment_borrowings SET 
            status = :status,
            actual_return_date = :actual_return_date,
            condition_on_return = :condition_on_return,
            remarks = :remarks
        WHERE id = :id
    ";

    $stmtUpdate = $pdo->prepare($updateSql);
    $stmtUpdate->execute([
        ':status'              => $newStatus,
        ':actual_return_date'  => $actualReturnDate,
        ':condition_on_return' => $conditionOnReturn,
        ':remarks'             => $finalRemarks,
        ':id'                  => $id
    ]);

    // Restore inventory quantity if item was linked and previously unreturned
    if ($record['status'] !== 'Returned' && $newStatus === 'Returned' && !empty($record['equipment_id'])) {
        $restoreSql = "UPDATE equipment_inventory SET available_quantity = LEAST(total_quantity, available_quantity + :qty) WHERE id = :equip_id";
        $stmtRestore = $pdo->prepare($restoreSql);
        $stmtRestore->execute([
            ':qty'      => $record['quantity'],
            ':equip_id' => $record['equipment_id']
        ]);
    }

    $pdo->commit();

    setFlash('success', 'Equipment borrowing #' . $id . ' for ' . htmlspecialchars($record['borrower_name']) . ' was successfully marked as RETURNED!');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('danger', 'Status update failed: ' . $e->getMessage());
}

if ($redirect === 'view.php') {
    header("Location: view.php?id={$id}");
} else {
    header("Location: {$redirect}");
}
exit;
