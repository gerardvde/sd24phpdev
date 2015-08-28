<?php


/*
 * Created on Mar 8, 2011
*
* To change the template for this generated file go to
* Window - Preferences - PHPeclipse - PHP - Code Templates
*/

require_once ('LWEVDBinclude.php');

require_once ('mailDocumentation.php');
require_once ('updateCase.php');
require_once ('deleteCase.php');
require_once ('updateTable.php');
require_once ('ignoreRecord.php');
require_once ('getCases.php');
require_once ('getBillCases.php');
require_once ('getTurnover.php');
require_once ('getContacts.php');
require_once ('setCaseStatus.php');
require_once ('updateCasemail.php');
require_once ('getCaseEstimates.php');
//Pasword functions
require_once('checkPassword.php');

$request = $_REQUEST['request'];
$partnerID=$_REQUEST['partnerID'];
$email=$_REQUEST['email'];
$table=$_REQUEST['table'];
$id=$_REQUEST['id'];
$zip = $_REQUEST['file'];
$compress = $_REQUEST['compress'];
$data = $_REQUEST['data'];
$id=$_REQUEST['id'];
if (get_magic_quotes_gpc()) {
	$data = stripslashes($data);
}
switch ($request) {
	case 'getfieldnames':
		$table=$_REQUEST['table'];
		$names=getFieldnames($table);
		echo json_encode($names);
		break;
	case 'ignorerecord':
		ignoreRecord($request,$table,$id);
		break;
	case 'getpartner':
		getPartner($request,$email);
		break;
	case 'getclient':
		getClient($request,$partnerID);
		break;
	case 'getobject':
		getBuilding($request,$partnerID);
		break;
	case 'getexpert':
		getExpert($request,$partnerID);
		break;
	case 'lockcase':
		lockCase($request,$_REQUEST);
		break;
	case 'getip':
		getIp($request);
		break;
	case 'gettable':
		getTable($request,$table);
		break;
	case 'gettables':
		getTables($request,$_REQUEST);
		break;
	case 'getrecord':
		getTable($request,$table,$id);
		break;
	case 'updatetable':
		updateTable($request, $data,$table);
		break;
	case 'deleterecord':
		deleteRecord($request, $id,$table);
		break; 
	case 'getpassword':
		getPassword($request,$_REQUEST);
		break;
	case 'sendpassword':
		sendPassword($request,$_REQUEST);
		break;
	case 'checkpassword':
		checkPassword($request,$_REQUEST);
		break;
	case 'checkusername':
		checkUsername($request,$_REQUEST);
		break;
	case 'sendmessage':
		sendMessage($request,$_REQUEST);
		break;
	case 'senderror':
		sendError($request,$_REQUEST);
		break;
	case 'senddocumentation':
		require_once ('sendDocumentation.php');
		sendDocumentation($request,$_REQUEST);
		break;
	case 'maildocumentation':
		//mailDocumentation($request,$_REQUEST);
		require_once ('sendRESTDocu.php');
		sendRESTDocu($request,$_REQUEST);
		break;
	case 'maildocurest':
		require_once ('sendRESTDocu.php');
		sendRESTDocu($request,$_REQUEST);
		break;
	case 'updatecasemail':
		updateCasemail($request,$_REQUEST);
		break;
	case 'updatecase':
		updateCase($request,$_REQUEST);
		break;
	case 'deletecase':
		deleteCase($request,$_REQUEST);
		break;
	case 'getcases':
		getCases($request,$_REQUEST);
		break;
	case 'getmediatorcases':
		getMediatorCases($request,$_REQUEST);
		break;
	case 'getbillcases';
		getBillCases($request,$_REQUEST);
		break;
	case 'getturnover';
		getTurnover($request,$_REQUEST);
		break;
	case 'getcase':
		$caseID=$_REQUEST['caseID'];
		getCase($request,$caseID);
		break;
	case 'getdirfiles':
		getDirFiles($request,$_REQUEST['casenr']);
		break;
	case 'getcasemail':
		getCasemail($request,$_REQUEST['caseID']);
		break;
	case 'getcasemessages':
		getCaseMessages($request,$_REQUEST['partnerID']);
		break;
	case 'getcaseestimates':
		getCaseEstimates($request,$_REQUEST['caseID']);
		break;
	case 'getcasedirs':
		getDirFiles($request,"");
		break;
	case 'getcontacts':
		getContacts($request,$_REQUEST);
		break;
	case 'getcontactemployee':
		getContactEmployee($request,$_REQUEST);
		break;
	case 'upload':
		uploadFile($_REQUEST,  $_FILES);
		break;
	case 'writelog':
		writeLog($request, $_REQUEST);
		break;
	case 'writebug':
		writeBug($request, $_REQUEST);
		break;
	case 'getbugs':
		getTable($request,'bugs');
		break;
	case 'setcasestatus':
		setCaseStatus($request,$_REQUEST);
		break;
	case 'getpartnerbills';
		require_once ('getPartnerbills.php');
		getPartnerbills($request,$_REQUEST);
		break;
	case 'createpartnerbills':
		require_once ('createPartnerBills.php');
		createPartnerBills($request,$_REQUEST);
		break;
	case 'createpartnerbillspdf':
		require_once ('createPartnerBillsPDF.php');
		createPartnerBillsPDF($request,$_REQUEST);
		break;
	case 'getcrediting';
		require_once ('getCrediting.php');
		getCrediting($request,$_REQUEST);
		break;
	case 'getmediatorcrediting';
		require_once ('getCrediting.php');
		getMediatorCrediting($request,$_REQUEST);
		break;
		
	default :
		sendRequestError($request, 'invalidrequest');
}



function generatePassword($text)
		{
			$ciffre=0;
			$ar=str_split($text);
			$i=count($ar)-1;
			while($i>=0)
			{
				$ciffre+=ord($ar[$i])*1013;
				$i--;
			}
			return dechex($ciffre);
		}
function uploadfile($data,$files)
{
	if (!is_uploaded_file($files['Filedata']['tmp_name'])) {

		sendRequestError('uploadfile', 'nofiletouploaded');

	}
	$request=$data['request'];
	$case=$data['casenr'];
	$filename = $files['Filedata']['name'];

	$uploadfile = FILEDIR."$case/$filename";
	
	$filedir=pathinfo($uploadfile,PATHINFO_DIRNAME );
	if(!file_exists($filedir))
	{
		mkdir($filedir, 0777, true);
	}
	if(!file_exists($filedir))
	{
		sendRequestError('uploadfile', 'dirnotcreated');
	}
	if (move_uploaded_file($files['Filedata']['tmp_name'], $uploadfile)) {
		if(file_exists($uploadfile))
		{
			chmod($uploadfile, 0666);
			
			sendRequestResult($request, $uploadfile);
		}
		else {
			sendRequestError($request, $uploadfile.'filenotmoved');
		}

	} else {

		sendRequestError($request, $uploadfile.'filenotuploaded');

	}

}
function getCasemail($request,$caseID)
{
	global $db;
	$sql ="SELECT * FROM caseMail WHERE caseID=\"$caseID\"";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json['caseMail']=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json['caseMail'][] = $record;
	}
	$directory = FILEDIR.$caseID.'/documents';
	 $json['files']=Array();
	if ($handle = opendir($directory)) 
	{
   		while (false !== ($file = readdir($handle))) 
   		{
        		if ($file != "." && $file != "..") {
          		 $json['files'][] =$file;
          	 }
        	}
    }
     closedir($handle);
   sendRequestResult($request,  json_encode($json));
}
function getDirFiles($request,$case)
{
	$directory = FILEDIR.$case;
	 $json['files']=Array();
	if ($handle = opendir($directory)) 
	{
   		while (false !== ($file = readdir($handle))) 
   		{
        		if ($file != "." && $file != "..") {
          		 $json['files'][] =$file;
          	 }
        	}
    }
     closedir($handle);
  	 sendRequestResult($request,  json_encode($json));
}
function getIp($request)
{
	$ip=$_SERVER['REMOTE_ADDR'];
  	 sendRequestResult($request,  $ip);
}
function lockCase($request,$data){
	global $db;
	
	$id=$data['caseID'];
	$ip=$data['ip'];
	
	$sql = "UPDATE  `case`  SET ip=\"$ip\" WHERE id=\"$id\"";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: caese sql:  $sql error: $error");
	}
	else
	{
		sendRequestResult($request, 'OK');
	}
}

function sendError($request,$data)
{
	$mailto='gerard@vandenelzen.de';
	$subject = utf8_decode('LWEV Error') ;
	$message=$data['message'];
	$message=str_replace("\n","<br>",$message);
	$mailhtml =<<<EOH
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>LWEV ERrormessage</title>
	<style type="text/css">
	<!--
	body {
		background-color: #EEEEEE;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #333333;
	}
	-->
	</style>
	</head>
	<body>
	<p><h2>Error message</h2></p>
	$message
	<p><br>
    </body>
	</html>
EOH;
	$body = $mailhtml;
	$headers = "From: $mailfrom\r\n";
	$headers.="Cc: $mailcc\r\n";
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
	if (!mail($mailto, $subject, $body, $headers)) {
		sendRequestError($request, 'mailfailed');
	} else {
		sendRequestResult($request, 'OK');
	}
}
function sendMessage($request,$data)
{
	$namefrom = $data['firstname'].' '.$data['lastname'];
	$email=$data['email'];
	if(!isset($email) || $email=='')
	{
		$email='info@immobilienschadenservice.de';
	}
	$mailfrom = "$namefrom<$email>";
	$mailfrom=utf8_decode($mailfrom);
	$namefrom=urlencode(utf8_decode($namefrom));
	//$mailto='info@immobilienschadenservice.de';
	$mailto='gerard@vandenelzen.de';

	$mailcc='gerard@vandenelzen.de';
	$subject = utf8_decode('LWEV Test') ;
	$message=$data['message'];
	$message=urldecode($message);
	$message = urlencode(utf8_decode($message),ENT_NOQUOTES,UTF-8);

	$message=str_replace("\n","<br>",$message);
	$mailhtml =<<<EOH
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>LWEV</title>
	<style type="text/css">
	<!--
	body {
		background-color: #EEEEEE;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #333333;
	}
	-->
	</style>
	</head>
	<body>
	<p><h2>Schadendokumentation</h2></p>

	<p>$namefrom<br>
	
	Bericht:<br>
	$message
	<p><br>
    </p>
    <table width="100%" border="0">
    	$costRows
	<table>
    </body>
	</html>
EOH;
	$body = $mailhtml;
	$headers = "From: $mailfrom\r\n";
	$headers.="Cc: $mailcc\r\n";
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
	if (!mail($mailto, $subject, $body, $headers)) {
		sendRequestError($request, 'mailfailed');
	} else {
		sendRequestResult($request, 'OK');
	}
}



function sendPassword($request,$data) {
	$user=$data['email'];
	if(!isset($user) || $user=='')
	{
		sendRequestError($request, 'mailfailed');
	}
	global $db;
	$sql ="SELECT * FROM technician where username=\"$user\" OR email=\"$user\"" ;	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'email not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==0)
	{
		$sql ="SELECT * FROM partner where email=\"$user\" " ;		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, 'email not in DB'.$sql );
		};
		if( mysql_num_rows ( $sqlresult )==0)
		{
			sendRequestError($request, 'email not in DB'.$sql );
		}
		$record=mysql_fetch_assoc($sqlresult);
		$email=$record['email'];
		$record['partnerID']=$record['id'];
		$record['username']=$record['email'];
		$record['laststname']='admin';
		$record['userlevel']=5;
		unset($record['id']);
		$pw=generatePassword($email);
		$password=generatePassword($pw);
		$tabelfields=getFieldnames("technician");
		foreach ($tabelfields as $tabelfield) {
			if(isset($record[$tabelfield])){
				$fields[]=$tabelfield;
				}
		}
		foreach ($fields as $field) {
			$columns[] = "`".$field."`";
			$value = enquote($record[$field]);
			$values[] = $value;
		}

		$recordcols = implode($columns, ',');
		$recordvals = implode($values, ',');
		$sql = "INSERT INTO  technician ($recordcols) VALUES ($recordvals)";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, 'email not in DB'.$sql );
		};
		$sql ="SELECT * FROM technician where  email=\"$user\"" ;		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, 'email not in DB'.$sql );
		};
		if( mysql_num_rows ( $sqlresult )==0)
		{
			sendRequestError($request, 'email not in DB'.$sql );
		}
	}
while ($record = mysql_fetch_assoc($sqlresult)) {

	$email=$record['email'];
	$id=$record['id'];
	$pw=generatePassword($email);
	$password=generatePassword($pw);
	$sql ="UPDATE technician SET password=\"$password\"  WHERE  id=$id" ;	
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'email not in DB'.$sql );
	};
}
	$mailfrom = "$email<$email>";
	
	$mailto=$email;
	$mailcc='gerard@vandenelzen.de,info@immobilienschadenservice.de';
	$subject = utf8_decode('Passwort Anfrage') ;
	$mailhtml =<<<EOH
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>LWEV Passwort-Anfrage</title>
	<style type="text/css">
	<!--
	body {
		background-color: #EEEEEE;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #333333;
	}
	-->
	</style>
	</head>
	<body>
	<p><h2>Passwort-Anfrage $source</h2></p>
	Email: $email<br>
	Password: $pw<br>

	<p><br>
    </p>
    </body>
	</html>
EOH;
	$body = $mailhtml;
	$headers = "From: $mailfrom\r\n";
	$headers.="Cc: $mailcc\r\n";
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
	if (!mail($mailto, $subject, $body, $headers)) {
		sendRequestError($request, 'mailfailed');
	} else {
		sendRequestResult($request, 'OK');
	}
}

	
function getPassword($request,$data) {

	$email=$data['email'];
	$source=$data['source'];
	if(!isset($email) || $email=='')
	{
		sendRequestError($request, 'mailfailed');
	}
	global $db;
	$sql ="SELECT email FROM partner where email=\"$email\" ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'email not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==0)
	{
		sendRequestError($request, 'email not in DB'.$sql );
	}
	switch($source)
		{
		case 'HC':
			$source='HausCheck';
			break;
		default:
			$source="";
		}
	
	$mailfrom = "$email<$email>";
	$pw=generatePassword($email);
	$mailto=$email;
	$mailcc='gerard@vandenelzen.de,info@immobilienschadenservice.de';
	$subject = utf8_decode('Passwort Anfrage') ;
	$mailhtml =<<<EOH
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>LWEV Passwort-Anfrage</title>
	<style type="text/css">
	<!--
	body {
		background-color: #EEEEEE;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #333333;
	}
	-->
	</style>
	</head>
	<body>
	<p><h2>Passwort-Anfrage $source</h2></p>
	Email: $email<br>
	Password: $pw<br>

	<p><br>
    </p>
    </body>
	</html>
EOH;
	$body = $mailhtml;
	$headers = "From: $mailfrom\r\n";
	$headers.="Cc: $mailcc\r\n";
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
	if (!mail($mailto, $subject, $body, $headers)) {
		sendRequestError($request, 'mailfailed');
	} else {
		sendRequestResult($request, 'OK');
	}
}
function getPartner($request,$email) {
	global $db;
	$sql ="SELECT * FROM partner where email=\"$email\" AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $sql. $error);
	};

	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json['partner'][] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getTables($request,$data) {
	global $db;
	$partnerID=$data['partnerID'];
	$tables=explode(",",$data['tables']);
	foreach($tables as $table)
	{
	$where=" WHERE `ignore` = 0 ";

	if(isset($partnerID))
	{
		$tabelfields=getFieldnames($table);
				foreach ($tabelfields as $tabelfield) {
			if($tabelfield=='partnerID')
			{
				$where=" WHERE partnerID=\"$partnerID\" AND  `ignore` = 0 ";
			}
		}
		}
		$sql ="SELECT * FROM $table $where";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, $table . $error);
		};
		$json[$table]=array();
		while ($record = mysql_fetch_assoc($sqlresult)) {
			foreach ($record as $key => $value) {
				$record[$key] = urlencode(($value));
			}
			$json[$table][] = $record;
		}
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getExpert($request,$partnerID) {
	global $db;
	$where="";
	$table='expert';
	$where=" WHERE `ignore` = 0 ";
	if(isset($partnerID))
	{
		$where=" WHERE partnerID=\"$partnerID\" AND  `ignore` = 0 ";
	}
	$sql ="SELECT * FROM $table $where";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json[$table]=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json[$table][] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getBuilding($request,$partnerID) {
	global $db;
	$table='building';
	$where=" WHERE `ignore` = 0 ";
	if(isset($partnerID))
	{
		$where=" WHERE partnerID=\"$partnerID\" AND `ignore` = 0 ";
	}
	$sql ="SELECT * FROM $table $where";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json[$table]=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json[$table][] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getClient($request,$partnerID) {
	global $db;
	$table='client';
	$where=" WHERE `ignore` = 0 ";	
	if(isset($partnerID))
	{
		$where=" WHERE partnerID=\"$partnerID\" AND  `ignore` = 0";
	}
	$sql ="SELECT * FROM $table $where";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json[$table]=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json[$table][] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getTable($request,$table,$id) {
	global $db;
	$where=" WHERE `ignore` = 0";
	if(isset($id))
	{
		$where=" WHERE id=\"$id\" AND  `ignore` = 0";
	}
	$sql ="SELECT * FROM $table $where";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json[$table]=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json[$table][] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}
function getCaseMessages($request,$partnerid) {
	global $db;
	$where="";
	if(isset($id))
	{
		$where=" WHERE partnerID=\"$partnerid\" ";
	}
	$sql ="SELECT * FROM caseMessage $where";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, $table . $error);
	};
	$json=array();
	while ($record = mysql_fetch_assoc($sqlresult)) {
		foreach ($record as $key => $value) {
			$record[$key] = urlencode(($value));
		}
		$json[] = $record;
	}
	sendRequestResult($request, json_encode($json));
	exit ();
}

function writeLog($request,$record){
	global $db;
	global $request;
	$fields=Array();
	mysql_query('SET NAMES utf8', $db);
	$tabelfields=getFieldnames("errorlog");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}

	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "INSERT INTO  `errorlog`  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	sendRequestResult($request, 'ok'.$recordcols);
}
function writeBug($request,$record){
	global $db;
	global $request;
	$fields=Array();
	mysql_query('SET NAMES utf8', $db);
	$tabelfields=getFieldnames("bugs");
	foreach ($tabelfields as $tabelfield) {
	if(isset($record[$tabelfield])){
		$fields[]=$tabelfield;
		}
	}

	foreach ($fields as $field) {
		$columns[] = "`".$field."`";
		$value = enquote($record[$field]);
		$values[] = $value;
	}
	$recordcols = implode($columns, ',');
	$recordvals = implode($values, ',');
	$sql = "INSERT INTO  `bugs`  ($recordcols) VALUES ($recordvals)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "update: $table sql:  $sql error: $error");
	};
	sendRequestResult($request, 'ok'.$recordcols);
}

?>


