<?php
//require_once ('LWEVDBinclude.php');
//$request='createPartnerBilld';
//$data['period']="20153";
//createPartnerBillsPDF($request,$data);
function createPartnerBillsPDF($request,$data)
{
	require_once ('getCrediting.php');
	require_once ('getPartnerbills.php');
	require_once('tcpdf/tcpdf.php');
	require_once('fpdi/fpdi.php');	
	$period=$data['period'];
	$billid=$data['id'];
	if(!isset($billid) && !isset($period))
	{
		sendRequestError($request, 'invalid period or billnr');
	}
	//START
	declareConstants();
	
	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {
	
		//Page header
		public function Header() {
		}
		// Page footer
		public function Footer() {
			$this->Image(TUV, 25, 265, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			$this->SetFont('helvetica', '', 7);
			$this->MultiCell(125, 20, FOOTER_TEXT, 0, 'L',false,0,73,267);
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

	//START PROCESSING
	//Create dir or clean contents
	if(file_exists ( OUTPUT_DIR ))
		{
			clearDir(OUTPUT_DIR);        
		}
	else
		{
			mkdir(OUTPUT_DIR);
		}
	
	if(isset($billid))
	{
		$where=" id = $billid";
	}
	else
	{
		$where="  period LIKE  \"$period\" ";	
	}

	
	$partnerbills=getPartnerbillsByCondition($request,$where);
	foreach($partnerbills as $partnerbill)
	{
		createPDF($partnerbill,false);
	}
		
	$creditings=getCreditingByCondition($request,$where);
	foreach($creditings as $crediting)
	{
		createPDF($crediting,true);
	}

	$pdffiles=getPDFFilesinDir(OUTPUT_DIR);
	$nr=count($pdffiles);
	if(isset($period))
	{
		$pdf = new ConcatPdf();
		$pdf->setFiles($pdffiles);
		$pdf->concat();
		$pdfname="T".$period.".pdf";
		$pdffile=OUTPUT_DIR.$pdfname;
		$pdf->Output($pdffile, 'F');	
	}
	if(isset($billid))
	{
		$pdffile=array_shift($pdffiles);
		$pdfname=pathinfo($pdffile,PATHINFO_BASENAME);
	}	
	$pdflink=HTTP_OUTPUT_DIR.$pdfname;
	// Move files to Folder
	//rename("/tmp/tmp_file.txt", "/home/user/login/docs/my_file.txt");
	/*
	foreach($pdffiles as $pdffile)
	{
		$basename=pathinfo ( $pdffile,PATHINFO_BASENAME  );
		writeLog('rename '.DATA_DIR.$basename);
		rename( $pdffile,DATA_DIR.$basename);
	}
	*/
	
	sendRequestResult($request, $pdflink);
}


function createPDF($paymentrecord,$credit)
{
	foreach ($paymentrecord as $key => $value) {
			if($key!='positions')
			{
				$paymentrecord[$key] = utf8_encode(urldecode($value));			}
		};
	$nr=$paymentrecord['billnr'];
	if($credit)
	{
		$partnerID=$paymentrecord['creditorID'];
	}
	else
	{
		$partnerID=$paymentrecord['partnerID'];
	}
	$period=$paymentrecord['period'];
	
	$vat=$paymentrecord['vat'];
	$dvat=$vat. "%";
	$directdebit=$paymentrecord['directdebit']==1?true:false;
	$displayperiod=convertPeriod($period);
	$date=$paymentrecord['date'];
	$displaydate=convertDBDate($date);
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	if($credit)
	{
		$PDFType='Gutschrift ';
	}
	else
	{
		$PDFType='Rechnung ';
	}

	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Schadendienst24 Netwerk');
	$pdf->SetTitle($PDFType.$nr);
	$pdf->SetSubject($PDFType);
	$pdf->SetKeywords("$PDFType, Schadendienst");
	
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
	
	// Partner adress
	$address=$paymentrecord['name'].CR.$paymentrecord['brand'].CR.$paymentrecord['street'].CR.$paymentrecord['zip'].' '.$paymentrecord['town'];
	$pdf->SetFont('helvetica', '', 11);
	$pdf->setCellHeightRatio(1.5);
	$pdf->MultiCell(90, 60,$address, 0, 'L',false,0,25,56);
	
	$pdf->setCellHeightRatio(1);
	$pdf->SetFont('helvetica', 'B', 20);
	$pdf->MultiCell(90, 12,$PDFType.$nr, 0, 'L',false,0,25,105);
	$pdf->SetFont('helvetica', 'B', 14);
	$pdf->MultiCell(90, 12,'Datum: '.$displaydate, 0, 'L',false,0,25,114);
	$pdf->MultiCell(90, 12,'Leistungszeitraum: '.$displayperiod, 0, 'L',false,0,25,120);
	// POSITIONS
	//HEADER
	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->MultiCell(10, 10,'Pos.', 0, 'L',false,0,25,130);
	$pdf->MultiCell(80, 20,'Bezeichnung.', 0, 'L',false,0,35,130);
	$pdf->MultiCell(25, 10,'Vorgänge (Stk.)', 0, 'L',false,0,120,130);
	$pdf->MultiCell(20, 10,'Betrag (Euro)', 0, 'R',false,0,150,130);
	$posnr=1;
	$ypos=135;
	$oldptype;
	$ptype;
	$typetotal=0;
	$typeamount=0;
	$total=0;
	$pdf->SetFont('helvetica', '', 7);
	setlocale(LC_MONETARY, 'de_DE');

	$positions=$paymentrecord['positions'];
	foreach($positions as $position)
	{
		$ptype=$position['positiontype'];
		$amount=	$position['amount'];
		$description=utf8_encode(urldecode($position['description']));
		if($ptype!=$oldptype)
		{
			if(isset($oldptype))
			{
				$dtypetotal=number_format ( $typetotal , 2 ,"," , "." );
				$pdf->MultiCell(10, 10,"".$posnr, 0, 'C',false,0,25,$ypos);
				$pdf->MultiCell(80, 20,$pdescription, 0, 'L',false,0,35,$ypos);
				$pdf->MultiCell(25, 10,$typeamount, 0, 'C',false,0,120,$ypos);
				$pdf->MultiCell(20, 10,$dtypetotal, 0, 'R',false,0,150,$ypos);
				$ypos+=4;
				$posnr++;
			}
			
			$oldptype=$ptype;
			$typetotal=0;
			$typeamount=0;
		}
		$pdescription=$description;
		$typeamount++;
		$typetotal+=$amount;
		$total+=$amount;
	}
	$dtypetotal=number_format ( $typetotal , 2 ,"," , "." );
	$pdf->MultiCell(10, 10,$posnr, 0, 'C',false,0,25,$ypos);
	$pdf->MultiCell(80, 20,$description, 0, 'L',false,0,35,$ypos);
	$pdf->MultiCell(25, 10,$typeamount, 0, 'C',false,0,120,$ypos);
	$pdf->MultiCell(20, 10,$dtypetotal, 0, 'R',false,0,150,$ypos);
	
	$dtotalnet=number_format ( $total , 2 ,"," , "." );
	$amountvat=(($vat/100)*$total);
	$damountvat=number_format ( $amountvat , 2 ,"," , "." );
	$totalbrut=$total+$amountvat;
	$dtotalbrut=number_format ( $totalbrut , 2 ,"," , "." );
	//Paymenttype
	if($credit)
	{
		$payment=	CREDIT_PAYMENT;
	}
	else
	{
		if($directdebit)
			{		
				$d=substr($date,6,2)+8;
				$m=substr($date,4,2);
				$y=substr($date,0,4);
				$paydateArray  = getdate(mktime(0, 0, 0, $m  , $d, $y));
				$paydate=$paydateArray['mday'].'.'.$paydateArray['mon'].'.'.$paydateArray['year'];
				$payment=DIRECT_DEBIT;
				$payment=str_replace(DATE,$paydate,$payment);
			}
		else
			{
				$payment=NON_DIRECT_DEBIT;
			}
	}
	$pdf->setCellHeightRatio(1.2);
	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->MultiCell(15, 12,'Zahlung: ', 0, 'L',false,0,25,240);
	$pdf->SetFont('helvetica', '', 7);
	$pdf->MultiCell(110, 20,$payment, 0, 'L',false,0,40,240);
	
	//TOTAL
	$pdf->Line	(25,250,175,250);
	$pdf->SetFont('helvetica', 'B', 7);
 	$pdf->MultiCell(30, 10,'Nettobetrag', 0, 'C',false,0,55,252);
	$pdf->MultiCell(30, 10,'MwSt '.$dvat, 0, 'C',false,0,100,252);
	$pdf->MultiCell(30, 10,'Rechnungsbetrag Euro', 0, 'C',false,0,145,252);	
	$pdf->SetFont('helvetica', '', 7);
	$pdf->MultiCell(30, 10,$dtotalnet, 0, 'C',false,0,55,256);
	$pdf->MultiCell(30, 10,$damountvat, 0, 'C',false,0,100,256);
	$pdf->MultiCell(30, 10,$dtotalbrut, 0, 'C',false,0,145,256);
	//Close and output PDF document
	if($credit)
	{
		$prefix='G';
	}
	else{
		$prefix='R';
	}
	
	$billpdf=OUTPUT_DIR.$prefix.$nr.'_'.$period.'_'.$partnerID.'.pdf';
	$pdf->Output($billpdf, 'F');
}
function getPDFFilesinDir($curdir){
        if(!$dir=opendir($curdir)){
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
function clearDir($curdir)
{
	if(!$dir=opendir($curdir)){
               return;
        }
        while(false !== ($file=readdir($dir))){
            if(stripos($file,'.pdf')!=false)
            {
            	unlink($curdir.$file);
            }
        }
}

function convertPeriod($period)
{
	return substr($period,4,1).'. Quartal '.substr($period,0,4);

}
function convertDBDate($dbdate)
{
	return substr($dbdate,6,2).'.'.substr($dbdate,4,2).'.'.substr($dbdate,0,4);

}
function declareConstants()
{
$footertext=<<<EO1
Assistancesysteme für Assekuranz & Facility-Management nach den Grundsätzen der Personalzertifizierung 
durch den TÜV Rheinland Personalzertifizierung PersCert. Datensicherheit & Qualitätssicherung aller Prozesse 
der Assitancepartnerbetriebe in Anlehnung an die Grundsätze der DIN EN ISO 9001:2008
EO1;
	
$sd24text="SchadenDienst24 GmbH\nStauffenbergstr. 29-35\n32257 Bünde\nTelefon: 0800 / 477477-3\nTelefax: 0800 / 477477-6\nwww.schadendienst24.de\ninfo@schadendienst24.de\nUSt-Id-Nr. DE 251567000\nGeschäftsführer: Andreas Knöbel, Uwe Rudolph\nAmtsgericht Bielefeld HRB 41458\nVolksbank Bielefeld-Gütersloh e.G.\nKonto 2009730500\nBLZ 478 601 25\nIBAN: DE 02478601252009730500\nBIC: VBGTDE3MXXX\n";
	
$sd24address="SchadenDienst24 GmbH,Stauffenbergstr.29-35,32257 Bünde";

$creditpayment="Überweisung  8 Tage netto\nWir überweisen Ihnen den Gutschriftbetrag auf das bei uns hinterlegte Bankkonto.";
$nondirectdebit="Überweisung  8 Tage netto\nBitte überweisen Sie den Rechnungsbetrag an die obig genannte Kontoverbindung.";
$directdebit="Bankeinzug 8 Tage netto\nDa Sie mit uns Lastschriftverfahren vereinbart haben, ziehen wir den Betrag zum {DATE}\nvon Ihrem bei uns hinterlegtem Bankkonto ein.";

	$docroot=$_SERVER['DOCUMENT_ROOT'];
	$scriptdir=dirname(__FILE__);
	$scriptdir=str_replace($docroot,'',$scriptdir);
	$server='http://'.$_SERVER["SERVER_NAME"];
	$httpoutputdir=$server.'/'.$scriptdir.'/output/';
	define ('CR',"\n");
	define ('DATA_DIR','../DATEN/');
	define ('IMAGE_DIR', dirname(__FILE__).'/images/');
	define ('OUTPUT_DIR', dirname(__FILE__).'/output/');
	define ('HTTP_OUTPUT_DIR',$httpoutputdir);
	define ('MAIN_DIR', dirname(__FILE__).'/');
	define ('FOOTER_TEXT', $footertext);
	define ('ADDRESS_LINE', $sd24address);
	define ('SD24_TEXT', $sd24text);
	define ('TUV',IMAGE_DIR.'certificat.jpg');
	define ('SD24LOGO',IMAGE_DIR.'logo.png');
	define ('DIVIDER',IMAGE_DIR.'divider.jpg');
	define ('DIRECT_DEBIT',$directdebit);
	define ('NON_DIRECT_DEBIT',$nondirectdebit);
	define ('CREDIT_PAYMENT',$creditpayment);
	define ('DATE',"{DATE}");

	
}

//============================================================+
// END OF FILE
//============================================================+
