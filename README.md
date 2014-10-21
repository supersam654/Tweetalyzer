# Twitter Analyzer Instructions
## Overview
This document will outline everything involved in setting up this project. This includes configuring certain components and moving files wherever they belong. This also briefly covers running (the backend of) the project.

## System Requirements
Below is a list of all system requirements to get things up and running. Next to each item, I listed the version that I am personally using. The version number is by no means a minimum or specific requirement, just an indication of what version I am using (and a version that works).

* MySQL (5.1.73)
* Python (2.6.6)
* Webserver (Nginx 1.0.15)
 * PHP (5.3.3)
* Linux (CentOS 6)

The rest of this tutorial assumes that the above are installed and working.

## Configuring MySQL

*If you just want the SQL statements to execute to get the database up and running, see `schema.sql`*

MySQL is the backbone that connects the frontend and the backend together. For the purposes of these instructions, I will assume that MySQL is installed and configured pretty typically.

To load the schema, run the following command:

    mysql -u root -p < schema.sql

This will create a new user, database, and password. The exact information is below (and in the schema file). If you decide to change any of these settings (for security reasons perhaps), you will also need to change these assumptions in `backend.py`.

* Host: `localhost`
* Database: `twitter_db`
* Username: `twitter`
* password: `tweet_tweet`
* table: `tweets`

## Configuring Python
Python needs access to MySQL (specifically through `MySQLdb`).

Python also needs `TwitterAPI`. Full installation instructions can be found [here](https://github.com/geduldig/TwitterAPI). The easiest way is to use python pip with 

	pip install TwitterAPI 

Installing `python-pip` and `python-mysql` are both easy but platform specific and therefore will not be covered in this tutorial.

## Included Files
These instructions should have come with a few separate files including:

* `www/` - Contains all of the files related to the frontend part of the project
* `README.md` - This file
* `sample_tweet.json` - A random tweet that I received from the Twitter API. It's a nice reference when trying to understand what info exactly is being put into the database. There are **many** fields in each tweet that I completely ignore.
* `schema.sql` - The database schema that contains all of the commands to get MySQL fully configured.
* `backend.py` - Python script that pulls tweets from Twitter and stores them in the databse.
* `credentials` - A four line file with Twitter API authentication information. These four values are clearly given to you when you upgrade a Twitter account to developer status. If you want to test out or modify this project, you need to request a developer account from Twitter (I think you just click a link) and they will happily give you these four "keys." Note that I have not given mine out as Twitter strongly recommends against doing so and they are uniquely tied to my personal Twitter account.
* `twitter.pptx` - The PowerPoint presentation I gave explaining the project. Rather detailed notes are included in the comments section of each slide.

### Specific Website Files
The frontend site is a pretty simple website. It's basically a couple of PHP files, a tiny bit of css customization, and a few javascript libraries ([JQuery](http://jquery.com/), [JQuery Datepicker](http://jqueryui.com/datepicker/), [Bootstrap](http://getbootstrap.com/), and [Morris Charts](http://www.oesmith.co.uk/morris.js/)). `index.php` is a typical html/php file that pulls hashtags from the database and displays them in a couple of places. `specific.php` is a page that is only accessable via links generated in `index.php`. It shows all tweets that have a certain hashtag and meet other constraints. `validation.php` is a library used by both other files for input sanitization and validation.

## Running the Project
Actually running everything is pretty simple. The webserver should be configured to serve up `index.php` by default and everything else should be fine. Note that the python script must always be running in the background. Technically it doesn't have to be running, but then new tweets won't get picked up. I have kept the script running in a few different ways. However, I was most recently successful with `nohup`. Something like this should do:

> `nohup ./project.py &`

## Room for Improvement
This project works and it works pretty well. However, given more time or a greater desire to be an absolute perfectionist, I would have:

* Upgraded to Python 3 (for better unicode support).
* Structured thed database into two tables. One for tweets and one for hashtags that link to tweets.
* Made acceptable locations an input to the Python script (each location has five parameters so command line arguments get a bit long).
* Made the site a bit more mobile friendly.
* Added more controls for picking which timeframe to pull tweets from (days are okay but hours would be nice).
* Devised a system to categorize similar tags (such as #job and #jobs) into one "super tag" (the frontend graph would then display the most common super tags).

## Screenshots
![Image of main page](/screenshots/main.png?raw=true "Screenshot of Main Page")
![Image of specifics page](/screenshots/specific.png?raw=true "Screenshot of Specific Tweets")
