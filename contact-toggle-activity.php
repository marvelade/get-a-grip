<?php

	require("config.php");
	ob_start();
	
	
	$ct = new MarveladeContact(intval($_GET['c_id']));
	if(!$ct -> toggle_activity($_SERVER['HTTP_REFERER']))
	{
		$err_str = "?err=1";
	}
	else
	{
		$err_str = "";
	}
	$ct = null;
	
	header("Location:contacts-manage.php".$err_str);
?>