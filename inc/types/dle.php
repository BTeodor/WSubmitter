<?
/*
Add in .htaccess after RewriteEngine On
	RewriteRule ^wsubmitter/$ wsubmitter/ [L]
	RewriteRule ^wsubmitter/(.*)$ wsubmitter/$1 [L]
*/
if(!is_file('../engine/data/dbconfig.php')) die('Check the type in config file and make sure the ddlsub folder is in the main folder');
define( 'DATALIFEENGINE', true );
define ( 'ENGINE_DIR', '../engine' );
require_once('inc/funcs.php');
require_once ('../engine/classes/mysql.php');
require_once ('../engine/data/dbconfig.php');

if($ws_config['debug']) { printr("<b>ws_config</b>"); printr($ws_config); }

$act=$_REQUEST['act'];
switch($act) {
	case 'selddls': {
		echo '<form id="selddlform">';
		echo '<select name = "sddl" id="sddl">';
		echo "<option value='0' dlimit='".$ws_config['limit']."'>Submit to all ddls</option>";
		for($i=1;$i<=$g;$i++) echo "<option value='g".$i."' dlimit='".$ws_config['limit']."'>Submit to Group #".$i."</option>";
		foreach($ws_config['ddls'] as $key=>$ddl)
		{
			$t = gett($ddl['type']);
			echo "<option value = '$key' durl='$ddl[url]' dname='$ddl[name]' dlimit='$ddl[limit]'>$ddl[name] ".$t."</option>";
		}
		echo '</select><p><input type ="submit" value = "Show Categories" name = "sonec" class="sonec"></p>';
		echo '</form>';
	} 
	break;
	case 'selectddls': {
		echo '<form id="selddlform">';
		echo '<select name = "sddl" id="sddl">';
		echo "<option value='0'>Submit to all ddls</option>";
		for($i=1;$i<=$g;$i++) echo "<option value='g".$i."' dlimit='".$ws_config['limit']."'>Submit to Group #".$i."</option>";
		foreach($ws_config['ddls'] as $key=>$ddl)
		{
			$t = gett($ddl['type']);
			echo "<option value = '$key' durl='$ddl[url]' dname='$ddl[name]' dlimit='$ddl[limit]'>$ddl[name] ".$t."</option>";
		}
		echo '</select><p><input type ="submit" value = "Show Categories" name = "showddlposts" class="showddlposts"></p>';
		echo '</form>';
	} 
	break;
	case 'sonec': {
		$cats=array();
		foreach($ws_config['cats'] as $cat) $cats[]=$cat['id'];
		$cats=implode(',',$cats);
		$db->query ( "SELECT name,id FROM " . USERPREFIX . "_category where id in (".$cats.")");
		echo '<form id="selform">';
		echo '<input type="hidden" id="ddl" value="'.$_REQUEST['ddl'].'" />';
		echo '<select name = "scategory" id="category">';
		echo "<option></option>";
		while ( $row = $db->get_row () )
		{
			echo "<option value = '$row[id]'>$row[name]</option>";
		}
		echo '</select><p><input type ="submit" value = "Show Posts" name = "showposts" class="showposts"></p>';
		echo '</form>';
	} 
	break;
	case 'getposts': {
		$num=0;
		$ddlid = $_REQUEST['ddl'];
		if($ddlid==0||stristr($ddlid,'g')) {
			$limit=$ws_config['limit'];
			$submitted_ids = get_submitted_ids();
		}
		else {
			$submitted_ids = get_submitted_ids($ddlid);
			$limit=$ws_config['ddls'][$ddlid]['limit'];
		}
		if($ws_config['debug']) { printr("<b>submitted_ids</b>"); printr($submitted_ids); }
		$cid=$_REQUEST['cid'];
		echo "<script type=\"text/javascript\">
		$(document).ready(function(){
				$('.changepage').livequery('click', function(event) {
					event.preventDefault();
					return false;  
				});
				$('.adddl').livequery('click', function(event) {
					event.preventDefault();
					return false;  
				});
				$('#backtocategories').livequery('click', function(event) {
					event.preventDefault();
					return false;  
				});
		
		});
		</script>";
		
		if($submitted_ids!='') $sub_not_in="  AND `id` NOT in (".$submitted_ids.")"; else $sub_not_in='';
		if($ws_config['debug']) { printr("<b>sub_not_in</b>"); printr($sub_not_in); }
		if($ws_config['latestposts']!='') {
			$last = date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d")-$ws_config['latestposts'],   date("Y")));
			$sub_latest = " AND date > '".$last."'"; 
		}
		else $sub_latest='';
		if($ws_config['debug']) { printr("<b>sub_auth</b>"); printr($sub_auth); }
		$db->query ( "SELECT `id` FROM " . USERPREFIX . "_post WHERE approve='1' and `category` = '".mysql_escape_string($cid)."'".$sub_latest.$sub_not_in."");
		$result = $db->num_rows();
		
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $ppage = intval($_REQUEST['page']); else $ppage = 1;
		$p = new pagination();
		$arr = $p->calculate_pages($result, $limit, $ppage);
		$offset = $arr['limit'];
		
		$db->query ( "SELECT `id`, `title` FROM " . USERPREFIX . "_post WHERE approve='1' and `category` = '".mysql_escape_string($cid)."'".$sub_latest.$sub_not_in."ORDER BY `id` DESC $offset");
		
		echo '<input type="submit" value= "Back" id="backtocategories"><br style="clear:both"/>';
		echo '<input type="submit" value= "Add All" id="addall"><br style="clear:both"/>';
		echo '<form>';
		echo '<table id="tdownloads" style="border: 2px solid #7bc4df; padding: 15px 10px; background: #faffff; width: 100%;">';
		$i=$offset+1;
		if($db->num_rows()==0) echo '<thead><tr><td><h2 class="center">Not Found</h2>
    		<p class="center">Sorry, but there aren\'t any downloads that you can submit here.</p></td></tr></thead>';
		while($re = $db->get_row())
		{
			foreach($ws_config['cats'] as $ccat) if($ccat['id']==$cid) { $ptype = $ccat['type']; break; }
			$purl=$ws_config['urlf'].'index.php?newsid='.$re['id'];
			$ptitle = clean($re['title']);
			echo '<tr id="row'.$re['id'].'" tid="'.$re['id'].'" ttitle="'.$ptitle.'" turl="'.$purl.'" ttype="'.$ptype.'""><td width="3%" style="border: 2px solid #7bc4df;">'.$i.'</td>';
			echo '<td class="adddl" width="97%" style="border: 2px solid #7bc4df;text-align:left; ">';
			echo $ptitle . '</td></tr>';
			$i++;
		}
		echo '</table></form>';
		echo '<center>';
		echo '<ul class="pagination paginationD paginationD02">
		  <li><a href="" class="first changepage" value="1" cid="'.$cid.'" ddl="'.$ddlid.'">First</a></li>
		  <li><a href="" class="previous changepage" value="'.$arr['previous'].'" cid="'.$cid.'" ddl="'.$ddlid.'">Previous</a></li>
		  ';
		  foreach($arr['pages'] as $page) {
				if($page==$arr['current']) echo '<li><a href="" class="current changepage" value="'.$page.'" cid="'.$cid.'" ddl="'.$ddlid.'">'.$page.'</a></li>';  
				else echo '<li><a href="" class="changepage" value="'.$page.'" cid="'.$cid.'" ddl="'.$ddlid.'">'.$page.'</a></li>';  
		  }
		  echo '
		  <li><a href="" class="next changepage" value="'.$arr['next'].'" cid="'.$cid.'" ddl="'.$ddlid.'">Next</a></li>
		  <li><a href="" class="last changepage" value="'.$arr['last'].'" cid="'.$cid.'" ddl="'.$ddlid.'">Last</a></li>
	  	</ul>';

		echo '</center>';
		wlog();
		}
	break;
	case 'sallc': {
		echo "<script type=\"text/javascript\">
		$(document).ready(function(){
				$('#submittoddl').livequery('click', function(event) {
					event.preventDefault();
					return false;  
				});
		
		});
		</script>";
		$postsids=array(); $catstoskip=array();$notin='';
		$ddlid = $_REQUEST['ddl'];
		if($ddlid==0||stristr($ddlid,'g')) {
			$limit=$ws_config['limit'];
			$cats = $ws_config['cats'];
			$submitted_ids = get_submitted_ids();
		}
		else {
			$submitted_ids = get_submitted_ids($ddlid);
			$limit=$ws_config['ddls'][$ddlid]['limit'];
			$cats = allowed_cats($ddlid);
		}
		if($ws_config['debug']) { printr("<b>submitted_ids</b>");printr($submitted_ids);}
		if($ws_config['debug']){  printr("<b>cats</b>");printr($cats);}
		$nr = count($cats);
		$eachdl = intval($limit/$nr);
		if($submitted_ids!='') $sub_not_in="  AND `id` NOT in (".$submitted_ids.")"; else $sub_not_in='';
		if($ws_config['debug']) { printr("<b>sub_not_in</b>");printr($sub_not_in); }
		if($ws_config['latestposts']!='') {
			$last = date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d")-$ws_config['latestposts'],   date("Y")));
			$sub_latest = " AND date > '".$last."'"; 
		}
		else $sub_latest='';
		require_once('inc/html/form_head.php');
		
		$lim=$eachdl;
		$nrs=0;$i=1;
		while($nrs<$limit) {

			$j=0;
		
			if(count($catstoskip)==count($cats)) break;
			foreach($cats as $cat) {
				if(count($postsids)>0) {
					$inpostsids=implode(',',$postsids);
					$notin=" and id not in (".$inpostsids.")";
				}
				if($ws_config['debug']) { printr("<b>where_cats</b>"); printr($where_cats); }
				if($ws_config['debug']) { printr("<b>catstoskip</b>"); printr($catstoskip); }
				if($ws_config['debug']) { printr("<b>postsids</b>");printr($postsids);}
				if($ws_config['debug']) { printr("<b>inpostsids</b>");printr($inpostsids);}
				if($ws_config['debug']) { printr("<b>notin</b>");printr($notin);}
				
				$db->query ( "SELECT id FROM " . USERPREFIX . "_post WHERE approve='1' and `category` = '".mysql_escape_string($cat['id'])."'".$sub_latest.$sub_not_in.$notin."");
				if($db->num_rows()==0) if(!in_array($cat['id'],$catstoskip)) $catstoskip[]=$cat['id'];
				$db->query("SELECT `id` , `title` FROM `".USERPREFIX."_post` WHERE approve='1' and `category` = '".mysql_escape_string($cat['id'])."'".$sub_latest.$sub_not_in.$notin." ORDER BY `id` DESC LIMIT $lim") or die(mysql_error());
				while($re = $db->get_row())
					{
						foreach($ws_config['cats'] as $ccat) if($ccat['id']==$cat['id']) { $ptype = $ccat['type']; break; }
						$postsids[]=$re['id'];
						$purl=$ws_config['urlf'].'index.php?newsid='.$re['id'];
						$pid = $re['id'];
						$ptitle = clean($re['title']);
						require('inc/html/form_row.php');
						$i++;$j++;
						if($nrs+$j==$limit) break 3;
					}
			}
			$nrs=$nrs+$j;
			$lim = intval(($limit - $nrs)/$nr);
			if($lim==0) $lim=2;
		}
		
		
		require_once('inc/html/form_foot.php');
		wlog();
	} 
	break;
	case 'submittoddls': {
		submittoddls($_POST);
		
	}
	break;
	case 'submittoddl': {
		submittoddl($_POST);
	}
	break;
}
?>