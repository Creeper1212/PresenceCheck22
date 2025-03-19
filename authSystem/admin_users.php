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

// --- Database Connections ---
// User database connection is already in $GLOBALS['userdb_conn']
// Connect to contacts database
$contacts_db = new mysqli('localhost', 'hansasystems', 'Bremen2025', 'contacts');
if ($contacts_db->connect_error) {
    die("Connection to contacts database failed: " . $contacts_db->connect_error);
}

// --- Helper Functions for User Management ---
function addUser(mysqli $userdb_conn) {
    $username = filter_input(INPUT_POST, 'new_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$username || !$password || strlen($username) < 3 || strlen($password) < 8) {
        return "Username (min 3 chars) and password (min 8 chars) are required.";
    }

    $stmt = $userdb_conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return "Username already exists.";
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $userdb_conn->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $isAdmin = 0;
    $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error adding user: " . $userdb_conn->error;
}

function deleteUser(int $userId, mysqli $userdb_conn) {
    if ($userId == $_SESSION['user_id']) {
        return "Cannot delete yourself.";
    }

    $stmt = $userdb_conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error deleting user: " . $userdb_conn->error;
}

function changePassword(int $userId, string $newPassword, mysqli $userdb_conn) {
    if (!$newPassword || strlen($newPassword) < 8) {
        return "New password must be at least 8 characters.";
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $userdb_conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error changing password: " . $userdb_conn->error;
}

// --- Helper Functions for Contact Management ---
function addContact(mysqli $contacts_db) {
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $priority = filter_input(INPUT_POST, 'priority', FILTER_VALIDATE_INT);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);

    // Validate required fields
    if (!$firstName || !$lastName || !$phoneNumber) {
        return "First name, last name, and phone number are required.";
    }
    
    // Validate phone number
    if (!preg_match('/^[0-9\+\-\(\)\s]{10,20}$/', $phoneNumber)) {
        return "Invalid phone number format.";
    }
    
    // Validate duration
    if (!$duration || $duration < 1) {
        return "Duration must be at least 1 second.";
    }
    
    // Ensure priority is valid
    if (!$priority || $priority < 1) {
        $priority = 100; // Default priority
    }

    $stmt = $contacts_db->prepare("INSERT INTO contacts (first_name, last_name, company, phone_number, priority, duration) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiii", $firstName, $lastName, $company, $phoneNumber, $priority, $duration);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error adding contact: " . $contacts_db->error;
}

function updateContact(mysqli $contacts_db) {
    $id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
    $firstName = filter_input(INPUT_POST, 'edit_first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastName = filter_input(INPUT_POST, 'edit_last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $company = filter_input(INPUT_POST, 'edit_company', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'edit_phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $priority = filter_input(INPUT_POST, 'edit_priority', FILTER_VALIDATE_INT);
    $duration = filter_input(INPUT_POST, 'edit_duration', FILTER_VALIDATE_INT);
    $active = isset($_POST['edit_active']) ? 1 : 0;

    // Validate required fields
    if (!$id || !$firstName || !$lastName || !$phoneNumber) {
        return "ID, first name, last name, and phone number are required.";
    }
    
    // Validate phone number
    if (!preg_match('/^[0-9\+\-\(\)\s]{10,20}$/', $phoneNumber)) {
        return "Invalid phone number format.";
    }
    
    // Validate duration
    if (!$duration || $duration < 1) {
        return "Duration must be at least 1 second.";
    }
    
    // Ensure priority is valid
    if (!$priority || $priority < 1) {
        $priority = 100; // Default priority
    }

    $stmt = $contacts_db->prepare("UPDATE contacts SET first_name = ?, last_name = ?, company = ?, phone_number = ?, priority = ?, duration = ?, active = ? WHERE id = ?");
    $stmt->bind_param("ssssiiis", $firstName, $lastName, $company, $phoneNumber, $priority, $duration, $active, $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error updating contact: " . $contacts_db->error;
}

function deleteContact(mysqli $contacts_db) {
    $id = filter_input(INPUT_POST, 'delete_contact', FILTER_VALIDATE_INT);
    
    if (!$id) {
        return "Invalid contact ID.";
    }
    
    // First check if there are any references in call_log
    $stmt = $contacts_db->prepare("SELECT COUNT(*) as count FROM call_log WHERE contact_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        return "Cannot delete contact: It has associated call logs. Please delete those first or deactivate the contact instead.";
    }
    
    $stmt = $contacts_db->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result ? true : "Error deleting contact: " . $contacts_db->error;
}

function getCallLogCount(int $contactId, mysqli $contacts_db) {
    $stmt = $contacts_db->prepare("SELECT COUNT(*) as count FROM call_log WHERE contact_id = ?");
    $stmt->bind_param("i", $contactId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'];
}

// --- Main Processing ---
$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token. Please refresh the page and try again.";
    } else {
        // User Management
        if (isset($_POST['add_user'])) {
            $result = addUser($GLOBALS['userdb_conn']);
            if ($result === true) {
                $success = "User added successfully!";
            } else {
                $error = $result;
            }
        } elseif (isset($_POST['delete_user'])) {
            $userId = filter_input(INPUT_POST, 'delete_user', FILTER_VALIDATE_INT);
            $result = $userId ? deleteUser($userId, $GLOBALS['userdb_conn']) : "Invalid user ID";
            if ($result === true) {
                $success = "User deleted successfully!";
            } else {
                $error = $result;
            }
        } elseif (isset($_POST['change_password'])) {
            $userId = filter_input(INPUT_POST, 'change_password', FILTER_VALIDATE_INT);
            $newPassword = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $result = ($userId && $newPassword) ? changePassword($userId, $newPassword, $GLOBALS['userdb_conn']) : "Invalid user ID or password";
            if ($result === true) {
                $success = "Password changed successfully!";
            } else {
                $error = $result;
            }
        } 
        // Contact Management
        elseif (isset($_POST['add_contact'])) {
            $result = addContact($contacts_db);
            if ($result === true) {
                $success = "Contact added successfully!";
            } else {
                $error = $result;
            }
        } elseif (isset($_POST['update_contact'])) {
            $result = updateContact($contacts_db);
            if ($result === true) {
                $success = "Contact updated successfully!";
            } else {
                $error = $result;
            }
        } elseif (isset($_POST['delete_contact'])) {
            $result = deleteContact($contacts_db);
            if ($result === true) {
                $success = "Contact deleted successfully!";
            } else {
                $error = $result;
            }
        }
    }
}

// --- Fetch Users ---
$stmt = $GLOBALS['userdb_conn']->prepare("SELECT id, username FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Fetch Contacts (sort by ID instead of priority) ---
$stmt = $contacts_db->prepare("SELECT * FROM contacts ORDER BY id ASC");
$stmt->execute();
$contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Admin Panel";
$pageDescription = "Manage users and contacts";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            margin-bottom: 20px;
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

        .nav-tabs {
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: 2rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 10px 10px 0 0;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            color: white;
            background-color: var(--primary-color);
        }

        .nav-tabs .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        .inactive-contact {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        
        .call-count {
            font-size: 0.85rem;
            font-weight: normal;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
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
                <div class="alert alert-danger shadow-sm">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success shadow-sm">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-content" type="button" role="tab" aria-controls="users-content" aria-selected="true">
                        <i class="bi bi-people"></i> User Management
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-content" type="button" role="tab" aria-controls="contacts-content" aria-selected="false">
                        <i class="bi bi-telephone"></i> Contact Management
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="adminTabsContent">
                <!-- Users Management Tab -->
                <div class="tab-pane fade show active" id="users-content" role="tabpanel" aria-labelledby="users-tab">
                    <div class="row">
                        <!-- Add User Section -->
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="bi bi-person-plus me-2"></i>Add New User
                                </div>
                                <div class="card-body">
                                    <form method="post" id="addUserForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <div class="mb-3">
                                            <label for="new_username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="new_username" name="new_username" minlength="3" required>
                                            <div class="form-text">Minimum 3 characters</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                                            <div class="form-text">Minimum 8 characters</div>
                                            <div class="password-strength"></div>
                                        </div>
                                        <button type="submit" name="add_user" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle me-2"></i>Add User
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Users Section -->
                        <div class="col-lg-8 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="bi bi-people me-2"></i>Existing Users
                                </div>
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
                                                <?php if (empty($users)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center py-3">No users found</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($users as $user): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                            <td>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                    <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger me-2" onclick="return confirm('Are you sure you want to delete this user?');">
                                                                        <i class="bi bi-trash"></i> Delete
                                                                    </button>
                                                                </form>
                                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal" data-userid="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                                    <i class="bi bi-key"></i> Change Password
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Management Tab -->
                <div class="tab-pane fade" id="contacts-content" role="tabpanel" aria-labelledby="contacts-tab">
                    <div class="row">
                        <!-- Add Contact Section -->
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="bi bi-person-plus me-2"></i>Add New Contact
                                </div>
                                <div class="card-body">
                                    <form method="post" id="addContactForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="company" class="form-label">Company</label>
                                            <input type="text" class="form-control" id="company" name="company">
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone_number" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phone_number" name="phone_number" pattern="[0-9\+\-\(\)\s]{10,20}" required>
                                            <div class="form-text">Format: +49123456789 or other valid phone number</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Call Duration (seconds)</label>
                                            <input type="number" class="form-control" id="duration" name="duration" value="15" min="1" required>
                                        </div>
                                        <button type="submit" name="add_contact" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle me-2"></i>Add Contact
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Contacts Section -->
                        <div class="col-lg-8 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="bi bi-telephone me-2"></i>Existing Contacts
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Company</th>
                                                    <th>Phone</th>
                                                    <th>Priority</th>
                                                    <th>Duration</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($contacts)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-3">No contacts found</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($contacts as $contact): 
                                                        $callCount = getCallLogCount($contact['id'], $contacts_db);
                                                    ?>
                                                        <tr class="<?php echo $contact['active'] ? '' : 'inactive-contact'; ?>">
                                                            <td><?php echo htmlspecialchars($contact['id']); ?></td>
                                                            <td>
                                                                <?php echo htmlspecialchars($contact['first_name'].' '.$contact['last_name']); ?>
                                                                <?php if ($callCount > 0): ?>
                                                                    <span class="badge bg-info ms-2 call-count"><?php echo $callCount; ?> calls</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($contact['company']); ?></td>
                                                            <td><?php echo htmlspecialchars($contact['phone_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($contact['priority']); ?></td>
                                                            <td><?php echo htmlspecialchars($contact['duration']); ?>s</td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $contact['active'] ? 'success' : 'secondary'; ?>">
                                                                    <?php echo $contact['active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editContactModal" 
                                                                    data-id="<?php echo $contact['id']; ?>"
                                                                    data-firstname="<?php echo htmlspecialchars($contact['first_name']); ?>"
                                                                    data-lastname="<?php echo htmlspecialchars($contact['last_name']); ?>"
                                                                    data-company="<?php echo htmlspecialchars($contact['company']); ?>"
                                                                    data-phone="<?php echo htmlspecialchars($contact['phone_number']); ?>"
                                                                    data-priority="<?php echo htmlspecialchars($contact['priority']); ?>"
                                                                    data-duration="<?php echo htmlspecialchars($contact['duration']); ?>"
                                                                    data-active="<?php echo $contact['active']; ?>"
                                                                    data-callcount="<?php echo $callCount; ?>">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                    <input type="hidden" name="delete_contact" value="<?php echo $contact['id']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this contact? This cannot be undone.');" <?php echo $callCount > 0 ? 'disabled title="Cannot delete contacts with call logs"' : ''; ?>>
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
                                    <input type="password" class="form
                                    <input type="password" class="form-control" id="modal_new_password" name="new_password" minlength="8" required>
                                    <div class="form-text">Minimum 8 characters</div>
                                    <div class="password-strength"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Contact Modal -->
            <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" id="editContactForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="contact_id" id="edit_contact_id">
                                <div class="mb-3">
                                    <label for="edit_first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="edit_first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="edit_last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_company" class="form-label">Company</label>
                                    <input type="text" class="form-control" id="edit_company" name="edit_company">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_phone_number" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="edit_phone_number" name="edit_phone_number" pattern="[0-9\+\-\(\)\s]{10,20}" required>
                                    <div class="form-text">Format: +49123456789 or other valid phone number</div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_priority" class="form-label">Priority (lower number = higher priority)</label>
                                    <input type="number" class="form-control" id="edit_priority" name="edit_priority" min="1" max="999">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_duration" class="form-label">Call Duration (seconds)</label>
                                    <input type="number" class="form-control" id="edit_duration" name="edit_duration" min="1" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_active" name="edit_active">
                                    <label class="form-check-label" for="edit_active">Active</label>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_contact" class="btn btn-primary">Update Contact</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Change Password Modal
        const changePasswordModal = document.getElementById('changePasswordModal');
        if (changePasswordModal) {
            changePasswordModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-userid');
                const username = button.getAttribute('data-username');
                
                const modalTitle = this.querySelector('.modal-title');
                const modalUserId = this.querySelector('#modal_user_id');
                
                modalTitle.textContent = 'Change Password for ' + username;
                modalUserId.value = userId;
            });
        }
        
        // Edit Contact Modal
        const editContactModal = document.getElementById('editContactModal');
        if (editContactModal) {
            editContactModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const firstName = button.getAttribute('data-firstname');
                const lastName = button.getAttribute('data-lastname');
                const company = button.getAttribute('data-company');
                const phone = button.getAttribute('data-phone');
                const priority = button.getAttribute('data-priority');
                const duration = button.getAttribute('data-duration');
                const active = button.getAttribute('data-active') === '1';
                const callCount = button.getAttribute('data-callcount');
                
                this.querySelector('#edit_contact_id').value = id;
                this.querySelector('#edit_first_name').value = firstName;
                this.querySelector('#edit_last_name').value = lastName;
                this.querySelector('#edit_company').value = company;
                this.querySelector('#edit_phone_number').value = phone;
                this.querySelector('#edit_priority').value = priority;
                this.querySelector('#edit_duration').value = duration;
                this.querySelector('#edit_active').checked = active;
                
                // Add warning if contact has call logs
                const modalTitle = this.querySelector('.modal-title');
                if (callCount > 0) {
                    modalTitle.innerHTML = `Edit Contact <span class="badge bg-info ms-2">${callCount} calls</span>`;
                } else {
                    modalTitle.textContent = 'Edit Contact';
                }
            });
        }
        
        // Password strength indicator
        const passwordInputs = document.querySelectorAll('#new_password, #modal_new_password');
        passwordInputs.forEach(input => {
            input.addEventListener('input', function() {
                const strength = input.parentElement.querySelector('.password-strength');
                const val = input.value;
                
                // Simple password strength indicator
                if (val.length < 8) {
                    strength.style.width = '25%';
                    strength.style.backgroundColor = '#dc3545';
                } else if (val.length >= 8 && val.length < 12) {
                    strength.style.width = '50%';
                    strength.style.backgroundColor = '#ffc107';
                } else if (val.length >= 12 && (!/[A-Z]/.test(val) || !/[0-9]/.test(val))) {
                    strength.style.width = '75%';
                    strength.style.backgroundColor = '#0dcaf0';
                } else {
                    strength.style.width = '100%';
                    strength.style.backgroundColor = '#198754';
                }
            });
        });
    </script>
</body>
</html>