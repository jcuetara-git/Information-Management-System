-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2026 at 04:34 AM
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
-- Database: `ccj_ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumni_profile`
--

CREATE TABLE `alumni_profile` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `year_graduated` int(4) NOT NULL,
  `date_of_licensure_exam` date DEFAULT NULL,
  `prc_board_rating` decimal(5,2) DEFAULT NULL,
  `current_job` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_audience` enum('all','students','faculty','alumni','specific_user') NOT NULL,
  `status` varchar(50) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `target_audience`, `status`, `target_user_id`, `created_at`) VALUES
(1, 'Start of Classes', 'August 3, 2026\r\nPlease be guided accordingly', 'specific_user', 'published', 100, '2026-07-29 02:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_profile`
--

CREATE TABLE `faculty_profile` (
  `id` int(11) NOT NULL,
  `faculty_no` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `cv` text DEFAULT NULL,
  `tor` text DEFAULT NULL,
  `diploma` text DEFAULT NULL,
  `prc_license` text DEFAULT NULL,
  `certificates_membership` text DEFAULT NULL,
  `seminars_regional` text DEFAULT NULL,
  `seminars_national` text DEFAULT NULL,
  `seminars_international` text DEFAULT NULL,
  `research_cert` text DEFAULT NULL,
  `research_presenter` text DEFAULT NULL,
  `community_extension` text DEFAULT NULL,
  `test_questionnaires` text DEFAULT NULL,
  `syllabi` text DEFAULT NULL,
  `tos` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indiana_jones_records`
--

CREATE TABLE `indiana_jones_records` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `year_level` varchar(50) NOT NULL,
  `date_recorded` date NOT NULL,
  `number_of_absences` int(11) NOT NULL DEFAULT 0,
  `undertaking_file_path` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `indiana_jones_records`
--

INSERT INTO `indiana_jones_records` (`id`, `student_no`, `firstname`, `lastname`, `middlename`, `year_level`, `date_recorded`, `number_of_absences`, `undertaking_file_path`, `status`, `created_at`) VALUES
(1, '100', 'Jomar', 'Cuetara', '', '4th Year', '2026-07-29', 3, '1785292402_100_Jones.pdf', 'Pending', '2026-07-29 02:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `retention_records`
--

CREATE TABLE `retention_records` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `year_level` varchar(50) NOT NULL,
  `failed_subjects_count` int(11) NOT NULL DEFAULT 0,
  `memo_issued_date` date DEFAULT NULL,
  `undertaking_file_path` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retention_records`
--

INSERT INTO `retention_records` (`id`, `student_no`, `firstname`, `lastname`, `middlename`, `year_level`, `failed_subjects_count`, `memo_issued_date`, `undertaking_file_path`, `status`, `created_at`) VALUES
(1, '100', 'Jomar', 'Cuetara', '', '4th Year', 7, '2026-07-29', '1785292392_100_Jones.pdf', 'Pending', '2026-07-29 02:33:12');

-- --------------------------------------------------------

--
-- Table structure for table `student_concerns`
--

CREATE TABLE `student_concerns` (
  `id` int(11) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `previous_gpa` varchar(10) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profile`
--

INSERT INTO `student_profile` (`id`, `student_no`, `first_name`, `middle_name`, `last_name`, `dob`, `age`, `gender`, `civil_status`, `religion`, `permanent_address`, `city_address`, `housing_type`, `contact_number`, `emergency_person`, `emergency_number`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `activities`, `previous_gpa`, `profile_pic`) VALUES
(19, '100', 'Jomar', 'Ferolino', 'Cuetara', '2000-04-10', 26, 'Male', 'Single', 'Roman Catholic', 'Oslob, Cebu', 'Cebu City', 'Rented', '09772882974', 'John Doe', '09777777777', 'Doe John', 'Pilot', 'Janna Doe', 'Astronaut', 'Legion of Mary', '1.1', 'profile_100_6a69642e5bc435.80195161.jpg');

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
(27, '100', 'Jomar', 'Cuetara', 'j.cuetara04@gmail.com', '4', '$2y$10$5gZEOdodW4/TcrcbHEsXK.yVhGePUrFLqqmoi9tIy/Xv0TxbWNuk6', 'student', '2026-07-29 02:19:46', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumni_profile`
--
ALTER TABLE `alumni_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty_profile`
--
ALTER TABLE `faculty_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_no` (`faculty_no`);

--
-- Indexes for table `indiana_jones_records`
--
ALTER TABLE `indiana_jones_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_no` (`student_no`);

--
-- Indexes for table `retention_records`
--
ALTER TABLE `retention_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_no` (`student_no`);

--
-- Indexes for table `student_concerns`
--
ALTER TABLE `student_concerns`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `alumni_profile`
--
ALTER TABLE `alumni_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty_profile`
--
ALTER TABLE `faculty_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indiana_jones_records`
--
ALTER TABLE `indiana_jones_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `retention_records`
--
ALTER TABLE `retention_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_concerns`
--
ALTER TABLE `student_concerns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faculty_profile`
--
ALTER TABLE `faculty_profile`
  ADD CONSTRAINT `faculty_profile_ibfk_1` FOREIGN KEY (`faculty_no`) REFERENCES `users` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `indiana_jones_records`
--
ALTER TABLE `indiana_jones_records`
  ADD CONSTRAINT `indiana_jones_records_ibfk_1` FOREIGN KEY (`student_no`) REFERENCES `users` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `retention_records`
--
ALTER TABLE `retention_records`
  ADD CONSTRAINT `retention_records_ibfk_1` FOREIGN KEY (`student_no`) REFERENCES `users` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_1` FOREIGN KEY (`student_no`) REFERENCES `users` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
