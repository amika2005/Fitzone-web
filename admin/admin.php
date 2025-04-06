<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize messages
$messages = [
    'class' => '',
    'trainer' => '',
    'membership' => '',
    'offer' => '',
    'user' => '',
    'error' => ''
];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add new class
        if (isset($_POST['class_name'])) {
            $stmt = $pdo->prepare("INSERT INTO classes (name, schedule) VALUES (?, ?)");
            $stmt->execute([htmlspecialchars($_POST['class_name']), htmlspecialchars($_POST['class_schedule'])]);
            $messages['class'] = "Class added successfully!";
        }
        // Add new trainer
        elseif (isset($_POST['trainer_name'])) {
            $stmt = $pdo->prepare("INSERT INTO trainers (name, experience) VALUES (?, ?)");
            $stmt->execute([htmlspecialchars($_POST['trainer_name']), htmlspecialchars($_POST['trainer_experience'])]);
            $messages['trainer'] = "Trainer added successfully!";
        }
        // Add new membership
        elseif (isset($_POST['membership_type'])) {
            $stmt = $pdo->prepare("INSERT INTO memberships (type, price) VALUES (?, ?)");
            $stmt->execute([htmlspecialchars($_POST['membership_type']), floatval($_POST['membership_price'])]);
            $messages['membership'] = "Membership added successfully!";
        }
        // Add new offer
        elseif (isset($_POST['offer_name'])) {
            $stmt = $pdo->prepare("INSERT INTO offers (name, discount) VALUES (?, ?)");
            $stmt->execute([htmlspecialchars($_POST['offer_name']), htmlspecialchars($_POST['offer_discount'])]);
            $messages['offer'] = "Offer added successfully!";
        }
        // Add new user
        elseif (isset($_POST['user_name'])) {
            $is_admin = (strtolower($_POST['user_role']) === 'admin') ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO users (full_name, is_admin) VALUES (?, ?)");
            $stmt->execute([htmlspecialchars($_POST['user_name']), $is_admin]);
            $messages['user'] = "User added successfully!";
        }
        // Update class
        elseif (isset($_POST['update_class'])) {
            $stmt = $pdo->prepare("UPDATE classes SET name = ?, schedule = ? WHERE id = ?");
            $stmt->execute([
                htmlspecialchars($_POST['update_class_name']),
                htmlspecialchars($_POST['update_class_schedule']),
                intval($_POST['update_class_id'])
            ]);
            $messages['class'] = "Class updated successfully!";
        }
        // Update trainer
        elseif (isset($_POST['update_trainer'])) {
            $stmt = $pdo->prepare("UPDATE trainers SET name = ?, experience = ? WHERE id = ?");
            $stmt->execute([
                htmlspecialchars($_POST['update_trainer_name']),
                htmlspecialchars($_POST['update_trainer_experience']),
                intval($_POST['update_trainer_id'])
            ]);
            $messages['trainer'] = "Trainer updated successfully!";
        }
        // Update membership
        elseif (isset($_POST['update_membership'])) {
            $stmt = $pdo->prepare("UPDATE memberships SET type = ?, price = ? WHERE id = ?");
            $stmt->execute([
                htmlspecialchars($_POST['update_membership_type']),
                floatval($_POST['update_membership_price']),
                intval($_POST['update_membership_id'])
            ]);
            $messages['membership'] = "Membership updated successfully!";
        }
        // Update offer
        elseif (isset($_POST['update_offer'])) {
            $stmt = $pdo->prepare("UPDATE offers SET name = ?, discount = ? WHERE id = ?");
            $stmt->execute([
                htmlspecialchars($_POST['update_offer_name']),
                htmlspecialchars($_POST['update_offer_discount']),
                intval($_POST['update_offer_id'])
            ]);
            $messages['offer'] = "Offer updated successfully!";
        }
        // Update user
        elseif (isset($_POST['update_user'])) {
            $is_admin = (strtolower($_POST['update_user_role']) === 'admin') ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, is_admin = ? WHERE id = ?");
            $stmt->execute([
                htmlspecialchars($_POST['update_user_name']),
                $is_admin,
                intval($_POST['update_user_id'])
            ]);
            $messages['user'] = "User updated successfully!";
        }
    } catch (PDOException $e) {
        $messages['error'] = "Database error: " . $e->getMessage();
    }
}

// Process delete actions
if (isset($_GET['delete'])) {
    try {
        $id = intval($_GET['id']);
        
        switch ($_GET['delete']) {
            case 'class':
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
                $stmt->execute([$id]);
                $messages['class'] = "Class deleted successfully!";
                break;
            case 'trainer':
                $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = ?");
                $stmt->execute([$id]);
                $messages['trainer'] = "Trainer deleted successfully!";
                break;
            case 'membership':
                $stmt = $pdo->prepare("DELETE FROM memberships WHERE id = ?");
                $stmt->execute([$id]);
                $messages['membership'] = "Membership deleted successfully!";
                break;
            case 'offer':
                $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ?");
                $stmt->execute([$id]);
                $messages['offer'] = "Offer deleted successfully!";
                break;
            case 'user':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $messages['user'] = "User deleted successfully!";
                break;
        }
        
        // Redirect to avoid resubmission
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    } catch (PDOException $e) {
        $messages['error'] = "Database error: " . $e->getMessage();
    }
}

// Fetch existing data
$tables = [
    'classes' => $pdo->query("SELECT * FROM classes ORDER BY created_at DESC")->fetchAll(),
    'trainers' => $pdo->query("SELECT * FROM trainers ORDER BY created_at DESC")->fetchAll(),
    'memberships' => $pdo->query("SELECT * FROM memberships ORDER BY created_at DESC")->fetchAll(),
    'offers' => $pdo->query("SELECT * FROM offers ORDER BY created_at DESC")->fetchAll(),
    'users' => $pdo->query("SELECT id, full_name, email, is_admin, created_at FROM users ORDER BY created_at DESC")->fetchAll()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <title>Admin Dashboard - FitZone Fitness Center</title>
    <style>
        .admin_container { display: flex; }
        .sidebar {
            width: 250px;
            background: #222;
            color: white;
            height: 100vh;
            padding: 20px;
        }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li {
            padding: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        .sidebar ul li:hover { background: #ff6600; }
        .content { flex: 1; padding: 20px; }
        .dashboard_section { display: none; }
        .dashboard_section.active { display: block; }
        form {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin-bottom: 20px;
        }
        label { display: block; font-weight: bold; margin: 10px 0 5px; }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        button {
            background: #ff6600;
            color: white;
            border: none;
            padding: 10px;
            margin-top: 10px;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover { background: #e05500; }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th { background-color: #f2f2f2; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        .action-buttons { display: flex; gap: 5px; }
        .action-buttons button {
            width: auto;
            padding: 5px 10px;
            font-size: 14px;
        }
        .edit-btn { background: #4CAF50; }
        .edit-btn:hover { background: #45a049; }
        .delete-btn { background: #f44336; }
        .delete-btn:hover { background: #d32f2f; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
    </style>
</head>
<body>
    <div class="admin_container">
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li onclick="showSection('classes')">Manage Classes</li>
                <li onclick="showSection('trainers')">Manage Trainers</li>
                <li onclick="showSection('memberships')">Manage Memberships</li>
                <li onclick="showSection('offers')">Manage Offers</li>
                <li onclick="showSection('users')">User Management</li>
                <li><a href="../index.html" style="color:white;">Back to Site</a></li>
            </ul>
        </nav>
        <div class="content">
            <!-- Display error message if any -->
            <?php if (!empty($messages['error'])): ?>
                <div class="message error"><?= $messages['error'] ?></div>
            <?php endif; ?>

            <!-- Classes Section -->
            <div id="classes" class="dashboard_section active">
                <h2>Manage Classes</h2>
                <?php if (!empty($messages['class'])): ?>
                    <div class="message success"><?= $messages['class'] ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label for="class_name">Class Name:</label>
                    <input type="text" id="class_name" name="class_name" required>
                    <label for="class_schedule">Schedule:</label>
                    <input type="text" id="class_schedule" name="class_schedule" required>
                    <button type="submit">Add Class</button>
                </form>
                
                <h3>Existing Classes</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Schedule</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables['classes'] as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['id']) ?></td>
                            <td><?= htmlspecialchars($class['name']) ?></td>
                            <td><?= htmlspecialchars($class['schedule']) ?></td>
                            <td><?= htmlspecialchars($class['created_at']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditClassModal(<?= $class['id'] ?>, '<?= htmlspecialchars($class['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($class['schedule'], ENT_QUOTES) ?>')">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete('class', <?= $class['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Trainers Section -->
            <div id="trainers" class="dashboard_section">
                <h2>Manage Trainers</h2>
                <?php if (!empty($messages['trainer'])): ?>
                    <div class="message success"><?= $messages['trainer'] ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label for="trainer_name">Trainer Name:</label>
                    <input type="text" id="trainer_name" name="trainer_name" required>
                    <label for="trainer_experience">Experience:</label>
                    <input type="text" id="trainer_experience" name="trainer_experience" required>
                    <button type="submit">Add Trainer</button>
                </form>
                
                <h3>Existing Trainers</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Experience</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables['trainers'] as $trainer): ?>
                        <tr>
                            <td><?= htmlspecialchars($trainer['id']) ?></td>
                            <td><?= htmlspecialchars($trainer['name']) ?></td>
                            <td><?= htmlspecialchars($trainer['experience']) ?></td>
                            <td><?= htmlspecialchars($trainer['created_at']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditTrainerModal(<?= $trainer['id'] ?>, '<?= htmlspecialchars($trainer['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($trainer['experience'], ENT_QUOTES) ?>')">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete('trainer', <?= $trainer['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Memberships Section -->
            <div id="memberships" class="dashboard_section">
                <h2>Manage Memberships</h2>
                <?php if (!empty($messages['membership'])): ?>
                    <div class="message success"><?= $messages['membership'] ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label for="membership_type">Membership Type:</label>
                    <input type="text" id="membership_type" name="membership_type" required>
                    <label for="membership_price">Price:</label>
                    <input type="number" step="0.01" id="membership_price" name="membership_price" required>
                    <button type="submit">Add Membership</button>
                </form>
                
                <h3>Existing Memberships</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables['memberships'] as $membership): ?>
                        <tr>
                            <td><?= htmlspecialchars($membership['id']) ?></td>
                            <td><?= htmlspecialchars($membership['type']) ?></td>
                            <td>$<?= number_format($membership['price'], 2) ?></td>
                            <td><?= htmlspecialchars($membership['created_at']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditMembershipModal(<?= $membership['id'] ?>, '<?= htmlspecialchars($membership['type'], ENT_QUOTES) ?>', <?= $membership['price'] ?>)">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete('membership', <?= $membership['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Offers Section -->
            <div id="offers" class="dashboard_section">
                <h2>Manage Offers</h2>
                <?php if (!empty($messages['offer'])): ?>
                    <div class="message success"><?= $messages['offer'] ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label for="offer_name">Offer Name:</label>
                    <input type="text" id="offer_name" name="offer_name" required>
                    <label for="offer_discount">Discount:</label>
                    <input type="text" id="offer_discount" name="offer_discount" required>
                    <button type="submit">Add Offer</button>
                </form>
                
                <h3>Existing Offers</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Discount</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables['offers'] as $offer): ?>
                        <tr>
                            <td><?= htmlspecialchars($offer['id']) ?></td>
                            <td><?= htmlspecialchars($offer['name']) ?></td>
                            <td><?= htmlspecialchars($offer['discount']) ?></td>
                            <td><?= htmlspecialchars($offer['created_at']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditOfferModal(<?= $offer['id'] ?>, '<?= htmlspecialchars($offer['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($offer['discount'], ENT_QUOTES) ?>')">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete('offer', <?= $offer['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Users Section -->
            <div id="users" class="dashboard_section">
                <h2>User Management</h2>
                <?php if (!empty($messages['user'])): ?>
                    <div class="message success"><?= $messages['user'] ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label for="user_name">User Name:</label>
                    <input type="text" id="user_name" name="user_name" required>
                    <label for="user_role">Role:</label>
                    <select id="user_role" name="user_role" required>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                    <button type="submit">Add User</button>
                </form>
                
                <h3>Existing Users</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables['users'] as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['is_admin'] ? 'Admin' : 'Member' ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditUserModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name'], ENT_QUOTES) ?>', '<?= $user['is_admin'] ? 'admin' : 'member' ?>')">Edit</button>
                                <button class="delete-btn" onclick="confirmDelete('user', <?= $user['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Edit Class Modal -->
    <div id="editClassModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editClassModal')">&times;</span>
            <h2>Edit Class</h2>
            <form method="POST">
                <input type="hidden" name="update_class_id" id="update_class_id">
                <label for="update_class_name">Class Name:</label>
                <input type="text" id="update_class_name" name="update_class_name" required>
                <label for="update_class_schedule">Schedule:</label>
                <input type="text" id="update_class_schedule" name="update_class_schedule" required>
                <button type="submit" name="update_class">Update Class</button>
            </form>
        </div>
    </div>

    <!-- Edit Trainer Modal -->
    <div id="editTrainerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editTrainerModal')">&times;</span>
            <h2>Edit Trainer</h2>
            <form method="POST">
                <input type="hidden" name="update_trainer_id" id="update_trainer_id">
                <label for="update_trainer_name">Trainer Name:</label>
                <input type="text" id="update_trainer_name" name="update_trainer_name" required>
                <label for="update_trainer_experience">Experience:</label>
                <input type="text" id="update_trainer_experience" name="update_trainer_experience" required>
                <button type="submit" name="update_trainer">Update Trainer</button>
            </form>
        </div>
    </div>

    <!-- Edit Membership Modal -->
    <div id="editMembershipModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editMembershipModal')">&times;</span>
            <h2>Edit Membership</h2>
            <form method="POST">
                <input type="hidden" name="update_membership_id" id="update_membership_id">
                <label for="update_membership_type">Membership Type:</label>
                <input type="text" id="update_membership_type" name="update_membership_type" required>
                <label for="update_membership_price">Price:</label>
                <input type="number" step="0.01" id="update_membership_price" name="update_membership_price" required>
                <button type="submit" name="update_membership">Update Membership</button>
            </form>
        </div>
    </div>

    <!-- Edit Offer Modal -->
    <div id="editOfferModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editOfferModal')">&times;</span>
            <h2>Edit Offer</h2>
            <form method="POST">
                <input type="hidden" name="update_offer_id" id="update_offer_id">
                <label for="update_offer_name">Offer Name:</label>
                <input type="text" id="update_offer_name" name="update_offer_name" required>
                <label for="update_offer_discount">Discount:</label>
                <input type="text" id="update_offer_discount" name="update_offer_discount" required>
                <button type="submit" name="update_offer">Update Offer</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="update_user_id" id="update_user_id">
                <label for="update_user_name">User Name:</label>
                <input type="text" id="update_user_name" name="update_user_name" required>
                <label for="update_user_role">Role:</label>
                <select id="update_user_role" name="update_user_role" required>
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="update_user">Update User</button>
            </form>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.dashboard_section').forEach((section) => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        // Class functions
        function openEditClassModal(id, name, schedule) {
            document.getElementById('update_class_id').value = id;
            document.getElementById('update_class_name').value = name;
            document.getElementById('update_class_schedule').value = schedule;
            document.getElementById('editClassModal').style.display = 'block';
        }

        // Trainer functions
        function openEditTrainerModal(id, name, experience) {
            document.getElementById('update_trainer_id').value = id;
            document.getElementById('update_trainer_name').value = name;
            document.getElementById('update_trainer_experience').value = experience;
            document.getElementById('editTrainerModal').style.display = 'block';
        }

        // Membership functions
        function openEditMembershipModal(id, type, price) {
            document.getElementById('update_membership_id').value = id;
            document.getElementById('update_membership_type').value = type;
            document.getElementById('update_membership_price').value = price;
            document.getElementById('editMembershipModal').style.display = 'block';
        }

        // Offer functions
        function openEditOfferModal(id, name, discount) {
            document.getElementById('update_offer_id').value = id;
            document.getElementById('update_offer_name').value = name;
            document.getElementById('update_offer_discount').value = discount;
            document.getElementById('editOfferModal').style.display = 'block';
        }

        // User functions
        function openEditUserModal(id, name, role) {
            document.getElementById('update_user_id').value = id;
            document.getElementById('update_user_name').value = name;
            document.getElementById('update_user_role').value = role;
            document.getElementById('editUserModal').style.display = 'block';
        }

        // General modal functions
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Delete confirmation
        function confirmDelete(type, id) {
            if (confirm(`Are you sure you want to delete this ${type}?`)) {
                window.location.href = `?delete=${type}&id=${id}`;
            }
        }
    </script>
</body>
</html>