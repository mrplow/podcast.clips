CREATE TABLE `podcast_clips`.`episodes`(
    `ep_rowid` INT NOT NULL AUTO_INCREMENT,
    `ep_filename` VARCHAR(256) NOT NULL,
    `ep_episode_num` INT NOT NULL,
    `ep_release_date` DATE NULL,
    `ep_title` VARCHAR(256) NULL,
    `ep_description` VARCHAR(4096) NULL,
    PRIMARY KEY(`ep_rowid`)
) ENGINE = INNODB;

CREATE TABLE `podcast_clips`.`users`(
    `us_rowid` INT NOT NULL AUTO_INCREMENT,
    `us_name` VARCHAR(256) NOT NULL,
    `us_cdate` DATE NOT NULL,
    `us_mdate` DATE NULL,
    `us_pass` VARCHAR(256) NOT NULL,
    PRIMARY KEY(`us_rowid`)
) ENGINE = INNODB;

CREATE TABLE `podcast_clips`.`segments`(
    `sg_rowid` INT NOT NULL AUTO_INCREMENT,
    `sg_rowid_episode` INT NOT NULL,
    `sg_cby` INT NOT NULL,
    `sg_cdate` DATE NOT NULL,
    `sg_mby` INT NULL,
    `sg_mdate` DATE NULL,
    `sg_comment` VARCHAR(256) NOT NULL,
    `sg_starttime` DECIMAL(32, 28) NOT NULL,
    `sg_endtime` DECIMAL(32, 28) NOT NULL,
    PRIMARY KEY(`sg_rowid`),
    FOREIGN KEY(sg_rowid_episode) REFERENCES episodes(ep_rowid),
    FOREIGN KEY(sg_cby) REFERENCES users(us_rowid),
    FOREIGN KEY(sg_mby) REFERENCES users(us_rowid)
) ENGINE = INNODB;

FLUSH PRIVILEGES;
