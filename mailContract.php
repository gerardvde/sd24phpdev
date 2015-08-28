<?php
require_once ('LWEVDBinclude.php');
require("mailer.php");
mailContract();
function mailContract()
{	

$mailhtml =<<<EOH
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style type="text/css">
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-style: normal;
	font-size: 12px;
	color: #000;
}
#footer {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
	font-weight: bold;
}
</style>
</head>

<body>
<p>$mailheader</p>
<p></p>

<br/>
<br/>
<p>footer<br/>
  <br />
  gerard van den elzen<br />
  friedrich ebertstr 43 <br clear="all" />
   59425 unna</p>
<p></p>
<p><strong>$telephone</strong></strong><br />
  <strong>$fax</strong><br />
    <a href="$homepage" >$homepage</a></strong><br />
    <br />
    <strong><a href="$partneremail">$partneremail</a></strong><br />
    <br />
    <br />
  <div id="footer">
    $mailinfo</div></p>
</body>
</html>
EOH;
	$mail = new mailer;
	$mail->Mailer="smtp";
	$mail->From = 'gerard@vandenelzen.de';
	$mail->AddAddress('gerard@vandenelzen.de', "gerardvde");
	$mail->FromName = 'Gerard van den Elzen';
	
	$mail->AddReplyTo($mailfrom, 'gerard');
	
	$mail->WordWrap = 200;    // set word wrap
	$file="../DATEN/144204E14A3/contract/Insel__Fhr__Sdstrand__Strand__Meer__Ebbe__Flut__Nordsee.jpg";
	$mail->AddAttachment($file);


	$mail->IsMail();
	$mail->IsHTML(true);    // set email format to HTML
	$mail->Subject = html_entity_decode($subject);
	$mail->Body = $mailhtml;
	$mail->Request = $request;
	$mail->Send(); // send message

	sendRequestResult($request, 'OK');
}
function setBR($txt)
{

	return str_replace("\n","<br>",$txt);
}
?>