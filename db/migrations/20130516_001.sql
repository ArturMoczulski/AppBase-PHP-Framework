USE `appbase`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` int(5) NOT NULL AUTO_INCREMENT,
    `email` varchar(50) CHARACTER SET utf8 NOT NULL,
    `salt` varchar(200) CHARACTER SET utf8 NOT NULL,
    `encrypted_password` varchar(200) CHARACTER SET utf8 NOT NULL,
    `group_id` int(5) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_users_groups` FOREIGN KEY `fk_users_groups` (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
