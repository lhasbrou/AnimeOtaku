<html>
<head>
<?php
include_once '../../includes/db_connect.php';
include_once '../../includes/functions.php';

sec_session_start();

?>
<script src='../../StarRating/jquery.js' type="text/javascript"></script>
<script src='../../StarRating/jquery.MetaData.js' type="text/javascript" language="javascript"></script>
<script src='../../StarRating/jquery.rating.js' type="text/javascript" language="javascript"></script>
<link href='../../StarRating/jquery.rating.css' type="text/css" rel="stylesheet"/>
<script src='../../StarRating/jquery-ui.min.js' type="text/javascript"></script>

<script type="text/JavaScript">
<!--
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
<style>

{ margin: 0; padding: 0; }

html {
background: url("../../IMGS/background.jpg") no-repeat center center fixed;
-webkit-background-size: cover;
-moz-background-size: cover;
-o-background-size: cover;
background-size: cover;
}

</style>
<title>
Sakurasou no Pet na Kanojo
</title>
<link rel="shortcut icon" href="../../IMGS/favicon.ico">
<!--<link rel="stylesheet" href="../../mainformat.css">-->
</head>
<body>
<center>
<img src="../../IMGS/banner.png">
<table border="3"  bordercolor="black"  bgcolor="#606060" style="opacity:0.95;" align=center  table width="1000">

<tr><td width="150"; valign="top">
<div style="height: 100px; padding-left:5px;">
<?php if (login_check($mysqli) == true) : ?>
        <p>Welcome <br><?php echo htmlentities($_SESSION['username']); ?>!
		<br><a href="../../includes/logout.php">logout</a></p>
<?php else : ?>
		<p>Please <a href="../../login.php">login</a>
		<br>or <a href="../../register.php">register</a></p>
<?php endif; ?>
</div>
<a href="../../index.php" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Home','','../../IMGS/HomeHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Home','','../../IMGS/HomeDown.png',1)"><img src="../../IMGS/Home.png" name="Home" border="0"></a><br>
<a href="../upcoming.html" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Upcoming','','../../IMGS/UpcomingHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Upcoming','','../../IMGS/UpcomingDown.png',1)"><img src="../../IMGS/Upcoming.png" name="Upcoming" border="0"></a><br>
<a href="../index.php" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Listing','','../../IMGS/ListingHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Listing','','../../IMGS/ListingDown.png',1)"><img src="../../IMGS/Listing.png" name="Listing" border="0"></a><br>
<a href="../genrelist.php" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Genre','','../../IMGS/GenreHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Genre','','../../IMGS/GenreDown.png',1)"><img src="../../IMGS/Genre.png" name="Genre" border="0"></a><br>
<a href="../recommendations.php" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Recommended','','../../IMGS/RecommendHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Recommended','','../../IMGS/RecommendDown.png',1)"><img src="../../IMGS/Recommend.png" name="Recommended" border="0"></a><br>
<a href="../search.html" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Search','','../../IMGS/SearchHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Search','','../../IMGS/SearchDown.png',1)"><img src="../../IMGS/Search.png" name="Search" border="0"></a><br>
<a href="../../Games/index.html" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Games','','../../IMGS/GamesHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Games','','../../IMGS/GamesDown.png',1)"><img src="../../IMGS/Games.png" name="Games" border="0"></a><br>
<a href="#" onMouseUp="MM_swapImgRestore()" onMouseOver="MM_swapImage('Music','','../../IMGS/MusicHover.png',1)" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Music','','../../IMGS/MusicDown.png',1); MyWindow=window.open('../../Music/MusicPlayer.html','MyWindow',width=300,height=300);"><img src="../../IMGS/Music.png" name="Music" border="0"></a><br>
</td><td width="850">
<br>
<?php
$animename = 'Sakurasou no Pet na Kanojo';
?>
<div class="starRate" style="padding-left:20px; position: absolute;">
<div>Currently rated: <br><?php get_star_rating($animename, $mysqli) ?></div>
</div>
<?php 
get_anime_page($animename, $mysqli);
?>
<center>
<br><br><br>
<br><br><br>
<hr>
<script>
$(function(){
 $('.hover-star').rating({
  focus: function(value, link){
    // 'this' is the hidden form element holding the current value
    // 'value' is the value selected
    // 'element' points to the link element that received the click.
    var tip = $('#hover-test');
    tip[0].data = tip[0].data || tip.html();
    tip.html(link.title || 'value: '+value);
  },
  blur: function(value, link){
    var tip = $('#hover-test');
    $('#hover-test').html(tip[0].data || '');
  }
 });
});
</script>

<div id="review_section">
<?php if (login_check($mysqli) == true) : ?>
<form method="post" action="../../includes/process_review.php" name="review_form">
<div style="font-family: Tempus Sans ITC; width: 500px; vertical-align: middle;">
<?php get_individual_star_rating($animename, $mysqli); ?>
<span id="hover-test" style="margin:0 0 0 20px;">&nbsp;</span>
</div>
<input type="hidden" name="returnURL" value="<?php echo($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]) ?>">
<input type="hidden" name="animename" value="<?php echo($animename) ?>">
<textarea name="review" cols="75" rows="7">
<?php 
get_review($animename, $mysqli);
?>
</textarea><br>
<input type="submit" value="Submit" />
</form>
<?php else : ?>
<div>You need to be logged in to rate.</div>
<?php endif; ?>
<hr>
<?php
get_all_reviews($animename, $mysqli);
?>
</div>
</td></tr>
</table>
<div id="footer">Copyright @ 2014 AnimeOtaku by Logan Hasbrouck</div>
</body>
</html>