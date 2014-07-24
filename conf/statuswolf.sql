
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table auth
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth`;

CREATE TABLE `auth` (
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `full_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`username`),
  KEY `password` (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO auth (username,password,full_name) VALUES ('swadmin','806aab343b43ca141f89f996e58f8667','StatusWolf Admin');



# Dump of table dashboard_rank
# ------------------------------------------------------------

DROP TABLE IF EXISTS `dashboard_rank`;

CREATE TABLE `dashboard_rank` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `count` int(15) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table saved_dashboards
# ------------------------------------------------------------

DROP TABLE IF EXISTS `saved_dashboards`;

CREATE TABLE `saved_dashboards` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `columns` int(2) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `widgets` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table saved_searches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `saved_searches`;

CREATE TABLE `saved_searches` (
  `id` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `search_params` mediumtext NOT NULL,
  `data_source` varchar(48) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table session_handler
# ------------------------------------------------------------

DROP TABLE IF EXISTS `session_handler`;

CREATE TABLE `session_handler` (
  `id` varchar(32) NOT NULL,
  `data` mediumtext NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table shared_searches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shared_searches`;

CREATE TABLE `shared_searches` (
  `search_id` varchar(32) NOT NULL,
  `data_source` varchar(50) NOT NULL,
  `search_params` mediumtext NOT NULL,
  `timestamp` int(255) NOT NULL,
  PRIMARY KEY (`search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sw_version
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sw_version`;

CREATE TABLE `sw_version` (
  `version` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL DEFAULT '',
  `roles` varchar(255) NOT NULL DEFAULT '',
  `auth_source` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO users (username,roles,auth_source) VALUES ('swadmin','ROLE_SUPER_USER','mysql');




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
