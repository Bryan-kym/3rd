-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2024 at 01:18 PM
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
-- Database: `data_requests`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `admin_name` text DEFAULT NULL,
  `admin_email` text DEFAULT NULL,
  `admin_role_id` int(11) DEFAULT NULL,
  `created_by` text DEFAULT NULL,
  `created_on` text DEFAULT NULL,
  `last_edited_by` text DEFAULT NULL,
  `last_edited_on` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `requestor_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `seen` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `requestor_id`, `content`, `request_id`, `seen`, `created_at`) VALUES
(1, 1, 'Request for Data Migration', 1, 0, '2024-08-11 10:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`) VALUES
(1, 'create_user'),
(2, 'delete_user'),
(3, 'view_user'),
(4, 'block_user');

-- --------------------------------------------------------

--
-- Table structure for table `requestors`
--

CREATE TABLE `requestors` (
  `id` int(11) NOT NULL,
  `fullnames` varchar(50) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `phone_number` int(13) DEFAULT NULL,
  `kra_pin` varchar(13) DEFAULT NULL,
  `requester_type` varchar(40) DEFAULT NULL,
  `taxagent_type` text DEFAULT NULL,
  `other_desc` varchar(200) DEFAULT NULL,
  `client_type` text DEFAULT NULL,
  `requester_affiliation_name` varchar(60) DEFAULT NULL,
  `requester_affiliation_phone` int(15) DEFAULT NULL,
  `requester_affiliation_email` varchar(90) DEFAULT NULL,
  `requester_affiliation_pin` varchar(13) DEFAULT NULL,
  `department` text DEFAULT NULL,
  `division` text DEFAULT NULL,
  `employee_id_no` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requestors`
--

INSERT INTO `requestors` (`id`, `fullnames`, `email`, `phone_number`, `kra_pin`, `requester_type`, `taxagent_type`, `other_desc`, `client_type`, `requester_affiliation_name`, `requester_affiliation_phone`, `requester_affiliation_email`, `requester_affiliation_pin`, `department`, `division`, `employee_id_no`) VALUES
(1, 'George', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'asdf adsf', 'adsf', NULL, NULL, 'publiccompany', NULL, NULL, NULL, 'adsf', 234234, 'asdf', 'asdf', NULL, NULL, NULL),
(28, 'Kim sasd', 'asdas', 1234234, 'adfsdf', 'publiccompany', NULL, NULL, NULL, 'zdfxghvjbn', 87654, 'sgdhfjk', 'kj,hmgnfbd', NULL, NULL, NULL),
(29, 'afd asdf', 'asdf', 2536, 'zvc', 'others', NULL, 'desc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'asdf asdf', 'asdf', 2345, 'asdf', 'others', NULL, 'asdfsdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'sdfgsdfg sdfgsdfg', 'sdfgsdfg', 34564356, 'sdfgs', 'others', NULL, 'sdfgsdfgdfg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'sdfgsdfg sdfgsdfg', 'sdfgsdfg', 34564356, 'sdfgs', 'others', NULL, 'sdfgsdfgdfg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Kirui Kim', 'bky@gmail.com', 788996677, 'A09876543H', 'taxpayer', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'wer tyu bnm', 'asd@dfg.com', 123456789, '', 'student', NULL, '', NULL, 'jasd inst', 987654321, 'jasd@khkj.com', '', NULL, NULL, NULL),
(35, 'Ombija Renhard Miyoma', 'miyoma@ren.com', 788213345, 'A908675234W', 'researcher', NULL, '', NULL, 'JAber inst', 789765432, 'jb@g.com', '', NULL, NULL, NULL),
(36, 'asdf asdf', 'dsf', 45, 'adsf', 'taxagent', 'individual', '', NULL, 'wer ', NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'tax tax', 'tax', 123, 'tax', 'taxagent', 'individual', '', NULL, 'cli cli', NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'agent agent 2', 'ag', 12, 'ag', 'taxagent', 'individual', '', NULL, 'client client2', NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'asd asd', 'asd', 123, 'asd', 'taxagent', 'individual', '', NULL, 'zxc zxc', 567, 'zxc', 'zxc', NULL, NULL, NULL),
(40, 'zxc ', 'zxc', 6578, 'zxc', 'taxagent', 'individual', '', NULL, 'asd asd', 123, 'asd', 'asd', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requestors_documents`
--

CREATE TABLE `requestors_documents` (
  `id` int(11) NOT NULL,
  `document_name` text DEFAULT NULL,
  `document_type` text DEFAULT NULL,
  `document_file_path` text DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `last_edited_by` text DEFAULT NULL,
  `last_edited_on` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requestors_documents`
--

INSERT INTO `requestors_documents` (`id`, `document_name`, `document_type`, `document_file_path`, `request_id`, `requester_id`, `last_edited_by`, `last_edited_on`) VALUES
(1, NULL, NULL, 'uploads/RFC - Revision of P9 form.pdf', 6, 34, NULL, NULL),
(2, NULL, NULL, 'uploads/Signed RFC - 3rd Party Data Requisition (1).pdf', 6, 34, NULL, NULL),
(3, NULL, NULL, 'uploads/minimum_technical_specifications_for_a_laptop_.pdf', 7, 35, NULL, NULL),
(4, NULL, NULL, 'uploads/loan test1.xls', 7, 35, NULL, NULL),
(5, NULL, NULL, 'uploads/Internal Systems - Q1-Q2 Isupport Tasks.xlsx', 8, 36, NULL, NULL),
(6, NULL, NULL, 'uploads/Internal Systems - Q1-Q2 Isupport Tasks.xlsx', 9, 37, NULL, NULL),
(7, NULL, NULL, 'uploads/Member status change API.pdf', 10, 38, NULL, NULL),
(8, NULL, NULL, 'uploads/NDA FORM 1 (2).docx', 11, 39, NULL, NULL),
(9, NULL, NULL, 'uploads/Kesra-dashboard-300-soapui-project.xml', 12, 40, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `specific_fields` text DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `period` text DEFAULT NULL,
  `request_status` varchar(22) DEFAULT 'pending',
  `request_purpose` text DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `date_requested` text DEFAULT NULL,
  `reviewed_by` text DEFAULT NULL,
  `date_reviewed` text DEFAULT NULL,
  `review_status` varchar(22) NOT NULL DEFAULT 'pending',
  `allocated_to` text DEFAULT NULL,
  `allocated_on` text DEFAULT NULL,
  `allocated_by` text DEFAULT NULL,
  `allocation_status` varchar(22) NOT NULL DEFAULT 'pending',
  `allocatee_feedback` text DEFAULT NULL,
  `allocatee_status` varchar(22) NOT NULL DEFAULT 'pending',
  `allocatee_attachments` text DEFAULT NULL,
  `allocatee_feedback_on` text DEFAULT NULL,
  `tracking_status` varchar(22) DEFAULT 'requested',
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_edited_by` text DEFAULT NULL,
  `last_edited_on` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `description`, `specific_fields`, `period_from`, `period_to`, `period`, `request_status`, `request_purpose`, `requested_by`, `date_requested`, `reviewed_by`, `date_reviewed`, `review_status`, `allocated_to`, `allocated_on`, `allocated_by`, `allocation_status`, `allocatee_feedback`, `allocatee_status`, `allocatee_attachments`, `allocatee_feedback_on`, `tracking_status`, `time_stamp`, `last_edited_by`, `last_edited_on`) VALUES
(1, 'Hello', NULL, NULL, NULL, NULL, 'pending', NULL, 1, NULL, 'George', '2024-09-09 13:48:41', 'reviewed', NULL, '2024-09-09 14:05:47', NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-08-09 13:07:25', NULL, NULL),
(2, 'desc of data', 'Field 1, Field 2, Field 3, Field 4, Field 5', '2024-11-01', '2024-11-29', NULL, 'pending', 'reason fro data', 30, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 07:02:25', NULL, NULL),
(3, 'sdfghjkl,hmgfds', 'Field 1, Field 2', '2024-11-04', '2024-11-27', NULL, 'pending', 'fsghdfjkl.,mhngfbd', 31, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 07:03:07', NULL, NULL),
(4, 'sdfghjkl,hmgfds', 'Field 1, Field 2', '2024-11-04', '2024-11-27', NULL, 'pending', 'fsghdfjkl.,mhngfbd', 32, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 07:03:51', NULL, NULL),
(5, 'data desc', 'Field 1, Field 2, Field 3, Field 4, Field 5', '2024-11-03', '2024-12-03', NULL, 'pending', 'reason', 33, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 07:48:00', NULL, NULL),
(6, 'desc data', 'Field 2, Field 3, Field 1', '2024-11-19', '2024-12-06', NULL, 'pending', 'reasonvhgsfvdhjd', 34, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 07:54:34', NULL, NULL),
(7, 'Data desc', 'Field 1, Field 3, Field 4, ajgsdhjsadhjfbgcvhsb, Field 5', '2024-11-04', '2024-11-25', NULL, 'pending', 'reasoin ya jaba', 35, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 08:06:19', NULL, NULL),
(8, '', '', '0000-00-00', '0000-00-00', NULL, 'pending', '', 36, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 11:38:17', NULL, NULL),
(9, 'asd', 'Field 1, Field 2', '2024-11-27', '2024-12-04', NULL, 'pending', 'asdasd', 37, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 11:46:39', NULL, NULL),
(10, 'ag', 'Field 1, Field 2', '2024-11-25', '2024-12-03', NULL, 'pending', 'ag', 38, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 11:54:21', NULL, NULL),
(11, 'sdfg', 'Field 2, Field 3', '2024-11-25', '2024-12-07', NULL, 'pending', 'sdfg', 39, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 11:57:39', NULL, NULL),
(12, 'zdsdf', 'Field 2', '2024-12-03', '2024-12-06', NULL, 'pending', 'asdfasdfdsf', 40, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, 'requested', '2024-11-07 12:01:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(2, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(5, 1, 1),
(6, 2, 3),
(7, 1, 2),
(8, 1, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_role_id` (`admin_role_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `requestor_id` (`requestor_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requestors`
--
ALTER TABLE `requestors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requestors_documents`
--
ALTER TABLE `requestors_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requested_by` (`requested_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `requestors`
--
ALTER TABLE `requestors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `requestors_documents`
--
ALTER TABLE `requestors_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`admin_role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`requestor_id`) REFERENCES `requestors` (`id`);

--
-- Constraints for table `requestors_documents`
--
ALTER TABLE `requestors_documents`
  ADD CONSTRAINT `requestors_documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`),
  ADD CONSTRAINT `requestors_documents_ibfk_2` FOREIGN KEY (`requester_id`) REFERENCES `requestors` (`id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `requestors` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `permissions` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  ADD CONSTRAINT `roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
