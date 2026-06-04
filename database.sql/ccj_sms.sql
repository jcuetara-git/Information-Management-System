-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 03:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ccj_sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `city_address` text DEFAULT NULL,
  `housing_type` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `emergency_person` varchar(100) DEFAULT NULL,
  `emergency_number` varchar(20) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `previous_gpa` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profile`
--

INSERT INTO `student_profile` (`id`, `student_no`, `first_name`, `middle_name`, `last_name`, `dob`, `age`, `gender`, `civil_status`, `religion`, `permanent_address`, `city_address`, `housing_type`, `contact_number`, `emergency_person`, `emergency_number`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `activities`, `previous_gpa`) VALUES
(17, '11', 'Jomar', '', 'Cuetara', '2009-10-04', 16, 'Male', 'Single', 'Roman Catholic', 'Oslob, Cebu', 'Urgello St., Cebu City', 'Owned', '09772882974', 'John Doe', '09111111212', 'Doe John', 'Pilot', 'Janna Doe', 'Astronaut', 'Table Tennis', '1.1'),
(18, '12', 'Jayson', 'Ferolino', 'Cuetara', '2021-06-16', 4, 'Male', 'Single', 'Roman Catholic', 'Oslob', 'Cebu  City', 'Owned', '09772882974', 'Jomar', '0934355343', 'Jomar', 'Pilot', 'Jomarie', 'Housewif', 'Pingpong', '1.1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_no`, `first_name`, `last_name`, `email`, `year_level`, `password`, `role`, `created_at`, `reset_token`, `token_expiry`) VALUES
(4, '022026', 'ucmain', 'ccj', 'ucmain_ccj@uc.edu.ph', 'admin', '$2y$10$QAq9MlRKpyfXIJTswRZSNOfre5KPTahQzwiastJo/eITyxfY3y9/C', 'admin', '2026-03-09 07:35:00', NULL, NULL),
(25, '11', 'Jomar', 'Cuetara', 'j.cuetara04@gmail.com', '4', '$2y$10$/.SLHVCQsb5mUb.83G6Ja./ubwWiQw7df/Nm9NusO7AdmKWmK78ty', 'student', '2026-04-29 05:15:23', 'df6efa08f1921431b7548b654a2e9c025535cdf0033def003801a93c23e7a5c1', '2026-05-06 04:45:27'),
(26, '12', 'Jayson', 'Cuetara', 'jaysoncuetara@gmail.com', '1', '$2y$10$Jz.KiOVd8T3NeQS1CZTDw.CQEqFIXzQYdoiMakVxu6z.ZaBK6jmda', 'student', '2026-05-06 01:47:34', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_1` FOREIGN KEY (`student_no`) REFERENCES `users` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
