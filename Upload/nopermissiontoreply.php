<?php
define('IN_MYBB', 1); require "./global.php";


add_breadcrumb("No permission to reply", "nopermissiontoreply.php");
$lang->load("ratio");
eval("\$page = \"".$templates->get("nopermtoreply_template")."\";"); 
output_page($page); 
?>
