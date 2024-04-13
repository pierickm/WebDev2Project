-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Apr 13, 2024 at 09:44 PM
-- Server version: 11.1.2-MariaDB-1:11.1.2+maria~ubu2204
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `developmentdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `Appointments`
--

CREATE TABLE `Appointments` (
  `appointmentId` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  `tutorId` int(11) NOT NULL,
  `appointmentDate` date NOT NULL,
  `appointmentTime` time NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Appointments`
--

INSERT INTO `Appointments` (`appointmentId`, `studentId`, `tutorId`, `appointmentDate`, `appointmentTime`, `comment`, `status`) VALUES
(18, 1, 1, '2024-04-16', '10:30:00', 'test', 'Cancelled'),
(19, 1, 1, '2024-04-16', '11:15:00', 'test', 'Confirmed'),
(20, 1, 1, '2024-04-16', '09:00:00', 'test', 'Cancelled'),
(21, 1, 1, '2024-04-16', '09:45:00', '', 'Confirmed'),
(22, 24, 1, '2024-04-16', '13:30:00', 'test', 'Confirmed'),
(23, 24, 1, '2024-04-18', '09:45:00', 'test', 'Cancelled'),
(24, 27, 11, '2024-04-15', '09:45:00', 'help me please', 'Confirmed'),
(26, 24, 13, '2024-04-29', '11:15:00', '', 'Confirmed'),
(27, 24, 12, '2024-04-16', '09:45:00', '', 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `Tutors`
--

CREATE TABLE `Tutors` (
  `tutorId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `hourlyRate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Tutors`
--

INSERT INTO `Tutors` (`tutorId`, `userId`, `specialization`, `hourlyRate`) VALUES
(1, 7, 'Math and History', 10),
(11, 22, 'Music theory, art', 15),
(12, 23, 'Biology, Physics', 14),
(13, 25, 'Chemistry, Math', 11),
(19, 28, 'German', 12);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `userId` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `userType` enum('Student','Tutor','Administrator') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profilePhoto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`userId`, `firstName`, `lastName`, `emailAddress`, `userType`, `password`, `profilePhoto`) VALUES
(1, 'Test', 'Admin', 'test@admin.com', 'Administrator', '$2y$10$jFo7uXahkGTV.Fvq0ssEGuOxu8WIwv7roX.YCQ5EfBUPUs4yFKTxS', '/uploads/profilephoto.jpg'),
(7, 'Sarah', 'Tutor', 'test@tutor.com', 'Tutor', '$2y$10$LYH0yZtlzNBsJLgJI38NXeRvs6gR9ijlFbVT5W.hI81y0JkdjFYje', '/uploads/profilephoto.jpg'),
(22, 'Rick', 'Rack', 'rick@rack.com', 'Tutor', '$2y$10$V3.AIQSN5obydejrr8YSQ.zZ5ZGC.cNmZ4I.pv4Ip0JjH9N2x.H82', '/uploads/hipster.jpg'),
(23, 'Greg', 'Holmes', 'greg@holmes.com', 'Tutor', '$2y$10$QYaSa9V4jls7zCkCPQDN8uXU0Vmruo.Eq7AFD85If92L6TeABJkL2', '/uploads/download.jpg'),
(24, 'Sammy', 'Student', 'test@student.com', 'Student', '$2y$10$LFSA8lqdYjrvzv8MVsp23.Ke31Y61M1v.u/Mec/QEoY.ZxXzduipG', '/uploads/brit.jpg'),
(25, 'Jack', 'Miller', 'jack@miller.com', 'Tutor', '$2y$10$8Sm5CTYl.2bEgu6qVl3BC..POzeVIA2bMC/b9XG0DNoPkkvD0HW8i', '/uploads/profPhoto.jpg'),
(26, 'Mike', 'Johnson', 'mike@johnson.com', 'Student', '$2y$10$gFhnXSZqPPa37YV664puuOb.SjUsDvvwpqBJH6AfL8MRq56wD9QQe', ''),
(27, 'Lisa', 'Schmidt', 'lisa@schmidt.com', 'Student', '$2y$10$O981hA.33HXIX1XPxc/Rv.Dh8iBwXZAxRNKZlISx/JJqgg/hH25gS', NULL),
(28, 'Gunther', 'Green', 'gunther@green.com', 'Tutor', '$2y$10$Noj0XqRzMAEVzBNKr8XdsOvBKtA4qBOZJzd/E8vX5p8k2btiuWzgW', '/uploads/default.jpg'),
(33, 'Jonas', 'Timmer', 'jonas@timmer.com', 'Student', '$2y$10$bNTtGi/pvJQmXgEYDRf6u.nahV65G.MLfmaFj1cXE2ky5vmHOysa6', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Appointments`
--
ALTER TABLE `Appointments`
  ADD PRIMARY KEY (`appointmentId`);

--
-- Indexes for table `Tutors`
--
ALTER TABLE `Tutors`
  ADD PRIMARY KEY (`tutorId`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`userId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Appointments`
--
ALTER TABLE `Appointments`
  MODIFY `appointmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `Tutors`
--
ALTER TABLE `Tutors`
  MODIFY `tutorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
