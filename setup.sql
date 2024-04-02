SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `bans` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` tinyint(4) NOT NULL,
  `bannedby` bigint(20) UNSIGNED NOT NULL,
  `reason` tinyint(4) NOT NULL,
  `removal` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `boards` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pinned` bigint(21) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `boards` (`id`, `name`, `description`) VALUES (
  'abc',
  'Hello world! (/abc/)',
  'Bit empty here, ain\'t it? Create boards inside the \'boards\' table in the database.'
);

CREATE TABLE `flags` (
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `flags` (`name`, `value`) VALUES
('maintenance_full', 0),
('maintenance_limited', 0);

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contents` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notificationreadreceipts` (
  `id` int(11) NOT NULL,
  `notification` int(11) NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `polls` (
  `post` bigint(20) UNSIGNED NOT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `board` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inreplyto` bigint(20) UNSIGNED DEFAULT NULL,
  `content` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `xff` int(10) UNSIGNED DEFAULT NULL,
  `v` tinyint(4) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `posts` (`id`, `board`, `content`, `uid`, `ip`) VALUES (
  16045690984503098046,
  'abc',
  '**Whoa! Your own Huechan server!**
  Good job! If you see this Post, you have successfully set up your very own webserver running the Huechan software. As you can see, your own Imageboard platform\'s kind of empty as it is right now. Here are some recommended steps you should take to change that:
  - Create boards by adding entries to the \'boards\' table inside the database
  - Make yourself staff. Keep reading to find out how
  - Delete this board and this post when you\'re finished
  
  **How to make yourself staff**
  Making yourself staff requires finding out your user ID, which you can do by looking at the \'uids\' table in the database. Look at the last entry and copy the contents of its \'id\' field. Then switch over to the \'roles\' table and create a new entry. Insert the ID you copied into the \'uid\' field and optionally assign a name in the \'name\' field. For making yourself an administrator, insert a 1 into the \'role\' field, for a moderator insert a 0. Leave the other two fields as they are. You are now staff.',
  0,
  0
);

CREATE TABLE `removals` (
  `id` int(10) UNSIGNED NOT NULL,
  `post` bigint(20) UNSIGNED NOT NULL,
  `reason` tinyint(3) UNSIGNED NOT NULL,
  `details` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `removedby` bigint(20) UNSIGNED NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `reason` tinyint(3) UNSIGNED NOT NULL,
  `details` mediumtext COLLATE utf8mb4_unicode_ci,
  `post` bigint(20) UNSIGNED DEFAULT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `xff` int(10) UNSIGNED DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `role` tinyint(1) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `subject` tinyint(4) UNSIGNED DEFAULT NULL,
  `details` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `xff` int(10) UNSIGNED DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verify` mediumint(9) UNSIGNED NOT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `uids` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `xff` int(10) UNSIGNED DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post` bigint(20) UNSIGNED NOT NULL,
  `vote` tinyint(3) UNSIGNED NOT NULL,
  `uid` bigint(20) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `xff` int(10) UNSIGNED DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `bans`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `boards`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `flags`
  ADD UNIQUE KEY `flag` (`name`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notificationreadreceipts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `polls`
  ADD PRIMARY KEY (`post`);

ALTER TABLE `posts`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `removals`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `uids`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `bans`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notificationreadreceipts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `removals`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
