# Tweetalyzer
## Aggregate geographically close tweets and see what they have in common.

This is a project I made in my senior year of high school. It was an alternative assignment for my
Intro to Internet Computing class.

## System Recommendations
Below are the relevant specs of the system I ran this on. These are in no way minimum requirements, 
just what happened to work for me.

* PHP 5.3.3
* Python 2.6.6
* * Python TwitterAPI (https://github.com/geduldig/TwitterAPI)
* MySQL 5.1.73
* CentOS 6 x64

## How to run:
1. `python backend.py`
1. Visit index.php with a web browser.

## Detailed Installation
Below is the entire database schema:
<code>
CREATE TABLE `tweets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(160) NOT NULL,
  `hash_tags` varchar(160) NOT NULL,
  `created_at` bigint(20) NOT NULL,
  `location` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40511 DEFAULT CHARSET=utf8;
</code>

To make my life easier (at the time), database information is hardcoded into the backend and 
frontend. Assuming you are using the exact code in the repository, you need a MySQL user named 
`twitter` with the password `tweet_tweet` who has access to the `twitter_db` database.

To install Python TwitterAPI, run `pip install TwitterAPI`.
