<?php

/*
 * Copyright (C) 2013 peredur.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once 'psl-config.php';

function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name 
    $secure = SECURE;

    // This stops JavaScript being able to access the session id.
    $httponly = true;

    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }

    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

    // Sets the session name to the one set above.
    session_name($session_name);

    session_start();            // Start the PHP session 
    session_regenerate_id();    // regenerated the session, delete the old one. 
}

function login($email, $password, $mysqli) {
    // Using prepared statements means that SQL injection is not possible. 
    if ($stmt = $mysqli->prepare("SELECT id, username, password, salt 
				  FROM members 
                                  WHERE email = ? LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();

        // get variables from result.
        $stmt->bind_result($user_id, $username, $db_password, $salt);
        $stmt->fetch();

        // hash the password with the unique salt.
        $password = hash('sha512', $password . $salt);
        if ($stmt->num_rows == 1) {
            // If the user exists we check if the account is locked
            // from too many login attempts 
            if (checkbrute($user_id, $mysqli) == true) {
                // Account is locked 
                // Send an email to user saying their account is locked 
                return false;
            } else {
                // Check if the password in the database matches 
                // the password the user submitted.
                if ($db_password == $password) {
                    // Password is correct!
                    // Get the user-agent string of the user.
                    $user_browser = $_SERVER['HTTP_USER_AGENT'];

                    // XSS protection as we might print this value
                    $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                    $_SESSION['user_id'] = $user_id;

                    // XSS protection as we might print this value
                    $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);

                    $_SESSION['username'] = $username;
                    $_SESSION['login_string'] = hash('sha512', $password . $user_browser);

                    // Login successful. 
                    return true;
                } else {
                    // Password is not correct 
                    // We record this attempt in the database 
                    $now = time();
                    if (!$mysqli->query("INSERT INTO login_attempts(user_id, time) 
                                    VALUES ('$user_id', '$now')")) {
                        header("Location: ../error.php?err=Database error: login_attempts");
                        exit();
                    }

                    return false;
                }
            }
        } else {
            // No user exists. 
            return false;
        }
    } else {
        // Could not create a prepared statement
        header("Location: ../error.php?err=Database error: cannot prepare statement");
        exit();
    }
}

function checkbrute($user_id, $mysqli) {
    // Get timestamp of current time 
    $now = time();

    // All login attempts are counted from the past 2 hours. 
    $valid_attempts = $now - (2 * 60 * 60);

    if ($stmt = $mysqli->prepare("SELECT time 
                                  FROM login_attempts 
                                  WHERE user_id = ? AND time > '$valid_attempts'")) {
        $stmt->bind_param('i', $user_id);

        // Execute the prepared query. 
        $stmt->execute();
        $stmt->store_result();

        // If there have been more than 5 failed logins 
        if ($stmt->num_rows > 5) {
            return true;
        } else {
            return false;
        }
    } else {
        // Could not create a prepared statement
        header("Location: ../error.php?err=Database error: cannot prepare statement");
        exit();
    }
}

function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];

        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        if ($stmt = $mysqli->prepare("SELECT password 
				      FROM members 
				      WHERE id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);

                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Could not prepare statement
            header("Location: ../error.php?err=Database error: cannot prepare statement");
            exit();
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {

    if ('' == $url) {
        return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
    
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
    
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
    
    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);
    
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function get_anime_page($animename, $mysqli) {

	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, GENRE, EPISODES, MOVIES, DESCRIPTION FROM ANIMEINFO WHERE ANIME_NAME='" . $animename . "'")) {
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($ANIME_NAME, $GENRE, $EPISODES, $MOVIES, $DESCRIPTION);
                $stmt->fetch();
				}
			echo("<div id=\"title\">" . $ANIME_NAME . "</div><br>" . "<div id=\"description\"><table><tr><td valign=\"top\">Genre</td><td>" . $GENRE . "</td></tr><tr><td valign=\"top\">Episodes</td><td>"
	 . $EPISODES . "</td></tr><tr><td valign=\"top\">Movies</td><td>" . $MOVIES . "</td></tr><tr><td valign=\"top\">Description</td><td>" . $DESCRIPTION . "</td></tr></table></div><br>");
	 }
}

function anime_a_to_z($mysqli) {
	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, ANIME_URL FROM ANIMEINFO ORDER BY ANIME_NAME ASC")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($ANIME_NAME, $ANIME_URL);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"" . $ANIME_URL . "\">" . $ANIME_NAME . "</a><br>");
        }
	}
}

function list_genres($mysqli) {
	echo("<table align=center><tr><td>");
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Action%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Action\">Action</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Adventure%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Adventure\">Adventure</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Comedy%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Comedy\">Comedy</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Demons%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Demons\">Demons</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Drama%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Drama\">Drama</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Ecchi%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Ecchi\">Ecchi</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Fantasy%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Fantasy\">Fantasy</a>[" . $GENREZ . "]<br>");
		}
	}
	echo("</td><td>");
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Harem%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Harem\">Harem</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Horror%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Horror\">Horror</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Magic%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Magic\">Magic</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Mahou Shoujo%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Mahou%20Shoujo\">Mahou Shoujo</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Mecha%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Mecha\">Mecha</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Mystery%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Mystery\">Mystery</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Parody%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Parody\">Parody</a>[" . $GENREZ . "]<br>");
		}
	}
	echo("</td><td>");
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Psychological%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Psychological\">Psychological</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Romance%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Romance\">Romance</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%School Life%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=School%20Life\">School Life</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Science Fiction%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Science%20Fiction\">Science Fiction</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Seinen%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Seinen\">Seinen</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Shounen%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Shounen\">Shounen</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Slapstick%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Slapstick\">Slapstick</a>[" . $GENREZ . "]<br>");
		}
	}
	echo("</td><td valign=\"top\">");
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Slice of Life%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Slice%20of%20Life\">Slice of Life</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Supernatural%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Supernatural\">Supernatural</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Thriller%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Thriller\">Thriller</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Tragedy%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Tragedy\">Tragedy</a>[" . $GENREZ . "]<br>");
		}
	}
	
	if ($stmt = $mysqli->prepare("SELECT COUNT(GENRE) FROM ANIMEINFO WHERE GENRE LIKE '%Vampire%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		$stmt->bind_result($GENREZ);
            while ($stmt->fetch()) {
                echo("<a class=\"linkz\" href=\"genresearch.php?this=Vampire\">Vampire</a>[" . $GENREZ . "]<br>");
		}
	}
	echo("</td></tr></table>");
}

function list_genre_results($thiz, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, ANIME_URL FROM ANIMEINFO WHERE GENRE LIKE '%" . $thiz . "%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		if ($stmt->num_rows >= 1) {
		
		$stmt->bind_result($ANIME_NAME, $ANIME_URL);
            while ($stmt->fetch()) {
                echo("<P><a href=\"" . $ANIME_URL . "\">" . $ANIME_NAME . "</a></P>");
			}
		}
		else {
		echo("<br><u>No results found.</u>");
		}
	}
}

function search_database($searchvalue, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, ANIME_URL FROM ANIMEINFO WHERE ANIME_NAME LIKE '%" . $searchvalue . "%'")) {
		$stmt->execute();
        $stmt->store_result();
		
		if ($stmt->num_rows >= 1) {
		
		$stmt->bind_result($ANIME_NAME, $ANIME_URL);
            while ($stmt->fetch()) {
                echo("<P><a href=\"" . $ANIME_URL . "\">" . $ANIME_NAME . "</a></P>");
			}
		}
		else {
		echo("<br><u>No results found.</u>");
		}
	}
}

function get_star_rating($animename, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT TRUNCATE((SUM(rating)/COUNT(rating)), 2) FROM ratings WHERE ANIME_ID = (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($ratings);
	$stmt->fetch();
	
	if(is_null($ratings)) {
	$ratings=0;
	}
	$ratings2=round($ratings*4);
	for($x=1; $x<=20; $x++)
		{
		if($x == $ratings2)
			{
			echo("<input class=\"star {split:4}\" type=\"radio\" name=\"rating-segments\" value=\"" . $x . "\" disabled=\"disabled\" checked=\"checked\"/>");
			}
		else 
			{
			echo("<input class=\"star {split:4}\" type=\"radio\" name=\"rating-segments\" value=\"" . $x . "\" disabled=\"disabled\"/>");
			}
		}
	}
}

function get_individual_star_rating($animename, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT rating FROM ratings WHERE id=" . $_SESSION['user_id'] . " AND ANIME_ID = (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($ratingz);
	$stmt->fetch();
	
	if(is_null($ratingz)) {
	$ratingz=0;
	}
	for($x=1; $x<=5; $x++)
		{
		echo("<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"");
		if($x == 1)
			{
			echo("1\" title=\"Terrible\"");
			if($ratingz == $x)
				{
				echo(" checked=\"checked\"/>");
				}
			else
				{
				echo("/>");
				}
			}
		else if($x == 2)
			{
			echo("2\" title=\"Poor\"");
			if($ratingz == $x)
				{
				echo(" checked=\"checked\"/>");
				}
			else
				{
				echo("/>");
				}
			}
		else if($x == 3)
			{
			echo("3\" title=\"Ok\"");
			if($ratingz == $x)
				{
				echo(" checked=\"checked\"/>");
				}
			else
				{
				echo("/>");
				}
			}
		else if($x == 4)
			{
			echo("4\" title=\"Good\"");
			if($ratingz == $x)
				{
				echo(" checked=\"checked\"/>");
				}
			else
				{
				echo("/>");
				}
			}
		else if($x == 5)
			{
			echo("5\" title=\"Awesome\"");
			if($ratingz == $x)
				{
				echo(" checked=\"checked\"/>");
				}
			else
				{
				echo("/>");
				}
			}
		else 
			{
			echo("<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"1\" title=\"Terrible\"/>
<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"2\" title=\"Poor\"/>
<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"3\" title=\"OK\"/>
<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"4\" title=\"Good\"/>
<input class=\"hover-star\" type=\"radio\" name=\"Star_Ratingz\" value=\"5\" title=\"Awesome\"/>");
			}
		}
	}
}

function get_review($animename, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT review FROM ratings WHERE id=" . $_SESSION['user_id'] . " AND ANIME_ID = (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($review);
	$stmt->fetch();
	
	if(is_null($review)) {
	echo("Type review here.");
	}
	else {
	
	echo($review);
		}
	}
}

function set_review($animename, $mysqli, $review, $rating) {
	if($rating == 0)
	{
	$rating = NULL;
	}
	if($review == "Type review here.")
	{
	$review = NULL;
	}
	if ($stmt = $mysqli->prepare("SELECT id FROM ratings WHERE id=" . $_SESSION['user_id'] . " AND ANIME_ID = (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($check);
	$stmt->fetch();
	if(empty($check)) {
		if ($stmt = $mysqli->prepare("INSERT INTO ratings (id, ANIME_ID, review, rating) VALUES (" . $_SESSION['user_id'] . ", (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "'), '" . $review . "', " . $rating . ")")) {
		$stmt->execute();
		}
	}
	else {
	if ($stmt = $mysqli->prepare("UPDATE ratings SET review='" . $review . "', rating=" . $rating . " WHERE id=" . $_SESSION['user_id'] . " AND ANIME_ID=(SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
		$stmt->execute();
			}
		}
	}
}

function get_all_reviews($animename, $mysqli) {
	if ($stmt = $mysqli->prepare("SELECT username, rating, review FROM ratings r JOIN members m ON r.id = m.id WHERE r.ANIME_ID = (SELECT ANIME_ID FROM ANIMEINFO WHERE ANIME_NAME ='" . $animename . "')")) {
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($name, $rating, $review);
	$counterz=1;
	while($stmt->fetch()) {
		echo("<div id=\"review\"><div id=\"reviewname\"><b>" . $name . "</b><br>");
		if(is_null($rating)) {
		$rating=0;
		}
		for($x=1; $x<=5; $x++)
		{
			if($x == $rating)
				{
				echo("<input class=\"hover-star\" type=\"radio\" name=\"individual-rating" . $counterz . "\" value=\"" . $x . "\" disabled=\"disabled\" checked=\"checked\"/>");
				}
			else 
				{
				echo("<input class=\"hover-star\" type=\"radio\" name=\"individual-rating" . $counterz . "\" value=\"" . $x . "\" disabled=\"disabled\"/>");
				}
		}
		echo("</div><div id=\"reviewcontent\">" . $review . "<br>&nbsp;</div></div>");
		$counterz++;
		}
	}
}

function get_music_files() {
	$pathz = "";
	$music = glob($pathz . "*.mp3");
	echo(count($music));
	for($x=0; $x<=count($music)-1; $x++) 
		{
		if($x == 0)
			{
			echo($music[0]);
			}
		else
			{
			echo("%20%7C%20" . $music[$x]);
			}
		}
	}

function get_starter_recommendations($mysqli) {
	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, ANIME_URL, summary FROM ANIMEINFO a JOIN recommendations r ON a.ANIME_ID = r.ANIME_ID WHERE starter = 1 ORDER BY ANIME_NAME")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($name, $url, $summary);
	while($stmt->fetch()) {
	echo("<div id=\"recommendation\"><a href=\"" . $url . "\">" . $name . "</a></div><div id=\"recommendation\">" . $summary . "</div><br>");
		}
	}
}

function get_favorite_recommendations($mysqli) {
	if ($stmt = $mysqli->prepare("SELECT ANIME_NAME, ANIME_URL, summary FROM ANIMEINFO a JOIN recommendations r ON a.ANIME_ID = r.ANIME_ID WHERE favorites = 1 ORDER BY ANIME_NAME")) {
	$stmt->execute();
    $stmt->store_result();
	$stmt->bind_result($name, $url, $summary);
	while($stmt->fetch()) {
	echo("<div id=\"recommendation\"><a href=\"" . $url . "\">" . $name . "</a></div><div id=\"recommendation\">" . $summary . "</div><br>");
		}
	}
}