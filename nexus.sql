-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 08:27 PM
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
-- Database: `nexus`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--


CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    news_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (news_id) REFERENCES news_card(id),
    UNIQUE KEY (user_id, news_id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    news_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (news_id) REFERENCES news_card(id)
);



-- CREATE TABLE `comments` (
--   `id` int(11) NOT NULL,
--   `news_id` int(11) DEFAULT NULL,
--   `user_id` int(11) DEFAULT NULL,
--   `content` text NOT NULL,
--   `created_at` timestamp NOT NULL DEFAULT current_timestamp()
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `news_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 1, 'hhvhvd', '2025-05-13 17:08:45'),
(2, 1, 1, 'hhvhvd', '2025-05-13 17:08:59'),
(3, 1, 1, 'hhvhvd', '2025-05-13 17:09:32'),
(4, 1, 1, 'hhvhvd', '2025-05-13 17:09:48'),
(5, 1, 1, 'hhvhvd', '2025-05-13 17:10:13');

-- --------------------------------------------------------

--
-- Table structure for table `news_card`
--

CREATE TABLE `news_card` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('technology','sports','politics','entertainment') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_card`
--

INSERT INTO `news_card` (`id`, `title`, `content`, `category`, `image_path`, `likes`, `created_at`, `updated_at`) VALUES
(1, 'AI Breakthrough', 'New chip doubles processing speed.', 'technology', 'https://via.placeholder.com/600x300', 154, '2025-05-13 15:37:25', '2025-05-13 18:07:46'),
(2, 'Sports Triumph', 'Local team wins championship.', 'sports', NULL, 75, '2025-05-13 15:37:25', '2025-05-13 18:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `created_at`) VALUES
(1, 'wwww@gmail.com', 'nnn', '$2y$10$iHgNjZZ6DpIhVLyWsZ/muOemMj0M2m1j77Au.nNkQ696TKqVzFccq', '2025-05-13 15:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_behavior`
--

CREATE TABLE `user_behavior` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `scroll_depth` float DEFAULT NULL,
  `dwell_time` int(11) DEFAULT NULL,
  `clicks` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_behavior`
--

INSERT INTO `user_behavior` (`id`, `user_id`, `news_id`, `scroll_depth`, `dwell_time`, `clicks`, `created_at`) VALUES
(1, 1, 1, NULL, NULL, 0, '2025-05-13 17:07:57'),
(2, 1, 1, NULL, NULL, 0, '2025-05-13 17:08:36'),
(3, 1, 1, NULL, NULL, 0, '2025-05-13 17:18:37'),
(4, 1, 1, NULL, NULL, 0, '2025-05-13 17:19:15'),
(5, 1, 2, NULL, NULL, 0, '2025-05-13 17:19:39'),
(6, 1, 1, NULL, NULL, 0, '2025-05-13 17:23:00'),
(7, 1, 1, NULL, NULL, 0, '2025-05-13 17:23:50'),
(8, 1, 1, NULL, NULL, 1, '2025-05-13 17:24:26'),
(9, 1, 1, NULL, NULL, 1, '2025-05-13 18:07:25'),
(10, 1, 1, NULL, NULL, 1, '2025-05-13 18:07:27'),
(11, 1, 1, NULL, NULL, 1, '2025-05-13 18:07:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `news_card`
--
ALTER TABLE `news_card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_behavior`
--
ALTER TABLE `user_behavior`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `news_id` (`news_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news_card`
--
ALTER TABLE `news_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_behavior`
--
ALTER TABLE `user_behavior`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news_card` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news_card` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_behavior`
--
ALTER TABLE `user_behavior`
  ADD CONSTRAINT `user_behavior_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_behavior_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news_card` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- update table
ALTER TABLE news_card 
MODIFY category ENUM(
  'technology','sports','politics','entertainment','business','health',
  'science','world','education','travel','environment','finance','fashion',
  'lifestyle','food','automotive','culture','crime','weather','opinion','other'
) NOT NULL;
