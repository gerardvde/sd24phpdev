<?php

function insertCase($request,$data)
{
	$case=$data['casenr'];
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
				case 'partner':
					$message.=formatPartner($value);
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
				case 'billservices':				
					$billRows=formatServices($value);
					break;
				case 'estimateservices':			
					$estimateRows=formatServices($value);
					break;
				case 'remark':
					$message.="$br$br<b>Bemerkung :</b>$br".$value;
					break;
				case 'description':
				 	 $header="$case $value";
				 	 break;
			}
		}
	}
	
}

function formatPartner($company)
{
	
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

	$html.=$tech['firstname']." ".$tech['lastname'].$br;
	$html.='Handy :'.$tech['telephone'].$br;
	return $html;
}
function formatClient($client)
{

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

	$html.="Schadenart:".$damage['type'].$br;
	$html.="Ursache:".$damage['cause'].$br;		
	$html.="Auswirkung:".$damage['effect'].$br;
	$html.="Schadenbeschreibung:".$damage['description'].$br;
	if($damage['thirdPartyText']!="")
		$html.="Drittverschulden:".$damage['thirdPartyText'].$br;

	return $html;
}

function formatServices($costs)
{
	if(count($costs)==0)
		return "";
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
		$billRows.=$costRow;
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
}

function insertNewCase()

{
/*
INSERT INTO `xa1103_db10`.`case` (
`id` ,
`partnerID` ,
`description` ,
`date` ,
`status` ,
`action`
)
VALUES (
'sdasdsdsadsd', '1', 'description', '20130ß101', '1', '1'
);
*/
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