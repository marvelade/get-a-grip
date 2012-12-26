<?php

try
{
	require_once("config.php");

	$dbh = new PDO(	'mysql:host=' . Settings::get('DB_HOST') . ';dbname=' . Settings::get('DB_NAME') , 
					Settings::get('DB_USER') , 
					Settings::get('DB_PASS') , 
					array(PDO::ATTR_PERSISTENT => true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, 1);
	Settings :: set('dbh' ,$dbh);
} 
catch(PDOException $e)
{
	echo 'Error connecting to MySQL!: '.$e->getMessage();
	exit();
}

?>