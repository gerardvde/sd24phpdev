<?php
function deleteCase($request,$data)
{
	$caseID=	$data['id'];
	if (!isset ($caseID) || $caseID == "" ) {
		sendRequestError($request, 'no case id');
	}
	global $db;
	mysql_query('SET NAMES utf8', $db);
	$sql = "UPDATE `case` SET `ignore`=1 WHERE id=\"$caseID\"";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "delete case:  sql:  $sql error: $error");
	};
	$reltables=Array('caseTech','caseClient','caseObject','caseExpert','caseClerk','caseTask','caseDamage','caseImage','caseCost');
	foreach($reltables as $reltable)
		{
			$sql = "UPDATE $reltable SET  `ignore`=1 WHERE caseID=\"$caseID\"";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "delete case:  sql:  $sql error: $error");
			};
		}
	$dataFolder = FILEDIR.$caseID;
	if(is_dir($dataFolder))
	{
		rrmdir($dataFolder);
	}
	
	sendRequestResult($request,  $caseID);	
}
function rrmdir($dir) { 
  		foreach(glob($dir . '/*') as $file) { 
    			if(is_dir($file)) rrmdir($file); else unlink($file); 
  			} 
  			rmdir($dir); 
}

?>