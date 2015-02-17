<? 
require_once('config.php'); 
require_once('inc/conf.php'); 

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>WSubmitter</title>
<link rel="stylesheet" href="inc/css/stylesheet.css" />
<link rel="stylesheet" href="inc/css/paginationD.css" />
<script src="inc/js/jquery.js" type="text/javascript"></script>
<script src="inc/js/jquery.livequery.js" type="text/javascript"></script>
<script src="inc/js/jquery.field.min.js" type="text/javascript"></script>
<script src="inc/js/jquery.scrollTo-min.js" type="text/javascript"></script>
<script src="inc/js/js.js" type="text/javascript"></script>
<META HTTP-EQUIV="Cache-Control" CONTENT="max-age=0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META http-equiv="expires" content="0">
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
</head>
<body>
	<div id="header">
    	 <div id="title"><a href=""><h1>WSubmiter</h1></a></div>
    </div>
	<div class="main" id="topm">
    	<div id="choose"><a href=\'\' class=\'';
		if($cskip!=0||$dskip!=0||$ccats==0||$cddls==0||$err==true) echo 'checka check'; else echo 'selddls';
		echo'\'>Submit from one forum</a><a href=\'\' class=\'';
		if($cskip!=0||$dskip!=0||$ccats==0||$cddls==0||$err==true) echo 'checkb check'; else echo 'sallc'; 
		echo '\'>Submit from all categories</a></div>
        <div id="content" style="display: none;"></div>
    </div>
    <div id="loading" style="display: none;"><div class="main"><center><img src="inc/images/load.gif" /></center></div></div>
    <div id="submit" style="display: none;">
    	<div class="main" id="con">';
 			include_once('inc/html/form_index.php');
       echo '</div>
    </div>
<div id="footer">Support or questions at gtht89@gmail.com. Add "WSubmitter Support" as subject for faster response. Created by <a href="mailto:gtht89@gmail.com?Subject=WSubmitter Support">t3od0r</a> | <a href="http://wsubmitter.zupedia.com">site</a></div>

</body>
</html>';
?>