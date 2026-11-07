<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$errors = [];
$success = [];

// Handle Create / Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];

        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO products (name, description, quantity, price, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidi", $name, $description, $quantity, $price, $user_id);
            if ($stmt->execute()) {
                $success[] = "Product added successfully.";
            } else {
                $errors[] = "Failed to add product.";
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'edit' && isset($_POST['product_id'])) {
            $product_id = (int)$_POST['product_id'];
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, quantity = ?, price = ? WHERE id = ?");
            $stmt->bind_param("ssidi", $name, $description, $quantity, $price, $product_id);
            if ($stmt->execute()) {
                $success[] = "Product updated successfully.";
            } else {
                $errors[] = "Failed to update product.";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $success[] = "Product deleted successfully.";
        } else {
            $errors[] = "Failed to delete product.";
        }
        $stmt->close();
    }
}

$products = $conn->query("SELECT p.*, u.username as creator FROM products p LEFT JOIN users u ON p.created_by = u.id ORDER BY p.id DESC");

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Bartolay Mart</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header"><i data-lucide="boxes"></i> Bartolay Mart</div>
            <ul class="nav-list">
                <li><a href="dashboard.php" class="nav-link"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
                <li><a href="products.php" class="nav-link active"><i data-lucide="package"></i> Products</a></li>
                <li><a href="users.php" class="nav-link"><i data-lucide="users"></i> Users</a></li>
                <?php if ($current_role === 'root'): ?>
                    <li><a href="approvals.php" class="nav-link"><i data-lucide="user-check"></i> Registration Approvals</a></li>
                <?php endif; ?>
                <li><a href="#" id="logoutLink" class="nav-link"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h2>Product Inventory</h2>
            <p style="color: var(--text-muted); margin-top: 4px;">Manage stock, pricing, and item records.</p>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><i data-lucide="alert-circle"></i> <?= h($e) ?></div>
            <?php endforeach; ?>
            <?php foreach ($success as $s): ?>
                <div class="alert alert-success"><i data-lucide="check-circle"></i> <?= h($s) ?></div>
            <?php endforeach; ?>

            <!-- Add / Edit Form Card -->
            <div class="card">
                <h3><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h3>
                <form action="products.php" method="post" class="product-form">
                    <input type="hidden" name="action" value="<?= $edit_product ? 'edit' : 'add' ?>">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group-inline">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?= $edit_product ? h($edit_product['name']) : '' ?>" required>
                    </div>

                    <div class="form-group-inline">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" value="<?= $edit_product ? h($edit_product['description']) : '' ?>">
                    </div>

                    <div class="form-group-inline">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="<?= $edit_product ? $edit_product['quantity'] : '' ?>" required>
                    </div>

                    <div class="form-group-inline">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?= $edit_product ? $edit_product['price'] : '' ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-sm"><?= $edit_product ? 'Update' : 'Add' ?></button>
                        <?php if ($edit_product): ?>
                            <a href="products.php" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows === 0): ?>
                        <tr><td colspan="7">No products found in inventory.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><strong><?= h($row['name']) ?></strong></td>
                                <td><?= h($row['description']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>₱<?= number_format($row['price'], 2) ?></td>
                                <td><?= h($row['creator'] ?? 'System') ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="products.php?edit=<?= $row['id'] ?>" class="btn-primary btn-sm" style="text-decoration:none;">Edit</a>
                                        <form action="products.php" method="post" class="delete-form" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                            <button type="button" class="btn-danger btn-sm" onclick="openModal(this.form, 'Delete Product', 'Are you sure you want to delete this product?')">Delete</button>
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

        function openModal(form, title, text) {
            isLogoutAction = false;
            targetForm = form;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalText').innerText = text;
            
            // Ensure confirm button looks like a danger button for deletion
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
                confirmBtn.className = "btn-primary btn-sm"; // Make it primary style
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