CREATE TABLE `podcast_clips`.`episodes`(
    `ep_rowid` INT NOT NULL AUTO_INCREMENT,
    `ep_filename` VARCHAR(256) NOT NULL,
    `ep_file_sha1` TEXT NOT NULL,
    `ep_episode_num` DECIMAL(11,1) NOT NULL,
    `ep_release_date` DATE NULL,
    `ep_title` VARCHAR(256) NULL,
    `ep_description` VARCHAR(4096) NULL,
    PRIMARY KEY(`ep_rowid`),
    UNIQUE KEY `ep_episode_num`(`ep_episode_num`)
) ENGINE = INNODB AUTO_INCREMENT = 1;

CREATE TABLE `podcast_clips`.`userlevel`(
    `ul_rowid` INT NOT NULL AUTO_INCREMENT,
    `ul_level` INT NOT NULL,
    `ul_descr` VARCHAR(40),
    PRIMARY KEY(`ul_rowid`),
    UNIQUE KEY `ul_level`(`ul_level`)
) ENGINE = INNODB AUTO_INCREMENT = 1;

CREATE TABLE `podcast_clips`.`users`(
    `us_rowid` INT NOT NULL AUTO_INCREMENT,
    `us_rowid_userlevel` INT NOT NULL,
    `us_username` VARCHAR(128) NOT NULL,
    `us_password` CHAR(60) NOT NULL,
    `us_email_addr` VARCHAR(256) NULL,
    `us_email_validated` VARCHAR(32) NULL,
    `us_cdate` DATETIME NOT NULL,
    `us_mdate` DATETIME NULL,
    `us_lastlogin` DATETIME NULL,
    `us_logincount` INT NOT NULL DEFAULT '1',
    `us_validated` DATETIME NULL,
    PRIMARY KEY(`us_rowid`),
    UNIQUE KEY `us_username`(`us_username`),
    FOREIGN KEY(us_rowid_userlevel) REFERENCES userlevel(ul_rowid)
) ENGINE = INNODB AUTO_INCREMENT = 1;

INSERT INTO `podcast_clips`.`userlevel`(`ul_rowid`, `ul_level`, `ul_descr`)
VALUES(1, 1, 'owner'),(2, 10, 'moderator'),(3, 20, 'normal');

CREATE TABLE `podcast_clips`.`segments`(
    `sg_rowid` INT NOT NULL AUTO_INCREMENT,
    `sg_rowid_episode` INT NOT NULL,
    `sg_cby` INT NOT NULL,
    `sg_cdate` DATETIME NOT NULL,
    `sg_mby` INT NULL,
    `sg_mdate` DATETIME NULL,
    `sg_comment` VARCHAR(256) NOT NULL,
    `sg_starttime` DECIMAL(32, 28) NOT NULL,
    `sg_endtime` DECIMAL(32, 28) NOT NULL,
    PRIMARY KEY(`sg_rowid`),
    FOREIGN KEY(sg_rowid_episode) REFERENCES episodes(ep_rowid),
    FOREIGN KEY(sg_cby) REFERENCES users(us_rowid),
    FOREIGN KEY(sg_mby) REFERENCES users(us_rowid)
) ENGINE = INNODB AUTO_INCREMENT = 1;

CREATE TABLE `podcast_clips`.`transcriptions`(
    `tr_rowid` INT NOT NULL AUTO_INCREMENT,
    `tr_rowid_episode` INT NOT NULL,
    `tr_time` DECIMAL(32, 28) NOT NULL,
    `tr_text` VARCHAR(1024) NOT NULL,
    PRIMARY KEY(`tr_rowid`),
    FOREIGN KEY(tr_rowid_episode) REFERENCES episodes(ep_rowid)
) ENGINE = INNODB AUTO_INCREMENT = 1;

ALTER TABLE
   `podcast_clips`.`transcriptions` ADD FULLTEXT INDEX `idx_text`(`tr_text`);

USE `podcast_clips`;

CREATE VIEW `podcast_clips`.`episode_transcriptions` AS SELECT
    `tr_rowid`,
    `ep_rowid`,
    `ep_episode_num`,
    `ep_title`,
    `ep_release_date`,
    SEC_TO_TIME(ROUND(`tr_time`, 2)) AS `hhmmss`,
    `tr_text`
FROM
    `episodes`
JOIN `transcriptions` ON `ep_rowid` = `tr_rowid_episode`
ORDER BY
    ep_episode_num ASC,
    tr_time ASC;


FLUSH PRIVILEGES;
