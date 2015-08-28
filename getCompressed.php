<?php
require_once ('LWEVDBinclude.php');
$compress=1;
getcontacts('getcontacts');

function getcontacts($request,$data)
{
	global $db;
	global $compress;
	$sql = "SELECT * FROM `case` ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get contacts sql:  $sql error: $error");

	}
	while ($contact = mysql_fetch_assoc($sqlresult)) {
		foreach ($contact as $key => $value) {
			$contact[$key] = urlencode(($value));
		}
		$json['contact'][] = $contact;
	}
$data=json_encode($json);
/*
$data=<<<EOT
{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}{"contact":[{"id":"13D97D2EB4B","partnerID":"7","description":"33619BI_33611BI_RICHTER_R_23.03.2013","date":"20130323","status":"8","action":"10000","contract":"http%3A%2F%2Fwww.immobilienschadenservice.de%2Fapp%2FDATEN%2F13D97D2EB4B%2Fcontract%2Fvertrag.jpg","sound":"","vatdue":"0","status13b":"0","changedate":"","checkdate":"","ip":"188.101.228.157","addTurnover":"0","ignore":"0"}]}
EOT;
*/
if($compress==1)
{
	$data=gzdeflate($data,9);
	$data= base64_encode ( $data );	
}
$result= '<?xml version="1.0" encoding="UTF-8" ?>';
$result .= "<result>\n<status>ok</status>\n<request>$request</request>\n<data><![CDATA[$data]]></data><zlib>$compress</zlib>\n</result>\n";
echo $result;
exit;

}

?>