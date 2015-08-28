<?php
	//phpinfo();
	   $body = http_get('http://ett.softproject.de/eBill/Teilnehmerliste/', array(httpauth => 'WS1231:test123'));
    print $body;
	/*
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"login: WS1231\r\n" .
              "password:test123\r\n"
  )
);
header("login: WS1231" );
header("password:test123" );
header("Location:http://ett.softproject.de/eBill/Teilnehmerliste" );
*/
?>