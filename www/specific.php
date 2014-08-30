<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	
	// Contains a bunch of methods that sanitize and validate $_GET parameters.
	require 'validation.php';

	$location = get_location();
	$oldest_time = (int) get_oldest_time();
	$newest_time = (int) get_newest_time();
	$custom_tag = get_custom_tag();
	
	$tweets = 'No tag selected.';
	if (empty($custom_tag)) {
		exit();
	}
	
	$db = new mysqli("localhost", "twitter", "tweet_tweet", "twitter_db");
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	// $location and $custom_tag are both sanitized.
	$query = "SELECT text, created_at, hash_tags FROM tweets WHERE location='$location' AND hash_tags LIKE '%$custom_tag%'";
	$result = $db->query($query);
	$result->data_seek(0);
	
	$tweets = '';	
	$raw_text = array();
	
	while ($row = $result->fetch_assoc()) {
		$date = $row['created_at'];
		if ($oldest_time <= $date && $date <= $newest_time) {
			$text = $row['text'];
			$tag_array = explode(' ', strtolower($row['hash_tags']));
			foreach ($tag_array as &$tag) {
				$text = preg_replace('/[^[:print:]]/', '',$text);
				// Ignore empty tags, partially-matched tags, or tweets that are already accounted for.
				// A more complicated database schema could completely remove this line.
				if ($tag == '' || $custom_tag !== $tag || in_array($text, $raw_text)) {
					continue;
				}
				if (stristr($text, $tag)) {
					$raw_text[] = $text;
					$tweets .='<p>' . $text . '</p>';
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="shortcut icon" href="favicon.ico" />
        <title>Tweetalyzer</title>
        <link href="css/bootstrap.min.css" rel="stylesheet" />
        <link href="css/template.css" rel="stylesheet" />
        <!-- Just for debugging purposes. Don't actually copy this line! -->
        <!--[if lt IE 9]><script src="js/ie8-responsive-file-warning.js"></script><![endif]-->
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
    </head>
    <body>
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
            </button> 
            <a class="navbar-brand" href="javascript:history.back()">Tweetalyzer</a></div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="/">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>Occurances of <?php echo $custom_tag?></h1>
		<div class="box full">
			<?php echo $tweets ?>
		</div>
	</div>	

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> 
    <script src="js/bootstrap.min.js"></script>
	</body>
</html>