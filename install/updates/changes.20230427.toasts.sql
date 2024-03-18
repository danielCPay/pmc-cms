CREATE TABLE s_yf_toasts (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `added` DATETIME NOT NULL,
  `displayed` DATETIME NULL,
  `owner` INT(10) NOT NULL,
  `level` VARCHAR(10) NOT NULL DEFAULT 'info' COLLATE 'utf8_general_ci',
  `title` VARCHAR(500) NULL COLLATE 'utf8_general_ci',
  `message` TEXT NOT NULL COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `unread` (`displayed`, `owner`) USING BTREE,
	CONSTRAINT `s_yf_toasts` FOREIGN KEY (`owner`) REFERENCES `vtiger_users` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE
);
