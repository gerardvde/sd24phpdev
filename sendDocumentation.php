<?php

function sendDocumentation($request,$data)
{
	$updatecase=$data['updatecase'];
	$case=$data['casenr'];
	$version=$data['version'];
	$namefrom = $data['firstname'].' '.$data['lastname'];
	$email=$data['email'];
	$emailsv=$data['emailsv'];
	if(!isset($email) || $email=='')
	{
		$email='info@immobilienschadenservice.de';
	}
	$mailfrom = "$namefrom<$email>";
	$mailfrom=utf8_decode($mailfrom);
	$namefrom=htmlentities(utf8_decode($namefrom));
	$mailto='info@immobilienschadenservice.de';
	$mailcc="gerard@vandenelzen.de,$mailfrom";
	$subject = utf8_decode('LWEV Auftrag: '.$case) ;
	$json=$data['json'];
	
	$json=stripslashes($json);
	$json= htmlentities(utf8_decode($json),ENT_NOQUOTES,UTF-8);
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

	}
	else
	{
		$jsonArray=json_decode($json,true);
	}
	updateCase($request,$_REQUEST);
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
	<p>$version </p>

	<p><H3>Neue Schadenmeldung $case</H3> </p>
>

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


?>