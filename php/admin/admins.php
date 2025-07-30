<?php
require_once 'admin_auth.php';

$auth = new AdminAuth();
$auth->requireAuth();

$current_admin = $auth->getCurrentAdmin();
$all_admins = $auth->getAllAdmins();
$admin_count = $auth->getAdminCount();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            
            $result = $auth->createAdmin($username, $email, $password, $full_name);
            
            if ($result['success']) {
                $success = $result['message'];
                $all_admins = $auth->getAllAdmins(); // Refresh list
                $admin_count = $auth->getAdminCount();
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'update':
            $admin_id = $_POST['admin_id'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            
            $data = [
                'email' => $email,
                'full_name' => $full_name
            ];
            
            $result = $auth->updateAdmin($admin_id, $data);
            
            if ($result['success']) {
                $success = $result['message'];
                $all_admins = $auth->getAllAdmins(); // Refresh list
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'deactivate':
            $admin_id = $_POST['admin_id'] ?? '';
            
            $result = $auth->deactivateAdmin($admin_id);
            
            if ($result['success']) {
                $success = $result['message'];
                $all_admins = $auth->getAllAdmins(); // Refresh list
                $admin_count = $auth->getAdminCount();
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'change_password':
            $admin_id = $_POST['admin_id'] ?? '';
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            $result = $auth->changePassword($admin_id, $current_password, $new_password);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Get action from URL
$action = $_GET['action'] ?? 'list';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Multi-Language Web Development Showcase</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.8;
        }

        .admin-info {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 0 20px 20px;
        }

        .admin-info h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .admin-info p {
            font-size: 12px;
            opacity: 0.8;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .btn-success {
            background: #38a169;
            color: white;
        }

        .btn-success:hover {
            background: #2f855a;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .alert-success {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #c6f6d5;
        }

        /* Admin Limit Warning */
        .admin-limit-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-limit-warning i {
            margin-right: 8px;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .card-content {
            padding: 20px;
        }

        /* Admin List */
        .admin-list {
            list-style: none;
        }

        .admin-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.3s ease;
        }

        .admin-item:hover {
            background: #f8f9fa;
        }

        .admin-item:last-child {
            border-bottom: none;
        }

        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            margin-right: 20px;
        }

        .admin-info-details {
            flex: 1;
        }

        .admin-info-details h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-info-details p {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .admin-info-details small {
            color: #999;
            font-size: 12px;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
        }

        .admin-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 15px;
        }

        .status-active {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-inactive {
            background: #fed7d7;
            color: #c53030;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .admin-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
                <p>Multi-Language Web Development Showcase</p>
            </div>

            <div class="admin-info">
                <h4><?php echo htmlspecialchars($current_admin['username']); ?></h4>
                <p><?php echo htmlspecialchars($current_admin['email']); ?></p>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="posts.php" class="nav-link">
                        <i class="fas fa-file-alt"></i> Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="comments.php" class="nav-link">
                        <i class="fas fa-comments"></i> Comments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="contacts.php" class="nav-link">
                        <i class="fas fa-envelope"></i> Contact Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admins.php" class="nav-link active">
                        <i class="fas fa-users-cog"></i> Admin Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="activity.php" class="nav-link">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Management</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <?php if ($admin_count < 3): ?>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Add Admin
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Admin Limit Warning -->
            <?php if ($admin_count >= 3): ?>
                <div class="admin-limit-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Admin Limit Reached:</strong> Maximum of 3 active admins allowed. Deactivate an admin to add a new one.
                </div>
            <?php endif; ?>

            <!-- Admin List -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Admin Accounts (<?php echo $admin_count; ?>/3)</h3>
                    <div>
                        <span class="admin-status status-active"><?php echo $admin_count; ?> Active</span>
                        <span class="admin-status status-inactive"><?php echo count($all_admins) - $admin_count; ?> Inactive</span>
                    </div>
                </div>
                <div class="card-content">
                    <?php if (empty($all_admins)): ?>
                        <p style="text-align: center; color: #666; padding: 20px;">No admin accounts found</p>
                    <?php else: ?>
                        <ul class="admin-list">
                            <?php foreach ($all_admins as $admin): ?>
                                <li class="admin-item">
                                    <div class="admin-avatar">
                                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                                    </div>
                                    <div class="admin-info-details">
                                        <h4><?php echo htmlspecialchars($admin['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                        <?php if ($admin['full_name']): ?>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['full_name']); ?></p>
                                        <?php endif; ?>
                                        <small>
                                            <strong>Created:</strong> <?php echo date('M j, Y', strtotime($admin['created_at'])); ?> |
                                            <strong>Last Login:</strong> <?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?>
                                        </small>
                                    </div>
                                    <span class="admin-status <?php echo $admin['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <div class="admin-actions">
                                        <button class="btn btn-outline" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($admin)); ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($admin['id'] != $current_admin['id']): ?>
                                            <?php if ($admin['is_active']): ?>
                                                <button class="btn btn-danger" onclick="deactivateAdmin(<?php echo $admin['id']; ?>)">
                                                    <i class="fas fa-user-slash"></i> Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success" onclick="activateAdmin(<?php echo $admin['id']; ?>)">
                                                    <i class="fas fa-user-check"></i> Activate
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <button class="btn btn-outline" onclick="openPasswordModal(<?php echo $admin['id']; ?>)">
                                            <i class="fas fa-key"></i> Password
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Create New Admin</h3>
                <span class="close" onclick="closeModal('createModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <small style="color: #666;">Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="closeModal('createModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Edit Admin</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_admin_id" name="admin_id">
                    
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" disabled style="background: #f8f9fa;">
                        <small style="color: #666;">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_email">Email *</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_full_name">Full Name *</label>
                            <input type="text" id="edit_full_name" name="full_name" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Change Password</h3>
                <span class="close" onclick="closeModal('passwordModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" id="password_admin_id" name="admin_id">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                        <small style="color: #666;">Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="closeModal('passwordModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }

        function openEditModal(admin) {
            document.getElementById('edit_admin_id').value = admin.id;
            document.getElementById('edit_username').value = admin.username;
            document.getElementById('edit_email').value = admin.email;
            document.getElementById('edit_full_name').value = admin.full_name || '';
            document.getElementById('editModal').style.display = 'block';
        }

        function openPasswordModal(adminId) {
            document.getElementById('password_admin_id').value = adminId;
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Admin actions
        function deactivateAdmin(adminId) {
            if (confirm('Are you sure you want to deactivate this admin?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="deactivate">
                    <input type="hidden" name="admin_id" value="${adminId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function activateAdmin(adminId) {
            if (confirm('Are you sure you want to activate this admin?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="admin_id" value="${adminId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-focus first input in modals
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const firstInput = modal.querySelector('input:not([type="hidden"])');
                if (firstInput) {
                    modal.addEventListener('shown', function() {
                        firstInput.focus();
                    });
                }
            });
        });
    </script>
</body>
</html> 