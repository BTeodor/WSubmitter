<?
$err=false;
//logs
if(!is_dir($logs_folder)||!is_writable($logs_folder)) {
	echo '<div class="message errormsg">Make sure you created the logs folder in the inc folder and chmoded to 777</div>';
	$err=true;
}
if(!is_dir($logs_logs_folder)&&is_dir($logs_folder)&&is_writable($logs_folder)) {
	mkdir($logs_logs_folder);
}
if(is_dir($logs_folder)&&is_writable($logs_folder)) {
	foreach($ws_config['ddls'] as $ddl) { $fddl = $logs_folder."/".$ddl['name']; if(!is_file($fddl)) {$fddl_h = fopen($fddl, 'w'); fclose($fddl_h); }}
}

if(!is_file($cats_file)) echo '<div class="message errormsg">Create cats.txt and add your categories</div>';
if(!is_file($ddls_file)) echo '<div class="message errormsg">Create ddls.txt and add your ddls</div>';

$file_cats = file($cats_file);
$ccats = count($file_cats);
if($ccats==0) echo '<div class="message warning">cats.txt is empty</div>';
$ddls = file($ddls_file);
$cddls = count($ddls);
if($cddls==0) echo '<div class="message errormsg">ddls.txt is empty</div>';

if($cskip!=0) echo '<div class="message warning">'.$cskip.' categories skipped. Check the cats.txt and make sure you added the categories like this: id,type</div>';
if($dskip!=0) echo '<div class="message warning">'.$dskip.' ddls skipped. Check the ddls.txt and make sure you added the ddls like this: submit url,name,type,limit,accept text</div>';
?>