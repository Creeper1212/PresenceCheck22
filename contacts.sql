-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 19. Mrz 2025 um 11:34
-- Server-Version: 10.11.6-MariaDB-0+deb12u1
-- PHP-Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `contacts`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `call_log`
--

CREATE TABLE `call_log` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `number_called` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `duration` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `call_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone_number` varchar(50) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 100,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `contacts`
--

INSERT INTO `contacts` (`id`, `first_name`, `last_name`, `company`, `phone_number`, `active`, `priority`, `created_at`, `duration`) VALUES
(1, 'Nummer1', 'Anruf', 'ASB', '004942143812301', 1, 10, '2025-03-13 12:45:24', 15),
(2, 'Nummer2', 'Anruf', 'ASB', '004915257686001', 1, 20, '2025-03-13 12:45:24', 15),
(3, 'Nummer3', 'Anruf', 'ASB', '004915227423193', 1, 30, '2025-03-13 12:45:24', 15);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log`
--

CREATE TABLE `log` (
  `id` int(10) NOT NULL,
  `number` varchar(40) NOT NULL,
  `timestamp` timestamp NOT NULL,
  `confirmed` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `log`
--

INSERT INTO `log` (`id`, `number`, `timestamp`, `confirmed`) VALUES
(47, '004942143812301', '2025-03-19 11:09:33', 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `call_log`
--
ALTER TABLE `call_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indizes für die Tabelle `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `call_log`
--
ALTER TABLE `call_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `log`
--
ALTER TABLE `log`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `call_log`
--
ALTER TABLE `call_log`
  ADD CONSTRAINT `call_log_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
