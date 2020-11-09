<?php
define('IN_AOS', true);
require('includes/aoshop.php');
if(is_mobile())
{
    require('mobile/'.$action.'.php');
}
else
{
	require('module/'.$action.'.php');
}
?>