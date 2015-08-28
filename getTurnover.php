<?php
function getTurnover($request, $data)
{
    writeMessageLog("Start $request ");
    global $db;
    $slim = isset($data['slim']) ? true : false;
    if (isset($data['partnerID'])) {
        $partnerID = $data['partnerID'];
        getPartnerTurnover($request, $partnerID, $slim);
    }
    if (isset($data['caseID'])) {
        $caseID = $data['caseID'];
        getCaseTurnover($request, $caseID, $slim);
    }

    if (isset($data['insuranceName'])) {
        $insurance = $data['insuranceName'];
        getInsuranceTurnover($request, $insurance, $slim);
    }

    sendRequestError($request, "unknownrequest");

}

function getInsuranceTurnover($request, $insurance, $slim)
{
    global $db;
    $totalturnover = Array();

    $like = "(buildinginsurance LIKE \"$insurance\" )";
    $sql = "SELECT case.id as caseID, case.partnerID as partnerID, case.addTurnover as addTurnover, caseTask.buildinginsurance as insurance
				FROM   `case` INNER JOIN `caseTask` ON  caseTask.caseID LIKE case.id  AND caseTask.ignore=0 AND $like 
				WHERE  case.status >=9 AND case.ignore = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get cases sql:  $sql error: $error");
    }
    $totalturnover = array_merge($totalturnover, processTurnoverCases($sqlresult, $slim));

    $like = "(contentinsurance LIKE \"$insurance\" )";
    $sql = "SELECT case.id as caseID, case.partnerID as partnerID, case.addTurnover as addTurnover, caseTask.contentinsurance as insurance
				FROM   `case` INNER JOIN `caseTask` ON  caseTask.caseID LIKE case.id  AND caseTask.ignore=0 AND $like 
				WHERE  case.status >=9 AND case.ignore = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get cases sql:  $sql error: $error");

    }
    $totalturnover = array_merge($totalturnover, processTurnoverCases($sqlresult, $slim));

    $like = "(liabinsurance LIKE \"$insurance\" )";
    $sql = "SELECT case.id as caseID, case.partnerID as partnerID, case.addTurnover as addTurnover, caseTask.liabinsurance as insurance
				FROM   `case` INNER JOIN `caseTask` ON  caseTask.caseID LIKE case.id  AND caseTask.ignore=0 AND $like 
				WHERE  case.status >=9 AND case.ignore = 0   ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get cases sql:  $sql error: $error");
    }
    $totalturnover = array_merge($totalturnover, processTurnoverCases($sqlresult, $slim));

    $json['caseturnover'] = $totalturnover;
    saveMessageLog();
    sendRequestResult($request, json_encode($json));
}

function getPartnerTurnover($request, $partnerID, $slim)
{
    global $db;
    $conditions = Array();
    $conditions[] = ' partnerID =  "' . $partnerID . '" ';
    $conditions[] = "`ignore` = 0 ";
    $conditions[] = ' status >=  9';
    $whereClause = "";
    foreach ($conditions as $cond) {
        if (strlen($whereClause) > 0) {
            $whereClause .= " AND ";
        }
        $whereClause .= $cond;

    }
    if (strlen($whereClause) > 0) {
        $whereClause = "WHERE " . $whereClause;
    } else {
        $whereClause = "";
    }
    $sql = "SELECT id AS  caseID,partnerID,addTurnover, date,changedate,status FROM `case` $whereClause  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get cases sql:  $sql error: $error");

    }
    $json['caseturnover'] = processTurnoverCases($sqlresult, $slim);
    saveMessageLog();
    sendRequestResult($request, json_encode($json));
}

function getCaseTurnover($request, $partnerID, $slim)
{
    global $db;
    $conditions = Array();
    $conditions[] = " id  lIKE  \"$caseID\" ";
    $conditions[] = "`ignore` = 0 ";
    $conditions[] = ' status >=  9';
    $whereClause = "";
    foreach ($conditions as $cond) {
        if (strlen($whereClause) > 0) {
            $whereClause .= " AND ";
        }
        $whereClause .= $cond;

    }
    if (strlen($whereClause) > 0) {
        $whereClause = "WHERE " . $whereClause;
    } else {
        $whereClause = "";
    }
    $sql = "SELECT id AS  caseID,partnerID,addTurnover, date,changedate,status FROM `case` $whereClause  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get cases sql:  $sql error: $error");

    }
    $json['caseturnover'] = processTurnoverCases($sqlresult, $slim);
    saveMessageLog();
    sendRequestResult($request, json_encode($json));
}

function processTurnoverCases($sqlresult, $slim)
{
    $turnover = Array();
    while ($case = mysql_fetch_assoc($sqlresult)) {
        foreach ($case as $key => $value) {
            $case[$key] = urlencode(($value));

        }
        $caseID = $case['caseID'];
        $addTurnover = $case['addTurnover'];
        $billtotal = getBillsTotal($request, $caseID);
        writeMessageLog("ALL Bills Total for $caseID  $billstotal addTurnover $addTurnover");
        $case['totalNet'] = $billtotal;
        if (!$slim) {
            $case['client'] = getClientData($request, $caseID);
            $case['caseObject'] = getObjectData($request, $caseID);
            $case['taskType'] = getTasktype($request, $caseID);
        }
        $turnover[] = $case;
    }
    return $turnover;
}

function getBillsTotal($request, $caseID)
{
    global $db;
    $sql = "SELECT totalNet, type FROM  caseBill  WHERE caseID=\"$caseID\" AND  status=9 AND `ignore` = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get caseBill  sql:  $sql error: $error");
    };
    $billstotal = 0;
    $amount = mysql_num_rows($sqlresult);
    if ($amount == 0) {
        return $billstotal;
    }

    while ($bill = mysql_fetch_assoc($sqlresult)) {
        $total = $bill['totalNet'];
        if ($bill['type'] == CREDIT) {
            writeMessageLog("Bills Total subtract  $total ");
            $billstotal -= $total;
        } else {
            writeMessageLog("Bills Total add  $total ");
            $billstotal += $bill['totalNet'];
        }

    }

    return $billstotal;
}

function getClientData($request, $caseID)
{
    global $db;
    $sql = "SELECT title,firstname,lastname,description FROM  caseClient  WHERE caseID=\"$caseID\"  AND `ignore` = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get caseClient  sql:  $sql error: $error");
    };
    $client = mysql_fetch_assoc($sqlresult);
    return $client;
}

function getObjectData($request, $caseID)
{
    global $db;
    $sql = "SELECT street,zip,town FROM  caseObject  WHERE caseID=\"$caseID\" AND  `ignore` = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get caseObject  sql:  $sql error: $error");
    };
    $object = mysql_fetch_assoc($sqlresult);
    return $object;
}

function getTasktype($request, $caseID)
{
    global $db;
    $sql = "SELECT type FROM  caseTask  WHERE caseID=\"$caseID\" AND `ignore` = 0  ";
    $sqlresult = mysql_query($sql, $db);
    if (!$sqlresult) {
        $error = mysql_error($db);
        sendRequestError($request, "get caseTask  sql:  $sql error: $error");
    };
    $amount = mysql_num_rows($sqlresult);
    if ($amount == 0) {
        return "";
    }

    $task = mysql_fetch_assoc($sqlresult);
    return $task['type'];
}
?>