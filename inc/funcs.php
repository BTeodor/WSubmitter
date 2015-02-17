<?
require_once('class/php-pagination-class.php');
function gett($t,$z=0) {
	switch($t) {
		case 0: {
			if($z==0) $tt = ' (Adult NOT allowed)';
			else $tt ='App, Game, Movie, TV, Music, eBook, Template, Script, Other, Mobile';
		}
		break;
		case 1: {
			if($z==0) $tt = ' (Adult allowed)';
			else $tt ='App, Game, Movie, TV, Music, XXX, eBook, Template, Script, Other, Mobile';
		}
		break;
		case 2: {
			if($z==0) $tt = ' (ONLY Adult allowed)';
			else $tt ='XXX';
		}
		break;
		case 3: {
			if($z==0) $tt = ' (ONLY MP3 allowed)';
			else $tt ='Music';
		}
		break;
		case 4: {
			if($z==0) $tt = ' (NO MP3 | NO Adult)';
			else $tt ='App, Game, Movie, TV, eBook, Template, Script, Other, Mobile';
		}
		break;
	}
	return $tt;
}
function send($string = "") {
	echo $string;
	echo str_pad('', 4096)."\n";
	@ob_flush();
	@flush();
}
function curl($link, $postfields = '')
{
	global $ws_config;
	$postfields = http_build_query($postfields);
	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $ws_config['user_agent']);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $ws_config['timeout']);     // timeout on connect
    curl_setopt($ch, CURLOPT_TIMEOUT, $ws_config['timeout']);      // timeout on response
	if($postfields)
	{
		curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	}
	
	$page = curl_exec($ch);
	print "<pre>\n";
print_r(curl_getinfo($ch));  // get error info
echo "\n\ncURL error number:" .curl_errno($ch); // print error info
echo "\n\ncURL error:" . curl_error($ch); 
print "</pre>\n";
	echo $page;
	die();
	if(curl_errno($ch)) return $page;
	else return($page);
	curl_close($ch);
}
function get_submitted_ids($ddlid = NULL) {
	global $logs_folder,$ws_config;
	$ids='';
	if(isset($ddlid)) {
		$f = file_get_contents($logs_folder.'/'.$ws_config['ddls'][$ddlid]['name']);
		$ids = $f;	
	}
	else
		foreach($ws_config['ddls'] as $ddl) {
			$f = file_get_contents($logs_folder.'/'.$ddl['name']);
			if($ids=='') $ids = $f;	
			else $ids .= ','.$f;
		}
	$ids = explode(',',$ids);
	$ids = array_unique($ids);
	$ids = array_filter($ids);
	$ids = implode(',',$ids);
	return $ids;
}
function wlog() {
	global $logs_folder;
	if(is_file($logs_folder.'/work')) { $myF=$logs_folder.'/work'; unlink($myF); $fhh = fopen($myF, 'w'); fclose($fhh); }
}
function submittoddls($_POST) {
	global $logs_folder,$logs_logs_folder,$ws_config;
	$data=array();$datab=array();$g=0;
	$data['sname']=$_POST['sname'];
	$data['surl']=$_POST['surl'];
	$data['email']=$_POST['email'];
	$data['title']=$_POST['title'];
	$data['url']=$_POST['url'];
	$data['type']=$_POST['type'];
	$data['wsubmitter']='wsubmitter';
	
	$datab['sitename']=$_POST['sname'];
	$datab['siteurl']=$_POST['surl'];
	$datab['siteemail']=$_POST['email'];
	$datab['title']=$_POST['title'];
	$datab['url']=$_POST['url'];
	$datab['type']=$_POST['type'];
	$datab['wsubmitter']='wsubmitter';
	
	$pids = implode(',',$_POST['pids']);
	$c = count($data['title']);
	if($g!=0) 
		foreach($ws_config['ddls'] as $key=>$ddl) {
			if($ddl['group']==$g) {
				send('Submiting to '.$ddl['name'].': ');
				if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab;
				else $dat = $data;
				$cts = allowed_cats($key,1);
				for($i=0;$i<$c;$i++) {
					if(!in_array($dat['type'][$i],$cts)) {
						unset($dat['type'][$i]);
						unset($dat['title'][$i]);
						unset($dat['url'][$i]);
					}
				}
				$dat['type'] = array_values($dat['type']);
				$dat['title'] = array_values($dat['title']);
				$dat['url'] = array_values($dat['url']);
				
				$cc = count($dat['title']);
				if($cc==0) {
					send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
					$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
					$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
					$tt = gett($ddl['type'],1);
					$page = rejectedlog(array($data['title'],$data['url'],$data['type']),$tt);
					$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
				}
				else {
					if(stristr($ddl['url'],'phaze.hk')) {
						for($ii=0;$ii<$cc;$ii++) {
							$dat['type[]'.$ii][] = $dat['type'][$ii];
							$dat['title[]'.$ii][] = $dat['title'][$ii];
							$dat['url[]'.$ii][] = $dat['url'][$ii];
						}
						unset($dat['type']);
						unset($dat['title']);
						unset($dat['url']);
					}
					$page = curl($ddl['url'],$dat);
					if($page=='Site Down') {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">'.$ddl['name'].' is down.</a>');
					}
					//check if accepted
					elseif(stristr($page,$ddl['accept_text'])) {
						$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
					}
					else {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name'].' or site is down. (Check the log for more info)</a>');
					}
				}
			send('<br />');
			}
		}
	else
		foreach($ws_config['ddls'] as $key=>$ddl) {
				send('Submiting to '.$ddl['name'].': ');
				if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab; else $dat = $data;
				$cts = allowed_cats($key,1);
				for($i=0;$i<$c;$i++) {
					if(!in_array($dat['type'][$i],$cts)) {
						unset($dat['type'][$i]);
						unset($dat['title'][$i]);
						unset($dat['url'][$i]);
					}
				}
				$dat['type'] = array_values($dat['type']);
				$dat['title'] = array_values($dat['title']);
				$dat['url'] = array_values($dat['url']);
				
				$cc = count($dat['title']);
				if($cc==0) {
					send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
					$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
					$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
					$tt = gett($ddl['type'],1);
					$page = rejectedlog(array($data['title'],$data['url'],$data['type']),$tt);
					$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
				}
				else {
					
					$page = curl($ddl['url'],$dat);
					if($page=='Site Down') {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">'.$ddl['name'].' is down.</a>');
					}
					//check if accepted
					elseif(stristr($page,$ddl['accept_text'])) {
						$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
					}
					else {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name'].' or site is down. (Check the log for more info)</a>');
					}
				}
			send('<br />');
		}
	send ("<script type=\"text/javascript\"> $(document).ready(function(){ $('#loading').hide();});</script>");
	send('<br /><h2>Done</h2>');
}
function submittoddl($_POST) {
	global $logs_folder,$logs_logs_folder,$ws_config;
	$data=array();$datab=array();
	
	$data['sname']=$_POST['sname'];
	$data['surl']=$_POST['surl'];
	$data['email']=$_POST['email'];
	$data['title']=$_POST['title'];
	$data['url']=$_POST['url'];
	$data['type']=$_POST['type'];
	$data['wsubmitter']='wsubmitter';
	$data['submit']='Submit Downloads';
	
	$datab['sitename']=$_POST['sname'];
	$datab['siteurl']=$_POST['surl'];
	$datab['siteemail']=$_POST['email'];
	$datab['title']=$_POST['title'];
	$datab['url']=$_POST['url'];
	$datab['type']=arraytolower($_POST['type']);
	$datab['submit']=$_POST['Submit'];
	$datab['wsubmitter']='wsubmitter';
	
	
	
	$pids = implode(',',$_POST['pids']);
	$ddll = $_POST['ddlid'];
	$ddl = $ws_config['ddls'][$ddll];
	send('Submiting to '.$ddl['name'].': ');

	if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab; else $dat = $data;
	$cts = allowed_cats($ddll,1);
	for($i=0;$i<$c;$i++) {
		if(!in_array($dat['type'][$i],$cts)) {
			unset($dat['type'][$i]);
			unset($dat['title'][$i]);
			unset($dat['url'][$i]);
		}
	}
	$dat['type'] = array_values($dat['type']);
	$dat['title'] = array_values($dat['title']);
	$dat['url'] = array_values($dat['url']);
	
	$cc = count($dat['title']);
	if($cc==0) {
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
			$ddlf = $logs_folder.'/'.$$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
			$myFile = $logs_logs_folder.'/'.$$ddl['name'].'_'.date('d-m-Y').'.html';
			$tt = gett($ddl['type'],1);
			$page = rejectedlog(array($data['title'],$data['url'],$data['type']),$tt);
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
		}
	else {	
		if(stristr($ddl['url'],'phaze.hk')) {
			for($ii=0;$ii<$cc;$ii++) {
				$dat['type[]'.$ii][] = $dat['type'][$ii];
				$dat['title[]'.$ii][] = $dat['title'][$ii];
				$dat['url[]'.$ii][] = $dat['url'][$ii];
			}
			unset($dat['type']);
			unset($dat['title']);
			unset($dat['url']);
		}
		$page = curl($ddl['url'],$dat);
		if($page=='Site Down') {
				$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
				$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
				send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">'.$ddl['name'].' is down.</a>');
		}
		//check if accepted
		elseif(stristr($page,$ddl['accept_text'])) {
			$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
			$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, "ddl accept: ".$ddl['accept_text']."\r\n\r\n".$page);fclose($fh);
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
		}
		else {
			$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name'].' or site is down. (Check the log for more info)</a>');
		}
	}
	send ("<script type=\"text/javascript\"> $(document).ready(function(){ $('#loading').hide();});</script>");
	send('<br /><h2>Done</h2>');
		
}
function allowed_cats($ddlid,$t=0) {
	global $ws_config;
	$cats = array();
	foreach($ws_config['cats'] as $cat) {
		if($ws_config['ddls'][$ddlid]['type']==0) {
			if($cat['type']!='XXX') if($t==0) $cats[]=$cat;	else $cats[]=$cat['type'];
		}
		elseif($ws_config['ddls'][$ddlid]['type']==2) {
			if($cat['type']=='XXX') if($t==0) $cats[]=$cat;	else $cats[]=$cat['type'];
		}
		elseif($ws_config['ddls'][$ddlid]['type']==1) {
			if($t==0) $cats[]=$cat;	else $cats[]=$cat['type'];
		}
		elseif($ws_config['ddls'][$ddlid]['type']==3) {
			if($cat['type']=='Music') if($t==0) $cats[]=$cat; else $cats[]=$cat['type'];
		}
		elseif($ws_config['ddls'][$ddlid]['type']==4) {
			if(($cat['type']!='XXX')&&($cat['type']!='Music')) if($t==0) $cats[]=$cat; else $cats[]=$cat['type'];
		}
	}	
	return $cats;
}
function clean ($string) {
	global $ws_config;
	$string = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $string);
	$string=stripslashes($string);
	$find=$ws_config['remove_words'];
	$string=str_ireplace($find,"",$string);
	$string = preg_replace('/\s{2,10}/',' ', $string);
	$string = trim($string);
	$string = cleann($string);
	return $string;
}
function cleann ($string) {
	global $ws_config;
	$string = preg_replace('/[^(\x20-\x7F)\x0A]*/','', $string);
	$string=stripslashes($string);
	$find=$ws_config['remove_chars'];
	$string=str_ireplace($find," ",$string);
	$string = preg_replace('/\s{2,10}/',' ', $string);
	$string = trim($string);
	return $string;
}

function rejectedlog($arr,$c) {
	$data = 'Allowed types for this ddl: '.$c;
	$data .= '<br /><br />You submitted: <br />';
	foreach($arr as $ar) {
		foreach($ar as $a) {
			$data .= $a.'<br />';	
		}
		$data .= '<br />';
	}
	$data .= '<br />No posts from allowed types were found.';
	return $data;
}
function arraytolower(array $array, $round = 0){
  return unserialize(strtolower(serialize($array)));
} 
function printr($arr) {
	$fh = fopen('inc/logs/debug.html','a') or die('can\'t open file');
	if(is_array($arr)) {
		foreach($arr as $key=>$ar) if(is_array($ar)) {fwrite($fh,$key." => "); printr($ar); fwrite($fh,"<br />");}
		else fwrite($fh,$key.' => '.$ar."<br />");	
	}
	else if($arr!='') fwrite($fh,$arr."<br />");	
	fclose($fh);
}
function submit($url, $query) {
	global $errno, $errstr,$ws_config;
	$uri = parse_url($url);
	if (!isset($uri['port']))
		$uri['port'] = 80;

	$req = "POST {$uri['path']} HTTP/1.1\r\n"
		 . "Host: {$uri['host']}\r\n"
		 . "Content-type: application/x-www-form-urlencoded\r\n"
		 . "User-Agent: ".$ws_config['user_agent']."\r\n"
		 . "Content-length: " . strlen($query) . "\r\n"
		 . "Connection: close\r\n\r\n"
		 . $query;

	$errno = 0;
	$errstr = '';

	if (!$fp = @fsockopen($uri['host'], $uri['port'], $errno, $errstr, $ws_config['timeout']))
		return false;
	fputs($fp, $req);
	$page = '';
	while (!feof($fp)) {
       $page .= fgets($fp, 128);
    }
	//$buff = fread($fp, 1024);
	//$ret = strpos($buff, '200 OK') ? true : false;

	fclose($fp);
	return $page;
}
function submit_to_all_no_curl($_POST) {
	global $logs_folder,$logs_logs_folder,$ws_config;
	$data=array();$datab=array();$g=0;
	$data['sname']=$_POST['sname'];
	$data['surl']=$_POST['surl'];
	$data['email']=$_POST['email'];
	$data['title']=$_POST['title'];
	$data['url']=$_POST['url'];
	$data['type']=$_POST['type'];
	$data['wsubmitter']='wsubmitter';
	
	$datab['sitename']=$_POST['sname'];
	$datab['siteurl']=$_POST['surl'];
	$datab['siteemail']=$_POST['email'];
	$datab['title']=$_POST['title'];
	$datab['url']=$_POST['url'];
	$datab['type']=$_POST['type'];
	$datab['wsubmitter']='wsubmitter';
	
	if(stristr($_POST['ddlid'],'g')) $g = str_ireplace('g','',$_POST['ddlid']);
	send('Submitting: <br />');
	$pids = implode(',',$_POST['pids']);
	$c = count($data['title']);
	if($g!=0) {
		foreach($ws_config['ddls'] as $key=>$ddl) {
			if($ddl['group']==$g) {
				send('Submitting to '.$ddl['name'].': ');
				if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab; else $dat = $data;
				$cts = allowed_cats($key,1);
				for($i=0;$i<$c;$i++) {
					if(!in_array($dat['type'][$i],$cts)) {
						unset($dat['type'][$i]);
						unset($dat['title'][$i]);
						unset($dat['url'][$i]);
					}
				}
				$dat['type'] = array_values($dat['type']);
				$dat['title'] = array_values($dat['title']);
				$dat['url'] = array_values($dat['url']);
				
				
				
				$cc = count($dat['title']);
				if($cc==0) {
					send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
					$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
					$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
					$tt = gett($ddl['type'],1);
					$page = rejectedlog(array($data['title'],$data['url'],$data['type']),$tt);
					$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
				}
				else {
					if(stristr($ddl['url'],'nexusddl.com')) $compile = "sitename={$dat['sitename']}&siteurl={$dat['siteurl']}&siteemail={$dat['siteemail']}";
					else $compile = "sname={$dat['sname']}&surl={$dat['surl']}&email={$dat['email']}";
					$i = 0;
					foreach ($dat['title'] as $key => $val) {
						if($val && $dat['url'][$key] && $dat['type'][$key]) {
							$i++;
							if(stristr($ddl['url'],'phaze.hk')) $compile .= "&title[]{$i}=$val&url[]{$i}={$dat['url'][$key]}&type[]{$i}={$dat['type'][$key]}";
							else $compile .= "&title[]=$val&url[]={$dat['url'][$key]}&type[]={$dat['type'][$key]}";
						}
					}
					
					$page = submit($ddl['url'], $compile);
					if(stristr($page,$ddl['accept_text'])) {
						$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
					} else {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name'].' or site is down. (Check the log for more info)</a>');
					
					}
				}
				send('<br />');
			}
		}
	}
	else {
		foreach($ws_config['ddls'] as $key=>$ddl) {
				send('Submitting to '.$ddl['name'].': ');
				if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab; else $dat = $data;
				$cts = allowed_cats($key,1);
				for($i=0;$i<$c;$i++) {
					if(!in_array($dat['type'][$i],$cts)) {
						unset($dat['type'][$i]);
						unset($dat['title'][$i]);
						unset($dat['url'][$i]);
					}
				}
				$dat['type'] = array_values($dat['type']);
				$dat['title'] = array_values($dat['title']);
				$dat['url'] = array_values($dat['url']);
				
				$cc = count($dat['title']);
				if($cc==0) {
					send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
					$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
					$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
					$tt = gett($ddl['type'],1);
					$page = rejectedlog(array($data['title'],$data['url'],$data['type']),$tt);
					$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
				}
				else {
					if(stristr($ddl['url'],'nexusddl.com')) $compile = "sitename={$dat['sitename']}&siteurl={$dat['siteurl']}&siteemail={$dat['siteemail']}";
					else $compile = "sname={$dat['sname']}&surl={$dat['surl']}&email={$dat['email']}";
					$i = 0;
					foreach ($dat['title'] as $key => $val) {
						if($val && $dat['url'][$key] && $dat['type'][$key]) {
							$i++;
							if(stristr($ddl['url'],'phaze.hk')) $compile .= "&title[]{$i}=$val&url[]{$i}={$dat['url'][$key]}&type[]{$i}={$dat['type'][$key]}";
							else $compile .= "&title[]=$val&url[]={$dat['url'][$key]}&type[]={$dat['type'][$key]}";
						}
					}
					$page = submit($ddl['url'], $compile);
					if(stristr($page,$ddl['accept_text'])) {
						$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
					}
					else {
						$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
						$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
						send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name'].' or site is down. (Check the log for more info)</a>');
					}
				}
			send('<br />');
		}
	}
	send ("<script type=\"text/javascript\"> $(document).ready(function(){ $('#loading').hide();});</script>");
	send('<br /><h2>Done</h2>');
}
function submit_no_curl($_POST) {
	global $logs_folder,$logs_logs_folder,$ws_config;
	$data=array();$datab=array();
	
	$data['sname']=$_POST['sname'];
	$data['surl']=$_POST['surl'];
	$data['email']=$_POST['email'];
	$data['title']=$_POST['title'];
	$data['url']=$_POST['url'];
	$data['type']=$_POST['type'];
	$data['wsubmitter']='wsubmitter';
	$data['submit']='Submit Downloads';
	
	$datab['sitename']=$_POST['sname'];
	$datab['siteurl']=$_POST['surl'];
	$datab['siteemail']=$_POST['email'];
	$datab['title']=$_POST['title'];
	$datab['url']=$_POST['url'];
	$datab['type']=arraytolower($_POST['type']);
	$datab['submit']=$_POST['Submit'];
	$datab['wsubmitter']='wsubmitter';
	
	
	
	$pids = implode(',',$_POST['pids']);
	$ddll = $_POST['ddlid'];
	$ddl = $ws_config['ddls'][$ddll];
	send('Submiting to '.$ddl['name'].': ');

	if(stristr($ddl['url'],'nexusddl.com')) $dat = $datab; else $dat = $data;
	$cts = allowed_cats($ddll,1);
	for($i=0;$i<$c;$i++) {
		if(!in_array($dat['type'][$i],$cts)) {
			unset($dat['type'][$i]);
			unset($dat['title'][$i]);
			unset($dat['url'][$i]);
		}
	}
	$dat['type'] = array_values($dat['type']);
	$dat['title'] = array_values($dat['title']);
	$dat['url'] = array_values($dat['url']);
	
	$cc = count($dat['title']);
	if($cc==0) {
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Failed: Not enough posts to submit after recount.</a>');
			$ddlf = $logs_folder.'/'.$$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
			$myFile = $logs_logs_folder.'/'.$$ddl['name'].'_'.date('d-m-Y').'.html';
			$tt = gett($ddl['type'],1);
			$page = rejectedlog(array($dat['title'],$dat['url'],$dat['type']),$tt);
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
		}
	else {	
		if(stristr($ddl['url'],'nexusddl.com')) $compile = "sitename={$dat['sitename']}&siteurl={$dat['siteurl']}&siteemail={$dat['siteemail']}";
		else $compile = "sname={$dat['sname']}&surl={$dat['surl']}&email={$dat['email']}";
		$i = 0;
		foreach ($dat['title'] as $key => $val) {
			if($val && $dat['url'][$key] && $dat['type'][$key]) {
				$i++;
				if(stristr($ddl['url'],'phaze.hk')) $compile .= "&title[]{$i}=$val&url[]{$i}={$dat['url'][$key]}&type[]{$i}={$dat['type'][$key]}";
				else $compile .= "&title[]=$val&url[]={$dat['url'][$key]}&type[]={$dat['type'][$key]}";
			}
		}
		$page = submit($ddl['url'], $compile);
		//check if accepted
		if(stristr($page,$ddl['accept_text'])) {
			$ddlf = $logs_folder.'/'.$ddl['name'];$fh = fopen($ddlf, 'a') or die("can't open file");if(filesize($ddlf)!=0) fwrite($fh, ','); fwrite($fh, $pids);fclose($fh);
			$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, "ddl accept: ".$ddl['accept_text']."\r\n\r\n".$page);fclose($fh);
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="accepted">Accepted to '.$ddl['name'].'</a>');
		}
		else {
			$myFile = $logs_logs_folder.'/'.$ddl['name'].'_'.date('d-m-Y').'.html';
			$fh = fopen($myFile, 'w') or die("can't open file");fwrite($fh, $page);fclose($fh);
			send('<a href="logs.php?log='.$ddl['name'].'_'.date('d-m-Y').'" target="_blank" class="rejected">Rejected to '.$ddl['name']. ' or site is down. (Check the log for more info)</a>');
		}
	}
	send ("<script type=\"text/javascript\"> $(document).ready(function(){ $('#loading').hide();});</script>");
	send('<br /><h2>Done</h2>');
		
}
?>