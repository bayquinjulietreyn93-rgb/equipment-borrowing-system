        </div><!-- /.container -->
    </main>

    <!-- Global Application Footer -->
    <footer class="mt-auto py-4">
        <div class="container">
            <div class="row align-items-center gy-3">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-1 fw-semibold text-dark">
                        <i class="bi bi-display me-1 text-primary"></i> <?= htmlspecialchars(APP_NAME) ?>
                    </p>
                    <p class="mb-0 text-muted small">
                        Built with PHP, MySQL, Bootstrap 5, HTML5 & CSS3. Optimized for Local XAMPP & InfinityFree Hosting.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0 small">
                        <li class="list-inline-item"><a href="index.php" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="list-inline-item text-muted">•</li>
                        <li class="list-inline-item"><a href="borrowings.php" class="text-decoration-none text-muted">Borrow Records</a></li>
                        <li class="list-inline-item text-muted">•</li>
                        <li class="list-inline-item"><a href="inventory.php" class="text-decoration-none text-muted">Equipment List</a></li>
                        <li class="list-inline-item text-muted">•</li>
                        <li class="list-inline-item"><a href="deployment_guide.php" class="text-decoration-none text-primary fw-medium">Deployment Guide</a></li>
                    </ul>
                    <div class="text-muted small mt-1">
                        &copy; <?= date('Y') ?> All Rights Reserved.
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom Project JavaScript -->
    <script src="assets/js/script.js"></script>
</body>
</html>
