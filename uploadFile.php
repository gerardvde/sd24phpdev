<?php
require_once ('general.php');

if (!is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
	sendRequestError('uploadfile', 'nofiltouploaded');
}
$filename = $_FILES['Filedata']['name'];
$uploadfile = "../" . $filename;
$filedir=pathinfo($uploadfile,PATHINFO_DIRNAME );
if(!file_exists($filedir))
{
	mkdir($filedir, 0777, true);
}
if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile)) {
	chmod($uploadfile, 0666);
	sendRequestResult('uploadfile', $filename);
} else {
	sendRequestError('uploadfile', $filename.'filenotuploaded');
}

?>