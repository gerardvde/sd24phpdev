<?php
require("phpmailer/phpmailer.inc.php");

class mailer extends phpmailer {
	
	var $Request="";
	// Replace the default error_handler
	function error_handler($msg) {
		sendRequestError($Request,  $msg);
	}
}
?>