<? 
set_time_limit(0);
ignore_user_abort(true);
require_once('config.php'); 
require_once('inc/conf.php'); 
require_once('inc/funcs.php'); 

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>WSubmitter</title>
<link rel="stylesheet" href="inc/css/stylesheet.css" />
<link rel="stylesheet" href="inc/css/paginationD.css" />
<script src="inc/js/jquery.js" type="text/javascript"></script>
<META HTTP-EQUIV="Cache-Control" CONTENT="max-age=0">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META http-equiv="expires" content="0">
<META HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 1980 1:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
</head>
<body>
	<div id="header">
    	 <div id="title"><a href="index.php"><h1>WSubmiter</h1></a></div>
    </div>
    <div id="loading" style="display: block;"><div class="main"><center><img src="inc/images/load.gif" /></center></div></div>
    <div id="submit">
    	<div class="main" id="con">';
			//echo '<pre>';
 			//print_r($_POST);
			send('WSubmitter ... working<br />');
			if($_POST) {
				if($ws_config['curl']) {
					if(stristr($_POST['ddlid'],'g')||$_POST['ddlid']=='0') submit_to_all_no_curl($_POST);
					else submittoddl($_POST);
				}
				else {
					if(stristr($_POST['ddlid'],'g')||$_POST['ddlid']=='0') submit_to_all_no_curl($_POST);
					else submit_no_curl($_POST);
				}
			}
       echo '</div>
    </div>
<div id="footer">Support or questions at gtht89@gmail.com. Add "WSubmitter Support" as subject for faster response. Created by <a href="mailto:gtht89@gmail.com?Subject=WSubmitter Support">t3od0r</a> | <a href="http://wsubmitter.zupedia.com">site</a></div>

</body>
</html>';
?>