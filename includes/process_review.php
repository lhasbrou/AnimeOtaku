<?php

include_once 'db_connect.php';
include_once 'functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['review'])) {
	$rating = $_POST['Star_Ratingz'];
	$animename = $_POST['animename'];
    $review = filter_input(INPUT_POST, 'review', FILTER_SANITIZE_STRING);
	$returnURL = $_POST['returnURL'];
    set_review($animename, $mysqli, $review, $rating);
        header('Location: http://' . $returnURL);
        exit();
		}