-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 10:58 AM
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
-- Database: `ridexpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_complaints`
--

CREATE TABLE `chat_complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('user','rider') NOT NULL,
  `message` text NOT NULL,
  `sender_type` enum('user','rider','admin') NOT NULL,
  `status` enum('open','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_complaints`
--

INSERT INTO `chat_complaints` (`id`, `user_id`, `user_type`, `message`, `sender_type`, `status`, `created_at`) VALUES
(32, 5, 'user', 'have an issue', 'rider', 'open', '2025-05-05 05:53:19'),
(33, 5, 'user', 'what', 'admin', 'open', '2025-05-05 05:53:29');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `complaint_text` text NOT NULL,
  `type` enum('user','rider') NOT NULL,
  `status` enum('open','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_type` enum('user','rider') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_type`, `sender_id`, `message`, `created_at`) VALUES
(1, '', 0, 'qwdqw', '2025-05-02 06:31:19'),
(2, '', 0, 'ascasc', '2025-05-02 06:33:03'),
(3, '', 0, 'iii', '2025-05-02 06:35:28'),
(4, '', 0, 'yy', '2025-05-02 06:35:33'),
(5, '', 0, 'qwdq', '2025-05-02 06:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `vehicle_type` enum('Motorcycle','Taxi','Van','Jeep') NOT NULL,
  `start_time` datetime NOT NULL,
  `duration_type` enum('hour','day') NOT NULL,
  `duration_value` int(11) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','declined') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `rider_id`, `vehicle_type`, `start_time`, `duration_type`, `duration_value`, `total_cost`, `created_at`, `status`) VALUES
(1, 1, 1, 'Motorcycle', '2025-04-30 18:20:00', 'hour', 10, 0.00, '2025-05-03 01:17:42', 'pending'),
(2, 1, 1, 'Motorcycle', '2025-04-30 18:20:00', 'hour', 10, 0.00, '2025-05-03 01:44:19', 'pending'),
(3, 1, 1, 'Motorcycle', '2025-04-30 18:20:00', 'hour', 10, 600.00, '2025-05-03 01:44:28', 'pending'),
(4, 1, 1, 'Motorcycle', '2025-05-09 20:58:00', 'hour', 5, 300.00, '2025-05-03 03:59:05', 'pending'),
(5, 1, 2, 'Van', '2025-05-15 02:55:00', 'hour', 8, 2400.00, '2025-05-03 09:55:26', 'pending'),
(6, 7, 5, 'Motorcycle', '2025-05-05 22:54:00', 'hour', 5, 500.00, '2025-05-05 05:55:39', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `ride_request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `ride_request_id`, `user_id`, `rider_id`, `rating`, `comment`, `created_at`) VALUES
(3, 14, 1, 1, 4, 'nice', '2025-05-02 14:18:41'),
(4, 11, 1, 1, 5, 'good', '2025-05-02 15:08:09'),
(5, 18, 1, 1, 5, 'gwapo ang driver', '2025-05-03 03:56:11'),
(6, 23, 1, 2, 5, '5 star kay pogi', '2025-05-03 09:53:07'),
(7, 25, 7, 5, 4, 'nice', '2025-05-05 05:52:37');

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `vehicle_type` enum('Motorcycle','Taxi','Van','Jeep') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rate_type` enum('hour','day') NOT NULL DEFAULT 'hour',
  `rate_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profile_picture` varchar(255) DEFAULT 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png',
  `is_checked` tinyint(1) DEFAULT 0,
  `last_checked_at` datetime DEFAULT NULL,
  `next_check_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`id`, `last_name`, `first_name`, `middle_name`, `age`, `gender`, `contact_number`, `address`, `vehicle_type`, `email`, `password`, `created_at`, `rate_type`, `rate_amount`, `profile_picture`, `is_checked`, `last_checked_at`, `next_check_at`) VALUES
(1, 'Genodepanon', 'Alsher', 'Aikems', 22, 'Male', '09609097035', 'Ibapu Mactan LLC', 'Motorcycle', '3@gmail.com', '$2y$10$HLs9etZ2g21tGbD9WegLB./v4n91ndWFWvc/2KHPCVsIZGKm1d0jq', '2025-02-10 11:57:33', 'hour', 60.00, 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png', 1, '2025-05-03 00:51:29', '2025-07-02 00:51:29'),
(2, 'James', 'Junior', 'A', 22, 'Male', '09609097035', 'Ibapu Mactan LLC', 'Van', 'zz@gmail.com', '$2y$10$XkJ5XGwTiPRrtL4eAYSjeOJ7ri3NhBfNbLDYM32aZyhTAvLWBeW8O', '2025-05-03 07:44:03', 'hour', 500.00, 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png', 1, '2025-05-03 01:39:37', '2025-07-02 01:39:37'),
(3, 'John', 'Alsher', 'Aikems', 22, 'Male', '09609097035', 'Ibapu Mactan LLC', 'Motorcycle', 'sample1@gmail.com', '$2y$10$57OdGT9hovSdM9AHWwEzVeWJV8BQwzyHGgvTVDqQwqzQe8jJooGOS', '2025-05-05 05:27:06', 'hour', 0.00, 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png', 1, '2025-05-04 22:27:55', '2025-07-03 22:27:55'),
(4, 'Dejito', 'Riean', 'E', 22, 'Male', '09609097035', 'dwqdq', 'Motorcycle', 'sample2@gmail.com', '$2y$10$Qz7WdYRE64qDeydFzkyPlO.KmcwLgYZxvRLqu37.O1pwq8tr5ZkPa', '2025-05-05 05:29:50', 'hour', 0.00, 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png', 1, '2025-05-04 22:30:21', '2025-07-03 22:30:21'),
(5, 'Riean', 'Dejito', 'E', 20, 'Male', '0999999999', 'MCTAN', 'Motorcycle', 'ALSHERAKS@GMAIL.COM', '$2y$10$qscROiZFBu0LQSuuWfKazOJ//iAI5Jxi63kR4eHMqgAfzWJslsdT6', '2025-05-05 05:48:29', 'hour', 100.00, 'https://www.babatpost.com/wp-content/uploads/2015/12/go-jek-2.png', 1, '2025-05-04 22:50:25', '2025-07-03 22:50:25');

-- --------------------------------------------------------

--
-- Table structure for table `rider_licenses`
--

CREATE TABLE `rider_licenses` (
  `id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `license_path` varchar(255) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rider_licenses`
--

INSERT INTO `rider_licenses` (`id`, `rider_id`, `license_path`, `status`, `uploaded_at`) VALUES
(1, 1, 'uploads/licenses/1746165286_20250109_225125.jpg', 'Rejected', '2025-05-02 05:54:46'),
(2, 1, 'uploads/licenses/1746165304_20250109_225125.jpg', 'Rejected', '2025-05-02 05:55:04'),
(3, 1, 'uploads/licenses/1746165307_20250109_225125.jpg', 'Rejected', '2025-05-02 05:55:07'),
(4, 1, 'uploads/licenses/1746165534_20250109_225125.jpg', 'Rejected', '2025-05-02 05:58:54'),
(5, 1, 'uploads/licenses/1746165549_20250109_225125.jpg', 'Rejected', '2025-05-02 05:59:09'),
(6, 1, 'uploads/licenses/1746165687_20250109_225125.jpg', 'Rejected', '2025-05-02 06:01:27'),
(7, 1, 'uploads/licenses/1746165804_20250109_225125.jpg', 'Rejected', '2025-05-02 06:03:24'),
(8, 1, 'uploads/licenses/1746165839_IMG_7656.jpg', 'Rejected', '2025-05-02 06:03:59'),
(9, 2, 'uploads/licenses/1746261857_20250109_225125.jpg', 'Rejected', '2025-05-03 08:44:17'),
(10, 1, 'uploads/licenses/1746422495_download.jpeg', 'Accepted', '2025-05-05 05:21:35'),
(11, 3, 'uploads/licenses/1746422869_download.jpeg', 'Pending', '2025-05-05 05:27:49'),
(12, 4, 'uploads/licenses/1746423010_download.jpeg', 'Accepted', '2025-05-05 05:30:10'),
(13, 5, 'uploads/licenses/1746424153__Fishbone Diagram final_20240928_055724_0000.pdf', 'Accepted', '2025-05-05 05:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `rider_reports`
--

CREATE TABLE `rider_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ride_id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `report_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rider_reports`
--

INSERT INTO `rider_reports` (`id`, `user_id`, `ride_id`, `rider_id`, `report_text`, `created_at`, `status`) VALUES
(1, 1, 12, 1, 'lazy', '2025-05-02 16:53:45', 'pending'),
(2, 1, 14, 1, 'too lazy and wreckless', '2025-05-02 17:13:59', 'resolved'),
(3, 1, 18, 1, 'garapal mag drive', '2025-05-02 20:31:58', 'reviewed'),
(4, 1, 23, 2, 'bahog ilok', '2025-05-03 02:54:22', 'resolved');

-- --------------------------------------------------------

--
-- Table structure for table `ride_requests`
--

CREATE TABLE `ride_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `pickup_lat` decimal(10,7) NOT NULL,
  `pickup_lng` decimal(10,7) NOT NULL,
  `destination_location` varchar(255) NOT NULL,
  `destination_lat` decimal(10,7) NOT NULL,
  `destination_lng` decimal(10,7) NOT NULL,
  `status` enum('pending','accepted','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ride_requests`
--

INSERT INTO `ride_requests` (`id`, `user_id`, `rider_id`, `pickup_location`, `pickup_lat`, `pickup_lng`, `destination_location`, `destination_lat`, `destination_lng`, `status`, `created_at`, `price`) VALUES
(1, 1, NULL, 'AJ & L Advertising & Printing Co., Dionisio Jakosalem Street, Kamagayan, Cebu City, Central Visayas, 6000, Philippines', 10.3001397, 123.9011478, 'N. Escario Street, Camputhaw, Cebu City, Central Visayas, 6000, Philippines', 10.3167363, 123.8926506, 'completed', '2025-02-10 12:15:36', 0.00),
(2, 1, NULL, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Hotel Le Carmen, Juana Osmeña Street, Camputhaw, Cebu City, Central Visayas, 6000, Philippines', 10.3115843, 123.8953543, 'completed', '2025-02-10 12:16:34', 0.00),
(3, 1, NULL, 'Mactan Circumferential Road, Soong, Mactan, Lapu-Lapu, Central Visayas, 6015, Philippines', 10.3080372, 124.0105551, 'Basak - Cagodoy - Bankal - Buaya Road, Bankal, Pajac, Lapu-Lapu, Central Visayas, 6015, Philippines', 10.3008962, 123.9811260, 'completed', '2025-02-10 12:49:48', 0.00),
(4, 1, NULL, 'Lorenzo Mangubat Road, Looc, Gun-ob, Lapu-Lapu, Central Visayas, 6016, Philippines', 10.3069389, 123.9450932, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'completed', '2025-02-10 14:08:42', 0.00),
(5, 1, NULL, 'A. Soriano, Jr. Avenue, Mabolo, Cebu City, Central Visayas, 6666, Philippines', 10.3105708, 123.9132071, 'Juana Osmeña Street, Camputhaw, Cebu City, Central Visayas, 6000, Philippines', 10.3117110, 123.8951397, 'completed', '2025-02-10 15:15:20', 0.00),
(6, 2, NULL, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Grace Baptist Church, Dr. Pablo Abella Street, Pailob, Happy Valley, Cebu City, Central Visayas, 6000, Philippines', 10.3088815, 123.8861704, 'completed', '2025-02-10 15:19:58', 0.00),
(7, 1, NULL, 'Mactan Road, Cebu Business Park, Cebu City, Central Visayas, 6666, Philippines', 10.3128500, 123.9096451, 'San Carlos Heights, Cebu City, Central Visayas, 6000, Philippines', 10.2984649, 123.8600349, 'completed', '2025-02-28 10:02:10', 0.00),
(8, 1, NULL, 'Vista Bella, Pajac, Lapu-Lapu, Central Visayas, 6015, Philippines', 10.2926747, 123.9785135, 'Deca Homes 3, Basak, Lapu-Lapu, Central Visayas, 6016, Philippines', 10.2882756, 123.9723977, 'completed', '2025-04-26 11:56:48', 0.00),
(9, 1, NULL, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Cebu Business Park, Cebu City, Central Visayas, 6666, Philippines', 10.3144972, 123.9044946, 'completed', '2025-04-26 12:02:34', 0.00),
(10, 1, 1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, '7-Eleven, Don Ramon Aboitiz Street, Gonzales Compound, Cebu City, Central Visayas, 6000, Philippines', 10.3130616, 123.8958673, 'accepted', '2025-04-30 10:30:55', 0.00),
(11, 1, 1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Englis, Cebu City, Central Visayas, 6000, Philippines', 10.3143705, 123.8832484, 'completed', '2025-04-30 10:32:17', 46.00),
(12, 1, 1, 'Doctor F.E. Zuellig Avenue, City South Special Economic Administrative Zone, Mandaue, Central Visayas, 6666, Philippines', 10.3126394, 123.9248394, 'Naval Base Rafael Ramos, Captain Veloso Pier, Radco, Lapu-Lapu, Central Visayas, 6016, Philippines', 10.3045747, 123.9404629, 'accepted', '2025-04-30 11:15:55', 106.00),
(13, 4, 1, 'Joneal School Supply, General Maxilom Avenue, Zapatera, Cebu City, Central Visayas, 6000, Philippines', 10.3116251, 123.9017047, 'M. Logarta Avenue, City South Special Economic Administrative Zone, Mandaue, Central Visayas, 6666, Philippines', 10.3184629, 123.9265992, 'completed', '2025-04-30 12:36:19', 58.00),
(14, 1, 1, 'Queensland Manor, General Maxilom Avenue Extension, Lorega, Cebu City, Central Visayas, 6000, Philippines', 10.3078263, 123.9096450, 'Deca Homes 5, Basak, Lapu-Lapu, Central Visayas, 6016, Philippines', 10.2860480, 123.9654292, 'completed', '2025-05-02 05:15:24', 154.00),
(15, 1, 1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'SM City Cebu PUJ Terminal, Juan Luna Avenue, Carreta, Cebu City, Central Visayas, 6666, Philippines', 10.3103166, 123.9182295, 'completed', '2025-05-03 00:07:54', 22.00),
(16, 1, 1, 'Mactan Road, Cebu Business Park, Cebu City, Central Visayas, 6666, Philippines', 10.3134823, 123.9101602, 'M. Logarta Bridge, M. Logarta Avenue, Subangdaku, Mandaue, Central Visayas, 6666, Philippines', 10.3147063, 123.9225187, 'completed', '2025-05-03 02:00:13', 22.00),
(18, 1, 1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'E.O. Perez, Subangdaku, Mandaue, Central Visayas, 6666, Philippines', 10.3206999, 123.9263379, 'completed', '2025-05-03 03:30:46', 46.00),
(19, 1, 2, 'M. Logarta Avenue, City South Special Economic Administrative Zone, Mandaue, Central Visayas, 6666, Philippines', 10.3165635, 123.9256942, 'Umapad Road, Pakna-an, Umapad, Mandaue, Central Visayas, 3359, Philippines', 10.3305424, 123.9685155, 'completed', '2025-05-03 08:06:25', 94.00),
(20, 1, 2, 'Cebu City Agriculture Department Nursery, F. Urot Street, Holy Name, Cebu City, Central Visayas, 6666, Philippines', 10.3139888, 123.9222183, 'Philippine Chinese Spiritual Temple, P. Rodriguez Street, Englis, Cebu City, Central Visayas, 6000, Philippines', 10.3141154, 123.8890901, 'completed', '2025-05-03 09:02:53', 58.00),
(21, 1, 2, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Englis, Cebu City, Central Visayas, 6000, Philippines', 10.3117517, 123.8835973, 'completed', '2025-05-03 09:24:57', 46.00),
(22, 1, 2, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3085860, 123.9087868, 'Banawa, Cebu City, Central Visayas, 6000, Philippines', 10.3145797, 123.8810655, 'completed', '2025-05-03 09:26:59', 46.00),
(23, 1, 2, 'Maritech Training Center, M.J. Cuenco Avenue, Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3089659, 123.9074565, 'Purok 8, Cebu City, Central Visayas, 6000, Philippines', 10.3230636, 123.8961276, 'completed', '2025-05-03 09:51:39', 34.00),
(24, 2, NULL, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'The Ridges, Cebu City, Central Visayas, 6000, Philippines', 10.3115407, 123.8713591, 'pending', '2025-05-05 05:34:02', 70.00),
(25, 7, 5, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Island Central Mactan, Manuel Luis Quezon National Highway, Pusok, Lapu-Lapu, Central Visayas, 6015, Philippines', 10.3260342, 123.9764688, 'completed', '2025-05-05 05:45:53', 130.00),
(26, 1, 1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 10.3088128, 123.9089152, 'Banawa, Cebu City, Central Visayas, 6000, Philippines', 10.3190110, 123.8763219, 'completed', '2025-07-12 11:38:29', 58.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `location`, `full_name`, `phone_number`, `email`, `password`, `created_at`, `latitude`, `longitude`) VALUES
(1, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', 'Alsher Aikems Genodepanon', NULL, '1@gmail.com', '$2y$10$.ie4e54fv82tEzefsJhbKu1g6VmUay/s9CxEewU7Kuc.xNidvyDjy', '2025-02-10 08:52:16', '10.3088128', '123.9089152'),
(2, 'Lorega, Cebu City, Central Visayas, 6666, Philippines', '2', NULL, '2@gmail.com', '$2y$10$TkxTqX0P30s2nf1m/QUIJ.QsZveifpG0KI2pnWTjSFUArrzYGL2te', '2025-02-10 09:11:03', '10.3088128', '123.9089152'),
(3, NULL, 'Alsher Aikems Genodepanon', NULL, '33@gmail.com', '$2y$10$mLG9Y01DTItag5g.xJ791OGeTaSgao5FUerNiCGtaMsCKim/U0cEe', '2025-02-10 12:07:17', NULL, NULL),
(4, NULL, 'Alsher Aikems', NULL, 'z@gmail.com', '$2y$10$MqUyRDWP4qZfUfvVKLYou.iMtBcQL3p3bGUG2aFaGyOwPEKjEWCtW', '2025-04-30 12:21:27', NULL, NULL),
(5, NULL, 'sample', NULL, 'sample@gmail.com', '$2y$10$5YdZstQFHfXduqM6MqZdEugW678agm1MimAbcfNsw0Q.GWcGNI2hu', '2025-05-05 05:24:05', NULL, NULL),
(6, NULL, 'kc', NULL, 'kc@gmail.com', '$2y$10$3mjdT.95JzkDZzyw46pfNO3lW3oT1v1AjrWxsFqwZ1Aq012drxn9m', '2025-05-05 05:41:26', NULL, NULL),
(7, NULL, 'Alsjher AIkems', NULL, 'alsher@gmail.com', '$2y$10$SKD4c50pW2PQuESaavZCx.SeWYaFykOy8468RhbJzo6HefZNpbeyy', '2025-05-05 05:44:47', NULL, NULL),
(8, NULL, 'yansk', NULL, 'yansk@gmail.com', '$2y$10$m8y6YhhALcLjoqNQxgcJUu8MJay8bWjAX0Z/wYaPppw9sHX7ocBva', '2025-05-05 05:46:39', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_complaints`
--
ALTER TABLE `chat_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rider_id` (`rider_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ride_request_id` (`ride_request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rider_id` (`rider_id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `rider_licenses`
--
ALTER TABLE `rider_licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rider_id` (`rider_id`);

--
-- Indexes for table `rider_reports`
--
ALTER TABLE `rider_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ride_id` (`ride_id`);

--
-- Indexes for table `ride_requests`
--
ALTER TABLE `ride_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_complaints`
--
ALTER TABLE `chat_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rider_licenses`
--
ALTER TABLE `rider_licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `rider_reports`
--
ALTER TABLE `rider_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ride_requests`
--
ALTER TABLE `ride_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_complaints`
--
ALTER TABLE `chat_complaints`
  ADD CONSTRAINT `chat_complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`ride_request_id`) REFERENCES `ride_requests` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`);

--
-- Constraints for table `rider_licenses`
--
ALTER TABLE `rider_licenses`
  ADD CONSTRAINT `rider_licenses_ibfk_1` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rider_reports`
--
ALTER TABLE `rider_reports`
  ADD CONSTRAINT `rider_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rider_reports_ibfk_2` FOREIGN KEY (`ride_id`) REFERENCES `ride_requests` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
