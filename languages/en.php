<?php
return [
    // General
    'dashboard' => 'Attendance Dashboard',
    'welcome_message' => 'Welcome to the attendance system. Please check in for today.',
    'name' => 'Name',
    'submit' => 'Check In',
    'enter_your_name' => 'Enter your full name',
    'name_validation_title' => 'Name must be 2-50 characters and contain only letters and spaces',
    
    // Messages
    'already_checked_in' => '{name} has already checked in today!',
    'check_in_allowed' => 'Check-in is currently allowed (Hours: {0} - {1})',
    'check_in_not_allowed' => 'Check-in is currently not allowed (Hours: {0} - {1})',
    'recent_check_ins' => 'Recent Check-ins',
    'view_all_check_ins' => 'View All Check-ins',
    'check_in_form' => 'Daily Check-in',
    
    // Errors
    'error_invalid_name' => 'Invalid name format. Please use only letters and spaces.',
    'error_db_connection' => 'Database connection error. Please try again later.',
    'error_time_range' => 'Check-in is not allowed at this time.',
    'system_error' => 'A system error has occurred. Please try again later.',
    'invalid_csrf' => 'Invalid request. Please refresh the page and try again.',
    
    // Status
    'status_present' => 'Present',
    'status_not_present' => 'Not Present',
    
    // Time Settings
    'time_settings_title' => 'Configure Login Time Limits',
    'time_settings_description' => 'Set login time restrictions for each day of the week.',
    'time_settings_save_success' => 'Time settings updated successfully!',
    'time_settings_no_changes' => 'No changes detected.',
    'time_settings_save_error' => 'Error updating time settings.',
    'time_settings_current_time' => 'Current time: ',
    'time_settings_system_active' => 'System is ACTIVE',
    'time_settings_system_inactive' => 'System is INACTIVE',
    'time_settings_today_is' => 'Today is',
    'time_settings_mark_as_closed' => 'Mark as Closed',
    'time_settings_closed_desc' => '(No check-ins will be allowed)',
    'time_settings_start_time' => 'Start Time',
    'time_settings_end_time' => 'End Time',
    'time_settings_reset' => 'Reset',
    'time_settings_save_changes' => 'Save Changes',
    'time_settings_day_closed' => '{day} has been marked as closed.',
    'time_settings_invalid_time' => 'Invalid time format provided.',
    'time_settings_end_time_error' => 'End time must be later than start time for {day}.',
    'time_settings_time_settings' => 'Time Settings',
    'is_open' => 'is open',
    
    // Days of the week
    'day_sunday' => 'Sunday',
    'day_monday' => 'Monday',
    'day_tuesday' => 'Tuesday',
    'day_wednesday' => 'Wednesday',
    'day_thursday' => 'Thursday',
    'day_friday' => 'Friday',
    'day_saturday' => 'Saturday',
    'today' => 'Today',
    
    // Success page
    'success_checked_in' => 'Successfully checked in for today!',
    'success_thank_you' => 'Thank you, {name}! You will be redirected shortly.',
    'success_redirect' => 'You will be redirected to the home page shortly.',
    'go_back_now' => 'Go Back Now',
    
    // Already checked in page
    'already_checked_in_title' => 'You have already checked in today!',
    'already_checked_in_message' => 'You will be redirected to the home page shortly.',
    
    // Is here today page
    'presence_title' => 'Presence',
    'presence_welcome' => 'Welcome to the presence system. Here you can see who is present today.',
    'todays_signins' => 'Today\'s Sign-ins',
    'name_header' => 'Name',
    'status_header' => 'Status',
    'time_header' => 'Time',
    'no_signins_today' => 'No sign-ins today.',
    
    // Authentication
    'login_title' => 'Login',
    'login_button' => 'Login',
    'username_label' => 'Username',
    'password_label' => 'Password',
    'logout' => 'Logout',
    'welcome_user' => 'Welcome, {username}',
    'admin_center' => 'Admin Center',
    'invalid_credentials' => 'Invalid credentials.',
    'user_not_found' => 'User not found.',
    'session_expired' => 'Your session has expired. Please log in again.',
    'session_invalid' => 'Your session is invalid. Please log in again.',
    'time_settings_nav' => 'Time Settings',
    'checked_in_users_nav' => 'Checked In Users',
    'check_in_nav' => 'Check In',
    'username_and_password_required' => 'Username and password are required.'
];
?>
