<?php
// Ensure database config is loaded
require_once __DIR__ . '/../config/database.php';

// Get current page script name for active navigation link
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($page_title)) {
    $page_title = 'Room Equipment Borrowing System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars(APP_NAME) ?></title>
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <span class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 p-2 rounded-3 text-warning">
                    <i class="bi bi-display-fill fs-5"></i>
                </span>
                <span>
                    <span class="fw-bold">Equip</span>Borrow<span class="text-warning">Hub</span>
                </span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'borrowings.php' || $current_page === 'view.php' || $current_page === 'edit.php') ? 'active' : '' ?>" href="borrowings.php">
                            <i class="bi bi-card-checklist me-1"></i> Borrow Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'create.php') ? 'active' : '' ?>" href="create.php">
                            <i class="bi bi-plus-circle me-1"></i> Borrow Equipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'inventory.php') ? 'active' : '' ?>" href="inventory.php">
                            <i class="bi bi-boxes me-1"></i> Equipment Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'deployment_guide.php') ? 'active' : '' ?>" href="deployment_guide.php">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Hosting & Setup Guide
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="create.php" class="btn btn-warning fw-semibold px-3 py-2 btn-rounded shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> New Request
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-4">
        <div class="container">
            <?php include_once __DIR__ . '/alerts.php'; ?>
