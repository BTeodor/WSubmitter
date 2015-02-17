<?
require_once('config.php');
if(is_file(getcwd().'/inc/types/'.$ws_config['type'].'.php')) require_once('inc/types/'.$ws_config['type'].'.php');
else die('Check the board type and make sure you have the plugin in the types folder');
?>