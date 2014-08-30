#!/usr/bin/python

from TwitterAPI import TwitterAPI, TwitterOAuth
import MySQLdb
import time

# Establishes a connection to Twitter through the streaming API.
# The returned stream is a stream of tweets from the given geo location.
def get_stream(api, geo_loc):
        stream = api.request('statuses/filter', {'locations':geo_loc})
        return stream

# Initializes the API (effectively authenticates with Twitter).
def init_api(credentials_file):
        auth = TwitterOAuth.read_file(credentials_file)
        api = TwitterAPI(auth.consumer_key, auth.consumer_secret, auth.access_token_key, auth.access_token_secret)
        return api

# Establishes a connection to the local MySQL DB.
def init_db(user, password):
        db = MySQLdb.Connection("localhost", user, password, "twitter_db", charset='utf8')
        return db

# Adds a tweet to the database (from a cursor that should come from the init_db method).
def add_tweet(db, tweet, location):
        cursor = db.cursor()
	text = tweet['text']
        tags_glob = tweet['entities']['hashtags']
	current_time = int(time.time())

	tags = ""

	for i in range(len(tags_glob)):
		tags += tags_glob[i]['text'] + ' '

	if not tags:
		return

        cursor.execute("""
               INSERT INTO tweets
               (text, hash_tags, location, created_at)
               VALUES (%s, %s, %s, %s)""",
               [text, tags, location, current_time])
        print tags


def main():
        loc_names = ['boca', 'nyc', 'boston', 'sf']
        loc_coords = [-80.5, 25.5, -79.5, 26.5, -122.75, 36.8, -121.75, 37.8, -71.5, 41.5, -70.5, 42.5, -123, 36, -121, 38]
        api = init_api('credentials')
        stream = get_stream(api, loc_coords)
        db = init_db("twitter", "tweet_tweet")
        db.autocommit(True)
        for tweet in stream.get_iterator():
                try:
                        geo_loc = tweet['coordinates']
			if geo_loc is None:
				continue;
			geo_loc = geo_loc['coordinates']
                        for i in range(len(loc_names)):
                                if loc_coords[i * 4] <= geo_loc[0] and loc_coords[i * 4 + 1] <= geo_loc[1] and loc_coords[i * 4 + 2] >= geo_loc[0] and loc_coords[i * 4 + 3] >= geo_loc[1]:
					add_tweet(db, tweet, loc_names[i])
					break
                except:
			# Deals with weird unicode problems that I can't quite identify/fix with Python 2.x.
                        pass
main()

