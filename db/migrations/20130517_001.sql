USE `appbase`;

-- Access Control Object (objects being request) --
CREATE TABLE IF NOT EXISTS `aco` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `default_access` int(5) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Access Request Object (objects which request) --
CREATE TABLE IF NOT EXISTS `aro` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `object_id` int(5) NOT NULL,
  `table_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- Permissions --
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `aco_id` int(5) NOT NULL,
  `aro_id` int(5) NOT NULL,
  `access` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `uc_aco_aro` UNIQUE (`aco_id`,`aro_id`),
  CONSTRAINT `fk_permissions_aco` FOREIGN KEY `fk_permissions_aco` (`aco_id`) REFERENCES `aco` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_permissions_aro` FOREIGN KEY `fk_permissions_aro` (`aro_id`) REFERENCES `aro` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
