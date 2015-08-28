<?php
error_reporting(1);
define('FILEDIR','../DATEN/');
define('BASEFILESURL','http://web2.23204.whserv.de/sd24/DATEN/');
define('LOGOSURL','http://web2.23204.whserv.de/sd24/DATEN/logos/');
define('DEFAULT_BILLNR',1);
define('CREATION_FEE',8);
define('BILL',"Rechnung");
define('CREDIT',"Gutschrift");

define('CASE_FEE',1);
define('ADD_FEE',2);
define('CASE_PROVISION',3);

$host = "localhost";
$user = "web2";
$pwd = "GvdE*2014*";
$port = "";
$sys_dbname = "web2_db1";
if (!$db = mysql_connect($host, $user, $pwd)) {
	sendRequestError("", 'dbnotconnected');
} else {
	if (!mysql_select_db($sys_dbname, $db)) {
		sendRequestError("",'dbnotselected');
	}
}
function enquote($item) {
	$item=addslashes( ($item));
	$item = "'".$item."'";
	return $item;
}
function getDirContent($dir)
{
	if ($handle = opendir($dir)) 
	{
   		while (false !== ($file = readdir($handle))) 
   		{
        		if ($file != "." && $file != "..") {
          	 	$files[] =$file;
          	 }
        	}
    }
     closedir($handle);
  	return  $files;
  }
function sendRequestResult($request, $data) {
	global $compress;
	$result = '<?xml version="1.0" encoding="UTF-8" ?>';
	if($compress==1)
	{
		$data=gzdeflate($data,9);
		$data= base64_encode ( $data );		
	}
	$result .= "<result>\n<status>ok</status>\n<request>$request</request>\n<data><![CDATA[$data]]></data><zlib>$compress</zlib>\n</result>\n";
	writeMessageLog("Result $result");
	echo $result;
	exit ();
}
function sendRequestError( $request, $error) {
	$result = '<?xml version="1.0" encoding="UTF-8" ?>';
	$result .= "<result>\n<status>error</status>\n<request>$request</request>\n<data>  <![CDATA[$error]]></data>\n</result>\n";
	writeMessageLog("Error $result");
	echo $result;
	exit ();
}
function getFieldnames($table) { 
	global $db;
	$fieldnames=array(); 
	$sql = "SHOW COLUMNS FROM `$table`";
     $result = mysql_query($sql,$db); 
     if (!$result) { 
        return $fieldnames;
      } 
      if (mysql_num_rows($result) > 0) { 
        while ($row = mysql_fetch_assoc($result)) { 
          $fieldnames[] = $row['Field']; 
        } 
      } 
      return $fieldnames; 
} 
function getFieldnamesObject($table) { 
	global $db;
	$fieldnames=array(); 
	$sql = "SHOW COLUMNS FROM `$table`";
     $result = mysql_query($sql,$db); 
     if (!$result) { 
        return $fieldnames;
      } 
      if (mysql_num_rows($result) > 0) { 
        while ($row = mysql_fetch_assoc($result)) { 
          $fieldnames[$row['Field']]=true; 
        } 
      } 
      return $fieldnames; 
} 
function sendErrormail($request, $error) {


	$mailto='gerard@vandenelzen.de';
	$mailfrom='gerard@vandenelzen.de';
		$subject = utf8_decode('Errorr Mail') ;
	$mailhtml =<<<EOH
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Error ISS</title>
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
	<p><h2>Error</h2></p>
	Request: $request<br>
	Error: $error<br>

	<p><br>
    </p>
    </body>
	</html>
EOH;
	$body = $mailhtml;
	$headers = "From: $mailfrom\r\n";
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
	mail($mailto, $subject, $body, $headers);
	}
	//
function checkJSON($json)
{
	$jsonArray=json_decode($json,true);
	if(json_decode($json,true) == NULL)
	{
		$message.='Invalid JSON<br>';
		switch (json_last_error()) {
       		 	case JSON_ERROR_NONE:
           	 	$message.= ' - No errors';
        			break;
        case JSON_ERROR_DEPTH:
            		$message.= ' - Maximum stack depth exceeded';
        			break;
        case JSON_ERROR_STATE_MISMATCH:
           		 $message.= ' - Underflow or the modes mismatch';
        			break;
        case JSON_ERROR_CTRL_CHAR:
            		$message.= ' - Unexpected control character found';
        			break;
        case JSON_ERROR_SYNTAX:
            		$message.= ' - Syntax error, malformed JSON';
        			break;
        case JSON_ERROR_UTF8:
            		$message.= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        			break;
        default:
           	 	$message.= ' - Unknown error';
        			break;
    		}
    		sendRequestError($request, 'JSONError'. $message);
	}
	return $jsonArray;
}

function multiSort() { 
    //get args of the function 
    $args = func_get_args(); 
    $c = count($args); 
    if ($c < 2) { 
        return false; 
    } 
    //get the array to sort 
    $array = array_splice($args, 0, 1); 
    $array = $array[0]; 
    //sort with an anoymous function using args 
    usort($array, function($a, $b) use($args) { 

        $i = 0; 
        $c = count($args); 
        $cmp = 0; 
        while($cmp == 0 && $i < $c) 
        { 
            $cmp = strcmp($a[ $args[ $i ] ], $b[ $args[ $i ] ]); 
            $i++; 
        } 

        return $cmp; 

    }); 
    return $array; 
} 
function saveMessageLog($filename)
{
	global $messages;
	if(!isset($filename))
	{
		$filename='L'.date('Ymd').'.log';
	}
	$logdir='logs';
	if(!file_exists ($logdir ))
	{
		mkdir ($logdir);
	}
	if(file_exists ($logdir ))
	{
	$filename=$logdir.'/'.$filename;
	}
	$nr=1;
	$path_parts = pathinfo($filename);
	$basename=$path_parts['basename'];
	$path=$path_parts['dirname'];
	$tmpArray=explode('.',$basename);
	$basename=$tmpArray[0];
	if(count($tmpArray)==1)
	{
		$extension='log';
	}
	else
	{
		$extension=$tmpArray[1];
	}
	while(file_exists ($filename ))
	{
		$newbasename=$basename.'_'.$nr;
		$filename= "$path/$newbasename.$extension";
		$nr++;
	}
	
	file_put_contents (  $filename , $messages );
}
function writeMessageLog($message)
{

	global $messages;
	$messages.="$message\n";
}
?>