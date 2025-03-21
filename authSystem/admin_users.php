<?php
require_once '../Dashboard/config.php';
include DASHBOARD_PATH . 'header.php';
require_once 'session_auth.php';

// --- Admin Check ---
if (!isset($_SESSION['username']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// --- CSRF Protection ---
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Helper Functions --- (unchanged from your original code)
function addUser(mysqli $userdb_conn) {
    $username = filter_input(INPUT_POST, 'new_username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);

    if (!$username || !$password) {
        return "Username and password are required.";
    }

    $stmt = $userdb_conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return "Username already exists.";
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $userdb_conn->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $isAdmin = 0;
    $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);
    return $stmt->execute() ? true : "Error adding user: " . $stmt->error;
}

function deleteUser(int $userId, mysqli $userdb_conn) {
    if ($userId == $_SESSION['user_id']) {
        return "Cannot delete yourself.";
    }

    $stmt = $userdb_conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute() ? true : "Error deleting user: " . $stmt->error;
}

function changePassword(int $userId, string $newPassword, mysqli $userdb_conn) {
    if (!$newPassword) {
        return "New password is required.";
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $userdb_conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    return $stmt->execute() ? true : "Error changing password: " . $stmt->error;
}

// --- Main Processing --- (unchanged)
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token";
    } else {
        if (isset($_POST['add_user'])) {
            $result = addUser($GLOBALS['userdb_conn']);
            $error = ($result === true) ? "User added successfully!" : $result;
        } elseif (isset($_POST['delete_user'])) {
            $userId = filter_input(INPUT_POST, 'delete_user', FILTER_VALIDATE_INT);
            $result = $userId ? deleteUser($userId, $GLOBALS['userdb_conn']) : "Invalid user ID";
            $error = ($result === true) ? "User deleted successfully!" : $result;
        } elseif (isset($_POST['change_password'])) {
            $userId = filter_input(INPUT_POST, 'change_password', FILTER_VALIDATE_INT);
            $newPassword = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
            $result = ($userId && $newPassword) ? changePassword($userId, $newPassword, $GLOBALS['userdb_conn']) : "Invalid user ID or password";
            $error = ($result === true) ? "Password changed successfully!" : $result;
        }
    }
}

// --- Fetch Users --- (unchanged)
$stmt = $GLOBALS['userdb_conn']->prepare("SELECT id, username FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "User Management";
$pageDescription = "Admin panel for managing users.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --primary-hover: #c82333;
        }

        .container-fluid {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background-color: #f8f9fa;
            color: #495057;
        }

        .table tbody tr {
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f1f3f5;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <main>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="display-5 fw-bold"><?php echo $pageTitle; ?></h1>
                <p class="lead text-muted"><?php echo $pageDescription; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-<?php echo strpos($error, 'successfully') !== false ? 'success' : 'danger'; ?> shadow-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Add User Section -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">Add New User</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="mb-3">
                                    <label for="new_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="new_username" name="new_username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <button type="submit" name="add_user" class="btn btn-primary w-100">Add User</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Existing Users Section -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">Existing Users</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger me-2" onclick="return confirm('Are you sure?');">Delete</button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal" data-userid="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">Change Password</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" id="changePasswordForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="change_password" id="modal_user_id">
                                <div class="mb-3">
                                    <label for="modal_new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="modal_new_password" name="new_password" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" form="changePasswordForm" class="btn btn-primary">Change Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const changePasswordModal = document.getElementById('changePasswordModal');
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-userid');
            const username = button.getAttribute('data-username');
            const modal = this;
            modal.querySelector('#modal_user_id').value = userId;
            modal.querySelector('.modal-title').textContent = `Change Password for ${username}`;
        });
    </script>
</body>
</html>