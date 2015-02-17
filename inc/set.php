<?
//setup
$cskip=0;$dskip=0;$ws_config['cats']=array();$ws_config['ddls']=array();
if($ws_config['full_path']=='') $dir = getcwd();
else $dir = $ws_config['full_path'];
$logs_folder = $dir."/inc/logs";
$cats_file = $dir.'/cats.txt';
$ddls_file = $dir.'/ddls.txt';
$words_file = $dir.'/words.txt';
$chars_file = $dir.'/special_chars.txt';
$logs_logs_folder = $logs_folder.'/logs';
$allowed_cats=array("App","Game","Movie","TV","Music","XXX","eBook","Template","Script","Other","Mobile");
$fcats = file($cats_file);
$k=1;
foreach($fcats as $cat) {
	$cat = explode(',',$cat);
	if(count($cat)!=2) $cskip++;
	else {
		if(!in_array(trim($cat['1']),$allowed_cats)||!is_numeric(trim($cat[0]))) $cskip++;
		else {
			$c = array('id'=>trim($cat[0]),'type'=>trim($cat['1']));
			$ws_config['cats'][$k++]=$c;
		}
	}
}
$allowed_types=array("0","1","2","3","4");
$ddls = file($ddls_file);
$k=1;
$g=1;
$i=0;
foreach($ddls as $ddl) {
	$i++;
	$ddl = explode(',',$ddl);
	if(count($ddl)!=5) $dskip++;
	else {
		if(!stristr(trim($ddl[0]),'http://')||!in_array(trim($ddl['2']),$allowed_types)||!is_numeric(trim($ddl[3]))) $dskip++;
		else {
			$d = array('url'=>trim($ddl[0]),'name'=>trim($ddl[1]),'type'=>trim($ddl[2]),'limit'=>trim($ddl[3]),'accept_text'=>trim($ddl[4]),'group'=>$g);
			$ws_config['ddls'][$k++]=$d;
			if($i%$ws_config['group_limit']==0) $g++;
			
		}
	}
}
$words = file($words_file);
$ws_config['remove_words']=array();
foreach($words as $word) if(strlen($word)>2) $ws_config['remove_words'][]=trim($word);
$chars = file($chars_file);
$ws_config['remove_chars']=array();
foreach($chars as $char) $ws_config['remove_chars'][]=trim($char);

?>