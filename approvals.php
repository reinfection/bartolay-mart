<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'root') {
    header("Location: index.php");
    exit;
}

$current_role = $_SESSION['role'];

// Handle Approve / Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['target_id'])) {
    $target_id = (int)$_POST['target_id'];
    $role = $_POST['role'] ?? 'staff';
    
    if ($_POST['action'] === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status = 'approved', role = ? WHERE id = ? AND role != 'root'");
        $stmt->bind_param('si', $role, $target_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role != 'root'");
        $stmt->bind_param('i', $target_id);
        $stmt->execute();
        $stmt->close();
    }
}

$pending_users = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Approvals - Bartolay Mart</title>
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
                <li><a href="users.php" class="nav-link"><i data-lucide="users"></i> Users</a></li>
                <li><a href="approvals.php" class="nav-link active"><i data-lucide="user-check"></i> Registration Approvals</a></li>
                <li><a href="#" id="logoutLink" class="nav-link"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <h2>Account Registration Approvals</h2>
            <p style="color: var(--text-muted); margin-top: 4px;">Admin Panel: Grant permissions to newly registered accounts.</p>
            
            <table class="table" style="margin-top: 24px;">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Registered Date</th>
                        <th>Assign Permission Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_users->num_rows === 0): ?>
                        <tr><td colspan="4">No pending account approvals found.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $pending_users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= h($row['username']) ?></strong></td>
                                <td><?= h($row['created_at']) ?></td>
                                <td>
                                    <form id="approve-form-<?= $row['id'] ?>" action="approvals.php" method="post" style="display: flex; gap: 8px; margin:0;">
                                        <input type="hidden" name="target_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <select name="role" class="form-control" style="padding: 4px 8px; width: 130px;">
                                            <option value="staff">Staff</option>
                                            <option value="manager">Manager</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Approve Button triggers modal passing the approval form and green button styling -->
                                        <button type="button" class="btn-success btn-sm" onclick="openModal(document.getElementById('approve-form-<?= $row['id'] ?>'), 'Approve Account', 'Are you sure you want to approve this user account?', 'btn-success', 'Approve')">Approve</button>
                                        
                                        <!-- Reject Form & Button -->
                                        <form action="approvals.php" method="post" style="margin:0;">
                                            <input type="hidden" name="target_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="button" class="btn-danger btn-sm" onclick="openModal(this.form, 'Reject Account', 'Are you sure you want to reject this user account?', 'btn-danger', 'Reject')">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
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

        function openModal(form, title, text, btnClass, btnText) {
            isLogoutAction = false;
            targetForm = form;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalText').innerText = text;
            
            const confirmBtn = document.getElementById('modalConfirmBtn');
            confirmBtn.className = btnClass + " btn-sm";
            confirmBtn.innerText = btnText;

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