<?php

function checkTaskLock($taskID)
{
    global $DB;

    $dbGetTasksOpt = 'SELECT '.CUF_T_LOCKED.' from `b_uts_tasks_task` WHERE `VALUE_ID` = ' . $taskID;
    $resGetTaskOpt = $DB->Query($dbGetTasksOpt, false, "File: ".__FILE__."<br>Line: ".__LINE__);

    $mainObserver = null;
    if($arTasksOpt = $resGetTaskOpt->Fetch())
        $opt = $arTasksOpt[CUF_T_LOCKED];

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

function getLabels($arUserField)
{
    $label = array("no", "yes");
    if(is_array($arUserField["SETTINGS"]["LABEL"]))
    {
        foreach($label as $key => $value)
        {
            if(strlen($arUserField["SETTINGS"]["LABEL"][$key]) > 0)
            {
                $label[$key] = $arUserField["SETTINGS"]["LABEL"][$key];
            }
        }
    }

    return $label;
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

        // This is the old project ID scheme.
        
        // Update the project's ID to include the last 2 digits of current year
        // $newPid = substr($ID,0,1).substr(date('Y'),-1).substr($ID,1);
        $newPid = substr($ID,0,1).substr(date('Y'),-1).'100';
        $query = 'update m_project set ID="'.$DB->ForSQL($newPid).'" where ID="'.$ID.'" limit 1';
        var_dump($query);
        exit();
        $DB->Query($query);
        
        if ($ID) {
            return json_encode(array('success' => true, 'last_id' => $newPid));
        }
    
    } else {

        // check if second digit of ID corresponds to current year
        $indicator = substr($ID,1, 1);

        // if current indicator is correct, return ID. Else, update with new year indicator
        if (substr(date('Y'),-1) == $indicator) {
            return json_encode(array('success' => true, 'last_id' => $ID));    
        
        } else {

            // as per IF-95, project IDs should start with 100. e.g. 68100, 69100
            // $newPid = substr($ID,0,1).substr(date('Y'),-1).substr($ID,2);

            $prefix = substr($ID,0,1).substr(date('Y'),-1);

            $queryLast = 'select * from m_project where ID like "'.$prefix.'%" order by ID desc limit 1';
            $rs = $DB->Query($queryLast);
            if ($row = $rs->Fetch()) {
                $newPid = $row['ID'];
                $newPid+=1;
            } else {
                $newPid = substr($ID,0,1).substr(date('Y'),-1).'100';
            }
            var_dump($newPid);

            $query = 'update m_project set ID="'.$DB->ForSQL($newPid).'" where ID="'.$ID.'" limit 1';
            var_dump($query);
            exit();
            $DB->Query($query);
            return json_encode(array('success' => true, 'last_id' => $newPid));
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
