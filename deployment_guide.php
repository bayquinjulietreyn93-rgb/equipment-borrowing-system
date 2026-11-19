<?php
$page_title = 'Hosting & Deployment Guide';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Deployment Guide</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Setup & Deployment Guide</h2>
            <p class="text-muted">Comprehensive step-by-step instructions for running locally on XAMPP and deploying to InfinityFree Free Web Hosting.</p>
        </div>

        <!-- Quick Summary Accordion or Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card card-stat h-100 p-4 border-start border-4 border-primary">
                    <div class="d-flex align-items-center mb-3">
                        <span class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-pc-display"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">Part 1: Local Development</h5>
                            <span class="badge bg-primary text-white">XAMPP Environment</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        Test and run the application locally on Windows using XAMPP's Apache web server and MySQL MariaDB database engine.
                    </p>
                    <a href="#section-xampp" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        View Local XAMPP Steps <i class="bi bi-arrow-down ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-stat h-100 p-4 border-start border-4 border-success">
                    <div class="d-flex align-items-center mb-3">
                        <span class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-globe-americas"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0">Part 2: Production Hosting</h5>
                            <span class="badge bg-success text-white">InfinityFree Cloud</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        Deploy your files, set up remote MySQL, import database tables, and make your application live on the public internet for free.
                    </p>
                    <a href="#section-infinityfree" class="btn btn-outline-success btn-sm rounded-pill w-100">
                        View InfinityFree Steps <i class="bi bi-arrow-down ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Section 1: Local Setup with XAMPP -->
        <div class="card card-standard mb-5" id="section-xampp">
            <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center">
                <i class="bi bi-laptop fs-4 me-2"></i>
                <h4 class="mb-0 fw-bold">Part 1: Local Setup with XAMPP</h4>
            </div>
            <div class="card-body p-4 p-md-5">
                
                <div class="timeline">
                    <!-- Step 1 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-6 px-3 py-2">1</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Place Project in XAMPP <code>htdocs</code></h5>
                            <p class="text-muted mb-2">
                                Copy the entire project folder to your XAMPP web root directory:
                            </p>
                            <div class="p-3 bg-light rounded-3 font-monospace small border mb-2">
                                C:\xampp\htdocs\room_equipment_borrowing\
                            </div>
                            <small class="text-secondary">All project files (index.php, create.php, config/, includes/, assets/) should reside inside this folder.</small>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-6 px-3 py-2">2</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Start Apache & MySQL in XAMPP Control Panel</h5>
                            <p class="text-muted mb-2">
                                Open the <strong>XAMPP Control Panel</strong> on Windows and click the <strong>Start</strong> buttons next to both:
                            </p>
                            <ul>
                                <li><strong>Apache</strong> (Default port: 80, 443)</li>
                                <li><strong>MySQL</strong> (Default port: 3306)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-6 px-3 py-2">3</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Import <code>database.sql</code> via phpMyAdmin</h5>
                            <p class="text-muted mb-2">
                                Open your web browser and navigate to:
                            </p>
                            <div class="p-2 bg-light rounded-3 font-monospace small border mb-2">
                                <a href="http://localhost/phpmyadmin" target="_blank" class="fw-bold text-primary">http://localhost/phpmyadmin</a>
                            </div>
                            <ol class="small text-muted mb-2 ps-3">
                                <li>Click on the <strong>Import</strong> tab at the top menu.</li>
                                <li>Click <strong>Choose File</strong> (Browse) and select <code>database.sql</code> from your project folder.</li>
                                <li>Scroll to the bottom and click <strong>Import</strong>.</li>
                                <li>It will automatically create the database <code>room_equipment_db</code> along with the tables and sample data.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-circle fs-6 px-3 py-2">4</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Launch the Application in Browser</h5>
                            <p class="text-muted mb-2">Visit the local URL in your browser:</p>
                            <div class="p-2 bg-light rounded-3 font-monospace small border">
                                <a href="http://localhost/room_equipment_borrowing/" target="_blank" class="fw-bold text-success">http://localhost/room_equipment_borrowing/</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 2: InfinityFree Hosting Deployment -->
        <div class="card card-standard mb-5" id="section-infinityfree">
            <div class="card-header bg-success text-white py-3 px-4 d-flex align-items-center">
                <i class="bi bi-cloud-check fs-4 me-2"></i>
                <h4 class="mb-0 fw-bold">Part 2: Deploying to InfinityFree Hosting</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
                    <div>
                        <strong>What is InfinityFree?</strong> InfinityFree is a free web hosting service offering PHP, MySQL, phpMyAdmin, and unlimited bandwidth. Perfect for hosting school projects, portfolios, and CRUD applications!
                    </div>
                </div>

                <div class="timeline">
                    <!-- Step 1 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">1</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Sign Up on InfinityFree & Create an Account</h5>
                            <ol class="small text-muted ps-3 mb-0">
                                <li>Go to <a href="https://www.infinityfree.com" target="_blank" class="fw-bold">https://www.infinityfree.com</a> and sign up for a free account.</li>
                                <li>Click <strong>Create Account</strong> in your Client Area.</li>
                                <li>Select <strong>Free Subdomain</strong> (e.g., <code>myroomborrow.epizy.com</code> or <code>equipmenthub.infinityfreeapp.com</code>).</li>
                                <li>Click <strong>Create Account</strong>. InfinityFree will generate a hosting username like <code>epiz_12345678</code>.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">2</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Create MySQL Database in InfinityFree Control Panel</h5>
                            <ol class="small text-muted ps-3 mb-2">
                                <li>Click <strong>Control Panel</strong> (vPanel) in your InfinityFree dashboard.</li>
                                <li>Under the <strong>Databases</strong> section, click <strong>MySQL Databases</strong>.</li>
                                <li>Under "Create New Database", enter a name: e.g. <code>room_equipment_db</code>.</li>
                                <li>Click <strong>Create Database</strong>.</li>
                                <li>Your full database name will look like: <br><strong class="text-dark font-monospace">epiz_12345678_room_equipment_db</strong></li>
                            </ol>
                            <div class="p-3 bg-light rounded-3 border small">
                                <strong class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Crucial Note:</strong> Note down your MySQL details from the MySQL Databases page:
                                <ul class="mb-0 mt-1 font-monospace">
                                    <li><strong>MySQL Host:</strong> sqlXXX.infinityfree.com (DO NOT use "localhost"!)</li>
                                    <li><strong>MySQL Username:</strong> epiz_12345678</li>
                                    <li><strong>MySQL Password:</strong> (Your vPanel / Account password)</li>
                                    <li><strong>MySQL Database Name:</strong> epiz_12345678_room_equipment_db</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">3</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Import <code>database.sql</code> in InfinityFree phpMyAdmin</h5>
                            <ol class="small text-muted ps-3 mb-0">
                                <li>In the InfinityFree MySQL Databases page, click the <strong>phpMyAdmin</strong> button next to your database.</li>
                                <li>Select your database name on the left sidebar.</li>
                                <li>Click the <strong>Import</strong> tab at the top.</li>
                                <li>Click <strong>Choose File</strong> and upload <code>database.sql</code> from this project.</li>
                                <li>Click <strong>Go</strong> (Import). The tables (<code>equipment_borrowings</code>, <code>equipment_inventory</code>) and sample records will be imported.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">4</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Update <code>config/database.php</code> with InfinityFree Credentials</h5>
                            <p class="text-muted small mb-2">
                                Open <code>config/database.php</code> in your code editor and update the InfinityFree block:
                            </p>
                            <pre class="bg-dark text-light p-3 rounded-3 small mb-0"><code>define('DB_HOST', 'sql100.infinityfree.com'); // Your actual InfinityFree MySQL Host
define('DB_USER', 'epiz_12345678');          // Your InfinityFree MySQL Username
define('DB_PASS', 'Your_vPanel_Password');   // Your InfinityFree Password
define('DB_NAME', 'epiz_12345678_room_equipment_db'); // Your InfinityFree Database Name
define('DB_PORT', '3306');</code></pre>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">5</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Upload Project Files to InfinityFree <code>htdocs/</code></h5>
                            <p class="text-muted small mb-2">
                                You can upload files using either the <strong>Online File Manager</strong> or <strong>FileZilla (FTP)</strong>:
                            </p>
                            
                            <div class="card border-0 bg-light p-3 mb-2">
                                <h6 class="fw-bold text-dark mb-1">Option A: Using InfinityFree Online File Manager (Easiest)</h6>
                                <ol class="small text-muted ps-3 mb-0">
                                    <li>In your InfinityFree dashboard, click <strong>File Manager</strong>.</li>
                                    <li>Double click to enter the <strong class="text-danger">htdocs</strong> folder.</li>
                                    <li>Delete any default placeholder file like <code>default2.html</code> or <code>index2.html</code>.</li>
                                    <li>Upload all files and folders (<code>index.php</code>, <code>borrowings.php</code>, <code>create.php</code>, <code>edit.php</code>, <code>delete.php</code>, <code>view.php</code>, <code>inventory.php</code>, <code>config/</code>, <code>includes/</code>, <code>assets/</code>) directly into <code>htdocs/</code>.</li>
                                </ol>
                            </div>

                            <div class="card border-0 bg-light p-3">
                                <h6 class="fw-bold text-dark mb-1">Option B: Using FileZilla (FTP Client)</h6>
                                <ol class="small text-muted ps-3 mb-0">
                                    <li>Download and install <a href="https://filezilla-project.org/" target="_blank">FileZilla Client</a>.</li>
                                    <li>Host: <code>ftpupload.net</code> | Port: <code>21</code></li>
                                    <li>Username & Password: Your InfinityFree FTP credentials from the Client Area.</li>
                                    <li>Drag and drop project files into the remote <code>htdocs/</code> folder.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success rounded-circle fs-6 px-3 py-2">6</span>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold text-dark mb-1">Test Your Live Application!</h5>
                            <p class="text-muted small mb-1">
                                Open your free website address (e.g. <code>http://yourdomain.epizy.com</code>) in any browser or smartphone. Perform CRUD operations (Add, View, Edit, Return, Delete) to verify database connectivity!
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 3: Database Design Specifications -->
        <div class="card card-standard mb-5">
            <div class="card-header bg-dark text-white py-3 px-4">
                <h5 class="mb-0 fw-bold"><i class="bi bi-database-check me-2"></i>Database Design Documentation</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    The requirement specifies at least 1 table with 5 or more fields, a proper primary key, and appropriate data types. The table <code>equipment_borrowings</code> contains <strong>18 well-defined fields</strong>:
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Field Name</th>
                                <th>Data Type</th>
                                <th>Key / Attributes</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>1</td><td><code>id</code></td><td>INT</td><td><span class="badge bg-primary">PRIMARY KEY</span> AUTO_INCREMENT</td><td>Unique borrowing transaction identifier</td></tr>
                            <tr><td>2</td><td><code>borrower_name</code></td><td>VARCHAR(100)</td><td>NOT NULL</td><td>Full name of the borrower</td></tr>
                            <tr><td>3</td><td><code>borrower_id_number</code></td><td>VARCHAR(50)</td><td>NOT NULL</td><td>Student / Faculty ID number</td></tr>
                            <tr><td>4</td><td><code>borrower_email</code></td><td>VARCHAR(100)</td><td>NOT NULL</td><td>Email for notification / records</td></tr>
                            <tr><td>5</td><td><code>borrower_contact</code></td><td>VARCHAR(20)</td><td>NOT NULL</td><td>Contact mobile / telephone number</td></tr>
                            <tr><td>6</td><td><code>equipment_id</code></td><td>INT</td><td>INDEX, NULLABLE</td><td>Foreign ID link to inventory item</td></tr>
                            <tr><td>7</td><td><code>equipment_name</code></td><td>VARCHAR(100)</td><td>NOT NULL</td><td>Equipment title / model description</td></tr>
                            <tr><td>8</td><td><code>room_assigned</code></td><td>VARCHAR(50)</td><td>NOT NULL</td><td>Target classroom, lab, or venue</td></tr>
                            <tr><td>9</td><td><code>quantity</code></td><td>INT</td><td>NOT NULL DEFAULT 1</td><td>Number of units borrowed</td></tr>
                            <tr><td>10</td><td><code>borrow_date</code></td><td>DATE</td><td>NOT NULL, INDEX</td><td>Date equipment was released</td></tr>
                            <tr><td>11</td><td><code>borrow_time</code></td><td>TIME</td><td>NOT NULL</td><td>Time equipment was released</td></tr>
                            <tr><td>12</td><td><code>expected_return_date</code></td><td>DATE</td><td>NOT NULL</td><td>Scheduled return deadline</td></tr>
                            <tr><td>13</td><td><code>actual_return_date</code></td><td>DATE</td><td>DEFAULT NULL</td><td>Date when equipment was returned</td></tr>
                            <tr><td>14</td><td><code>status</code></td><td>ENUM</td><td>NOT NULL, INDEX</td><td>'Borrowed', 'Returned', 'Overdue', 'Pending'</td></tr>
                            <tr><td>15</td><td><code>purpose</code></td><td>VARCHAR(255)</td><td>NOT NULL</td><td>Reason for borrowing / event title</td></tr>
                            <tr><td>16</td><td><code>condition_on_borrow</code></td><td>VARCHAR(100)</td><td>NOT NULL</td><td>Physical condition upon release</td></tr>
                            <tr><td>17</td><td><code>condition_on_return</code></td><td>VARCHAR(100)</td><td>DEFAULT NULL</td><td>Condition inspected upon return</td></tr>
                            <tr><td>18</td><td><code>remarks</code></td><td>TEXT</td><td>DEFAULT NULL</td><td>Accessories included, notes, inspection logs</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
