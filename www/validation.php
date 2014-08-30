<?php
	// Contains a bunch of input validation functions.
	// NOT AN ACTUAL PAGE!
	
	$print_locs = array('boca' => 'Boca Raton, FL', 'nyc' => 'New York City, NY', 'sf' => 'San Francisco, CA', 'boston' => 'Boston, Massachusetts');
	function get_location() {
		$fallback = 'boca';
		$known_locations = array('boca', 'nyc', 'sf', 'boston');
		// If a location is not given or the location is invalid, use Boca.
		if (empty($_GET['loc'])) {
			return $fallback;
		}
		if (in_array($_GET['loc'], $known_locations)){
			return $_GET['loc'];
		} else {
			return $fallback;
		}
	}

	function get_oldest_time() {
		$DAY_IN_SECONDS = 60 * 60 * 24;
		$fallback = time() - ($DAY_IN_SECONDS);
		if (empty($_GET['oldest'])) {
			$date = date_create_from_format('U', $fallback - ($fallback % $DAY_IN_SECONDS));
		} else {
			$date = date_create_from_format('m/d/Y', $_GET['oldest']);
			$date_errors = date_get_last_errors();
			// If something doesn't look right at all, go with the defaults.
			if ($date_errors['warning_count'] + $date_errors['error_count'] != 0) {
				$date = date_create_from_format('U', $fallback - ($fallback % $DAY_IN_SECONDS));
			}
		}
		return date_timestamp_get($date);
	}
	
	function get_newest_time() {
		$DAY_IN_SECONDS = 60 * 60 * 24;
		$fallback = time();
		if (empty($_GET['newest'])) {
			$date = date_create_from_format('U', $fallback - ($fallback % $DAY_IN_SECONDS));
		} else {
			$date = date_create_from_format('m/d/Y', $_GET['newest']);
			$date_errors = date_get_last_errors();
			// If something doesn't look right at all, go with the defaults.
			if ($date_errors['warning_count'] + $date_errors['error_count'] != 0) {
				$date = date_create_from_format('U', $fallback - ($fallback % $DAY_IN_SECONDS));
			}
		}
		return date_timestamp_get($date);
	}
	
	function get_k_topics() {
		$fallback = 5;
		if (empty($_GET['k'])) {
			return $fallback;
		}
		$k = $_GET['k'];
		
		if (!is_numeric($k)) {
			return $fallback;
		}
		
		// I arbitrarily limit the number of topics to no more than 15.
		if ($k > 0 && $k <= 15) {
			return $k;
		} else {
			return $fallback;
		}
	}
	
	function get_custom_tag() {
		$fallback = "";
		if (empty($_GET['custom'])) {
			return $fallback;
		}
		
		$custom = $_GET['custom'];
		
		// Hashtags don't have spaces and must fit in a tweet.
		if (preg_match('/\s/',$custom) || strlen($custom) > 160) {
			return $fallback;
		}
		
		return strtolower(trim($custom));
	}
?>