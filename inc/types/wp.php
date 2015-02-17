<?
error_reporting(0);
require_once('inc/funcs.php');
if(!is_file('../wp-config.php')) die('Check the type in config file and make sure the wsubmitter folder is in the main folder');
require_once('../wp-config.php');

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
		echo '<form id="selform">';
		echo '<input type="hidden" id="ddl" value="'.$_REQUEST['ddl'].'" />';
		echo '<select name = "scategory" id="category">';
		echo "<option></option>";
		foreach($ws_config['cats'] as $cat) echo "<option value = '".$cat['id']."'>".get_the_category_by_ID($cat['id'])."</option>";
		echo '</select><p><input type ="submit" value = "Show Posts" name = "showposts" class="showposts"></p>';
		echo '</form>';
	} 
	break;
	case 'getposts': {
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
		$subcats = get_category_children($cid);
		if ($subcats != "" && $ws_config['wordpress_subcategories']==true) {
			$subcats = $cid.$subcats;
			$subcats=explode("/",$subcats);
			$subcats=array_filter($subcats);
			$subcats = implode(',',$subcats);
			$where_cats = "WHERE $wpdb->terms.term_id in (".$subcats.")";
		 }
		 else $where_cats = "WHERE $wpdb->terms.term_id = '".$cid."'";
		if($ws_config['debug']) { printr("<b>where_cats</b>"); printr($where_cats); }
		if($submitted_ids!='') $sub_not_in=" AND $wpdb->posts.ID not in (".$submitted_ids.")"; else $sub_not_in='';
		if($ws_config['debug']) { printr("<b>sub_not_in</b>"); printr($sub_not_in); }
		if($ws_config['wordpress_authors']!='') $sub_auth=" AND $wpdb->posts.post_author in (".$ws_config['wordpress_authors'].")"; else $sub_auth='';
		if($ws_config['latestposts']!='') {
			$last = date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d")-$ws_config['latestposts'],   date("Y")));
			$sub_latest = " AND $wpdb->posts.post_date > '".$last."'"; 
		}
		else $sub_latest='';
		if($ws_config['debug']) { printr("<b>sub_auth</b>"); printr($sub_auth); }
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
		$querystra = "
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
			".$sub_auth."
			GROUP BY $wpdb->posts.ID
    		ORDER BY $wpdb->posts.post_date DESC
		";
		if($ws_config['debug']) { printr("<b>querystra</b>"); printr($querystra); }
		echo '<input type="submit" value= "Back" id="backtocategories"><br style="clear:both"/>';
		echo '<input type="submit" value= "Add All" id="addall"><br style="clear:both"/>';
		echo '<form>';
		echo '<table id="tdownloads" style="border: 2px solid #7bc4df; padding: 15px 10px; background: #faffff; width: 100%;">';
		$quer = mysql_query($querystra);
		$num = mysql_num_rows($quer);
		if($ws_config['debug']) printr('Maximum number of posts: '.$num);
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $ppage = intval($_REQUEST['page']); else $ppage = 1;
		$p = new pagination();
		$arr = $p->calculate_pages($num, $limit, $ppage);
		$offset = $arr['limit'];
			
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
			".$sub_auth."
			GROUP BY $wpdb->posts.ID
    		ORDER BY $wpdb->posts.post_date DESC
			$offset
		";
		if($ws_config['debug']) { printr("<b>querystrb</b>"); printr($querystrb); }
		$pageposts = $wpdb->get_results($querystrb, OBJECT); 
		if ($pageposts): 
			$i=$offset+1;
			global $post;
			foreach ($pageposts as $post): 
				setup_postdata($post); 
				$pid = $post->ID;
				$ptitle = clean($post->post_title);
				$purl = get_permalink( $pid );
				foreach($ws_config['cats'] as $ccat) if($ccat['id']==$cid) { $ptype = $ccat['type']; break; }
				echo '<tr id="row'.$pid.'" tid="'.$pid.'" ttitle="'.$ptitle.'" turl="'.$purl.'" ttype="'.$ptype.'""><td width="3%" style="border: 2px solid #7bc4df;">'.$i.'</td>';
				echo '<td class="adddl" width="97%" style="border: 2px solid #7bc4df;text-align:left; ">';
				echo $ptitle . '</td></tr>';
				$i++;
			endforeach;
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
		else :
			echo '<h2 class="center">Not Found</h2>
    		<p class="center">Sorry, but you are looking for something that isn\'t here.</p>';
		endif;
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
		require_once('inc/html/form_head.php');
		
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
						$ptitle = clean($post->post_title);
						$purl = get_permalink( $pid );
						foreach($ws_config['cats'] as $ccat) if($ccat['id']==$cat['id']) { $ptype = $ccat['type']; break; }
						$postsids[]=$pid;
						require('inc/html/form_row.php');

						$i++;$j++;
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