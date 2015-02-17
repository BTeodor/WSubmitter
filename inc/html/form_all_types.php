<?
echo '<select id="alltypes" style="width:100%;"><option selected="selected">Select All</option>';
foreach($allowed_cats as $allowed_cat) echo '<option value="'.$allowed_cat.'">'.$allowed_cat.'</option>';
echo '</select>';
?>