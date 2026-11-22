-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2026 at 03:44 PM
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
-- Database: `sound_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `artist_id` int(10) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `title`, `artist_id`, `year`, `cover`, `created_at`) VALUES
(1, 'Neon Skies', 1, 2025, NULL, '2026-10-17 12:26:46'),
(2, 'After Hours', 2, 2024, NULL, '2026-10-17 12:26:46'),
(3, 'Concrete Garden', 3, 2025, NULL, '2026-10-17 12:26:46'),
(4, 'Raga Reboot', 4, 2026, NULL, '2026-10-17 12:26:46'),
(5, 'Sol y Luna', 5, 2023, NULL, '2026-10-17 12:26:46'),
(6, 'Quiet Rooms', 6, 2022, NULL, '2026-10-17 12:26:46'),
(7, 'Rainy Tapes', 7, 2025, NULL, '2026-10-17 12:26:46'),
(8, 'Dil Ki Baat', 8, 2026, NULL, '2026-10-17 12:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

CREATE TABLE `artists` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`id`, `name`, `bio`, `image`, `created_at`) VALUES
(1, 'Nova Lane', 'SAMPLE: Synth-pop vocalist known for dreamy hooks.', NULL, '2026-10-17 12:26:46'),
(2, 'The Midnight Owls', 'SAMPLE: Four-piece alternative rock band.', NULL, '2026-10-17 12:26:46'),
(3, 'Kaya Rivers', 'SAMPLE: Hip-hop artist and producer.', NULL, '2026-10-17 12:26:46'),
(4, 'Aarav Sound', 'SAMPLE: Electronic producer blending raga and house.', NULL, '2026-10-17 12:26:46'),
(5, 'Luna Verde', 'SAMPLE: Spanish-language indie singer-songwriter.', NULL, '2026-10-17 12:26:46'),
(6, 'Elias Crane', 'SAMPLE: Contemporary classical pianist.', NULL, '2026-10-17 12:26:46'),
(7, 'Dreamer Tape', 'SAMPLE: Lo-fi beat maker.', NULL, '2026-10-17 12:26:46'),
(8, 'Sana Malik', 'SAMPLE: Urdu pop vocalist.', NULL, '2026-10-17 12:26:46'),
(9, 'taha', 'etc', NULL, '2026-10-17 13:32:33'),
(11, 'taha3', 'ffffffffffffff', NULL, '2026-10-17 13:37:59');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'Sample Visitor', 'visitor@example.com', 'SAMPLE: Hello', 'This is a sample contact message.', 0, '2026-10-17 12:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(5, 'Classical'),
(4, 'Electronic'),
(3, 'Hip-Hop'),
(6, 'Lo-fi'),
(1, 'Pop'),
(2, 'Rock');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`) VALUES
(1, 'English'),
(3, 'Hindi'),
(4, 'Spanish'),
(2, 'Urdu');

-- --------------------------------------------------------

--
-- Table structure for table `music`
--

CREATE TABLE `music` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `artist_id` int(10) UNSIGNED NOT NULL,
  `album_id` int(10) UNSIGNED DEFAULT NULL,
  `genre_id` int(10) UNSIGNED DEFAULT NULL,
  `language_id` int(10) UNSIGNED DEFAULT NULL,
  `year` smallint(5) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `audio_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `music`
--

INSERT INTO `music` (`id`, `title`, `artist_id`, `album_id`, `genre_id`, `language_id`, `year`, `description`, `cover`, `audio_file`, `created_at`) VALUES
(1, 'Neon Skies', 1, 1, 1, 1, 2025, 'SAMPLE: Title track with shimmering synths.', NULL, 'audio/sample_tone_a.wav', '2026-10-16 07:26:46'),
(2, 'City Lights', 1, 1, 1, 1, 2025, 'SAMPLE: A late-night drive anthem.', NULL, NULL, '2026-10-15 07:26:46'),
(3, 'Midnight Static', 2, 2, 2, 1, 2024, 'SAMPLE: Driving guitars and big chorus.', NULL, 'audio/sample_tone_b.wav', '2026-10-14 07:26:46'),
(4, 'Hollow Crown', 2, 2, 2, 1, 2024, 'SAMPLE: Slow-burning rock ballad.', NULL, NULL, '2026-09-27 07:26:46'),
(5, 'Concrete Garden', 3, 3, 3, 1, 2025, 'SAMPLE: Boom-bap beats with sharp lyricism.', NULL, NULL, '2026-10-13 07:26:46'),
(6, 'Raga Reboot', 4, 4, 4, 3, 2026, 'SAMPLE: Classical raga meets four-on-the-floor.', NULL, 'audio/sample_tone_c.wav', '2026-10-12 07:26:46'),
(7, 'Sol y Luna', 5, 5, 1, 4, 2023, 'SAMPLE: Warm acoustic guitar and soft vocals.', NULL, NULL, '2026-09-07 07:26:46'),
(8, 'Quiet Rooms', 6, 6, 5, 1, 2022, 'SAMPLE: Solo piano in three movements.', NULL, NULL, '2026-08-18 07:26:46'),
(9, 'Rainy Tape No.3', 7, 7, 6, 1, 2025, 'SAMPLE: Chilled beats for studying.', NULL, NULL, '2026-10-11 07:26:46'),
(10, 'Dil Ki Baat', 8, 8, 1, 2, 2026, 'SAMPLE: Heartfelt Urdu pop single.', NULL, NULL, '2026-10-10 07:26:46'),
(11, 'Afterglow', 1, 1, 4, 1, 2024, 'SAMPLE: Club remix of a fan favourite.', NULL, NULL, '2026-09-17 07:26:46'),
(12, 'Paper Planes', 3, 3, 3, 1, 2024, 'SAMPLE: Upbeat hip-hop with a jazzy loop.', NULL, NULL, '2026-09-22 07:26:46'),
(13, 'song3333333333333333', 4, 2, 5, 1, 2026, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'covers/d0c6d79e907dc387a2bda958.jpg', 'audio/1cee0ff2b565c34a47524d88.mp3', '2026-10-17 13:28:55');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `music_id` int(10) UNSIGNED DEFAULT NULL,
  `video_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `music_id`, `video_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NULL, 5, '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(2, 2, 3, NULL, 4, '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(3, 2, 6, NULL, 5, '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(6, 2, NULL, 1, 5, '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(7, 2, NULL, 2, 4, '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(8, 5, 13, NULL, 5, '2026-10-17 13:36:04', '2026-10-17 13:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `music_id` int(10) UNSIGNED DEFAULT NULL,
  `video_id` int(10) UNSIGNED DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `music_id`, `video_id`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NULL, 'SAMPLE REVIEW: Gorgeous synths, on repeat all week!', 'visible', '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(2, 2, 3, NULL, 'SAMPLE REVIEW: Huge chorus, great energy.', 'visible', '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(3, 2, NULL, 1, 'SAMPLE REVIEW: The visuals are stunning.', 'visible', '2026-10-17 12:26:46', '2026-10-17 12:26:46'),
(4, 5, 13, NULL, 'good music', 'visible', '2026-10-17 13:36:04', '2026-10-17 13:36:23');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('about_text', 'SOUND is a music and video entertainment platform where listeners discover new releases, explore artists and albums, and share ratings and reviews with the community.\n\nOur catalogue grows every week, and every title can be browsed by artist, album, year, genre and language.'),
('contact_address', 'SOUND Group, Karachi, Pakistan'),
('contact_email', 'hello@sound.example'),
('contact_phone', '+92 300 0000000'),
('footer_text', '© SOUND Group. All rights reserved.'),
('items_per_page', '12'),
('new_badge_days', '14'),
('site_name', 'SOUND'),
('tagline', 'Your world of music and video.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `full_name`, `address`, `phone`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(2, 'listener1', 'Demo Listener', '2 Music Road, Lahore', '+920000000002', 'listener1@sound.example', '$2y$10$ndV30fULEUaAkqu3VAHLiO8chVgQWjF0Zx5h8V/kK3Nd/Ol9dXEka', 'user', 'active', '2026-10-17 12:26:46'),
(4, 'Fareena_123', 'Fareena', 'HOUSE NO. R-622 SECTOR 15A/4 BUFFERZONE KARACHI', '03092681493', 'fareenak222@gmail.com', '$2y$10$i25Yew0NMbp7yy0mwEn3eegpd8ocwbrfQJt.tXf4vleADiq9YSIUC', 'admin', 'active', '2026-10-17 12:47:31'),
(5, 'sarakhan_123', 'sara', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '03092681493', 'sara@gmail.com', '$2y$10$4X8SEwYpZiuyfUDwhVKCL.Ux5r6BB645wl8jzovUTrRrkbCRyz5O.', 'admin', 'active', '2026-10-17 13:00:09');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `artist_id` int(10) UNSIGNED DEFAULT NULL,
  `genre_id` int(10) UNSIGNED DEFAULT NULL,
  `language_id` int(10) UNSIGNED DEFAULT NULL,
  `year` smallint(5) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `artist_id`, `genre_id`, `language_id`, `year`, `description`, `thumbnail`, `video_file`, `created_at`) VALUES
(1, 'Neon Skies (Official Video)', 1, 1, 1, 2025, 'SAMPLE: Cinematic music video shot in neon-lit streets.', NULL, NULL, '2026-10-16 07:26:46'),
(2, 'Midnight Static – Live Session', 2, 2, 1, 2024, 'SAMPLE: Live studio performance.', NULL, NULL, '2026-10-15 07:26:46'),
(3, 'Concrete Garden – Behind the Scenes', 3, 3, 1, 2025, 'SAMPLE: Studio footage and interviews.', NULL, NULL, '2026-10-14 07:26:46'),
(4, 'Raga Reboot Visualiser', 4, 4, 3, 2026, 'SAMPLE: Abstract visualiser.', NULL, NULL, '2026-10-13 07:26:46'),
(5, 'Sol y Luna – Acoustic', 5, 1, 4, 2023, 'SAMPLE: Acoustic rooftop performance.', NULL, NULL, '2026-10-12 07:26:46'),
(6, 'Quiet Rooms – Piano Film', 6, 5, 1, 2022, 'SAMPLE: Short film set to piano.', NULL, NULL, '2026-09-02 07:26:46'),
(7, 'Lo-fi Study Loop', 7, 6, 1, 2025, 'SAMPLE: One-hour looping lo-fi visual.', NULL, NULL, '2026-10-09 07:26:46'),
(8, 'Dil Ki Baat – Lyric Video', 8, 1, 2, 2026, 'SAMPLE: Animated lyric video.', NULL, NULL, '2026-10-11 07:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `years`
--

CREATE TABLE `years` (
  `year` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `years`
--

INSERT INTO `years` (`year`) VALUES
(2018),
(2019),
(2020),
(2021),
(2022),
(2023),
(2024),
(2025),
(2026);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_album` (`title`,`artist_id`),
  ADD KEY `fk_album_artist` (`artist_id`),
  ADD KEY `fk_album_year` (`year`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_artist_name` (`name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_genre` (`name`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_language` (`name`);

--
-- Indexes for table `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_music_created` (`created_at`),
  ADD KEY `idx_music_title` (`title`),
  ADD KEY `fk_music_artist` (`artist_id`),
  ADD KEY `fk_music_album` (`album_id`),
  ADD KEY `fk_music_genre` (`genre_id`),
  ADD KEY `fk_music_lang` (`language_id`),
  ADD KEY `fk_music_year` (`year`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rating_music` (`user_id`,`music_id`),
  ADD UNIQUE KEY `uq_rating_video` (`user_id`,`video_id`),
  ADD KEY `fk_rating_music` (`music_id`),
  ADD KEY `fk_rating_video` (`video_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_review_music` (`user_id`,`music_id`),
  ADD UNIQUE KEY `uq_review_video` (`user_id`,`video_id`),
  ADD KEY `fk_review_music` (`music_id`),
  ADD KEY `fk_review_video` (`video_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_userid` (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_video_created` (`created_at`),
  ADD KEY `idx_video_title` (`title`),
  ADD KEY `fk_video_artist` (`artist_id`),
  ADD KEY `fk_video_genre` (`genre_id`),
  ADD KEY `fk_video_lang` (`language_id`),
  ADD KEY `fk_video_year` (`year`);

--
-- Indexes for table `years`
--
ALTER TABLE `years`
  ADD PRIMARY KEY (`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `music`
--
ALTER TABLE `music`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `albums`
--
ALTER TABLE `albums`
  ADD CONSTRAINT `fk_album_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_album_year` FOREIGN KEY (`year`) REFERENCES `years` (`year`) ON UPDATE CASCADE;

--
-- Constraints for table `music`
--
ALTER TABLE `music`
  ADD CONSTRAINT `fk_music_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_music_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_music_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_music_lang` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_music_year` FOREIGN KEY (`year`) REFERENCES `years` (`year`) ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_rating_music` FOREIGN KEY (`music_id`) REFERENCES `music` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rating_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_music` FOREIGN KEY (`music_id`) REFERENCES `music` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_video` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_video_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_video_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_video_lang` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_video_year` FOREIGN KEY (`year`) REFERENCES `years` (`year`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
