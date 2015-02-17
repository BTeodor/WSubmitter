<?
echo '<tr id="drow'.$pid.'" tid="'.$pid.'"><td class="delete" style="cursor:pointer;width:2%;"><img src="inc/images/cross.gif" /></td><td class="ids" style="width:2%">'.$i.'</td><td style="width:42%;"><input type="hidden" name="pids[]" value="'.$pid.'" /><input style="width:90%;" type="text" value="'.$ptitle.'" name="title[]" /></td><td style="width:42%;"><input style="width:90%;" type="text" value="'.$purl.'" name="url[]" /></td><td style="width:12%;">';
require('form_types.php');
echo '</td></tr>';
?>