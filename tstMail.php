<?php
require_once ('LWEVDBinclude.php');
require("mailer.php");
tstMail();
function tstMail()
{
$mailheader = <<<EOD1
Sehr geehrte Damen und Herren,
anbei erhalten Sie die Dokumente zum Schadenfall zur weiteren Bearbeitung.
EOD1;
$mailfooter= <<<EOD2
Mit freundlichen Gr&uuml;&szlig;en
Uwe Dechert
EOD2;
$subject='testmail';	
$mailhtml=$subject."\n";
$mailhtml="";
$mailhtml .=$mailheader."\n";
$mailhtml .=$mailfooter."\n";
$mail = new mailer;
	//$mail->Mailer="smtp";

	$mail->From = 'gerard@vandenelzen.de';

	$mail->FromName = 'gerard van den Elzen';
	
	$mail->AddAddress("gerard@vandenelzen.de", 'gerard van den elzen');

	$mail->AddReplyTo( 'gerard@vandenelzen.de', 'gerard van den elzen');
	
	//$mail->WordWrap = 200;    // set word wrap

	$mail->IsMail();

	$mail->IsHTML(false);    // set email format to HTML

	$mail->Subject = html_entity_decode($subject);

	$mail->Body = html_entity_decode($mailhtml);
	$mail->Request = $request;

	$mail->Send(); // send message

	sendRequestResult($request, 'OK');
}
?>