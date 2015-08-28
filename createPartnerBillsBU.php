<?php


require_once('tcpdf/tcpdf.php');
require_once('fpdi/fpdi.php');
require_once('LWEVDBinclude.php');
global $messages;

$footertext=<<<EO1
Assistancesysteme für Assekuranz & Facility-Management nach den Grundsätzen der Personalzertifizierung 
durch den TÜV Rheinland Personalzertifizierung PersCert. Datensicherheit & Qualitätssicherung aller Prozesse 
der Assitancepartnerbetriebe in Anlehnung an die Grundsätze der DIN EN ISO 9001:2008
EO1;

$sd24text="SchadenDienst24 GmbH\nStauffenbergstr. 29-35\n32257 Bünde\nTelefon: 0800 / 477477-3\nTelefax: 0800 / 477477-6\nwww.schadendienst24.de\ninfo@schadendienst24.de\nUSt-Id-Nr. DE 251567000\nGeschäftsführer: Andreas Knöbel, Uwe Rudolph\nAmtsgericht Bielefeld HRB 41458\nVolksbank Bielefeld-Gütersloh e.G.\nKonto 2009730500\nBLZ 478 601 25\nIBAN: DE 02478601252009730500\nBIC: VBGTDE3MXXX\n";

$sd24address="SchadenDienst24 GmbH,Stauffenbergstr.29-35,32257 Bünde";
///Users/gerard/Documents/projects/SD24Online/deploy/phpprod/pdf/createBills.php

define ('DATA_DIR','../DATEN/');
define ('IMAGE_DIR', dirname(__FILE__).'/images/');
define ('OUTPUT_DIR', dirname(__FILE__).'/output/');
define ('MAIN_DIR', dirname(__FILE__).'/');
define ('FOOTER_TEXT', $footertext);
define ('ADDRESS_LINE', $sd24address);
define ('SD24_TEXT', $sd24text);
define ('TUV',IMAGE_DIR.'certificat.jpg');
define ('SD24LOGO',IMAGE_DIR.'logo.png');
define ('DIVIDER',IMAGE_DIR.'divider.jpg');
$messages='';
$period=$_REQUEST['period'];
$startdate =  date("d-m-Y");
$startnr = $_REQUEST['startnr'];

if(!isset($period))
{
	$period='20153';
}
//Check if bills for period exist

$exists=checkBillsForPeriod($period);
if($exists)
{
	writeLog( "Bills already exist");
	//exit();
}
else
{
	writeLog( "Bills do not exist");
}

$partnercases=getPartnerCases($period);

$oldpartnerID;
$billnr=getMaxBillNr();
if(isset($billnr))
{
	$billnr++;
}
else
{
	$billnr=200;
}
writeLog( " Maxbillnr $billnr ");
foreach($partnercases as $case)
{
	$partnerID=$case['partnerID'];
	if( $partnerID!=$oldpartnerID)
	{
		if(isset($oldpartnerID) )
		{
			createPartnerBill($period,$cases,$oldpartnerID);
		}
		$cases=array();
		$oldpartnerID=$partnerID;
	}
	$cases[]=$case;	
}
reportResult();
exit();
/*

$pdffiles=getPDFFilesinDir(DATA_DIR);
//MAIN
$output=OUTPUT_DIR.'D'.$period.'/';
if(file_exists ( $output ))
{
	while(false !== ($file=readdir($output))){
            unlink($output.$file);
        }
}
else
{
	mkdir($output);
}


for  ($nr=0;$nr<5;$nr++)
{
	ob_start();
	createPDFBill($startnr+$nr,$startdate,$output);
	ob_end_clean();
}


$pdffiles=getPDFFilesinDir($output);
// 
$nrOfFiles=count($pdffiles);
//writeLog( "$nrOfFiles kreiert\n";
$pdf = new ConcatPdf();

$pdf->setFiles($pdffiles);
$pdf->concat();
$pdffile=$output."T".$startdate.".pdf";
$pdf->Output($pdffile, 'F');
// Move files to Folder
//rename("/tmp/tmp_file.txt", "/home/user/login/docs/my_file.txt");
foreach($pdffiles as $pdffile)
{
	$basename=pathinfo ( $pdffile,PATHINFO_BASENAME  );
	writeLog('rename '.DATA_DIR.$basename);
	rename( $pdffile,DATA_DIR.$basename);
}
*/
reportResult();
//
function getMaxBillNr()
{
	global $db;
	$sql = "SELECT MAX(billnr)  FROM   `partnerbill`  WHERE `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partnerbill  sql:  $sql error: $error");
	};
	$record = mysql_fetch_assoc($sqlresult);
	return $record['MAX(billnr)'];

}

function createPartnerBill($period,$cases,$partnerID)
{
////{"caseID":"14E25BAD2E0","partnerID":"4","mediatorType":"0","mediatorID":"0","extraCost":""} 
	writeLog( "start createPartnerBill $partnerID");
	global $db;
	global $billnr;
	//Check if partner bill exists, we cannot rewrite ist!!
	$sql = "SELECT period  FROM   `partnerbill`  WHERE period LIKE \"$period\" AND partnerID=$partnerID AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )>0)
	{
			writeLog( "PartnerBill $partnerID already exists!");
		return ;
	}
  
	$sql = "SELECT brand,name,street,nr,zip, town,directdebit  FROM   `partner`  WHERE  id=$partnerID AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		//no valid partner!
		return ;
	}
	$totalnet=0;
	foreach($cases as $case)
	{
		$totalnet+=8;
		if(isset($case['extraCost']))
		{
			$totalnet+=$case['extraCost'];
		}
	}
	$vat=19;
	$date=enquote(date("Ymd"));
	$description="\"Leistungen\"";
	$period=enquote($period);
	$record = mysql_fetch_assoc($sqlresult);
	$brand=enquote($record['brand']);
	$name=enquote($record['name']);
	$street=enquote($record['street']." ".$record['nr']);
	$zip=enquote($record['zip']);
	$town=enquote($record['town']);
	$directdebit=$record['directdebit'];
	$values="$partnerID,$period,$billnr,$brand,$name,$description,$street,$zip,$town,$totalnet,$vat,$date,$directdebit";
	$sql = "INSERT INTO partnerbill ( `partnerID`, `period`, `billnr`, `brand`, `name`, `description`, `street`, `zip`, `town`, `totalNet`, `vat`, `date`, `directdebit`) VALUES ($values)";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get partner  sql:  $sql error: $error");
	};
	$billid=mysql_insert_id ();
	
	echo "insert  $billid<br>";
	//////{"caseID":"14E25BAD2E0","partnerID":"4","mediatorType":"0","mediatorID":"0","extraCost":""} 
	$sql = "DELETE FROM partnerBillposition WHERE  partnerbillID=$billid";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "delete partnerbillposition  sql:  $sql error: $error");
		};
	/*
id` int(11) NOT NULL AUTO_INCREMENT,
  `caseID` text COLLATE utf8_unicode_ci NOT NULL,
  `partnerbillID` int(11) NOT NULL,
  `positiontype` int(1) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `amount` float NOT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT '0',
	*/
	foreach($cases as $case)
	{
		$caseID=enquote($case['caseID']);
		$partnerbillID=$billid;
		$positiontype=1;
		$description=enquote("Controlling Vorgang");
		$amount=8;
		$values="$caseID,$partnerbillID,$positiontype,$description,$amount";
		$sql = "INSERT INTO partnerBillposition ( `caseID`, `partnerbillID`, `positiontype`, `description`, `amount`) VALUES ($values)";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get partner  sql:  $sql error: $error");
		};
	
	}
	
	
	writeLog( "created $billnr");
	$billnr++;


}

function getPartnerCases($period)
{
	$year=substr($period,0,4);
	$quart=substr($period,4,1);
	writeLog( "Quart $quart");
	$m1=1+(($quart-1)*3);
	$m2=$m1+1;
	$m3=$m1+2;
	
	
	while(strlen($m1)<2)
	{
		$m1='0'.$m1;
	}
	while(strlen($m2)<2)
	{
		$m2='0'.$m2;
	}
	while(strlen($m3)<2)
	{
		$m3='0'.$m3;
	}
	writeLog( "Months $m1 $m2 $m3 ");
	$like="(case.changedate LIKE \"$year$m1%\" OR case.changedate LIKE \"$year$m2%\" OR case.changedate LIKE \"$year$m3%\")";
	global $db;
	
		$sql = "SELECT case.id as caseID, case.partnerID as partnerID,case.mediatorType as mediatorType, case.mediatorID as mediatorID, caseCost.price as extraCost FROM   `case` LEFT JOIN `caseCost` ON   caseCost.caseID LIKE case.id AND caseCost.caseCostID=2 WHERE case.status = 11 AND case.ignore = 0   AND   $like ORDER BY partnerID";
		$sqlresult = mysql_query($sql, $db);
		if (!$sqlresult) {
			$error = mysql_error($db);
			sendRequestError($request, "get case  sql:  $sql error: $error");
		};
		while ($record = mysql_fetch_assoc($sqlresult))
			{
				foreach ($record  as $key => $value) {
					$record [$key] = urlencode(($value));
				}
				 $records[]=$record;
			}	
			return $records;	
}
function checkBillsForPeriod($period)
{
	global $db;
	$sql = "SELECT period  FROM   `partnerbill`  WHERE period=\"$period\"  AND `ignore` = 0 ";
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, "get caseBill  sql:  $sql error: $error");
	};
	if(mysql_num_rows (  $sqlresult )==0)
	{
		return false;
	}
	else
	{
	return true;
	}


}
function createPDFBill($nr,$startdate,$output)
{
	
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Schadendienst24 Netwerk');
	$pdf->SetTitle('Rechnung '.$nr);
	$pdf->SetSubject('Rechnung');
	$pdf->SetKeywords('Rechnung, Schadendienst');
	
	// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
	
	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
	// set default monospaced font
	//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	
	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}
	//-----------------------------------------------
	
	// set font
	$pdf->SetFont('helvetica', '', 11);
	
	// add a page
	$pdf->AddPage();
	
		//Header
			//DIVIDER
		$brktest="Gerard\nvan den Elzen";
		$pdf->Image(DIVIDER, 128, 13, 12, 77, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		// Logo
		$pdf->Image(SD24LOGO, 140, 15, 54, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		// Set font
		$pdf->SetFont('helvetica', '', 8);
		$pdf->setCellHeightRatio(1.2);
		$pdf->MultiCell(65, 55,SD24_TEXT, 0, 'L',false,0,140,38);
		//Adress Sd24
		$pdf->setCellHeightRatio(1);
		$pdf->SetFont('helvetica', '', 7);
		$pdf->MultiCell(90, 6,ADDRESS_LINE, 0, 'L',false,0,25,45);
		
		
	// ----------
	// Rechnung
	// ---------------------------------------------------------
	$pdf->SetFont('helvetica', 'B', 20);
	$pdf->MultiCell(90, 12,'Rechnung '.$nr, 0, 'L',false,0,25,105);
	$pdf->SetFont('helvetica', 'B', 14);
	$pdf->MultiCell(90, 12,'Datum '.$startdate, 0, 'L',false,0,25,115);
	//Close and output PDF document
	$pdf->Output($output."R$nr.pdf", 'F');
}
function getPDFFilesinDir($curdir){
        if(!$dir=opendir($curdir)){
               writeLog( "$curdir couldnt open");
               return;
        }

        while(false !== ($file=readdir($dir))){
            if(stripos($file,'.pdf')!=false)
            {
            $pdffiles[]=$curdir.$file;
            }
        }
         
        return  $pdffiles;
}
// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
	}
	// Page footer
	public function Footer() {
		$this->Image(TUV, 28, 265, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', '', 7);
		$this->MultiCell(120, 20, FOOTER_TEXT, 0, 'L',false,0,75,267);
	}
}

class ConcatPdf extends FPDI
{
    public $files = array();

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function concat()
    {
        foreach($this->files AS $file) {
            $pageCount = $this->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                 $tplIdx = $this->ImportPage($pageNo);
                 $s = $this->getTemplatesize($tplIdx);
                 $this->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
                 $this->useTemplate($tplIdx);
            }
        }
    }
}
function writeLog($message)
{
	global $messages;
	$messages.=$message."<br>";
}
function reportResult()
{
	global $messages;
	echo "Result :<br>  $messages";
}
//============================================================+
// END OF FILE
//============================================================+
