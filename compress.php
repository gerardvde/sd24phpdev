<?php
error_reporting(1);
$request='compress';
$result= '<?xml version="1.0" encoding="UTF-8" ?>';
$data=urlencode("Hello world <>ydjkhfkdj hfjdh6 6%54§§333333");
$data1["gerard"][]=$data;
$data1["jos"][]=$data;

$data1["claas"][]=$data;

//$data1["eva"][]=$data;
$data=json_encode($data1);

$data=gzdeflate($data,9);
$result .= "<result>\n<status>ok</status>\n<request>$request</request>\n<data><![CDATA[$data]]></data><zlib>1</zlib>\n</result>\n";
echo $result;
exit;
?>