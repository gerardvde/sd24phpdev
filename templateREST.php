<?php
$_template=<<<TEMPLATE
<?xml version="1.0" encoding="UTF-8"?>
<tns:RechnungsVersand xmlns:tns="http://sp24.softproject.de/Rechnungsaustausch" xmlns:type="http://sp24.softproject.de/Datentypen" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" >
	<Empfaenger>{RECEIVER}</Empfaenger>
	<Sender>{SENDER}</Sender>
{BILL}
{TASK}
{ATTACHMENT}
</tns:RechnungsVersand>
TEMPLATE;

$_bill=<<<BILL
	<Rechnungsdaten>
		<Nummer>{NR}</Nummer>
		<Anschrift>
			<Name>{NAME}</Name>
			<Strasse>{STREET}</Strasse>
			<Hausnummer>{HNR}</Hausnummer>
			<PLZ>{ZIP}</PLZ>
			<Ort>{TOWN}</Ort>
			<Land>{COUNTRY}</Land>
		</Anschrift>
		<Aktenzeichen></Aktenzeichen>
		<Rechnungsdatum>{BILLDATE}</Rechnungsdatum>
		<Begleittext>{EMAILBODY}</Begleittext>
		<Mailempfaenger>{EMAILS}</Mailempfaenger>
	</Rechnungsdaten>
BILL;

$_task=<<<TASK
<Auftragsdaten>
	<Auftragsnummer>{CASEID}</Auftragsnummer>
	<Auftraggeber>
		<Name>{NAME1}</Name>
		<Name2>{NAME2}</Name2>
		<Strasse>{STREET}</Strasse>
		<Hausnummer>{HNR}</Hausnummer>
		<PLZ>{ZIP}</PLZ>
		<Ort>{TOWN}</Ort>
		<Land>{COUNTRY}</Land>
		<Telefon>{TEL}</Telefon>
		<Fax>{FAX}</Fax>
		<Mail>{MAIL}</Mail>
		<Web>{WEB}</Web>
	</Auftraggeber>	
	<Versicherungsnummer>{INSURANCE_NR}</Versicherungsnummer>
</Auftragsdaten>
TASK;

$_attachment=<<<ATTACHMENT
<Anhang>
	<Id>{ID}</Id>
	<Typ>{TYPE}</Typ>
	<Name>{NAME}</Name>
	<Format>{FORMAT}</Format>
	<Dokument>{CONTENT}</Dokument>
</Anhang>
ATTACHMENT;
	
define('TEMPLATE',$_template);
define('BILL',$_bill);
define('TASK',$_task);
define('ATTACHMENT',$_attachment);
?>