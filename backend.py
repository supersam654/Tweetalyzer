#!/usr/bin/python

from TwitterAPI import TwitterAPI, TwitterOAuth
import MySQLdb
import time

def get_continuous_tweets(api, geo_loc):
	"""Create a connection to Twitter to look for tweets with the provided geo locations.

	Arguments:
	api -- An authenticated instance of TwitterAPI.
	geo_loc -- A collection of longitudes and latitudes. There must be a multiple of four positions
			specified. The longitude must be specified first. Each pair of pairs denotes a 
			bounding box around the area to collect tweets from.

	Return an iterator that will progressively yield tweets as they are collected.
	"""
	stream = api.request('statuses/filter', {'locations':geo_loc})
	return stream.get_iterator()

def init_api(credentials_file):
	"""Initialize a connection to Twitter.
	
	Arguments:
	credentials_file -- A file with Twitter developer credentials.
	
	Return an authenticated instance of TwitterAPI.
	"""
	auth = TwitterOAuth.read_file(credentials_file)
	api = TwitterAPI(auth.consumer_key, auth.consumer_secret, auth.access_token_key, auth.access_token_secret)
	return api

def init_db(user, password):
	"""Establish a connection to the local MySQL database.
	
	Arguments:
	user -- The username to authenticate with.
	password -- The password to authenticate with.
	"""
	db = MySQLdb.Connection("localhost", user, password, "twitter_db", charset='utf8')
	return db

def add_tweet(db, tweet, location):
	"""Add a tweet to the database.
	
	Arguments:
	db -- The database to add the tweet to.
	tweet -- A key-value pair object with a variety of tweet metadata (including the actual tweet).
			This is the type of object that is returned from `get_continueous_tweets(...)`.
	location -- A string representation of the geo location this tweet came from (city name).
	"""
	cursor = db.cursor()
	text = tweet['text']
	tags_glob = tweet['entities']['hashtags']
	current_time = int(time.time())

	tags = ' '.join((glob['text'] for glob in tags_glob))

	if not tags:
		return

	cursor.execute("""INSERT INTO tweets (text, hash_tags, location, created_at)
		VALUES (%s, %s, %s, %s)""", [text, tags, location, current_time])

def main():
	loc_names = ['boca', 'nyc', 'boston', 'sf']
	loc_coords = [-80.5, 25.5, -79.5, 26.5, -122.75, 36.8, -121.75, 37.8, -71.5, 41.5, -70.5, 42.5, -123, 36, -121, 38]
	api = init_api('credentials')
	db = init_db("twitter", "tweet_tweet")
	db.autocommit(True)
	for tweet in get_continuous_tweets(api, loc_coords):
		try:
			geo_loc = tweet['coordinates']
			if geo_loc is None:
				continue

			geo_loc = geo_loc['coordinates']
			for i in range(len(loc_names)):
				if loc_coords[i * 4] <= geo_loc[0] and loc_coords[i * 4 + 1] <= geo_loc[1] and loc_coords[i * 4 + 2] >= geo_loc[0] and loc_coords[i * 4 + 3] >= geo_loc[1]:
					add_tweet(db, tweet, loc_names[i])
					break
		except Exception:
		# Deals with weird Unicode problems that magically go away in Python 3.
			pass

if __name__ == "__main__":
	main()
