<?php
AddEventHandler('crm', 'OnBeforeCrmDealAdd', 'checkPostFields');
function checkPostFields($arFields)
{
}

AddEventHandler('crm', 'OnAfterCrmDealAdd', "setProjectActive");

/**
 * Update the project for a newly created deal
 * @param [type] $arFields [description]
 */
function setProjectActive($arFields)
{
    global $DB;

    $projectid = intval($arFields[CUF_D_PROJECT_ID]);

    $contactBindings = !empty($arFields['CONTACT_BINDINGS'])?current($arFields['CONTACT_BINDINGS']):array('CONTACT_ID' => 0);


    // check first if project exists
    $mproject = new MProject;
    $result = $mproject->getByID($projectid);
    
    if (($project = $result->Fetch()) && $arFields['IS_NEW'] == 'Y') {
        if ($mproject->isNew($project['ID'])) {
            
            // update the project
            $arUpdate = array(
                    'ACTIVE' => 'N',
                    'TITLE' => $DB->ForSQL($arFields[CUF_D_PROJECT_NAME]),
                    'CLIENT_NAME' =>  $DB->ForSQL($arFields[UF_CRM_1511431411]),
                    'TECHNOLOGY' =>  $DB->ForSQL($arFields[CUF_D_TECHNOLOGY]),
                    'PRODUCT_GROUP' => $DB->ForSQL($arFields['TYPE_ID']),
                    'COMMENTS' => $DB->ForSQL($arFields['COMMENTS']),
                    'PRIMARY_DEAL' => $DB->ForSQL($arFields['ID']),
                    'COMPANY_ID' => $DB->ForSQL($arFields['COMPANY_ID']),
                    'CONTACT_ID' => $DB->ForSQL($contactBindings['CONTACT_ID']),
                    // 'PROBABILITY' => $DB->ForSQL($arFields['PROBABILITY']),                    
                    // 'OPENED' => $DB->ForSQL($arFields['OPENED']),
                );



            $dateCreate = date('Y-m-d H:i:s');

            $strUpdate = $DB->PrepareUpdate("m_project", $arUpdate);
            $strUpdate .= ", DATE_CREATE = '" . $DB->ForSQL($dateCreate) . "', DATE_MODIFY = '" . $DB->ForSQL($dateCreate) . "'";
            $strSql = "UPDATE m_project SET {$strUpdate} WHERE ID=".$project['ID'];
            // $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
            $DB->Query($strSql);
        }
    }

    // ambot ngano pero dili mu-insert ang query kung dili mag-reference usab
    global $DB;
    $DB->Query('INSERT INTO m_deal_project (DEAL_ID, PROJECT_ID, ACTIVE) VALUES ("'.$DB->ForSQL($arFields['ID']).'","'.$DB->ForSQL($project['ID']).'","Y")');


    if(isset($contactBindings['CONTACT_ID']) && empty($arFields['COMPANY_ID']))
    {
        $selectContact = "select COMPANY_ID from b_crm_contact where ID = " . $DB->ForSQL($contactBindings['CONTACT_ID']);
        $resContact = $DB->Query($selectContact);

        if($getCompanyID = $resContact->Fetch()) {
            $arFields['COMPANY_ID'] = $getCompanyID["COMPANY_ID"];
            $strSqlUpdateCompany = "UPDATE m_project SET COMPANY_ID = '" . $DB->ForSQL($arFields['COMPANY_ID']) . "' WHERE ID = " . $project['ID'];
            $DB->Query($strSqlUpdateCompany);

            if($arFields['COMPANY_ID'] > 0) {
                $dealUp = new CCrmDeal;
                $dealUp->Update($arFields["ID"], $arFields, false, true, array());
            }

        }
    }    


    if(!empty($arFields['COMPANY_ID']))
    {
        $Project = new Project;
        $companyField = $Project->companyFields;
        $arCompany = $Project->getCompanyByIds(intval($arFields['COMPANY_ID']), $companyField);

        $_companyUFs = $Project->companyUF;
        $arCompanyUF = $Project->getUserFieldInfos(array_keys($_companyUFs), 'CRM_COMPANY');
        $arCompany['UF'] = current($Project->getUserFieldValues('company', $arCompanyUF, array($arFields['COMPANY_ID'])));

        if(!empty($arCompany['UF'][CUF_CM_CLIENTID_EXACT]["VALUES"][0]["VALUE_NAME"]))
        {
            $strSql = "UPDATE b_uts_crm_deal SET `" . CUF_D_DEBTOR_NUM . "` = '" . $DB->ForSQL($arCompany['UF'][CUF_CM_CLIENTID_EXACT]["VALUES"][0]["VALUE_NAME"]) . "' WHERE VALUE_ID = " . $arFields['ID'];
            $DB->Query($strSql);
        }
    }

    // set deal acquisitie kosten
    $dealAcqKosten = doubleval($arFields[CUF_D_ACQUISITIE]);
    $mod_project_task_acq = new mod_project_task(intval($arFields['ID']), 0, false);
    $mod_project_task_acq->addEntries();
    $mod_project_task_acq->getAllTaskData();
    $mod_project_task_acq->setTaskData('ACQUISITION_COSTS', $dealAcqKosten, 'BUDGET', true);
}

AddEventHandler('crm', 'OnAfterCrmDealUpdate', "setDealDetailsToMainProject");
function setDealDetailsToMainProject($arFields)
{
    CModule::IncludeModule("tasks");
    CModule::IncludeModule("crm");
    global $DB;
    $MProject = new MProject;
    $Project = new Project;

    if($arFields["STAGE_ID"] == "WON" || $arFields["STAGE_ID"] == "S")
    {
        $arFilter = array('=ID' => intval($arFields["ID"]));
        $resDeal = CCrmDeal::GetListEx(array(), $arFilter, false, false, array("*", "UF_*"), array());
        if($arDeal = $resDeal->Fetch())
        {
            $projectid = intval($arDeal[CUF_D_PROJECT_ID]);

            $crmstatus = $Project->getCrmStatus(array('DEAL_TYPE'));
            $result = $Project->getProjectSimpleData($projectid);

            //$result = $MProject->getByID($projectid);

            if($arMProject = $result->Fetch())
            {
                if($arMProject["ACTIVE"] == "N" && empty($arMProject["DATE_APPROVED"]))
                {
                    $arUpdate = array(
                        'ACTIVE' => 'Y',
                        'COMMENTS' => $DB->ForSQL($arDeal['COMMENTS']),
                        'ADDITIONAL_INFO' => $DB->ForSQL($arDeal['ADDITIONAL_INFO']),
                        'TYPE_ID' => $DB->ForSQL($arDeal['TYPE_ID']),
                        'TECHNOLOGY' => $DB->ForSQL($arDeal[CUF_D_TECHNOLOGY]),
                        'PRODUCT_GROUP' => $crmstatus["DEAL_TYPE"][$arDeal['TYPE_ID']]["STATUS_ID"],
                        'PRIMARY_DEAL' => $DB->ForSQL(intval($arFields["ID"]))
                    );

                    $dateApprove = date('Y-m-d H:i:s');

                    $strUpdate = $DB->PrepareUpdate("m_project", $arUpdate);
                    $strUpdate .= ", DATE_APPROVED = '" . $DB->ForSQL($dateApprove) . "', DATE_MODIFY = '" . $DB->ForSQL($dateApprove) . "'";
                    $strSql = "UPDATE m_project SET {$strUpdate} WHERE ID = " . $arMProject['ID'];
                    $DB->Query($strSql);
                
                    if(isset($arDeal['CONTACT_ID'])) 
                    {
                        $honorificList = CCrmStatus::GetStatusList('HONORIFIC');

                        $contactField = $Project->contactFields;
                        $arContact = $Project->getContactByIds(intval($arDeal['CONTACT_ID']), $contactField);

                        $_contactUFs = $Project->contactUF;
                        $arContactUF = $Project->getUserFieldInfos(array_keys($_contactUFs), 'CRM_CONTACT');
                        $arContact['UF'] = current($Project->getUserFieldValues('contact', $arContactUF, array($arDeal['CONTACT_ID'])));

                        $arContactInsert = array(
                            'PROJECT_ID' => $arMProject['ID'],
                            'CONTACT_ID' => $arDeal['CONTACT_ID'],
                            'SALUTATION' => $DB->ForSQL($honorificList[$arContact["HONORIFIC"]]),
                            'LAST_NAME' => $DB->ForSQL($arContact['LAST_NAME']),
                            'MIDDLE_NAME' => $DB->ForSQL($arContact['SECOND_NAME']),
                            'NAME' =>  $DB->ForSQL($arContact['NAME']),
                            'POST_ADD' => $DB->ForSQL($arContact['UF'][CUF_CN_POSTADRES]["VALUES"][0]["VALUE_NAME"]),
                            'POSTCODE' => $DB->ForSQL($arContact['UF'][CUF_CN_POSTCODE]["VALUES"][0]["VALUE_NAME"]),
                            'CITY' => $DB->ForSQL($arContact['UF'][CUF_CN_PLAATS]["VALUES"][0]["VALUE_NAME"]),
                            'TELEPHONE' => $DB->ForSQL($arContact["FM"]["PHONE"][0]["VALUE"]),
                            'EMAIL' => $DB->ForSQL($arContact["FM"]["EMAIL"][0]["VALUE"]),
                        );

                        $strContactInsert = $DB->PrepareUpdate("m_project_contact", $arContactInsert);
                        $strContactSql = "INSERT INTO m_project_contact SET {$strContactInsert}";
                        $DB->Query($strContactSql);

                        $selectContact = "select COMPANY_ID from b_crm_contact where ID = " . $DB->ForSQL($arDeal['CONTACT_ID']);
                        $resContact = $DB->Query($selectContact);

                        if($getCompanyID = $resContact->Fetch()) {
                            $arDeal['COMPANY_ID'] = $getCompanyID["COMPANY_ID"];
                            $strSqlUpdateCompany = "UPDATE m_project SET COMPANY_ID = '" . $DB->ForSQL($arDeal['COMPANY_ID']) . "' WHERE ID = " . $arMProject['ID'];
                            $DB->Query($strSqlUpdateCompany);
                        }
                    }

                    if(isset($arDeal['COMPANY_ID'])) 
                    {
                        $companyField = $Project->companyFields;
                        $arCompany = $Project->getCompanyByIds(intval($arDeal['COMPANY_ID']), $companyField);

                        $_companyUFs = $Project->companyUF;
                        $arCompanyUF = $Project->getUserFieldInfos(array_keys($_companyUFs), 'CRM_COMPANY');
                        $arCompany['UF'] = current($Project->getUserFieldValues('company', $arCompanyUF, array($arDeal['COMPANY_ID'])));

                        $arCompanyInsert = array(
                            'PROJECT_ID' => $arMProject['ID'],
                            'COMPANY_ID' => $arDeal['COMPANY_ID'],
                            'COMPANY_NAME' => $arCompany['TITLE'],
                            'ADDRESS' => $DB->ForSQL($arCompany['UF'][CUF_CM_BEZOEKADRES]["VALUES"][0]["VALUE_NAME"]),
                            'POSTCODE' => $DB->ForSQL($arCompany['UF'][CUF_CM_POSTCODE]["VALUES"][0]["VALUE_NAME"]),
                            'CITY' => $DB->ForSQL($arCompany['UF'][CUF_CM_PLAATS]["VALUES"][0]["VALUE_NAME"]),
                            'ADDRESS_INVOICE' => $DB->ForSQL($arCompany['UF'][CUF_CM_FACTUUR_BEDRIJF]["VALUES"][0]["VALUE_NAME"]),
                            'ADDRESS_BILLING' => $DB->ForSQL($arCompany['UF'][CUF_CM_FACTUUR_ADRES]["VALUES"][0]["VALUE_NAME"]),
                            'TAV' => $DB->ForSQL($arCompany['UF'][CUF_CM_TAV]["VALUES"][0]["VALUE_NAME"]),
                        );

                        $strCompanyInsert = $DB->PrepareUpdate("m_project_company", $arCompanyInsert);
                        $strCompanySql = "INSERT INTO m_project_company SET {$strCompanyInsert}";
                        $DB->Query($strCompanySql);

                        $strSqlUpdateCompany = "UPDATE m_project SET CLIENT_NAME = '" . $DB->ForSQL($arCompany['TITLE']) . "' WHERE ID = " . $arMProject['ID'];
                        $DB->Query($strSqlUpdateCompany);
                    }    

                }   
            }

            
        }

        #lets re-update tasks
        if(isset($_SESSION["dealtaskpendingstatus"][$arFields["ID"]]))
        {
            $arDealTask = $_SESSION["dealtaskpendingstatus"][$arFields["ID"]];

            foreach ($arDealTask as $key => $taskInfo) 
            {
                $arUpdateStatus["STATUS"] = $taskInfo["STATUS"];
                $arUpdateStatus["STATUS_ID"] = $taskInfo["STATUS"];
                $task = new CTasks;
                $task->Update($taskInfo["ID"], $arUpdateStatus, array());
            }

            unset($_SESSION["dealtaskpendingstatus"][$arFields["ID"]]);
        }

        // set deal acquisitie kosten
        if(isset($arFields[CUF_D_ACQUISITIE])) {
            $dealAcqKosten = doubleval($arFields[CUF_D_ACQUISITIE]);
            $mod_project_task_acq = new mod_project_task(intval($arFields['ID']), 0, false);
            $mod_project_task_acq->setTaskData('ACQUISITION_COSTS', $dealAcqKosten, 'BUDGET', true);
        }
    }

    // Reupdate Debtors Code
    if(isset($arFields['COMPANY_ID'])) 
    {
        $companyField = $Project->companyFields;
        $arCompany = $Project->getCompanyByIds(intval($arFields['COMPANY_ID']), $companyField);

        $_companyUFs = $Project->companyUF;
        $arCompanyUF = $Project->getUserFieldInfos(array_keys($_companyUFs), 'CRM_COMPANY');
        $arCompany['UF'] = current($Project->getUserFieldValues('company', $arCompanyUF, array($arFields['COMPANY_ID'])));
        
        $reupdate = false;
        if(empty($arFields[CUF_D_DEBTOR_NUM])) $reupdate = true;
        if(!empty($arFields[CUF_D_DEBTOR_NUM]) && $arFields[CUF_D_DEBTOR_NUM] != $arCompany['UF'][CUF_CM_CLIENTID_EXACT]["VALUES"][0]["VALUE_NAME"]) $reupdate = true;
        if(empty($arCompany['UF'][CUF_CM_CLIENTID_EXACT]["VALUES"][0]["VALUE_NAME"])) $reupdate = false;


        if($reupdate)
        {
            $strSql = "UPDATE b_uts_crm_deal SET `" . CUF_D_DEBTOR_NUM . "` = '" . $DB->ForSQL($arCompany['UF'][CUF_CM_CLIENTID_EXACT]["VALUES"][0]["VALUE_NAME"]) . "' WHERE VALUE_ID = " . $arFields['ID'];
            $DB->Query($strSql);
        }
        
    }

}

AddEventHandler("main", "OnEndBufferContent", array("intranetClassJS", "changePlannerJS"));

class intranetClassJS
{
    static public function changePlannerJS(&$content)
    {
        $content = str_replace("/bitrix/js/intranet/core_planner.js", "/bitrix/js/customjs/custom_intranet_core_planner.js", $content);
        $content = str_replace("/bitrix/js/calendar/core_planner_handler.js", "/bitrix/js/customjs/custom_calendar_planner_handler.js", $content);
        $content = str_replace("/bitrix/js/tasks/core_planner_handler.js", "/bitrix/js/customjs/custom_task_planner_handler.js", $content);
    }
}


AddEventHandler('crm', 'OnBeforeCrmDealUpdate', "getDealTaskAccepted");

function getDealTaskAccepted(&$arFields)
{   
    global $USER, $DB;
    CModule::IncludeModule("tasks");
    CModule::IncludeModule("crm");
    $hasProject = false;

    $checkIFProject = "select * from m_deal_project where DEAL_ID = " . $arFields["ID"];
    $resProject = $DB->Query($checkIFProject);
    $result = $resProject->Fetch();

    if(!empty($result))
        $hasProject = true;
    else
    {
        $checkIFProject = "select * from m_project where PRIMARY_DEAL = " . $arFields["ID"];
        $resProject = $DB->Query($checkIFProject);

        $result = $resProject->Fetch();

        if(!empty($result))
            $hasProject = true;
    }



    if(isset($_SESSION["dealtaskpendingstatus"][$arFields["ID"]]))
        unset($_SESSION["dealtaskpendingstatus"][$arFields["ID"]]);

    if($hasProject)
    {
        $arFilter["UF_CRM_TASK"] = "D_" . $arFields["ID"];
        $arFilter["!STATUS"] = 5;
        $dbRes = CTasks::GetList(array(), $arFilter, Array("*", "UF_*"), array());

        $_SESSION["dealtaskpendingstatus"][$arFields["ID"]] = array();
        while ($arTask = $dbRes->GetNext())
            $_SESSION["dealtaskpendingstatus"][$arFields["ID"]][] = $arTask;
    }
}

/**
 * Get the deal's parent project
 * @param  int $did The deal ID
 * @return int      The project ID
 */
function getDealProject($did)
{
    $did = intval($did);
    global $DB;
    $select = 'select * from m_deal_project where DEAL_ID="'.$DB->ForSQL($did).'" limit 1';
    $result = $DB->Query($select);
    $pid = null;
    if ($row = $result->Fetch()) {
        $pid = intval($row['PROJECT_ID']);
    }

    return $pid;
}

/**
 * Get array of project's tasks
 * @param  int $pid The project ID
 * @return array      The array of tasks
 */
function getProjectTasks($pid)
{
    if (is_null($pid)) {
        return array();
    }

    $pid = intval($pid);
    global $DB;

    $deals = array();
    $selectDealProject = 'select * from m_deal_project where PROJECT_ID="'.$DB->ForSQL($pid).'"';
    $result = $DB->Query($selectDealProject);
    while ($row = $result->Fetch()) {
        $row['DEAL_ID'] = intval($row['DEAL_ID']);
        if (!in_array($row['DEAL_ID'], $deals)) {
            $deals[] = $row['DEAL_ID'];
        }
    }
    
    $in = array();
    foreach ($deals as $did) {
        $in[] = '"D_'.$did.'"';
    }
    $selectUtmTask = 'select butt.*, bt.XML_ID from b_utm_tasks_task butt join b_tasks bt on bt.ID=butt.VALUE_ID where butt.VALUE IN ('.implode(',', $in).') and bt.ZOMBIE="N" order by bt.ID asc';
    $result = $DB->Query($selectUtmTask);
    while ($row = $result->Fetch()) {
        $tasks[] = $row;
    }

    return $tasks;
}

// set custom "DEAL_ID.TASK_ID" format for task classification
AddEventHandler('tasks', 'OnTaskAdd', 'setTaskId');
function setTaskId($ID, &$arFields)
{
    global $DB;
    CModule::IncludeModule('tasks');

    // deal ID
    $deal = null;
    if (isset($arFields['UF_CRM_TASK']) && !empty($arFields['UF_CRM_TASK'])) {
        if (is_array($arFields['UF_CRM_TASK']))
            $arFields['UF_CRM_TASK'] = current($arFields['UF_CRM_TASK']);
        else
            $arFields['UF_CRM_TASK'] = trim($arFields['UF_CRM_TASK']);
    }

    // get the task's parent deal
    $select = 'select butt.* from b_utm_tasks_task butt join b_tasks bt on bt.ID=butt.VALUE_ID where butt.VALUE="'.$DB->ForSQL($arFields['UF_CRM_TASK']).'" order by ID desc';
    $result = $DB->Query($select);
    if ($row = $result->Fetch()) {

        $deal = $row['VALUE'];
        if (stripos($deal, 'D_')!==false) {
            $deal = str_ireplace('D_', '', $deal);
        }
        $deal = intval($deal);

        // get the project's last task
        $pid = getDealProject($deal);
        $projectTasks = getProjectTasks($pid);

        if (empty($projectTasks)) {
            // update the task XML ID (serves as "DEAL_ID.TASK_ID" value)
            $newTaskid = '001';
            $newTaskid = $pid.'.'.$newTaskid;
            $update = 'update b_tasks set XML_ID="'.$DB->ForSQL($newTaskid).'" where ID='.$DB->ForSQL($row['VALUE_ID']).' limit 1';
            $DB->Query($update);

        } else {

            $unique = array();
            $unfiled = array();
            foreach ($projectTasks as $key => $row) {
                
                $row['VALUE_ID'] = intval($row['VALUE_ID']);

                if (stripos($row['XML_ID'], "{$pid}.")!==false && !isset($unique[$row['XML_ID']])) {
                    $unique[$row['XML_ID']] = $row['VALUE_ID'];
                } else {
                    $unfiled[] = $row['VALUE_ID'];
                }
            }

            // get the last PROJECT.TASK ID
            ksort($unique);
            reset($unique);
            $end = end($unique);
            $lastid = key($unique);
            list($kpid, $ktid) = explode('.', $lastid);
            $ktid = intval($ktid);
            reset($unique); // reset array's pointer

            if (!empty($unfiled)) {
                $newTaskid = $ktid+1;
                foreach ($unfiled as $utaskid) {
                    
                    $newTaskid = str_pad($newTaskid, 3, '0', STR_PAD_LEFT);
                    $newTaskid = $pid.'.'.$newTaskid;
                    $update = 'update b_tasks set XML_ID="'.$DB->ForSQL($newTaskid).'" where ID='.$DB->ForSQL($utaskid).' limit 1';
                    $DB->Query($update);

                    $newTaskid++;
                }
            }

            /*if (count($projectTasks) > 1) {
                $reference = $projectTasks[1];
                $arTask = CTasks::GetByID($reference, true, array('returnAsArray' => true));
                list($project, $lastId) = explode('.', $arTask['XML_ID']);
                $newTaskid = intval($lastId);
                $newTaskid+=1;
            } else {
                $newTaskid = count($projectTasks);
            }

            $newTaskid = str_pad($newTaskid, 3, '0', STR_PAD_LEFT);
            $newTaskid = $pid.'.'.$newTaskid;
            $update = 'update b_tasks set XML_ID="'.$DB->ForSQL($newTaskid).'" where ID='.$DB->ForSQL($row['VALUE_ID']).' limit 1';
            $DB->Query($update);*/
        }
    }
}

AddEventHandler('crm', 'OnAfterCrmCompanyUpdate', "setDealsDebtorNumber");
function setDealsDebtorNumber(&$arFields)
{
    global $DB;

    if(!empty($arFields[CUF_CM_CLIENTID_EXACT]))
    {
        // get all associated deals
        $selectDeals = "select ID from b_crm_deal where COMPANY_ID = " . $arFields["ID"];
        $resDeals = $DB->Query($selectDeals);
        $arDeals = array();

        while($arDeal = $resDeals->Fetch())
            $arDeals[] = $arDeal["ID"];

        if(!empty($arDeals))
        {
            $strSql = "UPDATE b_uts_crm_deal SET `" . CUF_D_DEBTOR_NUM . "` = '" . $DB->ForSQL($arFields[CUF_CM_CLIENTID_EXACT]) . "' WHERE VALUE_ID IN (" . implode(", ", $arDeals) . ")";
            $DB->Query($strSql);
        }
    }
}