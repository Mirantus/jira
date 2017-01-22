-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `snapshots`;
CREATE TABLE `snapshots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2017-01-22 04:22:06