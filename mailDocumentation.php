<?php
require("mailer.php");
function mailDocumentation($request,$data)
{	
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
				case 'partner':
					$partner=$value;
					break;
				case 'mailto':
					$mails=$value;
					break;
				case 'docs':
					$docsArray=$value;
					break;		
				case 'mailheader':
					$mailheader=$value;
					break;
				case 'mailfooter':
					$mailfooter=$value;
					break;
				case 'mailinfo':
					$mailinfo=$value;
					break;		
				case 'caseID':
					$caseID=$value;
					break;
				case 'mailsubject':
					 $subject=$value;
					 break;
			}
		}
	}

if(!isset($caseID))
{
	sendRequestError($request, 'Keine Vorgangskennung'.$keys);
}
foreach($mails as $madress)
	{
		foreach($madress as $key=>$value)
		{
			$mailtos[$key]=$value;		
		}
}
$logo=$partner['logo'];
$logo=LOGOSURL.$logo;
$logo="'$logo'";
$partnername= $partner['name'];
$street= $partner['street'];
$nr= $partner['nr'];
$zip= $partner['zip'];
$town= $partner['town'];
$telephone= $partner['telephone'];
$fax= $partner['fax'];
$partneremail= $partner['email'];
$homepage= $partner['homepage'];
$docsdir = FILEDIR.$caseID.'/documents/';
$mailfrom=$partneremail;
$mailhtml=$subject."\n\n";
$mailhtml .=$mailheader."\n";
$mailhtml .=$mailfooter."\n";
$mailhtml .=$partnername."\n";
$mailhtml .="$street $nr\n$zip  $town \n";
$mailhtml .="$telephone \n$fax \n$partneremail $homepage\n\n";
$mailhtml .=$mailinfo;

$mail = new mailer;
	//$mail->Mailer="smtp";

	$mail->From = $mailfrom;

	$mail->FromName = $partner['name'];
	foreach($mailtos as $key=>$value)
	{
		$mail->AddAddress($value, "");	
	}
	$mail->AddAddress($mailfrom, $partner['name']);
	$mail->AddAddress("controlling@schadendienst24.de", 'schadendienst24');

	$mail->AddReplyTo($mailfrom, $partner['name']);
	
	//$mail->WordWrap = 200;    // set word wrap
	foreach( $docsArray as $doc)
	{
		$file=$docsdir.$doc;
		$rfile=realpath($file);
		$exists=file_exists($file);
		if($exists)
		{
			$mail->AddAttachment($file);
		}
	}

	$mail->IsMail();

	$mail->IsHTML(false);    // set email format to HTML

	$mail->Subject = html_entity_decode($subject);

	$mail->Body = html_entity_decode($mailhtml);
	$mail->Request = $request;

	$mail->Send(); // send message

	sendRequestResult($request, 'OK');
}
?>