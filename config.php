<?
$ws_config['type']=''; // site type: wp|phpbb|vb|ipb|dle|mybb|smf|xenforo
$ws_config['wordpress_authors']=''; // id's of your uploaders; comma separated; works only for wordpress blogs
$ws_config['wordpress_subcategories']=false; // set this to true and you can add only the main category in cats.txt
$ws_config['latestposts']=30; // days, show only posts posted in the last 30 days
$ws_config['sitename']='Site Name'; // site name
$ws_config['siteurl']='siteurl.com'; // site url to submit to ddl; no http://; no www; no trailing slashes;
$ws_config['sitemail']='email@siteurl.com';  // email to submit to ddls
$ws_config['urlf']='http://www.siteurl.com/'; // trailing slash at the end /

$ws_config['limit']=10; //limit used to submit to all ddls at once. Default 10
$ws_config['group_limit']=10; //limit used to submit to all ddls at once. Default 10

$ws_config['curl']=false;

$ws_config['timeout']=5; //keep it low for shared hosts
$ws_config['user_agent']='Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.6 Safari/535.11';
$ws_config['debug']=false;

$ws_config['full_path']=''; //full path to the wsubmitter like: /home/user/public_html/wsubmitter (no trailing slash)

require_once('inc/set.php');
?>