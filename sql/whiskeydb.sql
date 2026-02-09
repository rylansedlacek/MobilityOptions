-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 09, 2026 at 11:09 AM
-- Server version: 8.4.5-5
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbkxnmd1xwle9j`
--
CREATE DATABASE IF NOT EXISTS `mobilitydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `mobilitydb`;

-- --------------------------------------------------------

--
-- Table structure for table `dbapplications`
--

CREATE TABLE `dbapplications` (
  `id` int NOT NULL,
  `user_id` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `event_id` int NOT NULL,
  `status` enum('Approved','Denied','Pending') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `flagged` tinyint(1) NOT NULL DEFAULT '0',
  `note` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dbapplications`
--

INSERT INTO `dbapplications` (`id`, `user_id`, `event_id`, `status`, `flagged`, `note`) VALUES
(1, 'test_person', 118, 'Denied', 0, 'TEST'),
(2, 'test_acc', 121, 'Denied', 0, 'DENIED'),
(3, 'test_persona', 126, 'Approved', 0, ''),
(4, 'navyspouse', 178, 'Denied', 0, 'Example denial message'),
(5, 'vmsroot', 173, 'Approved', 0, ''),
(6, 'vmsroot', 173, 'Approved', 0, ''),
(7, 'edarnell', 180, 'Denied', 1, 'DENY');

-- --------------------------------------------------------

--
-- Table structure for table `dbapplication_comments`
--

CREATE TABLE `dbapplication_comments` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `user_id` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbarchived_volunteers`
--

CREATE TABLE `dbarchived_volunteers` (
  `id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` text,
  `first_name` text NOT NULL,
  `last_name` text,
  `street_address` text,
  `city` text,
  `state` text,
  `zip_code` text,
  `phone1` varchar(12) NOT NULL,
  `phone1type` text,
  `emergency_contact_phone` varchar(12) DEFAULT NULL,
  `emergency_contact_phone_type` text,
  `birthday` text,
  `email` text,
  `emergency_contact_first_name` text NOT NULL,
  `contact_num` varchar(12) NOT NULL,
  `emergency_contact_relation` text NOT NULL,
  `contact_method` text,
  `type` text,
  `status` text,
  `notes` text,
  `password` text,
  `skills` text NOT NULL,
  `interests` text NOT NULL,
  `archived_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `emergency_contact_last_name` text NOT NULL,
  `is_new_volunteer` tinyint(1) NOT NULL DEFAULT '1',
  `is_community_service_volunteer` tinyint(1) NOT NULL DEFAULT '0',
  `total_hours_volunteered` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dbarchived_volunteers`
--

INSERT INTO `dbarchived_volunteers` (`id`, `start_date`, `first_name`, `last_name`, `street_address`, `city`, `state`, `zip_code`, `phone1`, `phone1type`, `emergency_contact_phone`, `emergency_contact_phone_type`, `birthday`, `email`, `emergency_contact_first_name`, `contact_num`, `emergency_contact_relation`, `contact_method`, `type`, `status`, `notes`, `password`, `skills`, `interests`, `archived_date`, `emergency_contact_last_name`, `is_new_volunteer`, `is_community_service_volunteer`, `total_hours_volunteered`) VALUES
('stephen_davies', '2022-05-10', 'Stephen', 'Davies', '456 Maple Avenue', 'Fredericksburg', 'VA', '22401', '5405557890', 'mobile', '5405551111', 'home', '1988-11-02', 'stephendavies@email.com', 'Robert', '5405551111', 'Father', 'phone', 'volunteer', 'Inactive', 'Archived due to relocation', '$2y$10$ABC789xyz456LMN123DEF', 'Music, Painting', 'Event Coordination', '2025-03-18 16:56:44', 'Davies', 0, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `dbattendance`
--

CREATE TABLE `dbattendance` (
  `id` int NOT NULL,
  `eventId` int NOT NULL,
  `userId` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `loggedById` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `attendanceNote` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbdiscussions`
--

CREATE TABLE `dbdiscussions` (
  `author_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dbdiscussions`
--

INSERT INTO `dbdiscussions` (`author_id`, `title`, `body`, `time`) VALUES
('vmsroot', 'test', 'this is test', '2025-04-30-10:13');

-- --------------------------------------------------------

--
-- Table structure for table `dbdrafts`
--

CREATE TABLE `dbdrafts` (
  `draftID` int NOT NULL,
  `userID` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `recipientID` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` text COLLATE utf8mb4_general_ci NOT NULL,
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `scheduledSend` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbeventmedia`
--

CREATE TABLE `dbeventmedia` (
  `id` int NOT NULL,
  `eventID` int NOT NULL,
  `file_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_format` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `altername_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbeventpersons`
--

CREATE TABLE `dbeventpersons` (
  `id` int NOT NULL,
  `eventID` int NOT NULL,
  `userID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attended` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbeventpersons`
--

INSERT INTO `dbeventpersons` (`id`, `eventID`, `userID`, `notes`, `attended`) VALUES
(7, 0, 'EvanTester', 'v', 0),
(8, 0, 'EvanTester', 'p', 0),
(9, 0, 'tester4', 'v', 0),
(10, 0, 'acarmich@mail.umw.edu', 'v', 0),
(11, 0, 'armyuser', 'p', 0),
(12, 0, 'armyuser', 'p', 0),
(13, 0, 'edarnell', 'p', 0),
(14, 0, 'EvanTester', 'p', 0),
(15, 0, 'toaster', 'v', 0),
(16, 0, 'edarnell', 'p', 0),
(17, 0, 'toaster', 'p', 0),
(18, 0, 'toaster', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(19, 0, 'toaster', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(20, 0, 'toaster', 'v', 0),
(21, 0, 'toaster', 'v', 0),
(22, 12, 'vmsroot', 'p', 0),
(24, 165, 'edarnell', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0),
(26, 177, 'armyuser', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0),
(29, 129, 'test_persona', '', 0),
(30, 129, 'test_persona', '', 0),
(31, 128, 'vmsroot', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(32, 164, 'vmsroot', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(33, 165, 'vmsroot', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(34, 174, 'toaster', 'Skills: dancin | Dietary restrictions:  | Disabilities: n/a | Materials: good vibes', 0),
(35, 165, 'fakename', 'Skills: allergies | Dietary restrictions:  | Disabilities:  | Materials: ', 0),
(36, 184, 'edarnell', 'Skills: 11 | Dietary restrictions:  | Disabilities: 22 | Materials: 33', 0),
(37, 178, 'edarnell', 'Skills: Skills | Dietary restrictions:  | Disabilities: Alergies | Materials: Nope', 0),
(38, 186, 'amongustest', 'Skills: sus | Dietary restrictions:  | Disabilities:  | Materials: ', 0),
(39, 186, 'vmsroot', 'Skills: among us | Dietary restrictions:  | Disabilities:  | Materials: ', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dbevents`
--

CREATE TABLE `dbevents` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Retreat','Normal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `startDate` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endDate` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `location` text COLLATE utf8mb4_unicode_ci,
  `affiliation` int DEFAULT NULL,
  `branch` int DEFAULT NULL,
  `access` enum('Public','Private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Public',
  `completed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `series_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbevents`
--

INSERT INTO `dbevents` (`id`, `name`, `type`, `startDate`, `startTime`, `endTime`, `endDate`, `description`, `capacity`, `location`, `affiliation`, `branch`, `access`, `completed`, `series_id`) VALUES
(118, 'Halloween Event', 'Normal', '2025-10-31', '18:00', '20:30', '', 'It is halloween!!', 50, 'Fredericksburg, VA', NULL, NULL, 'Public', 'Y', NULL),
(119, 'party :)', 'Normal', '2026-01-14', '01:00', '01:01', '', 'dancin', 1, 'my house', NULL, NULL, 'Public', 'N', NULL),
(120, 'SDLFjkafs', 'Normal', '2025-09-10', '12:00', '14:00', '', 'j;aksdfj', 99999, 'asdf;j', NULL, NULL, 'Public', 'N', NULL),
(121, 'Whikey Valor Tasting', 'Normal', '2025-09-24', '15:00', '18:00', '', 'Come have a taste of fine barrel aged whiskey with fellow Vets.', 25, 'Old Silk Mill', NULL, NULL, 'Public', 'N', NULL),
(122, 'Event', 'Normal', '2025-12-01', '13:00', '14:00', '', 'Use Case Event', 77, 'UMW', NULL, NULL, 'Public', 'N', NULL),
(123, 'Ethan&#039;s Birthday Party', 'Normal', '2025-10-03', '07:30', '19:30', '', 'Ethan is going to eat my cake.', 2147483647, 'Eagle 225', NULL, NULL, 'Public', 'N', NULL),
(124, 'Example event', 'Normal', '2025-09-11', '12:00', '14:00', '', 'This is a test event', 42, 'UMW', NULL, NULL, 'Public', 'N', NULL),
(125, 'Pet Adoption', 'Normal', '2025-09-13', '11:00', '17:00', '', 'Pet Adoption', 50, 'Fredericksburg, Virginia', NULL, NULL, 'Public', 'N', NULL),
(126, 'Squirrel Watching', 'Normal', '2025-09-22', '06:00', '09:00', '', 'Watch the squirrels to make sure they do not eat the bird seed', 6, '275 Butler Rd, Fredericksburg, VA 22405', NULL, NULL, 'Public', 'Y', NULL),
(127, 'Whoosky Volar Tasting', 'Normal', '2025-09-15', '09:00', '13:00', '', 'Test Event', 42, 'House', NULL, NULL, 'Public', 'N', NULL),
(128, 'Meet n&#039; Greet', 'Normal', '2025-12-01', '00:00', '14:00', '2025-12-01', 'Come say hello!', 77, 'UMW', NULL, NULL, 'Public', 'N', NULL),
(129, 'Test event Woak', 'Normal', '2025-10-31', '15:00', '18:00', '', 'testing thsi woa', 99, 'required but not listed', NULL, NULL, 'Public', 'Y', NULL),
(130, 'Class Example', 'Normal', '2025-09-24', '12:00', '14:00', '', 'This is an example', 10, 'Farmer', NULL, NULL, 'Public', 'N', NULL),
(131, 'sdfsgfsgdgfs', 'Normal', '2025-11-30', '13:00', '14:00', '2025-11-30', 'erfeere', 4, 'errereter', NULL, NULL, 'Public', 'N', 526),
(163, 'Testing event creation', 'Normal', '2025-12-01', '12:00', '20:00', '2025-12-01', 'Testing event', 11, 'Fredericksburg', NULL, NULL, 'Public', '', 90),
(164, 'Testing', 'Normal', '2025-12-03', '08:00', '13:00', '2025-12-03', 'Testing - A', 10, 'Fred', NULL, NULL, 'Public', 'N', 0),
(165, 'yello :D', 'Normal', '2025-12-01', '17:00', '18:00', '2025-12-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(166, 'yello :D', 'Normal', '2026-01-01', '17:00', '18:00', '2026-01-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(167, 'yello :D', 'Normal', '2026-02-01', '17:00', '18:00', '2026-02-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(168, 'yello :D', 'Normal', '2026-03-01', '17:00', '18:00', '2026-03-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', '', 19),
(169, 'yello :D', 'Normal', '2026-04-01', '17:00', '18:00', '2026-04-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(170, 'yello :D', 'Normal', '2026-05-01', '17:00', '18:00', '2026-05-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(171, 'yello :D', 'Normal', '2026-06-01', '17:00', '18:00', '2026-06-01', 'fgdgdfgfd', 5, 'gfdgdfg', NULL, NULL, 'Public', 'N', 19),
(172, 'Retreat Testing', 'Retreat', '2025-12-05', '08:00', '20:00', '2025-12-05', 'Retreat', 11, 'Fredericksburg', NULL, NULL, 'Public', 'N', 0),
(173, 'Retreat', 'Retreat', '2025-12-04', '12:00', '20:00', '2025-12-04', 'RETREAT', 11, 'FRED', NULL, NULL, 'Public', 'N', 0),
(174, 'The Rat God', 'Normal', '2025-12-26', '12:00', '13:00', '2025-12-26', '1', 10, 'SPACE', NULL, NULL, 'Public', 'N', 595285),
(175, 'Test event before Dryrun', 'Normal', '2025-12-18', '12:00', '13:00', '2025-12-18', '1', 20, 'SPACE', NULL, NULL, 'Public', '', 34),
(176, 'DRY RUN Retreat', 'Retreat', '2025-12-18', '12:00', '13:00', '2025-12-18', '1', 25, 'SPACE', NULL, NULL, 'Public', '', 0),
(177, '12/18/2025', 'Normal', '2025-12-18', '12:00', '13:00', '2025-12-18', '1', 20, 'SPACE', NULL, NULL, 'Public', '', 0),
(178, 'CMON RETREAT', 'Retreat', '2025-12-27', '07:30', '20:00', '2025-12-27', 'TEST', 20, 'SPACE', NULL, NULL, 'Public', 'N', 75),
(179, 'The Rat God', 'Retreat', '2025-12-27', '10:00', '23:30', '2025-12-27', 'TEST', 20, 'SPACE', NULL, NULL, 'Public', 'N', 0),
(180, 'Evan Darnell', 'Retreat', '2025-12-23', '22:15', '22:30', '2025-12-23', 'a', 3, 's', NULL, NULL, 'Public', 'N', 0),
(181, '7-day Retreat', 'Retreat', '2025-12-10', '09:30', '14:30', '2025-12-10', 'A retreat-type event.', 12, 'The Bahamas', NULL, NULL, 'Public', 'N', 0),
(182, 'Example Event', 'Normal', '2025-12-11', '12:46', '12:49', '2025-12-11', 'Wow! My super cool normal event!', 29, 'A place', NULL, NULL, 'Public', 'N', 0),
(183, 'Random Event', 'Normal', '2025-12-12', '10:30', '12:30', '2025-12-12', 'TEST', 20, 'SPACE', NULL, NULL, 'Public', 'N', 29795),
(184, 'Evan test', 'Normal', '2026-01-22', '22:01', '23:42', '2026-01-22', 'ssss', 10, 'Home', NULL, NULL, 'Public', 'N', 8),
(185, 'EVENT TEXT', 'Normal', '2026-01-10', '09:45', '21:45', '2026-01-10', 'EVENT TEXT', 99, 'Home', NULL, NULL, 'Public', 'N', 0),
(186, 'Whiskey Tasting', 'Normal', '2026-02-06', '19:00', '21:30', '2026-02-06', 'Whiskey Tasting', 50, 'Fredericksburg, VA', NULL, NULL, 'Public', 'N', 90);

-- --------------------------------------------------------

--
-- Table structure for table `dbgroups`
--

CREATE TABLE `dbgroups` (
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbgroups`
--

INSERT INTO `dbgroups` (`group_name`, `color_level`) VALUES
('cool guys', 'green'),
('test', 'green');

-- --------------------------------------------------------

--
-- Table structure for table `dbmessages`
--

CREATE TABLE `dbmessages` (
  `id` int NOT NULL,
  `senderID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipientID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wasRead` tinyint(1) NOT NULL DEFAULT '0',
  `prioritylevel` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbmessages`
--

INSERT INTO `dbmessages` (`id`, `senderID`, `recipientID`, `title`, `body`, `time`, `wasRead`, `prioritylevel`) VALUES
(27, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(28, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(29, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(30, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(32, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(34, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:35', 0, 0),
(36, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(37, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(38, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(39, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(41, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(43, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:36', 0, 0),
(45, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(46, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(47, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(48, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(50, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(52, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-15:38', 0, 0),
(54, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(55, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(56, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(57, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(59, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(61, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-16:47', 0, 0),
(63, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(64, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(65, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(66, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(68, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(70, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:48', 0, 0),
(72, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(73, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(74, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(75, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(77, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(79, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:50', 0, 0),
(81, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(82, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(83, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(84, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(86, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(88, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:52', 0, 0),
(90, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(91, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(92, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(93, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(95, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(97, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:53', 0, 0),
(99, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(100, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(101, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(102, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(104, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(106, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(108, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(109, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(110, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(111, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(113, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(115, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-19:55', 0, 0),
(117, 'vmsroot', 'BobVolunteer', 'You have been added to a group. View under Groups page.', 'You have been added to a', '2025-04-29-19:58', 0, 0),
(119, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(120, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(121, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(122, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(124, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(126, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(128, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(129, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(130, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(131, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(133, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(135, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(137, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(138, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(139, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(140, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(142, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(144, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:01', 0, 0),
(152, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(153, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(154, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(155, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(157, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(159, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(161, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(162, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(163, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(164, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(166, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(168, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(170, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(171, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(172, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(173, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(175, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(177, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(179, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(180, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(181, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(182, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(184, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(186, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:03', 0, 0),
(188, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(189, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(190, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(191, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(193, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(195, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(197, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(198, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(199, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(200, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(202, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(204, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:06', 0, 0),
(206, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(207, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(208, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(209, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(211, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(213, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(215, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(216, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(217, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(218, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(220, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(222, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(224, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(225, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(226, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(227, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(229, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(231, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:08', 0, 0),
(233, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(234, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(235, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(236, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(238, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(240, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(242, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(243, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(244, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(245, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(247, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(249, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:09', 0, 0),
(251, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(252, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(253, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(254, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(256, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(258, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(260, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(261, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(262, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(263, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(265, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(267, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(269, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(270, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(271, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(272, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(274, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(276, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(278, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(279, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(280, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(281, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(283, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(285, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:13', 0, 0),
(288, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(289, 'vmsroot', 'jane_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(290, 'vmsroot', 'john_doe', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(291, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(292, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(293, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(295, 'vmsroot', 'volunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-20:54', 0, 0),
(300, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:21', 0, 0),
(301, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:21', 0, 0),
(302, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:21', 0, 0),
(303, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:21', 0, 0),
(309, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 1, 0),
(310, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 0, 0),
(311, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 0, 0),
(312, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 0, 0),
(313, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 0, 0),
(315, 'vmsroot', 'Volunteer1', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:30', 0, 0),
(318, 'vmsroot', 'ameyer3', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-21:31', 1, 0),
(319, 'vmsroot', 'maddiev', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-21:31', 0, 0),
(323, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 1, 0),
(324, 'vmsroot', 'ameyer32', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(325, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(326, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(327, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(328, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(330, 'vmsroot', 'Volunteer1', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:43', 0, 0),
(332, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 1, 0),
(333, 'vmsroot', 'ameyer32', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(334, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(335, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(336, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(337, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(339, 'vmsroot', 'Volunteer1', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:44', 0, 0),
(340, 'vmsroot', 'ameyer32', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-21:45', 0, 0),
(343, 'vmsroot', 'ameyer123', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(344, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 1, 0),
(345, 'vmsroot', 'ameyer32', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(346, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(347, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(348, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(349, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(351, 'vmsroot', 'Volunteer1', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:50', 0, 0),
(352, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:52', 1, 0),
(353, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:52', 0, 0),
(354, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:52', 0, 0),
(355, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:52', 0, 0),
(356, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-21:52', 0, 0),
(358, 'vmsroot', 'BobVolunteer', 'You have been added to a group. View under Groups page.', 'You have been added to DAWGS', '2025-04-29-21:52', 0, 0),
(360, 'vmsroot', 'lukeg', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-21:53', 0, 0),
(361, 'vmsroot', 'maddiev', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-21:53', 0, 0),
(364, 'vmsroot', 'ameyer3', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-22:00', 1, 0),
(370, 'vmsroot', 'michellevb', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-22:07', 0, 0),
(372, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 1, 0),
(373, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(374, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(375, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(376, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(377, 'vmsroot', 'michellevb', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(381, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 1, 0),
(382, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(383, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(384, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(385, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(386, 'vmsroot', 'michellevb', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-29-22:20', 0, 0),
(388, 'vmsroot', 'ameyer3', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-22:21', 1, 0),
(389, 'vmsroot', 'maddiev', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-22:22', 0, 0),
(392, 'vmsroot', 'test_acc', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-29-23:44', 0, 0),
(394, 'vmsroot', 'BobVolunteer', 'You have been added to a group. View under Groups page.', 'You have been added to t', '2025-04-30-08:16', 0, 0),
(395, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 1, 0),
(396, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(397, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(398, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(399, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(400, 'vmsroot', 'michellevb', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(401, 'vmsroot', 'test_acc', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-04-30-10:13', 0, 0),
(405, 'vmsroot', 'ameyer3', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-30-10:15', 1, 0),
(406, 'vmsroot', 'BobVolunteer', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-30-10:15', 0, 0),
(407, 'vmsroot', 'lukeg', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-30-10:15', 0, 0),
(409, 'vmsroot', 'Volunteer25', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-30-10:21', 1, 0),
(412, 'vmsroot', 'lukeg', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-04-30-13:13', 0, 0),
(414, 'vmsroot', 'ameyer3', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 1, 0),
(415, 'vmsroot', 'BobVolunteer', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(416, 'vmsroot', 'lukeg', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(417, 'vmsroot', 'maddiev', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(418, 'vmsroot', 'michael_smith', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(419, 'vmsroot', 'michellevb', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(420, 'vmsroot', 'test_acc', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(422, 'vmsroot', 'Volunteer25', 'A new discussion has been created. View under discussions page.', 'New Discussion', '2025-05-01-11:32', 0, 0),
(423, 'vmsroot', 'maddiev', 'You have been added to a group. View under Groups page.', 'You have been added to test', '2025-05-01-11:32', 0, 0),
(427, 'vmsroot', 'vmsroot', 'You have been added to a group. View under Groups page.', 'You have been added to cool guys', '2025-09-10-11:35', 1, 0),
(428, 'vmsroot', 'vmsroot', 'vmsroot has replied to test. View under discussions page.', 'A user has replied to a discussion.', '2025-09-10-11:40', 1, 0),
(429, 'vmsroot', 'vmsroot', 'test_person has been added as a volunteer', 'New volunteer account has been created', '2025-10-26-22:59', 1, 0),
(430, 'vmsroot', 'vmsroot', 'test_persona has been added as a volunteer', 'New volunteer account has been created', '2025-10-28-13:53', 1, 0),
(431, 'vmsroot', 'test_person', 'You are now signed up for Ethan&#039;s Birthday Party!', 'Thank you for signing up for Ethan&#039;s Birthday Party!', '2025-10-29-12:21', 0, 0),
(432, 'vmsroot', 'vmsroot', 'armyuser has been added as a volunteer', 'New volunteer account has been created', '2025-11-30-14:33', 1, 0),
(433, 'vmsroot', 'vmsroot', 'navyspouse has been added as a volunteer', 'New volunteer account has been created', '2025-11-30-14:36', 1, 0),
(434, 'vmsroot', 'vmsroot', 'EvanTester has been added as a volunteer', 'New volunteer account has been created', '2025-12-01-10:38', 1, 0),
(435, 'vmsroot', 'vmsroot', 'tester4 has been added as a volunteer', 'New volunteer account has been created', '2025-12-01-11:51', 1, 0),
(436, 'vmsroot', 'vmsroot', 'acarmich@mail.umw.edu has been added as a volunteer', 'New volunteer account has been created', '2025-12-01-12:05', 1, 0),
(437, 'vmsroot', 'vmsroot', 'Jlipinsk has been added as a volunteer', 'New volunteer account has been created', '2025-12-03-18:05', 1, 0),
(438, 'vmsroot', 'vmsroot', 'edarnell has been added as a volunteer', 'New volunteer account has been created', '2025-12-03-21:56', 1, 0),
(439, 'vmsroot', 'vmsroot', 'Welp has been added as a volunteer', 'New volunteer account has been created', '2025-12-04-22:14', 1, 0),
(440, 'vmsroot', 'vmsroot', 'toaster has been added as a volunteer', 'New volunteer account has been created', '2025-12-08-16:08', 1, 0),
(441, 'vmsroot', 'vmsroot', 'toaster2 has been added as a volunteer', 'New volunteer account has been created', '2025-12-09-21:40', 1, 0),
(442, 'vmsroot', 'edarnell', 'You are now signed up for The Rat God!', 'Thank you for signing up for The Rat God!', '2025-12-10-07:30', 0, 0),
(443, 'vmsroot', 'edarnell', 'Your request to sign up for DRY RUN Retreat has been sent to an admin.', 'Your request to sign up for DRY RUN Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-07:40', 0, 0),
(444, 'vmsroot', 'edarnell', 'You are now signed up for yello :D!', 'Thank you for signing up for yello :D!', '2025-12-10-07:40', 0, 0),
(445, 'vmsroot', 'edarnell', 'You are now signed up for party :)!', 'Thank you for signing up for party :)!', '2025-12-10-07:41', 0, 0),
(446, 'vmsroot', 'vmsroot', 'Your request to sign up for Evan Darnell has been sent to an admin.', 'Your request to sign up for Evan Darnell will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-09:04', 1, 0),
(447, 'vmsroot', 'armyuser', 'Your request to sign up for 7-day Retreat has been sent to an admin.', 'Your request to sign up for 7-day Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-09:24', 0, 0),
(448, 'vmsroot', 'armyuser', 'You are now signed up for 12/18/2025!', 'Thank you for signing up for 12/18/2025!', '2025-12-10-10:05', 0, 0),
(449, 'vmsroot', 'armyuser', 'Your request to sign up for DRY RUN Retreat has been sent to an admin.', 'Your request to sign up for DRY RUN Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-10:30', 0, 0),
(450, 'vmsroot', 'navyspouse', 'Your request to sign up for DRY RUN Retreat has been sent to an admin.', 'Your request to sign up for DRY RUN Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-10:40', 0, 0),
(451, 'vmsroot', 'navyspouse', 'Your request to sign up for CMON RETREAT has been sent to an admin.', 'Your request to sign up for CMON RETREAT will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-10:41', 0, 0),
(452, 'vmsroot', 'vmsroot', 'Your request to sign up for Retreat has been sent to an admin.', 'Your request to sign up for Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-10:56', 1, 0),
(453, 'vmsroot', 'vmsroot', 'fakename has been added as a volunteer', 'New volunteer account has been created', '2025-12-10-11:25', 1, 0),
(454, 'vmsroot', 'fakename', 'You are now signed up for The Rat God!', 'Thank you for signing up for The Rat God!', '2025-12-10-11:37', 0, 0),
(455, 'vmsroot', 'fakename', 'You are now signed up for Test event before Dryrun!', 'Thank you for signing up for Test event before Dryrun!', '2025-12-10-11:55', 0, 0),
(456, 'vmsroot', 'vmsroot', 'You are now signed up for Meet n&#039; Greet!', 'Thank you for signing up for Meet n&#039; Greet!', '2025-12-10-11:59', 1, 0),
(457, 'vmsroot', 'vmsroot', 'You are now signed up for Testing!', 'Thank you for signing up for Testing!', '2025-12-10-11:59', 1, 0),
(458, 'vmsroot', 'vmsroot', 'You are now signed up for yello :D!', 'Thank you for signing up for yello :D!', '2025-12-10-12:00', 1, 0),
(459, 'vmsroot', 'toaster', 'You are now signed up for The Rat God!', 'Thank you for signing up for The Rat God!', '2025-12-10-12:01', 1, 0),
(460, 'vmsroot', 'vmsroot', 'Your request to sign up for Retreat has been sent to an admin.', 'Your request to sign up for Retreat will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-12:16', 1, 0),
(461, 'vmsroot', 'vmsroot', 'firstName has been added as a volunteer', 'New volunteer account has been created', '2025-12-10-13:22', 1, 0),
(462, 'vmsroot', 'fakename', 'You are now signed up for yello :D!', 'Thank you for signing up for yello :D!', '2025-12-10-13:24', 0, 0),
(463, 'vmsroot', 'edarnell', 'You are now signed up for Evan test!', 'Thank you for signing up for Evan test!', '2025-12-10-19:41', 0, 0),
(464, 'vmsroot', 'edarnell', 'Your request to sign up for Evan Darnell has been sent to an admin.', 'Your request to sign up for Evan Darnell will be reviewed by an admin shortly. You will get another notification when you are approved or denied.', '2025-12-10-19:42', 0, 0),
(465, 'vmsroot', 'edarnell', 'You are now signed up for CMON RETREAT!', 'Thank you for signing up for CMON RETREAT!', '2025-12-10-19:42', 0, 0),
(466, 'vmsroot', 'vmsroot', 'japper has been added as a volunteer', 'New volunteer account has been created', '2026-02-02-09:12', 1, 0),
(467, 'vmsroot', 'vmsroot', 'gabriel has been added as a volunteer', 'New volunteer account has been created', '2026-02-02-14:45', 1, 0),
(468, 'vmsroot', 'vmsroot', 'olivia has been added as a volunteer', 'New volunteer account has been created', '2026-02-04-13:19', 1, 0),
(469, 'vmsroot', 'vmsroot', 'Britorsk has been added as a volunteer', 'New volunteer account has been created', '2026-02-05-13:32', 1, 0),
(470, 'vmsroot', 'vmsroot', 'You are now signed up for Whiskey Tasting!', 'Thank you for signing up for Whiskey Tasting!', '2026-02-06-16:11', 1, 0),
(471, 'vmsroot', 'vmsroot', 'You are now signed up for Whiskey Tasting!', 'Thank you for signing up for Whiskey Tasting!', '2026-02-06-16:12', 1, 0),
(472, 'vmsroot', 'vmsroot', 'johnDoe123 has been added as a volunteer', 'New volunteer account has been created', '2026-02-07-20:46', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `dbpendingsignups`
--

CREATE TABLE `dbpendingsignups` (
  `username` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `role` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbpendingsignups`
--

INSERT INTO `dbpendingsignups` (`username`, `eventname`, `notes`, `attended`, `role`) VALUES
('vmsroot', '108', 'Skills: non | Dietary restrictions: ojnjo | Disabilities: jonoj | Materials: knock', 0, ''),
('vmsroot', '101', 'Skills: rvwav | Dietary restrictions: varv | Disabilities: var | Materials: arv', 0, ''),
('vmsroot', '108', 'Skills: non | Dietary restrictions: ojnjo | Disabilities: jonoj | Materials: knock', 0, ''),
('vmsroot', '101', 'Skills: rvwav | Dietary restrictions: varv | Disabilities: var | Materials: arv', 0, ''),
('john_doe', '118', '', 0, ''),
('ameyer123', '126', '', 0, ''),
('test_persona', '129', '', 0, ''),
('test_persona', '129', '', 0, ''),
('edarnell', '176', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0, 'p'),
('edarnell', '180', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0, 'p'),
('armyuser', '181', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0, 'p'),
('armyuser', '176', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0, 'p'),
('navyspouse', '176', 'Skills:  | Dietary restrictions:  | Disabilities:  | Materials: ', 0, 'p');

-- --------------------------------------------------------

--
-- Table structure for table `dbpersonhours`
--

CREATE TABLE `dbpersonhours` (
  `personID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventID` int NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbpersonhours`
--

INSERT INTO `dbpersonhours` (`personID`, `eventID`, `start_time`, `end_time`) VALUES
('john_doe', 100, '2024-11-23 22:00:00', '2024-11-23 23:00:00'),
('john_doe', 100, '2024-11-23 22:00:00', '2024-11-23 23:00:00'),
('vmsroot', 186, '2026-02-06 16:13:21', '2026-02-06 16:13:23'),
('vmsroot', 186, '2026-02-06 16:13:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dbpersons`
--

CREATE TABLE `dbpersons` (
  `id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` text,
  `first_name` text NOT NULL,
  `last_name` text,
  `street_address` text,
  `city` text,
  `state` varchar(2) DEFAULT NULL,
  `zip_code` text,
  `phone1` varchar(12) DEFAULT NULL,
  `over21` enum('true','false') DEFAULT NULL,
  `phone1type` text,
  `emergency_contact_phone` varchar(12) DEFAULT NULL,
  `emergency_contact_phone_type` text,
  `birthday` text,
  `email` text,
  `email_prefs` enum('true','false') DEFAULT NULL,
  `emergency_contact_first_name` text,
  `contact_num` varchar(255) DEFAULT 'n/a',
  `emergency_contact_relation` text,
  `contact_method` text,
  `type` text,
  `status` text,
  `notes` text,
  `password` text,
  `affiliation` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `emergency_contact_last_name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dbpersons`
--

INSERT INTO `dbpersons` (`id`, `start_date`, `first_name`, `last_name`, `street_address`, `city`, `state`, `zip_code`, `phone1`, `over21`, `phone1type`, `emergency_contact_phone`, `emergency_contact_phone_type`, `birthday`, `email`, `email_prefs`, `emergency_contact_first_name`, `contact_num`, `emergency_contact_relation`, `contact_method`, `type`, `status`, `notes`, `password`, `affiliation`, `branch`, `archived`, `emergency_contact_last_name`) VALUES
('acarmich@mail.umw.edu', '2025-12-01', 'John', 'Doe', NULL, 'Fredericksburg', 'VA', NULL, '5555555555', 'true', '', '', '', '', 'acarmich@mail.umw.edu', 'false', '', '', '', '', 'volunteer', '', '', '$2y$10$1CDYmdifcx5rfR80Ui8WLuM2ldqc4DTJiFbK1JMSLycE/0lLKPJUS', 'Family', 'Air Force', NULL, ''),
('ameyer3', '2025-03-26', 'Aidan', 'Meyer', '1541 Surry Hill Court', 'Charlottesville', 'VA', '22901', '4344222910', NULL, 'home', '4344222910', 'home', '2003-08-17', 'aidanmeyer32@gmail.com', NULL, 'Aidan', 'n/a', 'Father', NULL, 'volunteer', 'Active', NULL, '$2y$10$0R5pX4uTxS0JZ4rc7dGprOK4c/d1NEs0rnnaEmnW4sz8JIQVyNdBC', NULL, NULL, 0, 'Meyer'),
('armyuser', '2025-11-30', 'Army', 'Active Duty', NULL, 'FXBG', 'VA', NULL, '3243242342', 'true', '', '', '', '', 'example@example.com', 'false', '', '', '', '', '', '', '', '$2y$10$kdxwMq.xaGsYvl8gY/8l3.xwu9ABEhWernkR6kmro9QtNvvEjqPFi', 'Active duty', 'Army', NULL, ''),
('BobVolunteer', '2025-04-29', 'Bob', 'SPCA', '123 Dog Ave', 'Dogville', 'VA', '54321', '9806761234', NULL, 'home', '1234567788', 'home', '2020-03-03', 'fred54321@gmail.com', NULL, 'Luke', 'n/a', 'Bff', NULL, 'volunteer', 'Active', NULL, '$2y$10$4wUwAW0yoizxi5UFy1/OZu.yfYY7rzUsuYcZCdvfplLj95r7OknvG', NULL, NULL, 0, 'Blair'),
('Britorsk', '2026-02-05', 'Brian', 'Prelle', NULL, 'KING GEORGE', 'VA', NULL, '5402076085', 'true', '', '', '', '', 'brian2@prelle.net', 'false', '', '', '', '', '', '', '', '$2y$10$q9wFQJ/guFjlUnR7IfJt/.MRf5bDfK8FxebznfRt644twzYepM/bC', 'Family', 'Air Force', NULL, ''),
('exampleuser', '2025-10-20', 'example', 'user', '', 'test', 'VA', '', '2344564645', NULL, '', '', '', '', 'example@test.com', NULL, '', 'n/a', '', NULL, 'v', 'Active', NULL, '$2y$10$J0NgBjoyg9F6YMyy/qQpv.f94OLM2r19sY80BZMhMdcl38SN5vdre', NULL, NULL, 0, ''),
('fakename', '2025-12-10', 'fake', 'name', NULL, 'realtown', 'VA', NULL, '5555555555', 'true', '', '', '', '', 'fakeemail@email.email.com', 'true', '', '', '', '', '', '', '', '$2y$10$4h8ImkaTyMprwU3SzWrWx./NBI7yClMoqCkEbYJuA1/9cb0tSlUI.', 'Civilian', 'Marine Corp', NULL, ''),
('firstName', '2025-12-10', 'firstName', 'lastName', NULL, 'homeTown', 'TX', NULL, '5555555555', 'true', '', '', '', '', 'realemail@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$og/aLBzrg195Qph9d2M/DuX2DIPhP.0sVT3vtu/WUpGCse8B.k71m', 'Civilian', 'Navy', NULL, ''),
('gabriel', '2026-02-02', 'Gabriel', 'Courtney', NULL, 'King George', 'VA', NULL, '5404295285', 'true', '', '', '', '', 'gabrielcourtney04@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$4uvfLFyFy9Ui1i8Q1r0MWuFRGYfgvVh4.iUtvXksfVJm4pZpxxtSq', 'Active duty', 'Space Force', NULL, ''),
('japper', '2026-02-02', 'Jennifer', 'Polack', NULL, 'Fredericksburg', 'VA', NULL, '5406541318', 'true', '', '', '', '', 'jenniferpolack@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$mJzI.UGPGUmYgo7HxTamkeKlsmajzLwXM6su4NdxuHYHZXIGnb0xm', 'Family', 'Marine Corp', NULL, ''),
('Jlipinsk', '2025-12-03', 'Jake', 'Lipinski', NULL, 'Williamsburg', 'VA', NULL, '7577903325', 'true', '', '', '', '', 'jlipinsk@mail.umw.edu', 'true', '', '', '', '', '', '', '', '$2y$10$qz33T0Sq760IITyYajCYOeWlHR/7sRJH.U609EUkF3R5zRiWWddkG', 'Civilian', 'Army', NULL, ''),
('johnDoe123', '2026-02-07', 'John', 'Doe', NULL, 'Fredericksburg', 'VA', NULL, '2345678910', 'true', '', '', '', '', 'test@email.com', 'false', '', '', '', '', '', '', '', '$2y$10$LTVIuLeSZ4ferdNOe0JdTedaFHqFuEOAz7HDCQuZ4PG9kZrRJc7xS', 'Active duty', 'Navy', NULL, ''),
('lukeg', '2025-04-29', 'Luke', 'Gibson', '22 N Ave', 'Fredericksburg', 'VA', '22401', '1234567890', NULL, 'cellphone', '1234567890', 'cellphone', '2025-04-28', 'volunteer@volunteer.com', NULL, 'NoName', 'n/a', 'Brother', NULL, 'volunteer', 'Active', NULL, '$2y$10$KsNVJYhvO5D287GpKYsIPuci9FnL.Eng9R6lBpaetu2Y0yVJ7Uuiq', NULL, NULL, 0, 'YesName'),
('maddiev', '2025-04-28', 'maddie', 'van buren', '123 Blue st', 'fred', 'VA', '12343', '1234567890', NULL, 'cellphone', '1234567819', 'cellphone', '2003-05-17', 'mvanbure@mail.umw.edu', NULL, 'mommy', 'n/a', 'mom', NULL, 'volunteer', 'Active', NULL, '$2y$10$0mv3.e6gjqoIg.HfT5qVXOsI.Ca5E93DAy8BnT124W1PvMDxpfoxy', NULL, NULL, 0, 'van buren'),
('michael_smith', '2025-03-16', 'Michael', 'Smith', '789 Pine Street', 'Charlottesville', 'VA', '22903', '4345559876', NULL, 'mobile', '4345553322', 'work', '1995-08-22', 'michaelsmith@email.com', NULL, 'Sarah', '4345553322', 'Sister', 'email', 'volunteer', 'Active', '', '$2y$10$XYZ789xyz456LMN123DEF', NULL, NULL, 0, 'Smith'),
('michellevb', '2025-04-29', 'Michelle', 'Van Buren', '1234 Red St', 'Freddy', 'VA', '22401', '1234567890', NULL, 'cellphone', '0987654321', 'cellphone', '1980-08-18', 'michelle.vb@gmail.com', NULL, 'Madison', 'n/a', 'daughter', NULL, 'volunteer', 'Active', NULL, '$2y$10$bkqOWUdIJoSa6kZoRo5KH.cerZkBQf74RYsponUUgefJxNc8ExppK', NULL, NULL, 0, 'Van Buren'),
('navyspouse', '2025-11-30', 'Navy', 'Spouse', NULL, 'FXBG', 'VA', NULL, '3543534543', 'true', '', '', '', '', 'example@example.com', 'false', '', '', '', '', '', '', '', '$2y$10$nqoIFq4ru0k1wLkg0E/rfupwez.x1Gg6ldEuKgC.jIQemgCEuDzkG', 'Family', 'Navy', NULL, ''),
('olivia', '2026-02-04', 'Olivia', 'Blue', NULL, 'Fredericksburg', 'VA', NULL, '1112223333', 'false', '', '', '', '', 'oliviablue@gmail.com', 'false', '', '', '', '', '', '', '', '$2y$10$ew4nuUYBtx6.CbNBezMTYuAQGaxMJgxIs4I3uIx05Sb7SqxKHJO2S', 'Family', 'Marine Corp', NULL, ''),
('test_acc', '2025-04-29', 'test', 'test', 'test', 'test', 'VA', '22405', '5555555555', NULL, 'cellphone', '5555555555', 'cellphone', '2003-03-03', 'test@gmail.com', NULL, 'test', 'n/a', 't', NULL, 'volunteer', 'Active', NULL, '$2y$10$kpVA41EXvoJyv896uDBEF.fHCPmSlkVSaXjHojBl7DqbRnEm//kxy', NULL, NULL, 0, 'test'),
('test_person', '2025-10-26', 'Testina', 'Tester', NULL, 'Testville', 'VA', NULL, '5555555555', 'true', 'mobile', NULL, NULL, '1980-08-18', 'testing@gmail.com', 'false', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$blAQaBgCChBv5qRtBFVVAe1m6gIfwPf/wJ8HxzLFTYiY3aWpvaW8e', 'civilian', 'Army', NULL, NULL),
('test_persona', '2025-10-28', 'Testana', 'Tester', NULL, 'Testinaville', 'VA', NULL, '5555555555', 'true', NULL, NULL, NULL, NULL, 'testerana@gmail.com', 'true', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$s90qlNAJE9EbgLhZbhG5vO4IGSM.PIbK3Ve9IvpfoicMwXbFEXQFi', 'active', 'air_force', NULL, NULL),
('tester4', '2025-12-01', 'tester', 'testing', NULL, 'Fredericksburg', 'VA', NULL, '5405405405', 'true', '', '', '', '', 'tester@gmail.com', 'true', '', '', '', '', '', '', '', '$2y$10$nILE/qxdpSvIgROc1uQEV.MyflEdG0IuNLQQ1c1u54MSEYKlg2LC2', 'Active duty', 'Space Force', NULL, ''),
('testing123', '2025-10-26', 'Test', 'User', NULL, 'City', 'VA', NULL, '', 'true', NULL, NULL, NULL, NULL, 'example@email.com', 'true', NULL, 'n/a', NULL, NULL, NULL, NULL, NULL, '$2y$10$XbXkJUMSAGo9m1/GZQ3faebtJWbPMZYm/AeTA3jpDCaxZBNnMclxC', 'civ', 'marine_corp', NULL, NULL),
('toaster', '2025-12-08', 'toast', 'er', NULL, 'Fredericksburg', 'VA', NULL, '5405405405', 'true', '', '', '', '', 'toaster@gmail.com', 'false', '', '', '', '', '', '', '', '$2y$10$VzLJcSjn/WFh0jeI9iFAw.McczukN4ovZuzg9vgtKFlXL3i/O9oOq', 'Civilian', 'Navy', NULL, ''),
('vmsroot', NULL, 'vmsroot', '', 'N/A', 'N/A', 'VA', 'N/A', '', NULL, 'N/A', 'N/A', 'N/A', NULL, '', NULL, 'vmsroot', 'N/A', 'N/A', 'email', 'superadmin', 'Active', 'System root user account', '$2y$10$.3p8xvmUqmxNztEzMJQRBesLDwdiRU3xnt/HOcJtsglwsbUk88VTO', NULL, NULL, 0, 'vmsroot'),
('Volunteer25', '2025-04-30', 'Volley', 'McTear', '123 Dog St', 'Dogville', 'VA', '56748', '9887765543', NULL, 'home', '6565651122', 'home', '2025-04-29', 'volly@gmail.com', NULL, 'Holly', 'n/a', 'Besty', NULL, 'volunteer', 'Active', NULL, '$2y$10$45gKdbjW78pNKX/5ROtb7eU9OykSCsP/QCyTAvqBtord4J7V3Ywga', NULL, NULL, 0, 'McTear'),
('Welp', '2025-12-04', 'Jake', 'Lipinski', NULL, 'Apple', 'VA', NULL, '7577903325', 'true', '', '', '', '', 'mcdonalds@happymeal.com', 'true', '', '', '', '', '', '', '', '$2y$10$LvWD62DJ6pwlVGnWenQkneWCFINzgbHgzyvaBdiLn72/WwM4wo7Iy', 'Active duty', 'Air Force', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `dbscheduledemails`
--

CREATE TABLE `dbscheduledemails` (
  `id` int NOT NULL,
  `userID` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `recipientID` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` text COLLATE utf8mb4_general_ci NOT NULL,
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `scheduledSend` date NOT NULL,
  `sent` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dbscheduledemails`
--

INSERT INTO `dbscheduledemails` (`id`, `userID`, `recipientID`, `subject`, `body`, `scheduledSend`, `sent`, `created`) VALUES
(17, 'vmsroot', 'All Whiskey Valor Members', 'Scheduling an Email!', 'This is a scheduled email', '2025-12-04', 0, '2025-12-02 21:46:39'),
(18, 'vmsroot', 'Jake Lipinski', 'Scheduled email to myself', 'Please work', '2025-12-04', 0, '2025-12-04 19:21:48'),
(19, 'vmsroot', 'Evan Darnell', 'TEST SCHEDULE', 'This email will be sent on the morning of the selected send date.\r\n\r\nNote for me if this sends though, you made it', '2025-12-26', 0, '2025-12-09 15:56:46'),
(20, 'vmsroot', 'jlipinsk@mail.umw.edu', 'Yippee', 'Work', '2025-12-10', 0, '2025-12-10 07:13:15'),
(21, 'vmsroot', 'Jlipinsk', 'adfasf', 'ahg', '2025-12-10', 1, '2025-12-10 08:46:45'),
(22, 'vmsroot', 'Jlipinsk', 'Work Please!', 'Test', '2025-12-10', 1, '2025-12-10 10:02:09'),
(23, 'vmsroot', 'acarmich@mail.umw.edu', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(24, 'vmsroot', 'ameyer3', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(25, 'vmsroot', 'armyuser', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(26, 'vmsroot', 'BobVolunteer', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(27, 'vmsroot', 'edarnell', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(28, 'vmsroot', 'exampleuser', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(29, 'vmsroot', 'Jlipinsk', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(30, 'vmsroot', 'lukeg', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(31, 'vmsroot', 'maddiev', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(32, 'vmsroot', 'michael_smith', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(33, 'vmsroot', 'michellevb', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(34, 'vmsroot', 'navyspouse', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(35, 'vmsroot', 'test_acc', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(36, 'vmsroot', 'test_person', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(37, 'vmsroot', 'test_persona', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(38, 'vmsroot', 'tester4', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(39, 'vmsroot', 'testing123', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(40, 'vmsroot', 'toaster', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(41, 'vmsroot', 'Volunteer25', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(42, 'vmsroot', 'Welp', 'Test for Software Engineering Scheduled Emails', 'This is the full test for Scheduled Emails. By tomorrow\'s presentation this should have sent out in an email!', '2025-12-10', 1, '2025-12-10 10:04:36'),
(43, 'vmsroot', 'acarmich@mail.umw.edu', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(44, 'vmsroot', 'ameyer3', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(45, 'vmsroot', 'armyuser', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(46, 'vmsroot', 'BobVolunteer', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(47, 'vmsroot', 'Britorsk', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(48, 'vmsroot', 'exampleuser', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(49, 'vmsroot', 'fakename', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(50, 'vmsroot', 'firstName', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(51, 'vmsroot', 'gabriel', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(52, 'vmsroot', 'japper', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(53, 'vmsroot', 'Jlipinsk', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(54, 'vmsroot', 'lukeg', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(55, 'vmsroot', 'maddiev', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(56, 'vmsroot', 'michael_smith', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(57, 'vmsroot', 'michellevb', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(58, 'vmsroot', 'navyspouse', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(59, 'vmsroot', 'olivia', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(60, 'vmsroot', 'test_acc', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(61, 'vmsroot', 'test_person', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(62, 'vmsroot', 'test_persona', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(63, 'vmsroot', 'tester4', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(64, 'vmsroot', 'testing123', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(65, 'vmsroot', 'toaster', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(66, 'vmsroot', 'Volunteer25', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05'),
(67, 'vmsroot', 'Welp', 'the spungle', 'THIS ISN\'T SPAM', '2026-02-07', 1, '2026-02-06 21:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `dbshifts`
--

CREATE TABLE `dbshifts` (
  `shift_id` int NOT NULL,
  `person_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time DEFAULT NULL,
  `totalHours` decimal(5,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dbshifts`
--

INSERT INTO `dbshifts` (`shift_id`, `person_id`, `date`, `startTime`, `endTime`, `totalHours`, `description`) VALUES
(14, 'maddiev', '2025-04-29', '20:22:29', '00:30:40', 0.13, 'a'),
(15, 'ameyer3', '2025-04-29', '20:24:27', '00:30:36', 0.10, 'a'),
(16, 'jane_doe', '2025-04-29', '20:26:29', '00:30:40', 0.07, 'a'),
(17, 'ameyer3', '2025-04-29', '20:31:30', '00:32:09', 0.00, 'a'),
(18, 'jane_doe', '2025-04-29', '20:31:31', '00:32:09', 0.00, 'a'),
(19, 'ameyer3', '2025-04-29', '20:32:14', '00:32:39', 0.00, 'hello'),
(20, 'ameyer3', '2025-04-29', '21:25:49', '01:26:17', 0.00, 'hello'),
(21, 'ameyer32', '2025-04-29', '21:35:01', '01:35:25', 0.00, 'hello'),
(22, 'ameyer123', '2025-04-29', '21:48:53', '01:49:13', 0.00, 'hello'),
(23, 'ameyer3', '2025-04-29', '21:56:37', '01:56:54', 0.00, 'hello'),
(24, 'ameyer3', '2025-04-29', '22:03:00', '02:03:18', 0.00, 'hello'),
(25, 'michellevb', '2025-04-29', '22:08:04', '02:08:36', 0.00, 'yay'),
(26, 'ameyer3', '2025-04-29', '22:24:27', '02:24:43', 0.00, 'hello'),
(27, 'test_acc', '2025-04-29', '23:44:58', '23:45:40', -23.99, 'test'),
(28, 'BobVolunteer', '2025-04-30', '08:14:55', '12:15:09', 0.00, 'good job'),
(29, 'BobVolunteer', '2025-04-30', '08:15:29', NULL, NULL, NULL),
(30, 'Volunteer25', '2025-04-30', '10:21:39', '14:22:09', 0.00, 'test'),
(31, 'ameyer3', '2025-05-01', '11:37:23', '15:37:49', 0.00, 'hello'),
(32, 'lukeg', '2025-07-09', '10:57:46', '10:57:57', 0.00, 'Laundry'),
(33, 'lukeg', '2025-07-09', '11:04:46', NULL, NULL, NULL),
(34, 'vmsroot', '2025-09-10', '11:36:05', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dbsuggestions`
--

CREATE TABLE `dbsuggestions` (
  `id` int NOT NULL,
  `user_id` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `dbsuggestions`
--

INSERT INTO `dbsuggestions` (`id`, `user_id`, `title`, `body`, `created_at`) VALUES
(1, 'edarnell', 'TEST SUGGESTION', 'This suggestion is a test', '2025-12-08 15:25:28'),
(2, 'edarnell', 'Suggestion here', 'SUGGESTING THIS', '2025-12-09 10:39:51'),
(3, 'edarnell', 'Suggestion REAL', 'This is a good idea', '2025-12-09 10:41:14'),
(4, 'edarnell', 'THIS is the REAL suggestion', 'Suggesting some really cool things', '2025-12-09 10:49:55'),
(5, 'vmsroot', 'This is a test for styling', 'This is a styling test.', '2025-12-09 14:48:27'),
(6, 'vmsroot', 'This is a test for styling', 'This is a styling test.', '2025-12-09 14:48:45'),
(7, 'vmsroot', 'This is a test for styling', 'This is a styling test.', '2025-12-09 14:48:50'),
(8, 'fakename', 'AAAAA', 'mAKE THIS WORK', '2025-12-10 11:43:42'),
(9, 'fakename', 'A suggestion asefs', 'sasf', '2025-12-10 13:25:18'),
(10, 'edarnell', 'Test Suggestion 12-10', 'TEST SUGGESTION BODY TEXT HERE', '2025-12-10 19:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_replies`
--

CREATE TABLE `discussion_replies` (
  `reply_id` int NOT NULL,
  `user_reply_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discussion_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_reply_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussion_replies`
--

INSERT INTO `discussion_replies` (`reply_id`, `user_reply_id`, `author_id`, `discussion_title`, `reply_body`, `parent_reply_id`, `created_at`) VALUES
(12, 'Volunteer25', 'Volunteer25', 'test', 'great idea!', '9', '2025-04-30-10:24'),
(13, 'vmsroot', 'vmsroot', 'test', 'test', NULL, '2025-05-01-11:31'),
(14, 'ameyer3', 'ameyer3', 'test', 'hello', '13', '2025-05-01-11:38'),
(15, 'ameyer3', 'vmsroot', 'test', 'hello', NULL, '2025-05-01-11:38'),
(16, 'vmsroot', 'vmsroot', 'test', 'testt', NULL, '2025-09-10-11:40');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_hours_snapshot`
--

CREATE TABLE `monthly_hours_snapshot` (
  `id` int NOT NULL,
  `person_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `month_year` date DEFAULT NULL,
  `hours` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_hours_snapshot`
--

INSERT INTO `monthly_hours_snapshot` (`id`, `person_id`, `month_year`, `hours`) VALUES
(36, 'ameyer3', '2025-03-15', 77),
(37, 'jane_doe', '2025-03-15', 0),
(38, 'john_doe', '2025-03-15', 0),
(39, 'michael_smith', '2025-03-15', 0),
(40, 'vmsroot', '2025-03-15', 0),
(57, 'ameyer3', '2025-04-01', 96),
(58, 'jane_doe', '2025-04-01', 3),
(59, 'john_doe', '2025-04-01', 6),
(60, 'michael_smith', '2025-04-01', 8),
(61, 'vmsroot', '2025-04-01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`user_id`, `group_name`) VALUES
('ameyer3', 'test'),
('BobVolunteer', 'test'),
('vmsroot', 'cool guys');

-- --------------------------------------------------------

--
-- Table structure for table `user_verified_ids`
--

CREATE TABLE `user_verified_ids` (
  `record_id` int NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `id_type` varchar(50) NOT NULL,
  `approved_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `user_verified_ids`
--

INSERT INTO `user_verified_ids` (`record_id`, `user_id`, `id_type`, `approved_at`) VALUES
(1, 'edarnell', 'DL', '2025-12-08 20:28:26'),
(2, 'edarnell', 'Military', '2025-12-09 15:51:37'),
(3, 'fakename', 'Military', '2025-12-10 16:43:24'),
(4, 'fakename', 'DL', '2025-12-10 18:28:47'),
(5, 'fakename', 'Passport', '2025-12-10 18:28:50'),
(6, 'edarnell', 'Other', '2025-12-11 00:44:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbapplications`
--
ALTER TABLE `dbapplications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbarchived_volunteers`
--
ALTER TABLE `dbarchived_volunteers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbattendance`
--
ALTER TABLE `dbattendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbdiscussions`
--
ALTER TABLE `dbdiscussions`
  ADD PRIMARY KEY (`author_id`(255),`title`);

--
-- Indexes for table `dbdrafts`
--
ALTER TABLE `dbdrafts`
  ADD PRIMARY KEY (`draftID`);

--
-- Indexes for table `dbeventpersons`
--
ALTER TABLE `dbeventpersons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FKeventID` (`eventID`),
  ADD KEY `FKpersonID` (`userID`);

--
-- Indexes for table `dbevents`
--
ALTER TABLE `dbevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbgroups`
--
ALTER TABLE `dbgroups`
  ADD PRIMARY KEY (`group_name`);

--
-- Indexes for table `dbmessages`
--
ALTER TABLE `dbmessages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbpersonhours`
--
ALTER TABLE `dbpersonhours`
  ADD KEY `FkpersonID2` (`personID`),
  ADD KEY `FKeventID3` (`eventID`);

--
-- Indexes for table `dbpersons`
--
ALTER TABLE `dbpersons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbscheduledemails`
--
ALTER TABLE `dbscheduledemails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbshifts`
--
ALTER TABLE `dbshifts`
  ADD PRIMARY KEY (`shift_id`);

--
-- Indexes for table `dbsuggestions`
--
ALTER TABLE `dbsuggestions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `fk_author` (`author_id`),
  ADD KEY `fk_user` (`user_reply_id`),
  ADD KEY `fk_parent` (`parent_reply_id`);

--
-- Indexes for table `monthly_hours_snapshot`
--
ALTER TABLE `monthly_hours_snapshot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`user_id`,`group_name`);

--
-- Indexes for table `user_verified_ids`
--
ALTER TABLE `user_verified_ids`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `unique_user_id_type` (`user_id`,`id_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dbapplications`
--
ALTER TABLE `dbapplications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dbattendance`
--
ALTER TABLE `dbattendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dbdrafts`
--
ALTER TABLE `dbdrafts`
  MODIFY `draftID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `dbeventpersons`
--
ALTER TABLE `dbeventpersons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `dbevents`
--
ALTER TABLE `dbevents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `dbmessages`
--
ALTER TABLE `dbmessages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=473;

--
-- AUTO_INCREMENT for table `dbscheduledemails`
--
ALTER TABLE `dbscheduledemails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `dbshifts`
--
ALTER TABLE `dbshifts`
  MODIFY `shift_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `dbsuggestions`
--
ALTER TABLE `dbsuggestions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `discussion_replies`
--
ALTER TABLE `discussion_replies`
  MODIFY `reply_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `monthly_hours_snapshot`
--
ALTER TABLE `monthly_hours_snapshot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `user_verified_ids`
--
ALTER TABLE `user_verified_ids`
  MODIFY `record_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
