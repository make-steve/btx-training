<?php
include 'mod_project_fields.php';

class Project
{
    private $_DB;
    public $dealFields = array('ID', 'TITLE', 'COMPANY_ID', 'CONTACT_ID', 'TYPE_ID', 'CREATED_BY_ID');
    public $companyFields = array('ID', 'TITLE');
    public $contactFields = array('ID', 'FULL_NAME', 'NAME', 'LAST_NAME', 'HONORIFIC', 'SECOND_NAME');
    private $crmStatus = array();

    private $nullValue = '—';
    private $allFinancial = array();
    private $dealFinancial = array();
    private $taskFinancial = array();
    private $dealAcquisitie = array();

    public $dealUF = array(
        'UF_CRM_1511431411' => 'KLANT_NAAM',
        'UF_CRM_1493724413' => 'TECHNOLOGIE',
    );
    public $companyUF = array(
        'UF_CRM_1511437015' => 'BEZOEKADRES',
        'UF_CRM_1511437033' => 'POSTCODE',
        'UF_CRM_1511437053' => 'PLAATS',
        'UF_CRM_1511437080' => 'FACTUURBEDRIJF',
        'UF_CRM_1511437095' => 'FACTUURADRES',
        'UF_CRM_1511437111' => 'TAV',
        'UF_CRM_1511437130' => 'CLIENTEXACTID',
    );
    public $contactUF = array(
        'UF_CRM_1511437982' => 'POSTADRES',
        'UF_CRM_1511437995' => 'POSTCODE',
        'UF_CRM_1511438007' => 'PLAATS'
    );
    private $taskUF = array(
        'COST_IF' => 'UF_AUTO_775103888970',
        'THIRD_PARTY_COST' => 'UF_AUTO_834260910111',
        'COST_LIFT' => 'UF_AUTO_347562553598',
        'RESCUE' => 'UF_AUTO_197833829978',
        'REVENUE' => 'UF_AUTO_276127427586',
        'ACQUISITION_COSTS' => 'UF_AUTO_132407273723',
        'REFERENTIE_KLANT' => 'UF_AUTO_192582141226',
        'FACTUURMOMENTEN' => 'UF_AUTO_371316967809',
        'LOCKED' => 'UF_AUTO_713286985141',
        'COMPANY' => 'UF_AUTO_192582141227',
    );

    public function __construct()
    {
        global $DB;

        $this->_DB = $DB;

        $statusList = array(
            'DEAL_TYPE'
        );

        $this->crmStatus = $this->getCrmStatus($statusList);
    }

    public function getList($arFilter, $arSort, $arPagination = array())
    {
        $arProjectList = array();
        $arProjectIds = array();

        $filter = $this->buildProjectFilter($arFilter);
        $sort = $this->buildProjectSort($arSort);
        $limit = $this->buildQueryPager($arPagination);
        
        $filter .= " {$sort} {$limit}";
        $dbGetProject = 'SELECT * FROM `m_project` p ' . $filter;
        $rsGetProject = $this->_DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arProjectList = (array)$rsGetProject;
        while($arGetProject = $rsGetProject->fetch()) {
            $arProjectIds[] = $arGetProject['ID'];
        }

        $arProjectList['DATA'] = $this->getProjectDetail($arProjectIds, false, $sort);

        return $arProjectList;
    }

    function getListTotal($arFilter) {

        $filter = $this->buildProjectFilter($arFilter);

        $dbGetProject = 'SELECT * FROM `m_project` ' . $filter;
        $rsGetProject = $this->_DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        return $rsGetProject->SelectedRowsCount();
    }

    public function getProjectSimpleData($projectId) {

        $dbGetProject = 'SELECT * FROM `m_project` WHERE `ID` = ' . $this->_DB->ForSql(intval($projectId));
        $rsGetProject = $this->_DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        return $rsGetProject;
    }

    public function getProjectDetail($projecIds = array(), $getTask = false, $sort = "") {

        // get project
        $dbGetProject = 'SELECT p.*,'.
                            'pcom.`COMPANY_ID` CUSTOM_COM_ID,'.
                            'pcom.`COMPANY_NAME` COMPANY_NAME,'.
                            'pcon.`CONTACT_ID` CUSTOM_CON_ID,'.
                            'pcom.`PROJECT_ID` COMPANY_PROJECT_ID,'.
                            'pcom.`ADDRESS` COMPANY_ADDRESS,'.
                            'pcom.`POSTCODE` COMPANY_POSTCODE,'.
                            'pcom.`CITY` COMPANY_CITY,'.
                            'pcom.`ADDRESS_INVOICE` COMPANY_ADDRESS_INVOICE,'.
                            'pcom.`ADDRESS_BILLING` COMPANY_ADDRESS_BILLING,'.
                            'pcom.`TAV` COMPANY_TAV,'.
                            'pcon.`SALUTATION` CONTACT_SALUTATION,'.
                            'pcon.`LAST_NAME` CONTACT_LAST_NAME,'.
                            'pcon.`MIDDLE_NAME` CONTACT_MIDDLE_NAME,'.
                            'pcon.`NAME` CONTACT_NAME,'.
                            'pcon.`POST_ADD` CONTACT_POST_ADD,'.
                            'pcon.`POSTCODE` CONTACT_POSTCODE,'.
                            'pcon.`CITY` CONTACT_CITY,'.
                            'pcon.`TELEPHONE` CONTACT_TELEPHONE,'.
                            'pcon.`EMAIL` CONTACT_EMAIL '.
                        'FROM `m_project` p '.
                        'LEFT JOIN `m_project_company` pcom '.
                            'ON p.`ID`=pcom.`PROJECT_ID` '.
                        'LEFT JOIN `m_project_contact` pcon '.
                            'ON p.`ID`=pcon.`PROJECT_ID` '.
                        'WHERE p.`ID` IN ("'.implode('","', $projecIds).'") AND p.`ACTIVE` = "Y" ' . $sort;

        $rsGetProject = $this->_DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arFields = array();
        while($arGetProject = $rsGetProject->fetch()) {

            $fields = $arGetProject;
            $arDeal = array();

            // Get Product group
            $fields['PRODUCT_GROUP'] = $this->crmStatus['DEAL_TYPE'][$fields['PRODUCT_GROUP']]['NAME'];

            // Get Technology
            $fields['TECHNOLOGY'] = $this->getEnumValueById(intval($fields['TECHNOLOGY']));

            
            // get deals connected to project
            $arDealList = $this->getDealByProjectId($fields['ID']);
            if(!empty($arDealList)) {

                $arDealFields = $this->dealFields;

                // Get all deals data
                $extraFilter = array('STAGE_ID' => 'WON');
                $arDealInfos = $this->getDealByIds($arDealList, $arDealFields, $extraFilter);

                $arCompany = array(
                    'ID' => $fields['COMPANY_ID'],
                    'PROJECT_ID' => $fields['COMPANY_PROJECT_ID'],
                    'ADDRESS' => $fields['COMPANY_ADDRESS'],
                    'POSTCODE' => $fields['COMPANY_POSTCODE'],
                    'CITY' => $fields['COMPANY_CITY'],
                    'ADDRESS_INVOICE' => $fields['COMPANY_ADDRESS_INVOICE'],
                    'ADDRESS_BILLING' => $fields['COMPANY_ADDRESS_BILLING'],
                    'TAV' => $fields['COMPANY_TAV']
                );

                $contactFullname = trim($fields['CONTACT_SALUTATION']." ".$fields['CONTACT_NAME']." ".$fields['CONTACT_MIDDLE_NAME']." ".$fields['CONTACT_LAST_NAME']);
                $arContact = array(
                    'ID' => $fields['CONTACT_ID'],
                    'FULL_NAME' => $contactFullname,
                    'SALUTATION' => $fields['CONTACT_SALUTATION'],
                    'LAST_NAME' => $fields['CONTACT_LAST_NAME'],
                    'MIDDLE_NAME' => $fields['CONTACT_MIDDLE_NAME'],
                    'NAME' => $fields['CONTACT_NAME'],
                    'POST_ADD' => $fields['CONTACT_POST_ADD'],
                    'POSTCODE' => $fields['CONTACT_POSTCODE'],
                    'CITY' => $fields['CONTACT_CITY'],
                    'TELEPHONE' => $fields['CONTACT_TELEPHONE'],
                    'EMAIL' => $fields['CONTACT_EMAIL'],
                    'LINK' => "<a href='/crm/contact/show/".$fields['CONTACT_ID']."/' title='".$contactFullname."'>".$contactFullname."</a>"
                );

                $fields['COMPANY'] = $arCompany;
                $fields['CONTACT'] = $arContact;

                foreach($arDealInfos as $arDealInfo) {

                    $arCompany = $arContact = array();
                    $arDealId = intval($arDealInfo['ID']);

                    $arDealInfo['CREATED_BY_OBJ'] = $this->getUserObject(intval($arDealInfo['CREATED_BY_ID']));
                    $arDealInfo['CONTACT_PERSON'] = $this->getContactObject(intval($arDealInfo['CONTACT_ID']));

                    $arDeal[$arDealId] = $arDealInfo;
                }

                // Get task under deals
                if($getTask) {
                    $arTaskList = $this->getTaskByDealIds($arDealList);

                    foreach($arDeal as $__arDealKey => $__arDealVal) {

                        if(!empty($arTaskList[$__arDealKey]))
                            $arDeal[$__arDealKey]['TASK'] = $arTaskList[$__arDealKey];
                    }
                }
            }


            // financial summberies and task summaries 
            $arTaskFinancial = $this->getFinancialSummaries();
            $fields['PROJECT_FINANCIAL'] = $this->getProjectFinFinal($this->allFinancial);
            $fields['DEAL_FINANCIAL'] = $this->getDealFinFinal($this->dealFinancial);

            // check if the task can be deleted
            // if theres some value on actual. task cant be deleted
            foreach($arDeal as $__arDealId => $__arDealTask) {
                if(!empty($__arDealTask['TASK'])) {

                    foreach($__arDealTask['TASK'] as $__arTaskId => $__arTask) {

                        $total = 0;
                        foreach($arTaskFinancial[$__arDealId][$__arTaskId] as $__arTaskFinan) {

                            $total += doubleVal($__arTaskFinan['DATA']['ACTUAL']);
                        }

                        $arDeal[$__arDealId]['TASK'][$__arTaskId]['CAN_DELETE'] = ($total > 0) ? 'N' : 'Y';
                    }
                }
            }

            $fields['TASK_FINANCIAL'] = $arTaskFinancial;
            $fields['DEALS'] = $arDeal;
            $arFields[$fields['ID']] = $fields;
        }
        
        return $arFields;
    }

    private function getTaskByDealIds($dealIds = array()) {

        $typeTaskId = 3;
        $arTask = array();
        CModule::IncludeModule('tasks');

        if(!empty($dealIds) && count($dealIds)) {

            $dbGetTasks = 'SELECT * FROM '.
                                '`b_crm_act` ca '.
                            'LEFT JOIN `b_tasks` t '.
                            'ON ca.`ASSOCIATED_ENTITY_ID` = t.`ID` '.
                            'WHERE '.
                                'ca.`OWNER_ID` IN ("'.implode('","' , $dealIds).'") AND '.
                                'ca.`TYPE_ID` = '.$typeTaskId.' ORDER BY t.`CREATED_DATE` ASC;';

            $rsGetTasks = $this->_DB->Query($dbGetTasks, false, "File: ".__FILE__."<br>Line: ".__LINE__);

            $arTaskids = array();
            while($arGetTasks = $rsGetTasks->fetch()) {

                $arTaskids[] = $arGetTasks['ID'];

                $dbGetTasksObservers = 'SELECT USER_ID from `b_tasks_member` WHERE `TASK_ID` = ' . $arGetTasks['ID'] . " AND TYPE = 'U'";
                $resGetTaskObservers = $this->_DB->Query($dbGetTasksObservers, false, "File: ".__FILE__."<br>Line: ".__LINE__);

                $mainObserver = null;
                if($arTasksMember = $resGetTaskObservers->Fetch())
                    $mainObserver = $arTasksMember["USER_ID"];

                $arTask[$arGetTasks['OWNER_ID']][$arGetTasks['ID']] = array(
                    'ID' => $arGetTasks['ID'],
                    'SUBJECT' => $arGetTasks['SUBJECT'],
                    'TYPE_ID' => $arGetTasks['TYPE_ID'],
                    'OWNER_ID' => $arGetTasks['OWNER_ID'],
                    'OWNER_TYPE_ID' => $arGetTasks['OWNER_TYPE_ID'],
                    'CREATED_BY_ID' => $arGetTasks['CREATED_BY'],
                    'CREATED_BY_OBJ' => $this->getUserObject(intval($arGetTasks['CREATED_BY'])),
                    'RESPONSIBLE_ID' =>  $arGetTasks['RESPONSIBLE_ID'],
                    'RESPONSIBLE_OBJ' => $this->getUserObject(intval($arGetTasks['RESPONSIBLE_ID'])),
                    'TASK_NUMBER' => $arGetTasks['XML_ID'],
                    //'TASK_NUMBER' => $arGetTasks['OWNER_ID'].".".$arGetTasks['ID'],
                    'STATUS' => ucfirst(tasksStatus2String($arGetTasks['STATUS'])),
                );

                if(!empty($mainObserver)) {
                    $arTask[$arGetTasks['OWNER_ID']][$arGetTasks['ID']]["OBSERVER_BY_ID"] = $mainObserver;
                    $arTask[$arGetTasks['OWNER_ID']][$arGetTasks['ID']]["OBSERVER_BY_OBJ"] = $this->getUserObject(intval($mainObserver));
                }
            }

            // get Task ufs
            $arTaskUfs = $this->getTaskUf($arTaskids);

            foreach($arTask as $dealKey => $task) {

                foreach($task as $_tasKey => $_task) {

                    $arTask[$dealKey][$_tasKey]['UF'] = $arTaskUfs[$_tasKey];
                    $origData = $arTaskUfs[$_tasKey];

                    $mod_project_task = new mod_project_task(intval($dealKey), intval($_tasKey));
                    $newData = $mod_project_task->setUpOrigValue($origData);

                    $this->taskFinancial[$dealKey][$_tasKey] = $newData;
                }
            }

            // get Acquisitie kosten

             foreach($arTask as $dealKey => $task) {

                $mod_project_task = new mod_project_task(intval($dealKey), 0);
                $arAcqValue = $mod_project_task->getAcquisitieKosten();

                $this->dealAcquisitie[$dealKey] = $arAcqValue;
            }

            return $arTask;
        }
    }

    function getProjectFinFinal($arData) {

        $mod_project_task = new mod_project_task(0, 0);

        $arReturn = array();
        // set data
        $mod_project_task->setAllData($arData);
        // Calculate total cost
        $mod_project_task->calculate_totalCost();
        // Calculate Rendement
        $mod_project_task->calculate_rescue();

        $_arDatas = $mod_project_task->getAllData();

        // format arrays
        foreach($_arDatas as $__key => $__arDatas) {

            foreach($__arDatas as $inKey => $inVal) {
            
                if($inKey == 'COST_LIFT')
                    $arReturn[$inKey]['CLASS'] = 'value-underline';
                
                if($inKey == 'TOTAL_COSTS')
                    $arReturn[$inKey]['CLASS'] = 'value-bold';

                $arReturn[$inKey]['DATA'][$__key] = $inVal;
            }
        }
        return $arReturn;
    }

    function getDealFinFinal($arData) {

        $arReturn = array();
        if(!empty($arData)) {

            foreach($arData as $dealId => $data) {

                $mod_project_task = new mod_project_task(0, 0);

                // set data
                $mod_project_task->setAllData($data);
                // Calculate total cost
                $mod_project_task->calculate_totalCost();
                // Calculate Rendement
                $mod_project_task->calculate_rescue();

                $_arDatas = $mod_project_task->getAllData();

                // format arrays
                foreach($_arDatas as $__key => $__arDatas) {

                    foreach($__arDatas as $inKey => $inVal) {
                    
                        if($inKey == 'COST_LIFT')
                            $arReturn[$dealId][$inKey]['CLASS'] = 'value-underline';

                        if($inKey == 'TOTAL_COSTS')
                            $arReturn[$dealId][$inKey]['CLASS'] = 'value-bold';

                        $arReturn[$dealId][$inKey]['DATA'][$__key] = $inVal;
                    }
                }
            }
        }

        return $arReturn;
    }



    function getFinancialSummaries() {

        $mod_project_task = new mod_project_task(0, 0); // just assigned it class and value will be assinged later
        $arTaskFinancials = $this->taskFinancial;
        $arDealAcq = $this->dealAcquisitie;
        $arTaskFinancial = array();
        
        if(!empty($arTaskFinancials)) {

            // set acquisitie kosten first
            foreach($arTaskFinancials as $dealId => $task) {

                $arCurDealAcq = $arDealAcq[$dealId];

                // deal financial
                $arTaskFinancials[$dealId][0]['BUDGET']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['BUDGET']['ACQUISITION_COSTS']);
                $arTaskFinancials[$dealId][0]['ACTUAL']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['ACTUAL']['ACQUISITION_COSTS']);
                $arTaskFinancials[$dealId][0]['DIFF']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['DIFF']['ACQUISITION_COSTS']);

                $arTaskFinancials[$dealId][0]['BUDGET']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['BUDGET']['ACQUISITION_COSTS']);
                $arTaskFinancials[$dealId][0]['ACTUAL']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['ACTUAL']['ACQUISITION_COSTS']);
                $arTaskFinancials[$dealId][0]['DIFF']['ACQUISITION_COSTS'] = doubleVal($arCurDealAcq['DIFF']['ACQUISITION_COSTS']);
            }


            foreach($arTaskFinancials as $dealId => $task) {

                $arDealFinancial = array();

                foreach($task as $taskId => $taskFinancial) {

                    foreach($taskFinancial as $finanCol => $finanValue) {

                        foreach($finanValue as $key => $val) {

                            // format arrays
                            if($key == 'COST_LIFT')
                                $arTaskFinancial[$dealId][$taskId][$key]['CLASS'] = 'value-underline';
                            if($key == 'TOTAL_COSTS')
                                $arTaskFinancial[$dealId][$taskId][$key]['CLASS'] = 'value-bold';

                            $arTaskFinancial[$dealId][$taskId][$key]['DATA'][$finanCol] = $val;

                            // add all for deal
                            if(!array_key_exists($key, $this->dealFinancial[$dealId][$finanCol]))
                                $this->dealFinancial[$dealId][$finanCol][$key] = $val;
                            else {

                                $oldVal = doubleVal($this->dealFinancial[$dealId][$finanCol][$key]);
                                $newVal = doubleVal($val);
                                $total = doubleVal($oldVal + $newVal);

                                $this->dealFinancial[$dealId][$finanCol][$key] = $total;
                            }

                            // add all for project
                            if(!array_key_exists($key, $this->allFinancial[$finanCol]))
                                $this->allFinancial[$finanCol][$key] = $val;
                            else {

                                $oldVal = doubleVal($this->allFinancial[$finanCol][$key]);
                                $newVal = doubleVal($val);
                                $totalAll = doubleVal($oldVal + $newVal);

                                $this->allFinancial[$finanCol][$key] = $totalAll;
                            }
                        }
                    }
                }
            }
        }

        return $arTaskFinancial;
    }

    function getLogHoursPerTask($deal, $task) {

        $totalHrs = 0;
        $dbGetUserLog = 'SELECT * FROM `m_timetable_log` WHERE `DEAL_ID` = '.intval($deal).' AND `TASK_ID` = '.intval($task).'; ';
        $rsGetUserLog = $this->_DB->Query($dbGetUserLog, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        while($arGetUserLog = $rsGetUserLog->fetch()) {

            $totalHrs = doubleVal($totalHrs) + doubleVal($arGetUserLog['TOTAL_RATE']);
        }

        return $totalHrs;
    }

    private function getTaskUf($arTask) {

        $taskUf = array();
        $arTaskFields = $this->taskUF;
        $dbGetTaskUF = 'SELECT * FROM `b_uts_tasks_task` WHERE `VALUE_ID` IN ("' . implode('","', $arTask).'")';
        $rsGetTaskUF = $this->_DB->Query($dbGetTaskUF, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        while($arGetTaskUF = $rsGetTaskUF->fetch()) {

            $companyName = "";
            if(intval($arGetTaskUF[$arTaskFields['COMPANY']]) > 0) {

                $arTaskCompany = $this->getCompanyByIds(intval($arGetTaskUF[$arTaskFields['COMPANY']]));
                $companyName = $arTaskCompany['TITLE'];
            }

            $taskUf[$arGetTaskUF['VALUE_ID']] = array(
                'COST_IF' => $arGetTaskUF[$arTaskFields['COST_IF']],
                'THIRD_PARTY_COST' => $arGetTaskUF[$arTaskFields['THIRD_PARTY_COST']],
                'COST_LIFT' => $arGetTaskUF[$arTaskFields['COST_LIFT']],
                'RESCUE' => $arGetTaskUF[$arTaskFields['RESCUE']],
                'ACQUISITION_COSTS' => $arGetTaskUF[$arTaskFields['ACQUISITION_COSTS']],
                'REVENUE' => $arGetTaskUF[$arTaskFields['REVENUE']],
                'REFERENTIE_KLANT' => $arGetTaskUF[$arTaskFields['REFERENTIE_KLANT']],
                'FACTUURMOMENTEN' => $arGetTaskUF[$arTaskFields['FACTUURMOMENTEN']],
                'COMPANY_ID' => $arGetTaskUF[$arTaskFields['COMPANY']],
                'COMPANY_NAME' => $companyName,
            );
        }

        return $taskUf;
    }

    private function getUserObject($userid) {

        global $USER;

        $arUserObj = array();
        $dbUser = $USER->GetById(intval($userid));

        if($arUser = $dbUser->fetch()) {

            $fullname = $arUser['NAME'].' '.$arUser['LAST_NAME'];
            $arUserObj = array(
                'ID' => $arUser['ID'],
                'FULL_NAME' => $fullname,
                'NAME' => $arUser['NAME'],
                'LAST_NAME' => $arUser['LAST_NAME'],
                'LINK' => "<a href='/company/personal/user/".$userid."/'>".$fullname."</a>",
            );
        }

        return $arUserObj;
    }

    private function getContactObject($contactid) {

        $fields = (!empty($arFields)) ? "`" . implode("`,`", $arFields) . "`" : "*";
        $dbGetContact = 'SELECT '.$fields.' FROM `b_crm_contact` WHERE `ID` = '.intval($contactid).';';
        $rsContact = $this->_DB->Query($dbGetContact, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arContact = array();
        if($arGetContact = $rsContact->fetch()) {
           
            $contactFullname = $arGetContact['CONTACT_SALUTATION']." ".$arGetContact['NAME']." ".$arGetContact['MIDDLE_NAME']." ".$arGetContact['LAST_NAME'];
            $arContact = array(
                'FULL_NAME' => $contactFullname,
                'NAME' => $arGetContact['NAME'],
                'LAST_NAME' => $arGetContact['LAST_NAME'],
                'LINK' => "<a href='/crm/contact/show/".$arGetContact['CONTACT_ID']."/' title='".$contactFullname."'>".$contactFullname."</a>"
            );
        }

        return $arContact;
    }

    private function buildQueryPager($pager)
    {
        $limit = '';
        if (isset($pager['LIMIT']) && isset($pager['OFFSET'])) {
            $limit = " LIMIT ".$pager['OFFSET'].", ".$pager['LIMIT'];
        }

        return $limit;
    }

    private function buildProjectFilter($arFilter) {

        $_filter = array();
        $returnFilter = "";

        foreach($arFilter as $key => $filter) {

            if (stripos($key, '%')===0) {
                $key = str_ireplace('%', '', $key);
                $_filter[] = "(`".$key."` LIKE '%".$this->_DB->ForSql($filter)."%' OR " . "`ID` LIKE '%".$this->_DB->ForSql($filter)."%')";
            } else {
                $_filter[] = "`".$key."` = '".$this->_DB->ForSql($filter)."'";
            }
            
        }

        $returnFilter = (!empty($_filter) && count($_filter) > 0) ? "WHERE ".implode(" AND ", $_filter) : "";

        return $returnFilter;
    }
 
    private function buildProjectSort($arSort) {

        $_sort = array();
        foreach($arSort['sort'] as $sortKey => $sortValue) {

            switch (strtoupper($sortKey)) {
                case 'ID':
                case 'TITLE':
                case 'CLIENT_NAME':
                    $prefix = "p.";
                    $_sort[] = $prefix . "`".strtoupper($sortKey)."` ".strtoupper($sortValue);
                    break;

                case 'TECHNOLOGY':

                    $ufList = $this->getTaskUFList('TECHNOLOGY', strtoupper($sortValue));
                    $prefix = "p.";
                    $_sort[] = "FIELD(".$prefix."TECHNOLOGY, '".implode("','", $ufList)."') ";
                    break;

                case 'PRODUCT_GROUP':

                    $ufList = $this->getTaskUFList('PRODUCT_GROUP', strtoupper($sortValue));
                    $prefix = "p.";
                    $_sort[] = "FIELD(".$prefix."PRODUCT_GROUP, '".implode("','", $ufList)."') ";
                    break;
                      
                default:
                    # code...
                    break;
            }
        }

        //exit();
        return (!empty($_sort) && count($_sort) > 0) ? "ORDER BY ".implode(" , ", $_sort) : "";
    }

    private function getTaskUFList($field, $sort) {

        global $USER_FIELD_MANAGER;
        $modTask = new mod_task_data(0);
       
        $ufDpFields = $modTask->getUF();
        $taskUFDpValue = $USER_FIELD_MANAGER->GetUserFields("TASKS_TASK", 0, $langID);

        $arDpList = array();
        foreach($taskUFDpValue as $taskDp) {

            if($taskDp['FIELD_NAME'] == $ufDpFields[$field]) {

                $enumEntity = new \CUserFieldEnum();
                $dbResultEnum = $enumEntity->GetList(
                    array('VALUE' => strtoupper($sort)),
                    array('USER_FIELD_ID' => intval($taskDp['ID']))
                );
                
                
                $listItems = array();

                // this is for product group with 0 value
                if($field == 'PRODUCT_GROUP' && strtoupper($sort) == 'ASC')
                    $listItems = array("0" => 0);

                while($enum = $dbResultEnum->Fetch()) {
                    $listItems[$enum['XML_ID']] = $enum['XML_ID'];
                }

                // this is for product group with 0 value
                if($field == 'PRODUCT_GROUP' && strtoupper($sort) == 'DESC')
                    $listItems[] = 0;

                return $listItems;
            }
        }

        return "";
    }

    function getDealByProjectId($projectId) {

        $dbGetDeals = 'SELECT * FROM `m_deal_project` WHERE `PROJECT_ID` = '.intval($projectId).';';

        $rsDealsProject = $this->_DB->Query($dbGetDeals, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arDealsList = array();
        while($arGetDealsProject = $rsDealsProject->fetch()) {
            $arDealsList[] = intval($arGetDealsProject['DEAL_ID']);
        }

        return $arDealsList;
    }

    function getProjectByDealId($dealId) {

        $dbGetProject = 'SELECT `PROJECT_ID` FROM `m_deal_project` WHERE `DEAL_ID` = '.intval($dealId).';';

        $rsProjectDeal = $this->_DB->Query($dbGetProject, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $projectID = 0;
        if($arGetProjectDeal = $rsProjectDeal->fetch()) {
            $resProject = $this->getProjectSimpleData($arGetProjectDeal["PROJECT_ID"]);
            $arProjectData = $resProject->Fetch();
            return $arProjectData;
        }
        else
            return null;
    }

    function getDealByIds($arDealId, $arFields = array(), $extraFilter = array()) {

        $fields = (!empty($arFields)) ? "`" . implode("`,`", $arFields) . "`" : "*";
        $dbGetDeal = 'SELECT '.$fields.' FROM `b_crm_deal` WHERE `ID` IN("'.implode('","', $arDealId).'");';

        if(!empty($extraFilter)) {

            $newFilter = array();
            foreach($extraFilter as $key => $filter) {
                $newFilter[] = "`".$key."` = '".$filter."'";
            }
            $dbGetDeal = 'SELECT '.$fields.' FROM `b_crm_deal` WHERE `ID` IN("'.implode('","', $arDealId).'") AND '.implode(" AND ", $newFilter).';';
        }

        $rsDeal = $this->_DB->Query($dbGetDeal, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arDeal = array();
        while($arGetDeal = $rsDeal->fetch()) {
            $arDeal[] = $arGetDeal;
        }

        return $arDeal;
    }

    function getCompanyByIds($company_id, $arFields = array()) {

        $fields = (!empty($arFields)) ? "`" . implode("`,`", $arFields) . "`" : "*";
        $dbGetCompany = 'SELECT '.$fields.' FROM `b_crm_company` WHERE `ID` = '.intval($company_id).';';
        $rsCompany = $this->_DB->Query($dbGetCompany, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arCompany = array();
        if($arGetCompany = $rsCompany->fetch()) {
            $arCompany = $arGetCompany;
            $arCompany['FM'] = $this->getMultiFields($arGetCompany['ID'], 'COMPANY');
        }

        return $arCompany;
    }

    function getContactByIds($contactid, $arFields = array()) {

        $fields = (!empty($arFields)) ? "`" . implode("`,`", $arFields) . "`" : "*";
        $dbGetContact = 'SELECT '.$fields.' FROM `b_crm_contact` WHERE `ID` = '.intval($contactid).';';
        $rsContact = $this->_DB->Query($dbGetContact, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arContact = array();
        if($arGetContact = $rsContact->fetch()) {
            $arContact = $arGetContact;
            $arContact['FM'] = $this->getMultiFields($arGetContact['ID'], 'CONTACT');
        }

        return $arContact;
    }

    function getMultiFields($entityId, $entityType) {

        $dbQuery= "SELECT *  FROM `b_crm_field_multi` WHERE `ELEMENT_ID` = '".intval($entityId)."' AND `ENTITY_ID` = '".$entityType."' AND `TYPE_ID` IN ('EMAIL', 'PHONE', 'WEB', 'IM')";
        $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arMulti = array();
        while($arResult = $rsQuery->Fetch()) {
            $arMulti[$arResult['TYPE_ID']][] = array('VALUE_TYPE' => $arResult['VALUE_TYPE'], 'VALUE' => $arResult['VALUE']);
        }

        return $arMulti;
    }

    function getCrmStatus($arEntityId) {

        $dbQuery= "SELECT * FROM `b_crm_status` WHERE `ENTITY_ID` IN ('".implode("','", $arEntityId)."')";
        $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arStatus = array();
        while($arResult = $rsQuery->Fetch()) {
            $arStatus[$arResult['ENTITY_ID']][$arResult['STATUS_ID']] = array(
                'ID' => $arResult['ID'],
                'ENTITY_ID' => $arResult['ENTITY_ID'],
                'STATUS_ID' => $arResult['STATUS_ID'],
                'NAME' => $arResult['NAME'],
                'NAME_INIT' => $arResult['NAME_INIT'],
                'SORT' => $arResult['SORT'],
                'SYSTEM' => $arResult['SYSTEM']
            );
        }

        return $arStatus;
    }

    function getUserFieldInfos($arUF, $crmType = '') {

        $dbQuery = "SELECT ".
                            "uf.`ID` as UF_ID,".
                            "ufe.`ID` as UF_ENUM_ID,".
                            "uf.`ENTITY_ID`, ".
                            "uf.`FIELD_NAME`, ".
                            "uf.`USER_TYPE_ID`, ".
                            "uf.`XML_ID`, ".
                            "uf.`MULTIPLE`, ".
                            "ufe.`USER_FIELD_ID`, ".
                            "ufe.`VALUE` ".
                        "FROM ".
                        "`b_user_field` uf ".
                        "LEFT JOIN b_user_field_enum ufe ".
                            "ON uf.`ID` = ufe.`USER_FIELD_ID` ".
                        "WHERE ".
                            "uf.`FIELD_NAME` IN ('".implode("','", $arUF)."') AND ".
                            "uf.`ENTITY_ID` = '".$crmType."'";

        $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        $arUserFields = array();
        $arUTM = array();
        $arUTS = array();
        while($arResult = $rsQuery->Fetch()) {
            
            if($arResult['USER_TYPE_ID'] == 'enumeration') {
                $arUserFields['DATA']['ENUM'][$arResult['FIELD_NAME']][$arResult['UF_ENUM_ID']] = $arResult;
            }
            else 
                $arUserFields['DATA']['OTHER'][$arResult['FIELD_NAME']] = $arResult;

            if($arResult['MULTIPLE'] == 'Y') {
                $arUserFields['UTM'][$arResult['FIELD_NAME']] = array('CODE' => $arResult['FIELD_NAME'], 'ID' => $arResult['UF_ID'], 'TYPE' => ($arResult['USER_TYPE_ID'] == 'enumeration') ? 'list' : 'string', 'UF_TYPE' => 'utm');
            }
            else {
                $arUserFields['UTS'][$arResult['FIELD_NAME']] = array('CODE' => $arResult['FIELD_NAME'], 'ID' => $arResult['UF_ID'], 'TYPE' => ($arResult['USER_TYPE_ID'] == 'enumeration') ? 'list' : 'string', 'UF_TYPE' => 'uts');
            }
        }

        return $arUserFields;
    }

    function getEnumValue($enum, $id) {

        return ($enum[$id]['VALUE'] != '') ? $enum[$id]['VALUE'] : "";
    }

    function getUserFieldValues($crm, $arFieldInfos, $entityIds = array()) {

        if(trim($crm) == '') return ;

        $dbQuery = '';
        $arAllFields = array();
        $arAllFieldsfinal = array();
        if(isset($arFieldInfos['UTM']) && count($arFieldInfos['UTM']) > 0) {

            $arFields = array();
            foreach($arFieldInfos['UTM'] as $utm) {
                $arFields[] = $utm['ID'];
                $arAllFields[$utm['CODE']] = $utm;
            }

            if(!empty($arFields)) {

                $dbQuery = "SELECT * FROM `b_utm_crm_".strtolower($crm)."` WHERE `VALUE_ID` IN ('".implode("','", $entityIds)."') AND `FIELD_ID` IN ('".implode("','", $arFields)."');";
                $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

                $arUtmResult = array();
                while($arResult = $rsQuery->fetch()) {

                    foreach($arAllFields as $_fieldsKey => $_fields) {

                        if($_fields['UF_TYPE'] == 'utm') {
                            if($arResult['FIELD_ID'] == $_fields['ID']) {

                                $resValue = ($arResult['VALUE'] != "") ? $arResult['VALUE'] : intval($arResult['VALUE_INT']);
                                $arAllFields[$_fieldsKey]['VALUES'][] = array(

                                    'VALUE' => $resValue,
                                    'VALUE_NAME' => ($_fields['TYPE'] == 'list') ? $this->getEnumValue($arFieldInfos['DATA']['ENUM'][$_fieldsKey], $resValue) : $resValue
                                );

                                $arAllFieldsfinal[$arResult['VALUE_ID']][$_fieldsKey] = $arAllFields[$_fieldsKey];
                            }
                        }
                    }
                }
            }
        }

        
        if(isset($arFieldInfos['UTS']) && count($arFieldInfos['UTS']) > 0) {

            $arFields = array();
            foreach($arFieldInfos['UTS'] as $uts) {
                $arFields[] = $uts['CODE'];
                $arAllFields[$uts['CODE']] = $uts;
            }

            if(!empty($arFields)) {

                $dbQuery = "SELECT `".implode("`,`", $arFields)."`,`VALUE_ID` FROM `b_uts_crm_".strtolower($crm)."` WHERE `VALUE_ID` IN ('".implode("','", $entityIds)."');";
                $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

                while($arResult = $rsQuery->fetch()) {

                    foreach($arAllFields as $_fieldsKey => $_fields) {

                        if($_fields['UF_TYPE'] == 'uts') {
                            $resValue = $arResult[$_fields['CODE']];
                            $arAllFields[$_fieldsKey]['VALUES'][] = array(
                                'VALUE' => $resValue,
                                'VALUE_NAME' => ($_fields['TYPE'] == 'list') ? $this->getEnumValue($arFieldInfos['DATA']['ENUM'][$_fieldsKey], $resValue) : $resValue
                            );
                        }

                        $arAllFieldsfinal[$arResult['VALUE_ID']][$_fieldsKey] = $arAllFields[$_fieldsKey];
                    }
                }
            }
        }

        return $arAllFieldsfinal;
    }

    function getEnumValueById($ID) {

        $name = '';
        $dbQuery = "SELECT * FROM `b_user_field_enum` WHERE `ID` = '".intval($ID)."'";
        $rsQuery = $this->_DB->Query($dbQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);

        if($arResult = $rsQuery->fetch()) {
            $name = $arResult['VALUE'];
        }

        return $name;
    }

    function getCrmFields($entityType) {

        switch($entityType) {

            case 'DEAL':
                return $this->dealUF;
                break;
            case 'COMPANY':
                return $this->companyUF;
                break;
            case 'CONTACT':
                return $this->contactUF;
                break;
            default:
                break;
        }

        return false;
    }

    function getFieldbyName($name, $type) {

        $ar = array();
        $returnVal = '';
        switch($type) {

            case 'DEAL':
                $ar = $this->dealUF;
                break;
            case 'COMPANY':
                $ar = $this->companyUF;
                break;
            case 'CONTACT':
                $ar = $this->contactUF;
                break;
            default:
                break;
        }

        foreach($ar as $k => $a) {

            if($a == $name) {
                $returnVal = $k;
                break;
            }
        }

        return $returnVal;

    }
}