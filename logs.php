<?
require_once('config.php');
if(is_file(getcwd().'/inc/logs/logs/'.$_GET['log'].'.html')) require_once('inc/logs/logs/'.$_GET['log'].'.html');
else die('No log found');
?>