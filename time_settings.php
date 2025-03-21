<?php
include 'authSystem/auth_check.php'; // Sorgt dafür, dass man angemeldet sein muss
require_once 'Dashboard/config.php'; // Datenbank-Einstellungen (definiert $conn)
require 'TimeSettings/setgettime.php'; // Logik für die Zeiteinstellungen (definiert $days, $settings, usw.)
include 'Dashboard/header.php'; // Seitenkopf

// Fehlerbehandlung, falls Dateien oder Einstellungen fehlen
if (!file_exists('Dashboard/config.php')) {
    die("Fehler: Einstellungsdatei nicht gefunden!");
}

// Standardwerte festlegen, falls sie in den anderen Dateien nicht gesetzt sind
if (!isset($days)) {
    $days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
}
if (!isset($settings)) {
    $settings = [];
}

// Standard-Zeiteinstellungen festlegen, falls noch nicht definiert

$message = ""; // Nachricht für Rückmeldungen
$messageType = ""; // Art der Nachricht (Erfolg, Warnung, Info)
$pageTitle = "Anmeldezeiten einstellen";
$pageDescription = "Lege fest, wann man sich an den verschiedenen Tagen anmelden darf.";

// Formular-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hours'])) {
    // Eingabewerte prüfen
    $dayIndex = isset($_POST['day_index']) ? intval($_POST['day_index']) : 0;

    if ($dayIndex < 0 || $dayIndex >= count($days)) {
        $message = "Ungültiger Tag ausgewählt.";
        $messageType = "danger";
    } else {
        $dayName = $days[$dayIndex];
        $isClosed = isset($_POST['closed_' . $dayIndex]) ? 1 : 0;

        // Zeitformat prüfen, bevor es weitergeht
        $timePattern = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';
        $startTimeValid = $isClosed || (isset($_POST['start_time_' . $dayIndex]) &&
                         preg_match($timePattern, $_POST['start_time_' . $dayIndex]));
        $endTimeValid = $isClosed || (isset($_POST['end_time_' . $dayIndex]) &&
                       preg_match($timePattern, $_POST['end_time_' . $dayIndex]));

        if (!$startTimeValid || !$endTimeValid) {
            $message = "Ungültiges Zeitformat eingegeben.";
            $messageType = "danger";
        } else {
            $startTime = $isClosed ? "00:00:00" : $_POST['start_time_' . $dayIndex] . ":00";
            $endTime = $isClosed ? "00:00:00" : $_POST['end_time_' . $dayIndex] . ":00";

            // Prüfen, ob die Endzeit nach der Startzeit ist
            if (!$isClosed && $startTime >= $endTime) {
                $message = "Die Endzeit muss nach der Startzeit für $dayName sein.";
                $messageType = "warning";
            } else {
                // Prüfen, ob es überhaupt Änderungen gab
                $currentSetting = $settings[$dayName] ?? ['start_time' => DEFAULT_START_TIME, 'end_time' => DEFAULT_END_TIME];
                $currentIsClosed = ($currentSetting['start_time'] === '00:00:00' && $currentSetting['end_time'] === '00:00:00');

                if (($isClosed && $currentIsClosed) ||
                    (!$isClosed && !$currentIsClosed &&
                     $currentSetting['start_time'] === $startTime &&
                     $currentSetting['end_time'] === $endTime)) {
                    $message = "Keine Änderungen für $dayName gefunden.";
                    $messageType = "info";
                } else {
                    // Datenbank aktualisieren
                    $sql = "UPDATE timesettings SET start_time = ?, end_time = ? WHERE day_name = ?";
                    $stmt = $conn->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param("sss", $startTime, $endTime, $dayName);

                        if ($stmt->execute()) {
                            if ($isClosed) {
                                $message = "$dayName ist jetzt als geschlossen markiert.";
                            } else {
                                $message = "Zeiteinstellungen für $dayName erfolgreich geändert! Neue Zeiten: " .
                                           substr($startTime, 0, 5) . " bis " . substr($endTime, 0, 5);
                            }
                            $messageType = "success";

                            // Lokale Einstellungen aktualisieren, damit die Änderungen sofort sichtbar sind
                            $settings[$dayName] = ['start_time' => $startTime, 'end_time' => $endTime];
                        } else {
                            $message = "Fehler beim Ändern der Zeiteinstellungen für $dayName: " . $stmt->error;
                            $messageType = "danger";
                        }
                        $stmt->close();
                    } else {
                        $message = "Fehler beim Vorbereiten der Datenbank-Anfrage: " . $conn->error;
                        $messageType = "danger";
                    }
                }
            }
        }
    }
}

// Aktuelle Zeitinfos holen
$currentTime = date('H:i:s');
$currentDay = date('l');
$currentDayIndex = intval(date('w')); // In eine Zahl umwandeln, um sie besser vergleichen zu können
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@v3.0.1/modern-normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="favicon.png">
    <!-- Vielleicht dieses CSS in eine extra Datei packen, damit es schneller lädt -->
    <style>
        :root {
            --primary-color: #d40612;
            --primary-hover: #b00510;
            --primary-light: #ff4d57;
            --primary-ultra-light: #ffe6e7;
            --bg-light: #f8f9fa;
            --bg-gradient: linear-gradient(135deg, #f8f9fa, #e9ecef);
            --card-shadow: 0 8px 20px rgba(0,0,0,0.1);
            --transition-speed: 0.3s;
            --border-radius: 0.75rem;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            padding-bottom: 2rem;
        }

        /* Kopfbereich-Stile */
        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            position: relative;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100px;
            height: 3px;
            background-color: var(--primary-color);
        }

        /* Karten-Stile */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header .date-display {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .card-header .time-display {
            font-weight: bold;
            background-color: rgba(255,255,255,0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.5rem;
        }

        /* Navigations-Pills */
        .nav-pills {
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: thin;
        }

        .nav-pills::-webkit-scrollbar {
            height: 4px;
        }

        .nav-pills::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .nav-pills::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .nav-pills .nav-link {
            color: #495057;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem;
            transition: all var(--transition-speed);
            white-space: nowrap;
            display: flex;
            align-items: center;
            border: 1px solid transparent;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 3px 6px rgba(212, 6, 18, 0.2);
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: var(--primary-ultra-light);
            border-color: var(--primary-light);
        }

        .nav-pills .nav-link.today-tab {
            border: 1px solid var(--primary-color);
            position: relative;
        }

        .nav-pills .nav-link .badge {
            margin-left: 0.5rem;
            padding: 0.35em 0.65em;
            background-color: var(--primary-color);
        }

        /* Formularfelder */
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            transition: all var(--transition-speed);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(212, 6, 18, 0.25);
            border-color: var(--primary-color);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all var(--transition-speed);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(212, 6, 18, 0.3);
        }

        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            transition: all var(--transition-speed);
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #6c757d;
            transform: translateY(-2px);
        }

        /* Statusanzeigen */
        .alert {
            border-radius: 0.5rem;
            animation: slideDown 0.5s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all var(--transition-speed);
        }

        .status-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .status-open {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .status-closed {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Zeit-Eingabefeld */
        .time-input-container {
            position: relative;
        }

        .time-input-container .icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            pointer-events: none;
            z-index: 2;
        }

        .time-input-container .form-control {
            padding-right: 2.5rem;
        }

        /* Animationen */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Zeitstatus-Anzeige */
        .time-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-left: 1rem;
            font-weight: 500;
        }

        .time-status.active {
            background-color: rgba(40, 167, 69, 0.2);
            color: #155724;
        }

        .time-status.inactive {
            background-color: rgba(220, 53, 69, 0.2);
            color: #721c24;
        }

        .time-status .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .time-status.active .indicator {
            background-color: #28a745;
            animation: pulse 2s infinite;
        }

        .time-status.inactive .indicator {
            background-color: #dc3545;
        }

        /* Anpassungen für kleinere Bildschirme */
        @media (max-width: 768px) {
            .nav-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 1rem;
            }

            .nav-pills .nav-item {
                flex: 0 0 auto;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header .date-display {
                margin-top: 0.5rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .d-md-flex {
                flex-direction: column;
            }
        }

        /* Übergänge für Tab-Inhalte */
        .tab-pane {
            animation: fadeIn 0.5s ease;
        }

        /* Übergänge für Zeiteingabefelder */
        .time-fields {
            transition: opacity var(--transition-speed), filter var(--transition-speed);
        }

        .time-fields.disabled {
            opacity: 0.5;
            filter: grayscale(50%);
        }

        /* Anzeige der aktuellen Zeit */
        .current-time {
            font-weight: 600;
            color: var(--primary-color);
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <main class="time-settings">
        <div class="container py-4">
            <div class="page-header">
                <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($pageDescription); ?></p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> shadow-sm alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'info' ? 'info-circle' : 'exclamation-circle'); ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <span><i class="fas fa-clock me-2"></i>Zeiteinstellungen</span>
                        <?php
                        // Prüfen, ob die aktuelle Zeit im Zeitfenster von heute liegt
                        $todaySettings = $settings[$currentDay] ?? ['start_time' => DEFAULT_START_TIME, 'end_time' => DEFAULT_END_TIME];
                        $isInRange = ($currentTime >= $todaySettings['start_time'] && $currentTime <= $todaySettings['end_time']) &&
                                     !($todaySettings['start_time'] === '00:00:00' && $todaySettings['end_time'] === '00:00:00');
                        ?>
                        <div class="time-status <?php echo $isInRange ? 'active' : 'inactive'; ?>">
                            <span class="indicator"></span>
                            <span>System ist <?php echo $isInRange ? 'AKTIV' : 'INAKTIV'; ?></span>
                        </div>
                    </div>
                    <div class="date-display">
                        <span>Heute ist <?php echo date('l, F j, Y'); ?></span>
                        <span class="time-display" id="current-time"><?php echo date('H:i:s'); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tages-Navigation -->
                    <ul class="nav nav-pills mb-4" id="day-tabs" role="tablist">
                        <?php foreach ($days as $index => $day):
                            // Prüfen, ob der Tag als "geschlossen" markiert ist
                            $daySettings = $settings[$day] ?? ['start_time' => DEFAULT_START_TIME, 'end_time' => DEFAULT_END_TIME];
                            $isClosed = ($daySettings['start_time'] === '00:00:00' && $daySettings['end_time'] === '00:00:00');
                            $isToday = ($index == $currentDayIndex); // Prüfen, ob es heute ist
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $index === $currentDayIndex ? 'active' : ''; ?> <?php echo $isToday ? 'today-tab' : ''; ?>"
                                   id="tab-<?php echo $index; ?>"
                                   data-bs-toggle="pill"
                                   href="#pane-<?php echo $index; ?>"
                                   role="tab"
                                   aria-controls="pane-<?php echo $index; ?>"
                                   aria-selected="<?php echo $index === $currentDayIndex ? 'true' : 'false'; ?>">
                                    <?php echo htmlspecialchars($day); ?>
                                    <?php if ($isClosed): ?>
                                        <i class="fas fa-lock ms-1 text-danger" title="Geschlossen" aria-hidden="true"></i>
                                        <span class="visually-hidden">Geschlossen</span>
                                    <?php endif; ?>
                                    <?php if ($isToday): ?>
                                        <span class="badge bg-danger ms-1">Heute</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Zeiteinstellungs-Bereiche -->
                    <div class="tab-content">
                        <?php foreach ($days as $index => $day):
                            // Zeiten für den Tag holen oder Standardzeiten setzen
                            $daySettings = $settings[$day] ?? ['start_time' => DEFAULT_START_TIME, 'end_time' => DEFAULT_END_TIME];
                            $startTime = $daySettings['start_time'] ?? DEFAULT_START_TIME;
                            $endTime = $daySettings['end_time'] ?? DEFAULT_END_TIME;
                            $isClosed = ($startTime === '00:00:00' && $endTime === '00:00:00');
                            $isToday = ($index == $currentDayIndex);
                        ?>
                            <div class="tab-pane fade <?php echo $index === $currentDayIndex ? 'show active' : ''; ?>"
                                 id="pane-<?php echo $index; ?>"
                                 role="tabpanel"
                                 aria-labelledby="tab-<?php echo $index; ?>">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="settings-form">
                                    <input type="hidden" name="day_index" value="<?php echo $index; ?>">

                                    <!-- Aktuelle Statusanzeige -->
                                    <div class="status-badge <?php echo $isClosed ? 'status-closed' : 'status-open'; ?>">
                                        <i class="fas fa-<?php echo $isClosed ? 'lock' : 'clock'; ?> me-2" aria-hidden="true"></i>
                                        <?php echo $day; ?> ist momentan <?php echo $isClosed ? 'GESCHLOSSEN' : 'GEÖFFNET (' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . ')'; ?>
                                        <?php if ($isToday && !$isClosed): ?>
                                            <span class="ms-2 small">(Aktuelle Zeit: <span class="current-time" id="day-current-time"><?php echo date('H:i'); ?></span>)</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input closed-checkbox"
                                               id="closed_<?php echo $index; ?>"
                                               name="closed_<?php echo $index; ?>"
                                               <?php echo $isClosed ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="closed_<?php echo $index; ?>">
                                            <strong>Als geschlossen markieren</strong> <small class="text-muted">(An diesem Tag sind keine Check-ins möglich)</small>
                                        </label>
                                    </div>

                                    <div class="row time-fields <?php echo $isClosed ? 'disabled' : ''; ?>">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_time_<?php echo $index; ?>" class="form-label fw-medium">
                                                <i class="fas fa-play-circle me-1 text-danger" aria-hidden="true"></i> Startzeit
                                            </label>
                                            <div class="time-input-container">
                                                <input type="time" class="form-control time-input"
                                                    id="start_time_<?php echo $index; ?>"
                                                    name="start_time_<?php echo $index; ?>"
                                                    value="<?php echo htmlspecialchars(substr($startTime, 0, 5)); ?>"
                                                    <?php echo $isClosed ? 'disabled' : ''; ?>
                                                    required>
                                                <span class="icon" aria-hidden="true"><i class="fas fa-clock"></i></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="end_time_<?php echo $index; ?>" class="form-label fw-medium">
                                                <i class="fas fa-stop-circle me-1 text-danger" aria-hidden="true"></i> Endzeit
                                            </label>
                                            <div class="time-input-container">
                                                <input type="time" class="form-control time-input"
                                                    id="end_time_<?php echo $index; ?>"
                                                    name="end_time_<?php echo $index; ?>"
                                                    value="<?php echo htmlspecialchars(substr($endTime, 0, 5)); ?>"
                                                    <?php echo $isClosed ? 'disabled' : ''; ?>
                                                    required>
                                                <span class="icon" aria-hidden="true"><i class="fas fa-clock"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                        <button type="reset" class="btn btn-outline-secondary me-md-2">
                                            <i class="fas fa-undo me-1" aria-hidden="true"></i> Zurücksetzen
                                        </button>
                                        <button type="submit" name="save_hours" class="btn btn-primary">
                                            <i class="fas fa-save me-1" aria-hidden="true"></i> Änderungen speichern
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // Tage-Array am Anfang definieren, um Fehler zu vermeiden
        const days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

        document.addEventListener('DOMContentLoaded', () => {
            // Heutigen Tagesindex holen (0 = Sonntag, 6 = Samstag)
            const todayIndex = new Date().getDay();

            // Anzeige der aktuellen Zeit aktualisieren
            function updateCurrentTime() {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                // Zeit im Header aktualisieren
                const timeDisplay = document.getElementById('current-time');
                if (timeDisplay) {
                    timeDisplay.textContent = `${hours}:${minutes}:${seconds}`;
                }

                // Zeit im heutigen Tab aktualisieren
                const dayCurrentTime = document.getElementById('day-current-time');
                if (dayCurrentTime) {
                    dayCurrentTime.textContent = `${hours}:${minutes}`;
                }

                // Prüfen, ob die Zeit für heute im Zeitfenster liegt
                const todayTab = document.querySelector(`#tab-${todayIndex}`);
                if (todayTab) {
                    const todayPane = document.querySelector(`#pane-${todayIndex}`);
                    if (todayPane) {
                        const startTimeInput = todayPane.querySelector(`#start_time_${todayIndex}`);
                        const endTimeInput = todayPane.querySelector(`#end_time_${todayIndex}`);
                        const closedCheckbox = todayPane.querySelector(`#closed_${todayIndex}`);

                        if (startTimeInput && endTimeInput && closedCheckbox && !closedCheckbox.checked) {
                            const startTime = startTimeInput.value;
                            const endTime = endTimeInput.value;
                            const currentTime = `${hours}:${minutes}`;

                            const isInRange = currentTime >= startTime && currentTime <= endTime;

                            // Statusanzeige aktualisieren
                            const statusIndicator = document.querySelector('.time-status');
                            if (statusIndicator) {
                                if (isInRange) {
                                    statusIndicator.classList.remove('inactive');
                                    statusIndicator.classList.add('active');
                                    statusIndicator.querySelector('span:last-child').textContent = ' System ist AKTIV';
                                } else {
                                    statusIndicator.classList.remove('active');
                                    statusIndicator.classList.add('inactive');
                                    statusIndicator.querySelector('span:last-child').textContent = ' System ist INAKTIV';
                                }
                            }
                        }
                    }
                }
            }

            // Erste Ausführung und Intervall setzen
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);

            // Verhalten der "Geschlossen"-Checkbox
            document.querySelectorAll('.closed-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const index = this.id.split('_')[1];
                    const startInput = document.getElementById(`start_time_${index}`);
                    const endInput = document.getElementById(`end_time_${index}`);
                    const timeFieldsContainer = this.closest('form').querySelector('.time-fields');

                    if (this.checked) {
                        // Aktuelle Werte speichern, falls Checkbox wieder deaktiviert wird
                        startInput.dataset.prevValue = startInput.value;
                        endInput.dataset.prevValue = endInput.value;

                        startInput.value = '00:00';
                        endInput.value = '00:00';
                        startInput.disabled = true;
                        endInput.disabled = true;
                        timeFieldsContainer.classList.add('disabled');

                        // Statusanzeige aktualisieren
                        const statusBadge = this.closest('form').querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.classList.remove('status-open');
                            statusBadge.classList.add('status-closed');
                            statusBadge.innerHTML = `<i class="fas fa-lock me-2" aria-hidden="true"></i> ${days[index]} ist momentan GESCHLOSSEN`;
                        }
                    } else {
                        startInput.disabled = false;
                        endInput.disabled = false;
                        timeFieldsContainer.classList.remove('disabled');

                        // Vorherige Werte wiederherstellen, falls vorhanden
                        if (startInput.dataset.prevValue) {
                            startInput.value = startInput.dataset.prevValue;
                        } else if (startInput.value === '00:00') {
                            startInput.value = '08:00';
                        }

                        if (endInput.dataset.prevValue) {
                            endInput.value = endInput.dataset.prevValue;
                        } else if (endInput.value === '00:00') {
                            endInput.value = '18:00';
                        }

                        // Statusanzeige aktualisieren
                        const statusBadge = this.closest('form').querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.classList.remove('status-closed');
                            statusBadge.classList.add('status-open');
                            const dayText = days[index];
                            const isToday = (parseInt(index) === todayIndex);
                            let badgeHTML = `<i class="fas fa-clock me-2" aria-hidden="true"></i> ${dayText} ist momentan GEÖFFNET (${startInput.value} - ${endInput.value})`;
                            if (isToday) {
                                badgeHTML += ` <span class="ms-2 small">(Aktuelle Zeit: <span class="current-time" id="day-current-time">${new Date().getHours().toString().padStart(2, '0')}:${new Date().getMinutes().toString().padStart(2, '0')}</span>)</span>`;
                            }
                            statusBadge.innerHTML = badgeHTML;
                        }
                    }
                });
            });

            // Formularprüfung mit direkter Rückmeldung
            document.querySelectorAll('.time-input').forEach(input => {
                input.addEventListener('change', function() {
                    const form = this.closest('form');
                    const index = form.querySelector('[name^="day_index"]').value;
                    const startTimeInput = form.querySelector(`#start_time_${index}`);
                    const endTimeInput = form.querySelector(`#end_time_${index}`);

                    if (!startTimeInput || !endTimeInput) return;

                    const startTime = startTimeInput.value;
                    const endTime = endTimeInput.value;

                    // Zeitbereich prüfen
                    if (startTime && endTime && startTime >= endTime) {
                        alert('Fehler: Die Endzeit muss nach der Startzeit sein.');
                        // Auf vorherige gültige Werte zurücksetzen
                        if (this.id.includes('start_time')) {
                            this.value = this.dataset.lastValidValue || '08:00';
                        } else {
                            this.value = this.dataset.lastValidValue || '18:00';
                        }
                    } else {
                        // Gültige Werte speichern
                        this.dataset.lastValidValue = this.value;

                        // Statusanzeige mit neuen Zeiten aktualisieren
                        const statusBadge = form.querySelector('.status-badge');
                        if (statusBadge && statusBadge.classList.contains('status-open') && startTime && endTime) {
                            const dayText = days[index];
                            const isToday = (parseInt(index) === todayIndex);
                            let badgeHTML = `<i class="fas fa-clock me-2" aria-hidden="true"></i> ${dayText} ist momentan GEÖFFNET (${startTime} - ${endTime})`;
                            if (isToday) {
                                badgeHTML += ` <span class="ms-2 small">(Aktuelle Zeit: <span class="current-time" id="day-current-time">${new Date().getHours().toString().padStart(2, '0')}:${new Date().getMinutes().toString().padStart(2, '0')}</span>)</span>`;
                            }
                            statusBadge.innerHTML = badgeHTML;
                        }
                    }
                });
            });

            // Formular-Absende-Prüfung
            document.querySelectorAll('.settings-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const index = this.querySelector('[name^="day_index"]').value;
                    const startTimeInput = this.querySelector(`#start_time_${index}`);
                    const endTimeInput = this.querySelector(`#end_time_${index}`);
                    const closedCheckbox = this.querySelector(`#closed_${index}`);

                    if (!startTimeInput || !endTimeInput || !closedCheckbox) return true;

                    if (!closedCheckbox.checked) {
                        // Nur Zeitfelder prüfen, wenn der Tag nicht geschlossen ist
                        if (!startTimeInput.value || !endTimeInput.value) {
                            e.preventDefault();
                            alert('Bitte Start- und Endzeit festlegen.');
                            return false;
                        }

                        if (startTimeInput.value >= endTimeInput.value) {
                            e.preventDefault();
                            alert('Die Endzeit muss nach der Startzeit sein.');
                            return false;
                        }
                    }

                    return true;
                });
            });

            // Tooltips aktivieren (falls vorhanden)
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
</body>
</html>