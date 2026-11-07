<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_role = $_SESSION['role'];
$current_user_id = $_SESSION['user_id'];

// Handle Deletion with privilege rules
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    
    // Fetch target user's details
    $target_stmt = $conn->prepare("SELECT role, username FROM users WHERE id = ?");
    $target_stmt->bind_param('i', $delete_id);
    $target_stmt->execute();
    $target_user = $target_stmt->get_result()->fetch_assoc();
    $target_stmt->close();

    if ($target_user && $target_user['role'] !== 'root') {
        // Root can delete anyone (except root); Manager cannot delete managers or root
        if ($current_role === 'root' || ($current_role === 'manager' && $target_user['role'] !== 'manager')) {
            $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $del_stmt->bind_param('i', $delete_id);
            if ($del_stmt->execute()) {
                if (function_exists('logAction')) {
                    logAction($conn, $current_user_id, 'User Removed', 'Removed user: ' . $target_user['username']);
                }
            }
            $del_stmt->close();
        }
    }
}

$users = $conn->query("SELECT id, username, role, status, created_at FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users - Bartolay Mart</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header"><i data-lucide="boxes"></i> Bartolay Mart</div>
            <ul class="nav-list">
                <li><a href="dashboard.php" class="nav-link"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li>
                    <a href="products.php" class="nav-link">
                        <i data-lucide="package"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li><a href="users.php" class="nav-link active"><i data-lucide="users"></i> Users</a></li>
                <?php if ($current_role === 'root'): ?>
                    <li><a href="approvals.php" class="nav-link"><i data-lucide="user-check"></i> Registration Approvals</a></li>
                <?php endif; ?>
                <li><a href="#" id="logoutLink" class="nav-link"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h2>User Management</h2>
            <p style="color: var(--text-muted); margin-top: 4px;">Manage user accounts, roles, and system access.</p>

            <table class="table" style="margin-top: 24px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= h($u['username']) ?></strong></td>
                            <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td><span class="badge badge-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                            <td>
                                <?php 
                                    $can_delete = false;
                                    // Added check: status must NOT be 'pending'
                                    if ($u['role'] !== 'root' && $u['id'] !== $current_user_id && $u['status'] !== 'pending') {
                                        if ($current_role === 'root') {
                                            $can_delete = true;
                                        } elseif ($current_role === 'manager' && $u['role'] === 'staff') {
                                            $can_delete = true;
                                        }
                                    }
                                ?>
                                <?php if ($can_delete): ?>
                                    <form action="users.php" method="post" class="delete-form" style="margin:0;">
                                        <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                        <button type="button" class="btn-danger btn-sm" onclick="openModal(this.form, 'Remove User', 'Are you sure you want to remove this user?')">Remove</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Custom Modal Structure -->
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
        let targetForm = null;
        let isLogoutAction = false;

        function openModal(form, title, text) {
            isLogoutAction = false;
            targetForm = form;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalText').innerText = text;
            
            const confirmBtn = document.getElementById('modalConfirmBtn');
            confirmBtn.className = "btn-danger btn-sm";
            confirmBtn.innerText = "Confirm";

            document.getElementById('customModal').classList.add('active');
        }

        function closeModal() {
            targetForm = null;
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
            } else if (targetForm) {
                targetForm.submit();
            }
        });
    </script>
    <script>lucide.createIcons();</script>
</body>
</html>