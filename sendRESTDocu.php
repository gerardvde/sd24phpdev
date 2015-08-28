<?php
function sendRESTDocu($request,$data)
{
require_once('templateREST.php');
//$url = "http://test-ws-gateway.schadenportal24.de/eBill/RechnungSenden";
$url="https://www.schadenportal24.de/eBill/RechnungSenden";


$today= getdate ();
$day=$today["yday"];
$logfile=__DIR__."/D$day.txt";
$username     = "SchadenDienst24";
$password    = "VU04012015A";
$sender="161625";//produktion
$receiver="161625";
//$sender="160285";
//$receiver="160285";

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
			case 'client':
				$client=$value;
				break;
			case 'object':
				$object=$value;
				break;
			case 'task':
				$task=$value;
				break;
			case 'damage':
				$damage=$value;
				break;
			case 'technician':
				$technician=$value;
				break;
			case 'clerk':
				$clerk=$value;
				break;
			case 'bill':
				$bill=$value;
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
				$emailinfo=$value;
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

if(!isset($technician)||!isset($damage)||!isset($client)||!isset($object))
{
//('caseTech','caseClient','caseObject','caseExpert','caseTask','caseDamage');
	$addJSON=getCaseOnly($caseID);
	$case=$addJSON['case'];
	$technician=$case['caseTech'];
				foreach ($technician as $key => $value) {
				$technician[$key] = urldecode(($value));	
			}
	if(!isset($damage))
		{
		$damage=$case['caseDamage'];
			foreach ($damage as $key => $value) {
				$damage[$key] = urldecode(($value));	
			}
		}
	if(!isset($task))
		{
		$task=$case['caseTask'];
					foreach ($task as $key => $value) {
				$task[$key] = urldecode(($value));	
			}
		}
	if(!isset($client))
		{
		$client=$case['caseClient'];
		foreach ($client as $key => $value) {
				$client[$key] = urldecode(($value));	
			}
		}
	if(!isset($object))
		{
		$object=$case['caseObject'];
		foreach ($object as $key => $value) {
				$object[$key] = urldecode(($value));	
			}
		}
}

$billnr="";
if(isset($bill))
{
	$billnr=utf8_encode(html_entity_decode($bill['number'].$bill['billnr']));
	$billdate=utf8_encode(html_entity_decode($bill['date']));
	//date":"20150310"
};
/*
get insurance number
*/
if(isset($task["binsurancenr"]) && strlen($task["binsurancenr"])>0)
{
	$insurancenr=$task["binsurancenr"];
}
if(isset($task["cinsurancenr"])&& strlen($task["cinsurancenr"])>0)
{
	$insurancenr=$task["cinsurancenr"];
}
if(isset($task["linsurancenr"])&& strlen($task["linsurancenr"])>0)
{
	$insurancenr=$task["linsurancenr"];
}
$insurancenr=utf8_encode(html_entity_decode($insurancenr));

$clientname= utf8_encode(html_entity_decode($client['firstname']." ".$client['lastname']));
$clientname=htmlspecialchars($clientname,ENT_QUOTES);
$clientstreet= utf8_encode(html_entity_decode($client['street']));
$clientstreet=htmlspecialchars($clientstreet,ENT_QUOTES);
$clienthnr= $client['nr'];
$clienttown= utf8_encode(html_entity_decode( $client['town']));
$clienttown=htmlspecialchars($clienttown,ENT_QUOTES);
$clientzip=  utf8_encode(html_entity_decode($client['zip']));
$clientemail=  utf8_encode(html_entity_decode($client['email']));
$clientweb=  utf8_encode(html_entity_decode($client['homepage']));


$partnername1=utf8_encode( html_entity_decode($partner['name']));
if(isset($technician))
{
	$partnername1=utf8_encode(html_entity_decode($technician['firstname']." ".$technician['lastname']));
}
$partnername1=htmlspecialchars($partnername1,ENT_QUOTES);

$partnername2= utf8_encode(html_entity_decode($partner['name']));
$partnername2=htmlspecialchars($partnername2,ENT_QUOTES);
$partnerstreet= utf8_encode(html_entity_decode($partner['street']));
$partnerstreet=htmlspecialchars($partnerstreet,ENT_QUOTES);
$partnerhnr= $partner['nr'];
$partnerzip= utf8_encode(html_entity_decode($partner['zip']));
$partnertown=utf8_encode( html_entity_decode($partner['town']));
$partnertown=htmlspecialchars($partnertown,ENT_QUOTES);
$partnertelephone= utf8_encode( html_entity_decode($partner['telephone']));
$partnerfax=utf8_encode( html_entity_decode( $partner['fax']));
$partneremail= utf8_encode( html_entity_decode($partner['email']));
$partneremail=htmlspecialchars($partneremail,ENT_QUOTES);
$partnerweb=utf8_encode( html_entity_decode($partner['homepage']));

$docsdir = FILEDIR.$caseID.'/documents/';
$mailfrom=$partneremail;

$mailheader=utf8_encode( html_entity_decode($mailheader));
//partneremail
$mailinfo=utf8_encode( html_entity_decode($mailinfo));
$mailinfo=htmlspecialchars($mailinfo,ENT_QUOTES);
$mailfooter=utf8_encode( html_entity_decode($mailfooter));
$mailfooter=htmlspecialchars($mailfooter,ENT_QUOTES);
$subject=utf8_encode( html_entity_decode($subject));
$subject=htmlspecialchars($subject,ENT_QUOTES);

foreach($mails as $madress)
	{
		foreach($madress as $key=>$value)
		{
			$mailtos[$key]=utf8_encode( html_entity_decode($value));		
		}
}
$emailsto=implode( ';' ,$mailtos );
foreach( $docsArray as $doc)
{
	$file=$docsdir.$doc;
	$rfile=realpath($file);
	$exists=file_exists($file);
	if($exists)
	{
		$files[]=$file;
	}
}

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
foreach($files as $file)
{
	$attachment=ATTACHMENT;
	$content=base64_encode ( file_get_contents($file) );
	$filename=basename($file);
	$attachment= str_replace('{ID}', '1245', $attachment);
	$attachment= str_replace('{TYPE}', '03', $attachment);
	$attachment= str_replace('{FORMAT}', 'pdf', $attachment);
	$attachment= str_replace('{NAME}', $filename, $attachment);
	$attachment= str_replace('{CONTENT}', $content, $attachment);
	$attachments=$attachments."\n".$attachment;
}

$resttemplate= str_replace('{ATTACHMENT}', $attachments, $resttemplate);
$mailbody=$resttemplate.$json.json_encode($addJSON);
sendRESTMAil($request,$mailbody);
$response='OK';

// PHP cURL  for https connection with auth
/*
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
   		$response = htmlentities( $response); 
            curl_close($ch);
            // $parser = simplexml_load_string($response);
          

		*/
		            $data="\n######################### START #############################\n$json\n------------------\n$resttemplate\n------------------\n$response\n####################### END #########################\n";
		
	file_put_contents ( $logfile ,  $data ,FILE_APPEND );
 	 sendRequestResult($request, $response);
  }; 
function sendRESTMAil($request,$resttemplate)
{
	require_once("mailer.php");
	
	$mail = new mailer;
	$mailfrom= 'gerard@vandenelzen.de';
	$mailfromname= "Gerard van den Elzen";
	$mailhtml=$resttemplate;
	$mail->From =$mailfrom;
	$mail->FromName = $mailfromname;
	$mail->AddAddress($mailfrom, $mailfromname);
	//$mail->AddAddress("s.peters@monty-gmbh.de", 'Stefan Peters');
	$mail->AddReplyTo($mailfrom,  $mailfromname);
		$mail->IsMail();

	$mail->IsHTML(false);    // set email format to HTML

	$mail->Subject = html_entity_decode('testRestMAIL');

	$mail->Body = html_entity_decode($mailhtml);
	$mail->Request = $request;

	$mail->Send(); // send message

	sendRequestResult($request, 'OK');

}        
?>