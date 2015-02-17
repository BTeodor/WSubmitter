<? 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('config.php'); 
$dir = getcwd();
$logs_folder = $dir."/inc/logs";
$cats_file = $dir.'/cats.txt';
$ddls_file = $dir.'/ddls.txt';
$words_file = $dir.'/words.txt';
$chars_file = $dir.'/special_chars.txt';
$logs_logs_folder = $logs_folder.'/logs';
if(is_dir($logs_folder)&&is_writable($logs_folder)) {
	foreach($ws_config['ddls'] as $ddl) { $fddl = $logs_folder."/".$ddl['name']; if(!is_file($fddl)) {$fddl_h = fopen($fddl, 'w'); fclose($fddl_h); }}
}
if(!is_dir($logs_logs_folder)&&is_dir($logs_folder)&&is_writable($logs_folder)) {
	mkdir($logs_logs_folder);
}
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
foreach($ddls as $ddl) {
	$ddl = explode(',',$ddl);
	if(count($ddl)!=5) $dskip++;
	else {
		if(!stristr(trim($ddl[0]),'http://')||!in_array(trim($ddl['2']),$allowed_types)||!is_numeric(trim($ddl[3]))) $dskip++;
		else {
			$d = array('url'=>trim($ddl[0]),'name'=>trim($ddl[1]),'type'=>trim($ddl[2]),'limit'=>trim($ddl[3]),'accept_text'=>trim($ddl[4]));
			$ws_config['ddls'][$k++]=$d;
		}
	}
}
$words = file($words_file);
$ws_config['remove_words']=array();
foreach($words as $word) if(strlen($word)>2) $ws_config['remove_words'][]=trim($word);
$chars = file($chars_file);
$ws_config['remove_chars']=array();
foreach($chars as $char) $ws_config['remove_chars'][]=trim($char);

//config
$ws_config['downloads_limit'] = '10';

/////////////////////////////////////////////////////////////////
error_reporting(0);
require_once('inc/funcs.php');
if(!is_file('../wp-config.php')) die('no wp config');
require_once('../wp-config.php');

if($ws_config['debug']) { printr("<b>ws_config</b>"); printr($ws_config); }


	$postsids=array(); $catstoskip=array();$notin='';

		$limit=$ws_config['limit'];
		$cats = $ws_config['cats'];
		$submitted_ids = get_submitted_ids();
	
	if($ws_config['debug']) { printr("<b>submitted_ids</b>");printr($submitted_ids);}
	if($ws_config['debug']){  printr("<b>cats</b>");printr($cats);}
	$nr = count($cats);
	if($nr!=0) $eachdl = intval($limit/$nr); else $eachdl=0;
	if($eachdl==0) $eachdl=1;
	if($submitted_ids!='') $sub_not_in=" AND $wpdb->posts.ID not in (".$submitted_ids.")"; else $sub_not_in='';
	if($ws_config['wordpress_authors']!='') $sub_auth=" AND $wpdb->posts.post_author in (".$ws_config['wordpress_authors'].")"; else $sub_auth='';
	if($ws_config['latestposts']!='') {
		$last = date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d")-$ws_config['latestposts'],   date("Y")));
		$sub_latest = " AND $wpdb->posts.post_date > '".$last."'"; 
	}
	if($ws_config['debug']) { printr("<b>sub_not_in</b>");printr($sub_not_in); }
	if($ws_config['debug']) { printr("<b>sub_auth</b>");printr($sub_auth); }
	
	$lim=$eachdl;
	$nrs=0;$i=1;
	while($nrs<$limit) {
		$j=0;
		if(count($catstoskip)==count($cats)) break;
		foreach($cats as $cat) {
			$subcats = get_category_children($cat['id']);
			if ($subcats != "" && $ws_config['wordpress_subcategories']==true) {
				$subcats = $cat['id'].$subcats;
				$subcats=explode("/",$subcats);
				$subcats=array_filter($subcats);
				$subcats = implode(',',$subcats);
				$where_cats = "WHERE $wpdb->terms.term_id in (".$subcats.")";
			 }
			 else $where_cats = "WHERE $wpdb->terms.term_id = '".$cat['id']."'";
			if(count($postsids)>0) {
				$inpostsids=implode(',',$postsids);
				$notin=" AND $wpdb->posts.ID not in (".$inpostsids.")";
			}
			if($ws_config['debug']) { printr("<b>where_cats</b>"); printr($where_cats); }
			if($ws_config['debug']) { printr("<b>catstoskip</b>"); printr($catstoskip); }
			if($ws_config['debug']) { printr("<b>postsids</b>");printr($postsids);}
			if($ws_config['debug']) { printr("<b>inpostsids</b>");printr($inpostsids);}
			if($ws_config['debug']) { printr("<b>notin</b>");printr($notin);}
			$querystrb = "
				SELECT * FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
				LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
				".$where_cats."
				AND $wpdb->term_taxonomy.taxonomy = 'category'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.post_type = 'post'
				".$sub_latest."
				".$sub_not_in."
				".$notin."
				".$sub_auth."
				GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_date DESC
				LIMIT $lim
			";
			if($ws_config['debug']) { printr("<b>querystrb</b>"); printr($querystrb); }
			$pageposts = $wpdb->get_results($querystrb, OBJECT); 
			if ($pageposts):
				global $post;
				foreach ($pageposts as $post): 
					setup_postdata($post); 
					$pid = $post->ID;
					if(!preg_match('/[^\x21-\x7E]+/',$post->post_title)) {
						$ptitle = clean($post->post_title);
						$ptitle = ucwords(strtolower($ptitle));
						$purl = get_permalink( $pid );
						foreach($ws_config['cats'] as $ccat) if($ccat['id']==$cat['id']) { $ptype = $ccat['type']; break; }
						$_POST['title'][$j] = $ptitle;
						$_POST['url'][$j] = $purl;
						$_POST['type'][$j] = $ptype;
						$i++;$j++;
					}
					$_POST['pids'][] = $pid;
					$postsids[]=$pid;
					if($nrs+$j==$limit) break 3;
				endforeach;
			else :
				if(!in_array($cat['id'],$catstoskip)) $catstoskip[]=$cat['id'];
			endif;
		}
		$nrs=$nrs+$j;
		$lim = intval(($limit - $nrs)/$nr);
		if($lim==0) $lim=2;
	}
$_POST['sname'] = $ws_config['sitename'];
$_POST['surl'] = $ws_config['siteurl'];
$_POST['email'] = $ws_config['sitemail'];
print_r($_POST);
submittoddls($_POST);