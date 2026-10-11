-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2026 at 09:37 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_ticketing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `ticket_type_id` int(11) NOT NULL,
  `quantity_booked` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_status` enum('Confirmed','Upcoming','Completed','Cancelled') DEFAULT 'Upcoming',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `customer_id`, `ticket_type_id`, `quantity_booked`, `total_price`, `booking_status`, `booking_date`) VALUES
(1, 3, 1, 2, 300.00, 'Completed', '2026-10-10 19:36:59'),
(2, 3, 2, 1, 350.00, 'Cancelled', '2026-10-10 19:36:59'),
(3, 4, 3, 3, 240.00, 'Upcoming', '2026-10-10 19:36:59'),
(4, 4, 5, 1, 500.00, 'Upcoming', '2026-10-10 19:36:59'),
(5, 3, 7, 2, 100.00, 'Upcoming', '2026-10-10 19:36:59');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `venue_id` int(11) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `event_date`, `venue_id`, `organiser_id`, `created_at`) VALUES
(1, 'Tech Conference 2026', 'Annual technology, AI, and cloud developer summit.', '2026-09-15 09:00:00', 1, 2, '2026-10-10 19:36:59'),
(2, 'Asian Esports Championship', 'International competitive gaming and esports finals.', '2026-12-05 14:00:00', 2, 2, '2026-10-10 19:36:59'),
(3, 'Southeast Asia Fintech Summit', 'Networking and keynote presentations on digital banking.', '2026-11-20 10:00:00', 3, 5, '2026-10-10 19:36:59'),
(4, 'Global Startup Expo', 'Exhibition showcasing breakthrough early-stage tech ventures.', '2026-12-10 09:30:00', 4, 5, '2026-10-10 19:36:59'),
(5, 'Cybersecurity Innovation Forum', 'Deep dive into cloud security and modern threat mitigation.', '2026-12-18 11:00:00', 5, 2, '2026-10-10 19:36:59');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_type_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity_available` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_type_id`, `event_id`, `type_name`, `price`, `quantity_available`) VALUES
(1, 1, 'General Admission', 150.00, 195),
(2, 1, 'VIP Pass', 350.00, 48),
(3, 2, 'Standard Arena Seat', 80.00, 497),
(4, 2, 'Courtside VIP Seat', 220.00, 100),
(5, 3, 'Executive Delegate', 500.00, 99),
(6, 3, 'Virtual Streaming Pass', 120.00, 300),
(7, 4, 'Standard Day Pass', 50.00, 298),
(8, 4, 'Exhibitor & Investor Access', 250.00, 50),
(9, 5, 'Professional Ticket', 180.00, 150),
(10, 5, 'Student / Academic Pass', 60.00, 75);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Administrator','Organiser','Customer') DEFAULT 'Customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@eventticketing.com', 'admin123', 'Administrator', '2026-10-10 19:36:59'),
(2, 'Tech Events Ltd', 'events@techcorp.com', 'tech123', 'Organiser', '2026-10-10 19:36:59'),
(3, 'Siti Sarah', 'siti@mail.com', 'siti123', 'Customer', '2026-10-10 19:36:59'),
(4, 'Ahmad Ali', 'ali@mail.com', 'ali123', 'Customer', '2026-10-10 19:36:59'),
(5, 'Global Conferences', 'organizer@globalcon.com', 'gc123', 'Organiser', '2026-10-10 19:36:59');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(150) NOT NULL,
  `capacity` int(11) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`venue_id`, `venue_name`, `capacity`, `contact_phone`, `address`, `created_at`) VALUES
(1, 'Kuala Lumpur Convention Centre', 3000, '+60323332888', 'Kuala Lumpur City Centre, 50088 Kuala Lumpur', '2026-10-10 19:36:59'),
(2, 'Axiata Arena', 10000, '+60389920888', 'Bukit Jalil, 57000 Kuala Lumpur', '2026-10-10 19:36:59'),
(3, 'Plenary Hall KLCC', 1500, '+60323332800', 'Kuala Lumpur City Centre, 50088 Kuala Lumpur', '2026-10-10 19:36:59'),
(4, 'Merdeka Hall WTC', 2500, '+60326146262', '41 Jalan Tun Ismail, 50480 Kuala Lumpur', '2026-10-10 19:36:59'),
(5, 'MITEC Grand Ballroom', 5000, '+60362060100', '8 Jalan Dutamas 2, 50480 Kuala Lumpur', '2026-10-10 19:36:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `ticket_type_id` (`ticket_type_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `organiser_id` (`organiser_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_type_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`ticket_type_id`) REFERENCES `tickets` (`ticket_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`organiser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
