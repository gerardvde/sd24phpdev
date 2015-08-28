<?php
require_once('templateREST.php');
//$url = "http://test-ws-gateway.schadenportal24.de/eBill/RechnungSenden";
$url="https://www.schadenportal24.de/eBill/RechnungSenden";
$username     = "SchadenDienst24";
$password    = "VU04012015A";
$sender="161625";//produktion
$receiver="161625";
//$sender="160285";
//$receiver="160285";


$billnr="1245661";
if(isset($bill))
{
	$billnr=utf8_encode(html_entity_decode($bill['number'].$bill['billnr']));
	$billdate=utf8_encode(html_entity_decode($bill['date']));
	//date":"20150310"
};
$billdate='12.12.2015';
$insurancenr="AD345tre2";

$clientname= "mustermann";
$clientstreet="testStreet"; utf8_encode(html_entity_decode($client['street']));
$clienthnr= "24453";
$clienttown= "testtown";
$clientzip=  "3415";
$clientemail=  "gerard@vandenelzen.de";
$clientweb=  "hhtp:www.adobe.com";


$partnername1="pname";

$partnername2 ="pname2";
$partnerstreet= "street ";
$partnerhnr= "233451";
$partnerzip= "4567";
$partnertown="ptown";
$partnertelephone= "0230367543";
$partnerfax="0230367543";
$partneremail= "gerard@vandenelzen.de";

$partnerweb="http://www.adobe.com";

$docsdir = FILEDIR.$caseID.'/documents/';
$mailfrom=$partneremail;

$mailheader="mailbody";
$mailinfo="mailinfo";
$mailfooter="mailfooter";;
$subject="mailsubject";;
$emailsto='gerard@vandenelzen.de';

$resttemplate=TEMPLATE;
$resttemplate= str_replace('{SENDER}', $sender, $resttemplate);
$resttemplate= str_replace('{RECEIVER}', $receiver, $resttemplate);
// create bill
//Client daten
$billtemplate=BILL;
$billtemplate=str_replace('{NR}', $billnr, $billtemplate);
$billtemplate=str_replace('{NAME}', $clientname, $billtemplate);
$billtemplate=str_replace('{STREET}', $clientstreet, $billtemplate);
$billtemplate=str_replace('{HNR}', $clienthnr, $billtemplate);
$billtemplate=str_replace('{ZIP}', $clientzip, $billtemplate);
$billtemplate=str_replace('{TOWN}', $clienttown, $billtemplate);
$billtemplate=str_replace('{COUNTRY}', $clientcountry, $billtemplate);
$billtemplate=str_replace('{BILLDATE}', $billdate, $billtemplate);
$billtemplate=str_replace('{EMAILS}', $emailsto, $billtemplate);
$billtemplate=str_replace('{EMAILBODY}', $mailheader, $billtemplate);

$resttemplate= str_replace('{BILL}', $billtemplate, $resttemplate);

//Create Task
// Partner daten
$tasktemplate=TASK;
$tasktemplate= str_replace('{CASEID}',$caseID,$tasktemplate);
$tasktemplate=str_replace('{MAIL}', $partneremail, $tasktemplate);
$tasktemplate=str_replace('{WEB}', $partnerweb, $tasktemplate);
$tasktemplate=str_replace('{NAME1}', $partnername1, $tasktemplate);
$tasktemplate=str_replace('{NAME2}', $partnername2, $tasktemplate);
$tasktemplate=str_replace('{STREET}', $partnerstreet, $tasktemplate);
$tasktemplate=str_replace('{HNR}', $partnerhnr, $tasktemplate);
$tasktemplate=str_replace('{ZIP}', $partnerzip, $tasktemplate);
$tasktemplate=str_replace('{TOWN}', $partnertown, $tasktemplate);
$tasktemplate=str_replace('{COUNTRY}', $partnercountry, $tasktemplate);
$tasktemplate=str_replace('{TEL}', $partnertelephone, $tasktemplate);
$tasktemplate=str_replace('{FAX}', $partnerfax, $tasktemplate);
$tasktemplate=str_replace('{INSURANCE_NR}', $insurancenr, $tasktemplate);

$resttemplate= str_replace('{TASK}', $tasktemplate, $resttemplate);

//Create attachment
$attachments="";
$file='Rechnung.pdf'; 
	$attachment=ATTACHMENT;
	$content=base64_encode ( file_get_contents($file) );
	$filename=basename($file);
	$attachment= str_replace('{ID}', '1245', $attachment);
	$attachment= str_replace('{TYPE}', '03', $attachment);
	$attachment= str_replace('{FORMAT}', 'pdf', $attachment);
	$attachment= str_replace('{NAME}', $filename, $attachment);
	$attachment= str_replace('{CONTENT}', $content, $attachment);
	$attachments=$attachments."\n".$attachment;
$resttemplate= str_replace('{ATTACHMENT}', $attachments, $resttemplate);

$response='OK';

// PHP cURL  for https connection with auth
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $resttemplate); 
    $headers = array(
 					"login: $username",
					"password: $password",
                      		"Content-type: text/xml",
                        "Content-length:".strlen($resttemplate) ,
                    );       
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // converting
            $response=curl_exec($ch);
            curl_close($ch);
            // $parser = simplexml_load_string($response);

 	echo $response;
?>