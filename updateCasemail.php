<?php
function updateCasemail($request,$data)
{	
	global $db;
	$json=$data['json'];
	$json=stripslashes($json);
	$json= htmlentities(utf8_decode($json),ENT_NOQUOTES,UTF-8);
	$jsonArray=checkJSON($json);
	$keys="";
	foreach($jsonArray  as $jsonObject)
	{
		foreach ($jsonObject as $key=>$value)
		{
			$keys.=" $key";
			switch($key)
			{
				case 'mailto':
					$mails=$value;
					foreach($value as $madress)
					{
						foreach($madress as $key=>$value)
							{
								$mailtos[$key]=$value;							}
					}
					break;
				case 'docs':
					$docs=$value;
					break;						
				case 'caseID':
					$caseID=$value;
					break;
			}
		}
	}

	if(!isset($caseID))
	{
		sendRequestError($request, 'Keine Vorgangskennung'.$keys);
	}
	foreach($mailtos as $key=>$value)
	{
		$email=$value;	
		if($email=="")
			continue;
		foreach($docs as $doc)
		{
			$sql = "INSERT INTO  `caseMail`  (caseID,email,docname) VALUES (\"$caseID\",\"$email\",\"$doc\")";
			$sqlresult = mysql_query($sql, $db);
			if (!$sqlresult) {
				$error = mysql_error($db);
				sendRequestError($request, "insert: caseMail sql:  $sql error: $error");
			};
		}
	}
	sendRequestResult($request, 'OK');
}
?>