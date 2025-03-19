<?php
return [
    // General
    'dashboard' => 'Anwesenheits-Dashboard',
    'welcome_message' => 'Willkommen beim Anwesenheitssystem. Bitte melden Sie sich für heute an.',
    'name' => 'Name',
    'submit' => 'Einchecken',
    'enter_your_name' => 'Geben Sie Ihren vollständigen Namen ein',
    'name_validation_title' => 'Der Name muss 2-50 Zeichen lang sein und darf nur Buchstaben und Leerzeichen enthalten',
    
    // Messages
    'already_checked_in' => '{name} hat sich heute bereits angemeldet!',
    'check_in_allowed' => 'Check-in ist derzeit erlaubt (Stunden: {0} - {1})',
    'check_in_not_allowed' => 'Check-in ist derzeit nicht erlaubt (Stunden: {0} - {1})',
    'recent_check_ins' => 'Letzte Anmeldungen',
    'view_all_check_ins' => 'Alle Anmeldungen anzeigen',
    'check_in_form' => 'Tägliche Anmeldung',
    
    // Errors
    'error_invalid_name' => 'Ungültiges Namensformat. Bitte verwenden Sie nur Buchstaben und Leerzeichen.',
    'error_db_connection' => 'Datenbankverbindungsfehler. Bitte versuchen Sie es später erneut.',
    'error_time_range' => 'Check-in ist zu diesem Zeitpunkt nicht erlaubt.',
    'system_error' => 'Ein Systemfehler ist aufgetreten. Bitte versuchen Sie es später erneut.',
    'invalid_csrf' => 'Ungültige Anforderung. Bitte laden Sie die Seite neu und versuchen Sie es erneut.',
    
    // Status
    'status_present' => 'Anwesend',
    'status_not_present' => 'Nicht anwesend',
    
    // Time Settings
    'time_settings_title' => 'Anmeldezeit-Limits konfigurieren',
    'time_settings_description' => 'Legen Sie Anmeldezeitbeschränkungen für jeden Wochentag fest.',
    'time_settings_save_success' => 'Zeiteinstellungen erfolgreich aktualisiert!',
    'time_settings_no_changes' => 'Keine Änderungen erkannt.',
    'time_settings_save_error' => 'Fehler beim Aktualisieren der Zeiteinstellungen.',
    'time_settings_current_time' => 'Aktuelle Zeit: ',
    'time_settings_system_active' => 'System ist AKTIV',
    'time_settings_system_inactive' => 'System ist INAKTIV',
    'time_settings_today_is' => 'Heute ist',
    'time_settings_mark_as_closed' => 'Als geschlossen markieren',
    'time_settings_closed_desc' => '(Keine Anmeldungen werden erlaubt)',
    'time_settings_start_time' => 'Startzeit',
    'time_settings_end_time' => 'Endzeit',
    'time_settings_reset' => 'Zurücksetzen',
    'time_settings_save_changes' => 'Änderungen speichern',
    'time_settings_day_closed' => '{day} wurde als geschlossen markiert.',
    'time_settings_invalid_time' => 'Ungültiges Zeitformat angegeben.',
    'time_settings_end_time_error' => 'Endzeit muss nach der Startzeit für {day} liegen.',
    'time_settings_time_settings' => 'Zeiteinstellungen',
    'is_open' => 'ist geöffnet',
    
    // Days of the week
    'day_sunday' => 'Sonntag',
    'day_monday' => 'Montag',
    'day_tuesday' => 'Dienstag',
    'day_wednesday' => 'Mittwoch',
    'day_thursday' => 'Donnerstag',
    'day_friday' => 'Freitag',
    'day_saturday' => 'Samstag',
    'today' => 'Heute',
    
    // Success page
    'success_checked_in' => 'Erfolgreich für heute eingecheckt!',
    'success_thank_you' => 'Vielen Dank, {name}! Sie werden in Kürze weitergeleitet.',
    'success_redirect' => 'Sie werden in Kürze zur Startseite weitergeleitet.',
    'go_back_now' => 'Jetzt zurückgehen',
    
    // Already checked in page
    'already_checked_in_title' => 'Sie haben sich heute bereits angemeldet!',
    'already_checked_in_message' => 'Sie werden in Kürze zur Startseite weitergeleitet.',
    
    // Is here today page
    'presence_title' => 'Anwesenheit',
    'presence_welcome' => 'Willkommen beim Anwesenheitssystem. Hier können Sie sehen, wer heute anwesend ist.',
    'todays_signins' => 'Heutige Anmeldungen',
    'name_header' => 'Name',
    'status_header' => 'Status',
    'time_header' => 'Zeit',
    'no_signins_today' => 'Heute keine Anmeldungen.',
    
    // Authentication
    'login_title' => 'Anmelden',
    'login_button' => 'Anmelden',
    'username_label' => 'Benutzername',
    'password_label' => 'Passwort',
    'logout' => 'Abmelden',
    'welcome_user' => 'Willkommen, {username}',
    'admin_center' => 'Admin-Bereich',
    'invalid_credentials' => 'Ungültige Anmeldeinformationen.',
    'user_not_found' => 'Benutzer nicht gefunden.',
    'session_expired' => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
    'session_invalid' => 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.',
    'time_settings_nav' => 'Zeiteinstellungen',
    'checked_in_users_nav' => 'Eingecheckte Benutzer',
    'check_in_nav' => 'Einchecken',
    'username_and_password_required' => 'Benutzername und Passwort sind erforderlich.'
];
?>
