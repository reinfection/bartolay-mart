<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_role = $_SESSION['role'];
$username = $_SESSION['username'];

// Fetch live statistics for dashboard cards
$product_stats = $conn->query("SELECT COUNT(*) as total_products, COALESCE(SUM(quantity * price), 0) as total_value FROM products")->fetch_assoc();
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$pending_count = 0;
if ($current_role === 'root') {
    $pending_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'")->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bartolay Mart Inventory</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i data-lucide="boxes"></i>
                <span>Bartolay Mart</span>
            </div>
            <ul class="nav-list">
                <li>
                    <a href="dashboard.php" class="nav-link active">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="products.php" class="nav-link">
                        <i data-lucide="package"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="nav-link">
                        <i data-lucide="users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <?php if ($current_role === 'root'): ?>
                <li>
                    <a href="approvals.php" class="nav-link">
                        <i data-lucide="user-check"></i>
                        <span>Registration Approvals</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="#" id="logoutLink" class="nav-link">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <h2>Dashboard</h2>
            <p style="color: var(--text-muted); margin-top: 4px;">Welcome back, <strong><?= h($username) ?></strong>! Here is an overview of your inventory system.</p>
            
            <!-- Metric Summary Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 24px;">
                <!-- Role Card -->
                <div class="card" style="margin-top:0; border-left: 4px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Your Role</h3>
                        <i data-lucide="shield" style="color: var(--primary); width: 20px; height: 20px;"></i>
                    </div>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 12px; text-transform: capitalize;"><?= h($current_role) ?></p>
                </div>

                <!-- Total Products Card -->
                <div class="card" style="margin-top:0; border-left: 4px solid var(--success);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Total Products</h3>
                        <i data-lucide="package" style="color: var(--success); width: 20px; height: 20px;"></i>
                    </div>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 12px;"><?= number_format($product_stats['total_products']) ?></p>
                </div>

                <!-- Total Inventory Value Card -->
                <div class="card" style="margin-top:0; border-left: 4px solid var(--warning);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Inventory Value</h3>
                        <i data-lucide="tag" style="color: var(--warning); width: 20px; height: 20px;"></i>
                    </div>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 12px;">₱<?= number_format($product_stats['total_value'], 2) ?></p>
                </div>

                <!-- Total Users Card -->
                <div class="card" style="margin-top:0; border-left: 4px solid #8b5cf6;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">System Users</h3>
                        <i data-lucide="users" style="color: #8b5cf6; width: 20px; height: 20px;"></i>
                    </div>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 12px;"><?= number_format($user_count) ?></p>
                </div>

                <?php if ($current_role === 'root'): ?>
                <!-- Pending Approvals Card -->
                <div class="card" style="margin-top:0; border-left: 4px solid var(--danger);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Pending Approvals</h3>
                        <i data-lucide="user-check" style="color: var(--danger); width: 20px; height: 20px;"></i>
                    </div>
                    <p style="font-size: 1.5rem; font-weight: bold; margin-top: 12px;"><?= number_format($pending_count) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Action Shortcuts Banner -->
            <div class="card" style="margin-top: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 600;">Quick Navigation</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 2px;">Jump directly to inventory management or user oversight.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="products.php" class="btn-primary" style="text-decoration: none;">Manage Products</a>
                    <a href="users.php" class="btn-secondary" style="text-decoration: none;">View Users</a>
                </div>
            </div>
        </main>
    </div>

    <!-- Custom Modal Structure for Logout -->
    <div id="customModal" class="modal-overlay">
        <div class="modal-card">
            <div id="modalTitle" class="modal-title">Confirm Action</div>
            <div id="modalText" class="modal-text">Are you sure?</div>
            <div class="modal-buttons">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" id="modalConfirmBtn" class="btn-danger btn-sm">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let isLogoutAction = false;

        function closeModal() {
            isLogoutAction = false;
            document.getElementById('customModal').classList.remove('active');
        }

        // Handle Logout Modal Trigger
        const logoutLink = document.getElementById('logoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                isLogoutAction = true;
                
                document.getElementById('modalTitle').innerText = 'Confirm Logout';
                document.getElementById('modalText').innerText = 'Are you sure you want to log out of your account?';
                
                const confirmBtn = document.getElementById('modalConfirmBtn');
                confirmBtn.className = "btn-primary btn-sm";
                confirmBtn.innerText = "Log Out";

                document.getElementById('customModal').classList.add('active');
            });
        }

        // Global confirmation button click handler
        document.getElementById('modalConfirmBtn').addEventListener('click', function() {
            if (isLogoutAction) {
                window.location.href = "logout.php";
            }
        });
    </script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>