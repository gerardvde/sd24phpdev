<?php

function sendDamage($request,$data)
{
	$action=$data['action'];
	$actiontext="Schadendokumentation zentral erstellen";
	switch($action)
	{
		case 10000:
			$actiontext="Zentraler Dokumentationsservice<br>";
			$actiontext.="- Schadendokumentation zentral erstellen";
			break;
		case 11100:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an VU per Mail senden<br>";
			break;
		case 11010:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an Dritten per Mail senden<br>";			break;
		case 11001:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Postversand der Dokumentation mit Rechnung an den Auftraggeber<br>";
			break;	
		case 11110:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an VU per Mail senden<br>";
			$actiontext.="- Dokumentation mit Rechnung an Dritten per Mail senden<br>";
			break;
		case 11011:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an Dritten per Mail senden<br>";
			$actiontext.="- Postversand der Dokumentation mit Rechnung an den Auftraggeber<br>";			break;
		case 11101:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an VU per Mail senden<br>";
			$actiontext.="- Postversand der Dokumentation mit Rechnung an den Auftraggeber<br>";			break;
		case 11111:
			$actiontext="Zentraler Versandservice<br>";
			$actiontext.="- Dokumentation mit Rechnung an VU per Mail senden<br>";
			$actiontext.="- Dokumentation mit Rechnung an Dritten per Mail senden<br>";
			$actiontext.="- Postversand der Dokumentation mit Rechnung an den Auftraggeber<br>";
			break;
	}
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
	if(isset($emailsv))
	{
		$mailto=$emailsv;
		$mailcc="gerard@vandenelzen.de,info@immobilienschadenservice.de";
		//$mailcc="gerard@vandenelzen.de";
	}
	else
	{
		$mailcc="gerard@vandenelzen.de,$mailfrom";
	}
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
	$br='<br>';
	foreach($jsonArray  as $jsonObject)
	{
		foreach ($jsonObject as $key=>$value)
		{
			
			switch($key)
			{
				case 'company':
					$message.=formatCompany($value);
					break;
				case 'technician':
					$message.=formatTechnician($value);
					break;		
				case 'client':
					$message.=$br.formatClient($value);
					break;
				case 'building':
					$message.=$br.formatObject($value);
					break;	
				case 'expert':
					$message.=$br.formatExpert($value);
					break;
				case 'task':
					$message.=$br.formatTask($value);
					break;
				case 'damage':
					$message.=$br.formatDamage($value);
					break;
				case 'costs':					
					$costRows=formatCosts($value);
					break;
				case 'remark':
					$message.="$br$br<b>Bemerkung :</b>$br".$value;
					break;
				case 'description':
				 	 $header="$case $value";
				 	 break;
				case 'bill':
				 	 if($value=='true')
				 	 	$bill=true;
					break;
			}
		}
	}
	
	//Get type
	if(isset($bill))
	{
		if($bill)
			$type="RECHNUNG";
		else
			$type="ANGEBOT";
	}
	else
	{
	
		if(strpos( $header, 'Rechnung')===false)
			$type="KOSTENVORANSCHLAG";
	else
			$type="RECHNUNG";	
	}
	
	$dataFolder = "../auftrag/$case";
	if(file_exists($dataFolder))
	{
	$zip_file=compressFile($dataFolder);
	$baseURL="http://www.immobilienschadenservice.de/cms_iss/app/auftrag/";
	if(file_exists($zip_file))
	{
		$url=$baseURL.$case.'.zip';
		$message.="<br>Daten als zip unter <a href='".$url."'".">$case</a>";
	}

	$folderURL="$baseURL"."$case/";
        if($dir=opendir($dataFolder)){
        		while(false !== ($file=readdir($dir))){
                $myfile=$dataFolder."/".$file;
                if(is_file($myfile)){
                 		$imgattr=getimagesize ($myfile);
                		$imgw=round($imgattr[0]/2);
                		$imgh=round($imgattr[1]/2);
               		 $url=$folderURL.$file;
                     	 $images.="<img src=\"$url\"  width=\"$imgw\" height=\"$imgh\"/><br>";
                }
      	 }
        }
	}


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

	<p><H3>Schadenmeldung $header</H3> </p>
	
	<p><b>$actiontext</b><p>
	<p>Schadendokumentation:<br>
		$message<br>
    </p>
    <h2>$type</h2>
    <table width="100%" border="0">
    $costRows
    </table>
    <br>
    	$images
    	<br>
     <p>Action :$action</p>

    <br>
    <!--
    <p>Ignorieren ist zum testen<br></p>
     <p>Action :$action</p>
    $json;
    -->
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

function formatCompany($company)
{
	$br='<br>';
	$html='<b>FACHUNTERNEHMEN</b>'.$br;
	
	$html.=$company['name'].$br;
	$html.=$company['street'].$br;
	$html.=$company['zip']." ".$company['town'].$br;
	$html.='Email :'.$company['email'].$br;
	$html.='Region :'.$company['region'].$br;
	$html.='S-Rechnungsnr :'.$company['yearnumber'].$br;
	return $html;
}

function formatTechnician($tech)
{
	$br='<br>';
	$html='<b>EINSATZLEITER</b>'.$br;
	$html.=$tech['firstname']." ".$tech['lastname'].$br;
	$html.='Handy :'.$tech['telephone'].$br;
	return $html;
}
function formatClient($client)
{
	$br='<br>';
	$html='<b>KUNDE</b>'.$br;	
	$html.=$client['title'].' '.$client['firstname'].' '.$client['lastname'].$br;
	$html.=$client['street'].$br;
	$html.=$client['zip']." ".$client['town'].$br;
	$html.='Email :'.$client['email'].$br;
	$html.='Telefon :'.$client['telephone'].$br;
	$html.='Bemerkung :'.$client['remark'].$br;
	return $html;
}
function formatExpert($expert)
{
	$br='<br>';
	$html='<b>INFORMATION AN DRITTE (Sachverst&auml;ndige / Regulierer / Hausverwaltungen / Sonstige)</b>'.$br;
	$html.=$expert['role'].$br;
	$html.=$expert['title'].' '.$expert['firstname'].' '.$expert['lastname'].$br;
	$html.=$expert['street'].$br;
	$html.=$expert['zip']." ".$expert['town'].$br;
	$html.='Email :'.$expert['email'].$br;
	$html.='Telefon :'.$expert['telephone'].$br;
	$html.='Bemerkung :'.$expert['remark'].$br;

	return $html;
}
function formatObject($object)
{
	$br='<br>';
	$html='<b>OBJEKT</b>'.$br;
	$html.=$object['type'].$br;
	if($object['person']!="")
	{
		$html.='Zust&auml;ndig :'.$object['person'].$br;
	}
	$html.=$object['street'].$br;
	$html.=$object['zip']." ".$object['town'].$br;
	if($object['telephone']!="")
		$html.='Telefon :'.$object['telephone'].$br;
	return $html;
}
function formatTask($task)
{
	$br='<br>';
	$html='<b>AUFTRAG</b>'.$br;
	$html.="Auftragsart :".$task['type'].$br;
	$html.="Autragsumfang :".$task['load'].$br;
	$html.="Zahlungsweg :".$task['payment'].$br;
	$html.="Geb&auml;udeversicherung :".$task['buildingInsurance'].$br;
	$html.="Versicherungschein-Nr :".$task['insuranceNr'].$br;
	$html.="InhaltsVersicherung :".$task['contentInsurance'].$br;
	return $html;
}
function formatDamage($damage)
{
	$br='<br>';
	$html='<b>SCHADENDETAILS</b>'.$br;
	
	$html.="Schadenart:".$damage['type'].$br;
	$html.="Ursache:".$damage['cause'].$br;		
	$html.="Auswirkung:".$damage['effect'].$br;
	$html.="Schadenbeschreibung:".$damage['description'].$br;
	if($damage['thirdPartyText']!="")
		$html.="Drittverschulden:".$damage['thirdPartyText'].$br;

	return $html;
}

function formatCosts($costs)
{
	if(count($costs)==0)
		return "";
$startRow=<<<SROW
<tr><td colspan="6" align="center" valign="middle"><hr /></td></tr>
<tr><td width="100">Pos</td><td width="177">Menge</td><td width="115">Einheit</td><td width="98">MwSt.</td><td width="141">Einzelpreis</td><td width="165">Gesamtpreis</td> </tr>
<tr><td colspan="6" align="center" valign="middle"><hr /></td>   </tr>
SROW;

$costBlock=<<<CBLOCK
<tr><td>{POS}</td><td>{AMOUNT}</td><td>{UNIT}</td><td>19,00%</td><td>{PRICE}</td><td align='right'>{TOTAL}</td> </tr>
<tr><td>&nbsp;</td><td>{CODE}</td> <td colspan="4" align="left">{SHORTDESC}<td width="10"></tr>
<tr><td>&nbsp;</td><td colspan="4" align="left" valign="top">{LONGDESC}</td><td>&nbsp;</td></tr>
<tr><td colspan="6" align="center" valign="middle"><hr /></td>   </tr>
CBLOCK;

$totalBlock=<<<TBLOCK
<tr><td >Gesamtsumme</td><td colspan="4"></td><td  align="right">{TOTNET}</td></tr>
<tr><td colspan="2">zzgl. 19,00% MwSt. auf {TOTNET}</td><td colspan="3"></td><td  align="right">{VAT}</td></tr>
<tr><td colspan="6" align="center" valign="middle"><hr /></td></tr>
<tr><td width="100">Bruttobetrag</td><td colspan="4"></td><td  align="right">{TOTBRUT}</td></tr>
TBLOCK;

	$br='<br>';
	$eu=" &euro;";
	$html='<b>KOSTEN</b>'.$br;
	$gentotal=0;
	
	$n=1;
	$costRows=$startRow;
	foreach ($costs as $cost)
		{
		$costRow=$costBlock;
		$costRow=str_replace('{POS}',$n,$costRow);
		$costRow=str_replace('{AMOUNT}',$cost['amount'],$costRow);
		$costRow=str_replace('{UNIT}',$cost['description'],$costRow);
		$costRow=str_replace('{PRICE}',$cost['price'].$eu,$costRow);
		$costRow=str_replace('{VAT}',$cost['price'].$eu,$costRow);

		$total=$cost['total'];
		$costRow=str_replace('{TOTAL}',$total.$eu,$costRow);
		$sdesc=html_entity_decode($cost['cost']);
		$sdesc=strtoupper($sdesc);
		$sdesc=htmlentities($sdesc);
		$costRow=str_replace('{SHORTDESC}',$sdesc,$costRow);
		$costRow=str_replace('{LONGDESC}',$cost['longdescription'],$costRow);
		$costRow=str_replace('{CODE}',$cost['costID'],$costRow);
		$gentotal+=textToNumber($cost['total']);		
		$costRows.=$costRow;
		$n++;
		}
	$totalRow=$totalBlock;
	$vat=$gentotal*0.19;
	$vatD=numberToText($gentotal*0.19);
	$totalNet=numberToText($gentotal);
	$totalBrut=numberToText($vat+$gentotal);
	$totalRow=str_replace('{TOTNET}',$totalNet.$eu,$totalRow);
	$totalRow=str_replace('{VAT}',$vatD.$eu,$totalRow);
	$totalRow=str_replace('{TOTBRUT}',$totalBrut.$eu,$totalRow);
	return $costRows.$totalRow;
}
function textToNumber($txt)

{
$txt=str_replace('.','#',$txt);
$txt=str_replace(',','.',$txt);
$txt=str_replace('#',',',$txt);
return floatval($txt);
}
function numberToText($num)

{
	return number_format ($num , 2 , ',' , '.' );
}

?>

