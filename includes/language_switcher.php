<?php
// includes/language_switcher.php

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Check for language parameter in URL
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'de'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Define language file path
$langFile = 'languages/' . $_SESSION['lang'] . '.php';

// Include language file, fallback to English if not found
if (file_exists($langFile)) {
    $translations = include $langFile;
} else {
    $translations = include 'languages/en.php';
}

// Function to handle language switching
function displayLanguageSwitcher() {
    global $translations;
    $currentLang = $_SESSION['lang'];
    $otherLang = ($currentLang === 'en') ? 'de' : 'en';
    $currentLangName = ($currentLang === 'en') ? 'English' : 'Deutsch';
    $otherLangName = ($otherLang === 'en') ? 'English' : 'Deutsch';

    echo "<li class='nav-item dropdown'>";
    echo "<a class='nav-link dropdown-toggle' href='#' id='languageDropdown' role='button' data-bs-toggle='dropdown' aria-expanded='false'>";
    echo $currentLangName;
    echo "</a>";
    echo "<ul class='dropdown-menu' aria-labelledby='languageDropdown'>";
    echo "<li><a class='dropdown-item' href='?lang=$otherLang'>$otherLangName</a></li>";
    echo "</ul>";
    echo "</li>";
}
?>
