<?php

try
{
	require_once("config.php");

	$dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME , DB_USER , DB_PASS , array(PDO::ATTR_PERSISTENT => true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, 1);
} 
catch(PDOException $e)
{
	echo 'Error connecting to MySQL!: '.$e->getMessage();
	exit();
}

?>