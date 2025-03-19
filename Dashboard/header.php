<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path if not already defined (good practice for consistency)
defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 1) . '/'); // Goes up two levels

// Include language switcher function
require_once BASE_PATH . 'includes/language_switcher.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "Dashboard"; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : "Dashboard for presence and time management"; ?>">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@3.0.1/modern-normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/header_styles.css">
    <link rel="icon" type="image/png" href="<?php echo BASE_PATH; ?>favicon.png">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <!-- Logo and Brand -->
                <a class="navbar-brand" href="<?php echo BASE_PATH; ?>index.php">
                    <img src="<?php echo BASE_PATH; ?>assets/images/logo.png" alt="ASB Logo" height="40" class="d-inline-block align-text-top logo-spin">
                    <span class="brand-text">ASB</span>
                </a>

                <!-- Toggler for mobile view -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation Links -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === '/index.php' ? 'active' : ''; ?>" href="/index.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Einchecken' : 'Check In'; ?></a>
                        </li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === '/time_settings.php' ? 'active' : ''; ?>" href="/time_settings.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Zeiteinstellungen' : 'Time Settings'; ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === '/isheretoday.php' ? 'active' : ''; ?>" href="/isheretoday.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Eingecheckte Benutzer' : 'Checked In Users'; ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === '/admin_users.php' ? 'active' : ''; ?>" href="/authSystem/admin_users.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Admin-Bereich' : 'Admin Center'; ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- User Navigation Links -->
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['username'])): ?>
                            <li class="nav-item">
                                <span class="nav-link welcome-text">
                                    <?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Willkommen, ' : 'Welcome, '; ?><?php echo htmlspecialchars($_SESSION['username']); ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_PATH; ?>authSystem/logout.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Abmelden' : 'Logout'; ?></a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : ''; ?>" href="<?php echo BASE_PATH; ?>authSystem/login.php"><?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Anmelden' : 'Login'; ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'de') ? 'Deutsch' : 'English'; ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item" href="?lang=en">English</a></li>
                                <li><a class="dropdown-item" href="?lang=de">Deutsch</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <!-- Main content goes here -->
