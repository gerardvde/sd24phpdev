<?php
$directory="../DATEN/1470F919BE6/documents";
$newname="$directory/Dokumentation.Dignazio.pdf";


	if ($handle = opendir($directory)) 
	{
   		while (false !== ($file = readdir($handle))) 
   		{
        		if ($file != "." && $file != "..") {
        		echo $file;
        		if(strpos ( $file ,'Doku') )
          		rename ( $file ,  $newname );
          	 }
        	}
    }
     closedir($handle);
?>