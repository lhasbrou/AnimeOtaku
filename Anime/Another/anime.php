<html>
<head>
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
Welcome to AnimeOtaku! 
</title>
<link rel="shortcut icon" href="../../IMGS/favicon.ico">
<link rel="stylesheet" href="../../mainformat.css">
</head>
<body>
<center>
<img src="../../IMGS/banner.png">
<table border="3"  bordercolor="black"  bgcolor="#606060" style="opacity:0.95;" align=center  table width="1000">

<tr><td width="150"; valign="top">
<a href="../../index.html" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Home','','../../IMGS/HomeDown.png',1)"><img src="../../IMGS/Home.png" name="Home" border="0"></a><br>
<a href="../upcoming.html" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Upcoming','','../../IMGS/UpcomingDown.png',1)"><img src="../../IMGS/Upcoming.png" name="Upcoming" border="0"></a><br>
<a href="../index.php" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Listing','','../../IMGS/ListingDown.png',1)"><img src="../../IMGS/Listing.png" name="Listing" border="0"></a><br>
<a href="../genrelist.php" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Genre','','../../IMGS/GenreDown.png',1)"><img src="../../IMGS/Genre.png" name="Genre" border="0"></a><br>
<a href="../search.html" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Search','','../../IMGS/SearchDown.png',1)"><img src="../../IMGS/Search.png" name="Search" border="0"></a><br>
<a href="../../Games/index.html" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Games','','../../IMGS/GamesDown.png',1)"><img src="../../IMGS/Games.png" name="Games" border="0"></a><br>
<a href="#" onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" onMouseDown="MM_swapImage('Music','','../../IMGS/MusicDown.png',1); MyWindow=window.open('../../Music/MusicPlayer.html','MyWindow',width=300,height=300);"><img src="../../IMGS/Music.png" name="Music" border="0"></a><br>
</font>
</td><td width="850">
<br>

<?php 
include_once '../../includes/db_connect.php';
include_once '../../includes/functions.php';

$animename = 'Another';
get_anime_page($animename, $mysqli);

?>

<center><iframe width="640" height="360" src="//www.youtube-nocookie.com/embed/KJBWedpRPlQ?rel=0" frameborder="0" allowfullscreen></iframe>
<br><br><br>
<br><br><br>
</td></tr>
</table>
<div id="footer">Copyright @ 2014 AnimeOtaku by Logan Hasbrouck</div>
</body>
</html>