/*
 * Running every command in this file will completely set up the MySQL database.
 *
 * Specifically, run the following command from terminal:
 *
 *     mysql -u root -p < schema.sql
 */

CREATE DATABASE IF NOT EXISTS twitter_db;

GRANT ALL PRIVILEGES ON twitter_db.* TO 'twitter'@'localhost' IDENTIFIED BY 'tweet_tweet';
FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS twitter_db.tweets (
        id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
        text varchar(160) NOT NULL,
        hash_tags varchar(160) NOT NULL,
        created_at BIGINT(20) NOT NULL,
        location varchar(20) DEFAULT NULL,
        PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
