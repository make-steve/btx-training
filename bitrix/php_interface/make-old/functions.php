<?php

function checkTaskLock($taskID)
{
    global $DB;

    $dbGetTasksOpt = 'SELECT UF_AUTO_713286985141 from `b_uts_tasks_task` WHERE `VALUE_ID` = ' . $taskID;
    $resGetTaskOpt = $DB->Query($dbGetTasksOpt, false, "File: ".__FILE__."<br>Line: ".__LINE__);

    $mainObserver = null;
    if($arTasksOpt = $resGetTaskOpt->Fetch())
        $opt = $arTasksOpt["UF_AUTO_713286985141"];

    if($opt == "1")
        return true; #lock
    else
        return false; #unlock
}

function setProjectDraft()
{
    CModule::IncludeModule('iblock');
    global $DB;
}


function setProject($params = array())
{
    if (!$params || empty($params)) {
        return false;
    }

    global $DB,$USER;
    $userid = $USER->GetID();
    $title = 'project_'.date('Ymd_his');
    
    $arFields = array();
    $arFields['CREATED_BY_ID'] = $userid;
    $arFields['MODIFY_BY_ID'] = $userid;
    $arFields['ASSIGNED_BY_ID'] = $userid;
    $arFields['IS_NEW'] = 'Y';
    $arFields['DATE_CREATE'] = date('Y-m-d h:i:s');
    if (isset($params['title'])) {
        $arFields['TITLE'] = $DB->ForSQL($params['title']);
    }
    if (!isset($arFields['TITLE'])) {
        $arFields['TITLE'] = $title;
    }

    $ID = $DB->Add("m_project", $arFields);
    if (strlen($ID)<=4) {
        // Update the project's ID to include the last 2 digits of current year
        $newId = substr($ID,0,1).substr(date('Y'),-1).substr($ID,1);
        $query = 'update m_project set ID="'.$DB->ForSQL($newId).'" where ID="'.$ID.'" limit 1';
        $DB->Query($query);
        
        if ($ID) {
            return json_encode(array('success' => true, 'last_id' => $newId));
        }
    
    } else {

        // check if second digit of ID corresponds to current year
        $indicator = substr($ID,1, 1);

        // if current indicator is correct, return ID. Else, update with new year indicator
        if (substr(date('Y'),-1) == $indicator) {
            return json_encode(array('success' => true, 'last_id' => $ID));    
        
        } else {
            $newId = substr($ID,0,1).substr(date('Y'),-1).substr($ID,2);
            $query = 'update m_project set ID="'.$DB->ForSQL($newId).'" where ID="'.$ID.'" limit 1';
            $DB->Query($query);
            return json_encode(array('success' => true, 'last_id' => $newId));
        }
        
    }

    return json_encode(array('success' => false));
}


function getDealTechnology() {

    global $DB;

    $project = new Project;
    $arUF = $project->getCrmFields('DEAL');

    $arTechnologyField = array();
    $arTechnology = array();
    foreach($arUF as $ufKey => $uf) {
        if($uf == 'TECHNOLOGIE') {
            $arTechnologyField[] = $ufKey;
        }
    }

    if(!empty($arTechnologyField)) {

        $curTechnology = current($arTechnologyField);
        $arUFFields = $project->getUserFieldInfos($arTechnologyField, 'CRM_DEAL');

        foreach($arUFFields['DATA']['ENUM'][$curTechnology] as $techKey => $techVal) {
            $arTechnology[$techKey] = $techVal['VALUE'];
        }
    }

    return $arTechnology;
}

function getDealProductGroup() {
    global $DB;

    $project = new Project;
    $arProductGroup = array();
    $rsProductGroup = $project->getCrmStatus(array('DEAL_TYPE'));

    foreach($rsProductGroup['DEAL_TYPE'] as $grpKey => $grpValue) {
        $arProductGroup[$grpValue['STATUS_ID']] = $grpValue['NAME'];
    }
   
    return $arProductGroup;
}

function cleanMoney($value) {

    return str_replace(array(".", ","), array("", "."), $value);
}

function formatMoney($value) {
    return number_format($value, 2, ',', '.');
}

function getDealCompany($deal)
{
    $deal = intval($deal);
    global $DB;
    $query = 'select * from b_crm_deal where ID="'.$DB->ForSQl($deal).'" limit 1';
    $rsDeal = $DB->Query($query); {
        if ($deal = $rsDeal->Fetch()) {
            if (!empty($deal['COMPANY_ID'])) {
                $query = 'select * from b_crm_company where ID="'.$DB->ForSQl($deal['COMPANY_ID']).'"';
                $rsCompany = $DB->Query($query);
                if ($company = $rsCompany->Fetch()) {
                    return $company;
                }
            }
            
        }
    }
    return array();
}


function ifmail($to,$from,$subject,$textmail,$htmlmail = NULL, $attachment = NULL)
{
    // require_once 'lib/swift_required.php';    
    // $mailCc = 'brands@exota.com';

    //Mail
    $transport = \Swift_MailTransport::newInstance();
    
    //Create the Mailer using your created Transport
    $mailer = \Swift_Mailer::newInstance($transport);
    
    //Create the message
    $message = \Swift_Message::newInstance()
    
    //Give the message a subject
    ->setSubject($subject)
    
    //Set the From address with an associative array
    ->setFrom($from)
    
    //Set the To addresses with an associative array
    ->setTo($to)

    // add cc
    // ->addCc($mailCc)
    
    //Give it a body
    ->setBody($textmail);
    
    if($htmlmail !=''){
    
        //And optionally an alternative body
        $message->addPart($htmlmail, 'text/html');
    
    }
    
    if(!is_null($attachment) && !empty($attachment)){
        if (is_array($attachment)) {
            foreach ($attachment as $file) {
                //Optionally add any attachments
                $message->attach(
                      \Swift_Attachment::fromPath($file)->setDisposition('inline')
                    );
            }
        } else {
            $message->attach(
                  \Swift_Attachment::fromPath($attachment)->setDisposition('inline')
                );
        }
    }
    
    //Send the message
    $result = $mailer->send($message);
    
    return $result;
}
