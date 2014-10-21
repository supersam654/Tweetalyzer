<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'validation.php';

    $location = get_location();
    $k = get_k_topics();
    $custom_tag = get_custom_tag();
    $oldest_time = (int) get_oldest_time();
    $newest_time = (int) get_newest_time();

    // I'm just going to assume that if the times are flipped, then the user screwed up and we will take the assumed values.
    if ($oldest_time > $newest_time) {
        // Horribly unreadable way to swap two integers.
        $oldest_time ^= $newest_time ^= $oldest_time ^= $newest_time;
    }

    $dsn = "mysql:host=localhost;dbname=twitter_db;charset=utf8";
    $opt = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );
    $db = new PDO($dsn, 'twitter', 'tweet_tweet', $opt);

    $all_tags = array();

    // $location has been sanitized.
    $statement = $db->prepare("SELECT hash_tags, created_at FROM tweets WHERE location=:location");
    $statement->execute(array(':location' => $location));

    foreach ($statement as $row) {
        $date = $row['created_at'];
        if ($oldest_time <= $date && $date <= $newest_time) {
            $tag_array = explode(' ', strtolower($row['hash_tags']));

            foreach ($tag_array as &$tag) {
                if ($tag == '') {
                continue;
                }

                if (empty($all_tags[$tag])) {
                    $all_tags[$tag] = 1;
                } else {
                    $all_tags[$tag] = $all_tags[$tag] + 1;
                }
            }
        }
    }

    // Sort popular tags to the beginning.
    arsort($all_tags);
    // Chop off all tags that aren't within the k number of tags specified.
    $tags = array_slice($all_tags, 0, $k);

    if (strlen($custom_tag) > 0) {
        if (empty($all_tags[$custom_tag])) {
            $tags[$custom_tag] = 0;
        } else {
            $tags[$custom_tag] = $all_tags[$custom_tag];
        }
    }

    $graph_data = "";
    $hover_displays = "";
    $link = '/specific.php?loc=' . $location . '&oldest=' . date('m/d/Y', $oldest_time) . '&newest=' . date('m/d/Y', $newest_time) . '&custom=';
    foreach ($tags as $tag => $count) {
        $graph_data .= ',{tag: "' . $tag . '", count: ' . $count . '}';
        $hover_displays .= ',"<div class=\"hover-title\"><a href=\"' . $link . $tag . '\">' . $tag . '</a></div><b>Frequency: ' . $count . '</b>"';
    }

    // Remove extra comma at beginning of the strings.
    $graph_data = substr($graph_data, 1);
    $hover_displays = substr($hover_displays, 1);
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
        <link href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
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
        <a class="navbar-brand" href="#">Tweetalyzer</a></div>
        <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
            <li class="active">
            <a href="#">Home</a>
            </li>
        </ul>
        </div>
    </div>
    </div>
    <div class="container">
    <h1>Twitter Data</h1>
        <div class="box full">
            <h2>Trending Topics</h2>
            <h3>Most popular hashtags in <?php echo $print_locs[$location] ?> right now.</h3>
            <div id="tweets"></div>
        </div>
        <div class="form-group">
            <form name="options" class="form-inline" role="form" method="get">
                <div class="box left">
                    <h2>Polling Location</h2>
                    <label class="sr-only" for="loc">Geo Location</label>
                    <select type="presets" class="form-control" name="loc" id="loc">
                        <option selected="selected" value="boca">Boca Raton, FL</option>
                        <option value="nyc">New York City, NY</option>
                        <option value="boston">Boston, MA</option>
                        <option value="sf">San Francisco, CA</option>
                    </select>
                </div>

                <div class="box right">
                    <h2>Date Range</h2>
                    <p>From: <input type="text" name="oldest" id="oldest"> To: <input type="text" name="newest" id="newest"></p>
                </div>
                <div class="box left">
                    <h2>Custom Tag Search</h2>
                    <input type="text" name="custom" id="custom">
                </div>
                <div class="box right">
                    <h2>Refresh Rate</h2>
                    <label class="sr-only" for="refresh">Refresh Rate</label>
                    <select type="presets" class="form-control" name="refresh" id="refresh">
                        <option selected="selected" value="60">Slow (60 Seconds)</option>
                        <option value="5">Fast (5 Seconds)</option>
                    </select>
                </div>
                <div class="box left">
                    <h2>Number of Results</h2>
                    <!--<p>No more than 15 results can be displayed at once. If you are looking for something specific, type it into the Custom Tag Search box.</p>-->
                    <input type="text" name="k" id="k">
                </div>
                <div class="box right">
                    <button class="btn btn-default major" type="submit" class="btn btn-default">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script>
        //<!--
        // Populates the graph with data.
        new Morris.Bar({
            element: 'tweets',
            // Data that is generated from PHP.
            data: [<?php echo $graph_data ?>],
            hovers: [<?php echo $hover_displays ?>],
            xkey: 'tag',
            ykeys: ['count'],
            labels: ['Frequency'],
            hoverCallback: function (index, options, content) {
                return options.hovers[index];
            }
        });

        // Makes fancy calendars pop up for picking timespans.

        $(function() {$("#oldest").datepicker();});
        $(function() {$("#newest").datepicker();});

        // Deals with reapplying custom settings after a refresh.

        function getVar(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                if(pair[0] == variable) {
                    return pair[1];
                }
            }
            return(false);
        }

        var loc = getVar("loc");
        var oldest = getVar("oldest");
        var newest = getVar("newest");
        var k = getVar("k");
        var custom = getVar("custom");
        var refresh = getVar("refresh");

        if (loc !== false) {
            document.getElementById('loc').value = loc;
        }

        if (oldest !== false) {
            document.getElementById('oldest').value = decodeURIComponent(oldest);
        }

        if (newest !== false) {
            document.getElementById('newest').value = decodeURIComponent(newest);
        }

        if (k !== false) {
            document.getElementById('k').value = k;
        }

        if (custom !== false) {
            document.getElementById('custom').value = decodeURIComponent(custom);
        }

        if (refresh !== false) {
            document.getElementById('refresh').value = refresh;
        }

        // Deals with refreshing the page
        function beginrefresh(){
            if (!document.images)
                return
            if (refresh == 1)
                window.location.reload()
            else{
                refresh -= 1
                curmin=Math.floor(refresh / 60)
                cursec= refresh % 60
            if (curmin != 0)
                curtime = curmin + " minutes and " + cursec + " seconds left until page refresh!"
            else
                curtime = cursec + " seconds left until page refresh!"
                window.status = curtime
                setTimeout("beginrefresh()", 1000)
            }
        }

        window.onload=beginrefresh
        //-->
    </script>
    </body>
</html>
