<?php
define('FILEDIR','../DATEN/');
$uploadfile = FILEDIR."testdir/gerard";
	
	$filedir=pathinfo($uploadfile,PATHINFO_DIRNAME );
	if(!file_exists($filedir))
	{
		mkdir($filedir, 0777, true);
	}
	if(!file_exists($filedir))
	{
		echo "dirnotcreated. $filedir";
	}
?>