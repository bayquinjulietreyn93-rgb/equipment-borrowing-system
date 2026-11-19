<?php
/**
 * Room Equipment Borrowing System
 * Database Configuration & Helper Functions
 * 
 * Works seamlessly on:
 * 1. Localhost XAMPP (default)
 * 2. InfinityFree Hosting (just update the InfinityFree variables below)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// 1. DATABASE CONFIGURATION
// ============================================================================

// Detect if running locally or on InfinityFree
$is_local = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1']);

if ($is_local) {
    // ------------------------------------------------------------------------
    // Local XAMPP Settings
    // ------------------------------------------------------------------------
    define('DB_HOST', 'sql312.infinityfree.com');
    define('DB_USER', 'if0_43450726');
    define('DB_PASS', 'genanda18');
    define('DB_NAME', 'if0_43450726_bayquin_db');
    define('DB_PORT', '3306');
} else {
    // ------------------------------------------------------------------------
    // InfinityFree Settings (Update these after creating your database on InfinityFree)
    // ------------------------------------------------------------------------
    define('DB_HOST', 'sql312.infinityfree.com'); // e.g. sql123.infinityfree.com or sql123.epizy.com
    define('DB_USER', 'if0_43450726');          // e.g. epiz_34567890
    define('DB_PASS', 'genanda18');   // Found in InfinityFree Client Area
    define('DB_NAME', 'if0_43450726_bayquin_db'); // Must include your epiz_ prefix
    define('DB_PORT', '3306');
}

// System-wide Settings
define('APP_NAME', 'Room Equipment Borrowing System');
define('APP_VERSION', '1.0.0');

// ============================================================================
// 2. PDO CONNECTION HANDLER
// ============================================================================
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database does not exist locally; attempt auto-creation on local root
            if ($e->getCode() == 1049 && (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1')) {
                try {
                    $rootDsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
                    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Import database.sql if present
                    $sqlFile = dirname(__DIR__) . '/database.sql';
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        $rootPdo->exec("USE `" . DB_NAME . "`");
                        $rootPdo->exec($sqlContent);
                    }
                    
                    // Reconnect to newly created database
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    return $pdo;
                } catch (Exception $subEx) {
                    displayDbError($e->getMessage(), true);
                    exit;
                }
            }

            displayDbError($e->getMessage(), false);
            exit;
        }
    }

    return $pdo;
}

// Pretty error screen if database connection fails
function displayDbError($errorMessage, $isCreationAttempt = false) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Notice - <?= htmlspecialchars(APP_NAME) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="card-header bg-danger text-white py-3 px-4">
                            <h4 class="mb-0 fw-bold"><i class="bi bi-database-exclamation me-2"></i>Database Connection Notice</h4>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <div class="alert alert-warning border-0 shadow-sm mb-4">
                                <strong>Error Details:</strong> <?= htmlspecialchars($errorMessage) ?>
                            </div>

                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tools text-primary me-2"></i>How to resolve this:</h5>
                            <ol class="list-group list-group-numbered mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-start border-0 ps-0">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">If running Locally on XAMPP:</div>
                                        Make sure <strong>Apache</strong> and <strong>MySQL</strong> are started in the XAMPP Control Panel. Open <a href="http://localhost/phpmyadmin" target="_blank" class="fw-bold">phpMyAdmin</a>, click <strong>Import</strong>, and upload the <code>database.sql</code> file included in this project folder.
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start border-0 ps-0">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">If hosted on InfinityFree:</div>
                                        Open <code>config/database.php</code> and fill in your exact InfinityFree MySQL credentials:
                                        <ul class="mt-2 mb-1">
                                            <li><strong>DB_HOST:</strong> e.g., <code>sql123.infinityfree.com</code></li>
                                            <li><strong>DB_USER:</strong> e.g., <code>epiz_12345678</code></li>
                                            <li><strong>DB_PASS:</strong> Your vPanel / Account Password</li>
                                            <li><strong>DB_NAME:</strong> e.g., <code>epiz_12345678_room_equipment_db</code></li>
                                        </ul>
                                    </div>
                                </li>
                            </ol>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="index.php" class="btn btn-primary px-4 py-2 rounded-pill"><i class="bi bi-arrow-clockwise me-1"></i> Retry Connection</a>
                                <a href="deployment_guide.php" class="btn btn-outline-secondary px-4 py-2 rounded-pill"><i class="bi bi-book me-1"></i> View Deployment Guide</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// ============================================================================
// 3. HELPER FUNCTIONS
// ============================================================================

/**
 * Sanitize user input to prevent XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message in session
 */
function setFlash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Generate Bootstrap 5 Status Badges
 */
function renderStatusBadge($status) {
    switch (trim($status)) {
        case 'Borrowed':
            return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1"><i class="bi bi-arrow-right-circle me-1"></i>Borrowed</span>';
        case 'Returned':
            return '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="bi bi-check-circle me-1"></i>Returned</span>';
        case 'Overdue':
            return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Overdue</span>';
        case 'Pending':
            return '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending</span>';
        default:
            return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">' . htmlspecialchars($status) . '</span>';
    }
}

/**
 * Format Date into user-friendly format (e.g. Nov 16, 2026)
 */
function formatDateFriendly($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00') {
        return '<span class="text-muted fst-italic">Not returned yet</span>';
    }
    $timestamp = strtotime($dateStr);
    return date('M d, Y', $timestamp);
}

/**
 * Format Time into user-friendly 12-hour format (e.g. 08:30 AM)
 */
function formatTimeFriendly($timeStr) {
    if (empty($timeStr)) {
        return '';
    }
    $timestamp = strtotime($timeStr);
    return date('h:i A', $timestamp);
}
