<?php 
	$db = mysqli_connect('127.0.0.1','root','','app');
	if (mysqli_connect_errno()) {
		echo 'Database connection failled with following errors: '. mysqli_connect_errno();
		die();
	}

	require_once $_SERVER['DOCUMENT_ROOT'].'/Ecom/config.php';
	require_once BASEURL.'helpers/helpers.php';
 ?>