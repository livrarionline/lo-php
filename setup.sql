CREATE SCHEMA `smartlocker`;
USE `smartlocker`;

CREATE TABLE `wp_lo_delivery_points` (
  `dp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dp_denumire` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_adresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_judet` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_oras` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_tara` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_cod_postal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dp_gps_lat` double NOT NULL,
  `dp_gps_long` double NOT NULL,
  `dp_tip` int(11) DEFAULT 1,
  `dp_active` tinyint(1) NOT NULL DEFAULT 0,
  `version_id` int(11) NOT NULL,
  `stamp_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `dp_temperatura` decimal(10,2) DEFAULT NULL,
  `dp_indicatii` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `termosensibil` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`dp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `lo_dp_day_exceptions` (
  `leg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dp_id` int(10) unsigned NOT NULL,
  `exception_day` date NOT NULL,
  `dp_start_program` time NOT NULL DEFAULT '00:00:00',
  `dp_end_program` time NOT NULL DEFAULT '00:00:00',
  `active` tinyint(1) NOT NULL,
  `version_id` int(10) NOT NULL,
  `stamp_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`leg_id`),
  UNIQUE KEY `dp_id_exception_day` (`dp_id`,`exception_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lo_dp_program` (
  `leg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dp_start_program` time NOT NULL DEFAULT '00:00:00',
  `dp_end_program` time NOT NULL DEFAULT '00:00:00',
  `dp_id` int(10) unsigned NOT NULL,
  `day_active` tinyint(1) NOT NULL,
  `version_id` int(10) NOT NULL,
  `day_number` int(11) NOT NULL,
  `day` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_sort_order` int(1) NOT NULL,
  `stamp_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`leg_id`),
  UNIQUE KEY `dp_id_day_number` (`dp_id`,`day_number`),
  KEY `dp_id_day` (`dp_id`,`day`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Triggers structure for table lo_dp_program
-- ----------------------------
DROP TRIGGER IF EXISTS `lo_dp_program_BEFORE_INSERT`;
delimiter ;;
CREATE TRIGGER `lo_dp_program_BEFORE_INSERT` BEFORE INSERT ON `lo_dp_program` FOR EACH ROW SET new.`day_sort_order` =
    CASE
        WHEN (new.`day_number` = 1) THEN 1
        WHEN (new.`day_number` = 2) THEN 2
        WHEN (new.`day_number` = 3) THEN 3
        WHEN (new.`day_number` = 4) THEN 4
        WHEN (new.`day_number` = 5) THEN 5
        WHEN (new.`day_number` = 6) THEN 6
        WHEN (new.`day_number` = 0) THEN 7
END
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table lo_dp_program
-- ----------------------------
DROP TRIGGER IF EXISTS `lo_dp_program_BEFORE_UPDATE`;
delimiter ;;
CREATE TRIGGER `lo_dp_program_BEFORE_UPDATE` BEFORE UPDATE ON `lo_dp_program` FOR EACH ROW SET new.`day_sort_order` =
    CASE
        WHEN (new.`day_number` = 1) THEN 1
        WHEN (new.`day_number` = 2) THEN 2
        WHEN (new.`day_number` = 3) THEN 3
        WHEN (new.`day_number` = 4) THEN 4
        WHEN (new.`day_number` = 5) THEN 5
        WHEN (new.`day_number` = 6) THEN 6
        WHEN (new.`day_number` = 0) THEN 7
END
;;
delimiter ;

CREATE TABLE `lo_locker_push` (
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wp_lo_awb` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `awb` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `f_token` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_comanda` int(11) unsigned NOT NULL,
  `id_serviciu` int(11) NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `generat` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_awb_price` decimal(10,2) DEFAULT NULL,
  `payload` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`awb`,`id_comanda`),
  KEY `id_comanda` (`id_comanda`,`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
