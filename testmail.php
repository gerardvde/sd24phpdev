<?php
require_once ('LWEVDBinclude.php');
require("mailer.php");
$logo=("http://www.immobilienschadenservice.de/cms_iss/app/DATEN/logos/SD24 Logo Rhein-Main.jpg");
$logo="'$logo'";
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
<p>$mailfooter<br/>
  <br />
  partnername<br />
  street<br clear="all" />
   zip  town</p>
<p><img src=$logo width="200"></p>
<p><strong>$telephone</strong></strong><br />
  <strong>$fax</strong><br />
    <a href="$homepage" >$homepage</a></strong><br />
    <br />
    <strong><a href="gerard@vandenelzen.de">Gerard van den Elzen</a></strong><br />
    <br />
    <br />
  <div id="footer">
    Test</div></p>
</body>
</html>
EOH;
$mail = new mailer;
$mail->Mailer="smtp";
$mail->From = 'gerard@vandenelzen.de';
$mail->FromName = "Gerard van den Elzen";
$mail->AddAddress('gerard@vandenelzen.de',  "Gerard van den Elzen");
$mail->AddReplyTo('gerard@vandenelzen.de',  "Gerard van den Elzen");
$mail->WordWrap = 200;    // set word wrap

$mail->IsMail();
$mail->IsHTML(true);    // set email format to HTML
$mail->Subject = "Testmail";
$mail->Body = $mailhtml;
$mail->Request = 'test';
$mail->Send(); // send message
echo "Logo $logo<br>";
?>