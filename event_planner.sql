-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2025 at 09:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_planner`
--

-- --------------------------------------------------------

--
-- Table structure for table `eusers`
--

CREATE TABLE `eusers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eusers`
--

INSERT INTO `eusers` (`id`, `name`, `email`, `password_hash`, `role`, `department`, `created_at`, `updated_at`) VALUES
(1, 'Natnael', 'mitikunathan@gmail.com', '$2y$10$Q6OINpiuDZr3kW/Hbp.zU.RFvjs1h.NlKMdn3.R6e96GKvQfg18Cq', 'admin', NULL, '2025-08-12 21:42:32', '2025-08-12 22:33:42'),
(2, '', 'bekele@gmail.com', '$2y$10$tKkL3BQ6XBeH94K27OHIv.MXG9sfsr5xc208IqrwoJK/Rz2noKyuu', 'user', NULL, '2025-08-12 22:42:54', '2025-08-12 22:42:54'),
(6, 'Yeabsira Goitom', 'abbytesfamichael@gmail.com', '$2y$10$MmUkWavsYONNg89CQdstd.nbe4Q48u8nSM5UsiphHjIR2uBLloMbW', 'admin', NULL, '2025-08-17 12:58:13', '2025-08-17 12:58:13'),
(7, 'Natnael Mitiku', 'mitikunathans@gmail.com', '$2y$10$KvXZNUa7a6aDv0oSf/RaFuCy/y75RHe5BprLD1oEfRHbpK2UPhso.', 'user', NULL, '2025-08-17 14:05:19', '2025-08-17 14:05:19'),
(8, 'Nahom Mitiku', 'Nahom@gmail.com', '$2y$10$SZXVaWmG2cl.hjbvqiHIYeb2wCGtji8NkvGOqy/R9Wn0TKGVe9vo.', 'user', NULL, '2025-10-10 22:25:23', '2025-10-10 22:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `location`, `category`, `organizer_id`, `created_at`, `updated_at`) VALUES
(7, 'Abby\'s new event', 'Hello checking if it works', '2025-08-20 16:46:00', '2025-08-24 16:46:00', 'Addis Ababa', 'Meeting', 6, '2025-08-17 13:46:53', '2025-08-17 13:46:53'),
(15, 'check', 'check', '2025-10-12 00:44:00', '2025-10-15 00:45:00', 'Addis Ababa', 'Training', 1, '2025-10-10 21:45:13', '2025-10-10 21:45:13'),
(18, 'checker', 'checker', '2025-10-11 01:36:00', '2025-10-13 01:36:00', 'Addis Ababa', 'Meeting', 1, '2025-10-10 22:39:35', '2025-10-10 22:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `event_attachments`
--

CREATE TABLE `event_attachments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

CREATE TABLE `event_attendees` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rsvp_status` enum('Going','Not Going','Maybe') NOT NULL,
  `responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_attendees`
--

INSERT INTO `event_attendees` (`id`, `event_id`, `user_id`, `rsvp_status`, `responded_at`) VALUES
(1, 7, 2, 'Going', '2025-08-17 13:47:20'),
(2, 7, 7, 'Going', '2025-08-17 14:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `event_rsvps`
--

CREATE TABLE `event_rsvps` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rsvp_status` enum('yes','no','maybe') DEFAULT 'yes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_rsvps`
--

INSERT INTO `event_rsvps` (`id`, `event_id`, `user_id`, `rsvp_status`, `created_at`) VALUES
(5, 7, 2, '', '2025-08-17 13:47:20'),
(6, 7, 7, '', '2025-08-17 14:05:38'),
(7, 15, 2, '', '2025-10-10 22:35:17'),
(8, 18, 2, 'maybe', '2025-10-10 22:43:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eusers`
--
ALTER TABLE `eusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `event_attachments`
--
ALTER TABLE `event_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eusers`
--
ALTER TABLE `eusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `event_attachments`
--
ALTER TABLE `event_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_attendees`
--
ALTER TABLE `event_attendees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `eusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_attachments`
--
ALTER TABLE `event_attachments`
  ADD CONSTRAINT `event_attachments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD CONSTRAINT `event_attendees_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_attendees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `eusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD CONSTRAINT `event_rsvps_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_rsvps_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `eusers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
