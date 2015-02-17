<?
echo '<select style="width:100%;" class="selects" name="type[]" ><option value="'.$ptype.'" selected="selected">'.$ptype.'</option>';
foreach($allowed_cats as $allowed_cat) echo '<option value="'.$allowed_cat.'">'.$allowed_cat.'</option>';
echo '</select>';
?>