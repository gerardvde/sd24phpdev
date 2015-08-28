<?php
function checkPassword($request,$data) {
	/*
		public static const SUPER:int=9;
		public static const ADMIN:int=7;
		public static const PARTNER:int=1;
		public static const MEDIATOR:int=2;
		public static const PARTNER_ADMIN:int=5;
		*/
	define('SUPER',9);
	define('ADMIN',7);
	define('PARTNER',1);
	define('MEDIATOR',2);
	define('PARTNER_ADMIN',2);
	
	$user=$data['user'];
	$password=$data['password'];
	global $db;
	$sql ="SELECT * FROM user where username=\"$user\" AND password=\"$password\"" ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	registerLogon($user);
	if( mysql_num_rows ( $sqlresult )==1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=SUPER;
			sendRequestResult($request, json_encode($record));
		}
	
	$sql ="SELECT * FROM technician where email=\"$user\" AND password=\"$password\"" ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )>=1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=PARTNER;
			sendRequestResult($request, json_encode($record));
		}

	$sql ="SELECT * FROM technician where username=\"$user\" AND password=\"$password\"" ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=PARTNER;
			sendRequestResult($request, json_encode($record));
	}
	$sql ="SELECT * FROM technician where email=\"$user\"  AND password=\"\" " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==1)
		{

			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=PARTNER;
			sendRequestResult($request, json_encode($record));
	}
	$sql ="SELECT * FROM technician where username=\"$user\" AND password=\"$password\"" ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=PARTNER;
			sendRequestResult($request, json_encode($record));
	}
	$sql ="SELECT * FROM mediator where username=\"$user\"  AND password=\"$password\" " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=MEDIATOR;
			sendRequestResult($request, json_encode($record));
	}
	$sql ="SELECT * FROM mediator where email=\"$user\"  AND password=\"$password\" " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )==1)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$record['userlevel']=MEDIATOR;
			sendRequestResult($request, json_encode($record));
	}
	sendRequestError($request, 'nouser');
}
function checkUsername($request,$data) {

	$user=$data['username'];
	global $db;
	$sql ="SELECT * FROM user where username=\"$user\" " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'Error '.$sql );
	};
	if( mysql_num_rows ( $sqlresult )>0)
		{
			$record = mysql_fetch_assoc($sqlresult);
			sendRequestResult($request, json_encode($record));
		}
	
	$sql ="SELECT * FROM technician where email=\"$user\"  " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'Error in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )>0)
		{
			$record = mysql_fetch_assoc($sqlresult);
			$json['user']=$record;
			sendRequestResult($request, json_encode($record));
		}

	$sql ="SELECT * FROM technician where username=\"$user\" " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )>0)
		{
			$record = mysql_fetch_assoc($sqlresult);
			sendRequestResult($request, json_encode($record));
	}
	$sql ="SELECT * FROM mediator where username=\"$user\"   " ;
	$sqlresult = mysql_query($sql, $db);
	if (!$sqlresult) {
		$error = mysql_error($db);
		sendRequestError($request, 'user not in DB'.$sql );
	};
	if( mysql_num_rows ( $sqlresult )>0)
		{
			$record = mysql_fetch_assoc($sqlresult);
			sendRequestResult($request, json_encode($record));
	}
	sendRequestResult($request, 'OK');
}

function registerLogon($user)
{
	global $db;
	$ip=$_SERVER['REMOTE_ADDR'];
	$sql = "INSERT INTO  `logon`  (name,ip) VALUES (\"$user\",\"$ip\")";
		$sqlresult = mysql_query($sql, $db);
}
?>