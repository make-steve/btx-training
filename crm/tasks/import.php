<?php
@define('NOT_CHECK_PERMISSIONS', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../');
if (!defined('DOCUMENT_ROOT')) {
    define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));
}

require(DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_before.php");
global $DB;
$uploadPath = $DOCUMENT_ROOT.'/crm/tasks/import';

if ($_FILES["importfile"]["error"] == UPLOAD_ERR_OK) {
    $tmp_name = $_FILES["importfile"]["tmp_name"];
    
    // basename() may prevent filesystem traversal attacks;
    // further validation/sanitation of the filename may be appropriate
    $name = basename($_FILES["importfile"]["name"]);
    $destination = "$uploadPath/$name";
    move_uploaded_file($tmp_name, $destination);

    if (file_exists($destination)) {

        $tasksXml = simplexml_load_file($destination);
        
        if (!empty($tasksXml)) {
            
            foreach ($tasksXml->Project as $xml) {
                
                foreach ($xml->attributes() as $key => $value) {
                    if ('taskid' == strtolower($key)) {

                        
                        // query the proper task ID;
                        $value = trim($value);
                        $select = 'select * from b_tasks where XML_ID="'.$DB->ForSQL($value).'" order by ID desc limit 1';
                        $result = $DB->Query($select);
                        if ($task = $result->Fetch()) {

                            $tasklistUpdate = false;
                            // $budget = !empty($xml->budget)?doubleval($xml->budget):0.00;
                            $betaald = !empty($xml->Betaald)?doubleval($xml->Betaald):0.00;
                            // $kosten_uren = isset($xml->KostenUren)?doubleval($xml->KostenUren):0.00;
                            $kosten_derden = !empty($xml->KostenDerden)?doubleval($xml->KostenDerden):0.00;
                            $kosten_lift = !empty($xml->KostenDerdenLift)?doubleval($xml->KostenDerdenLift):0.00;
                            $inkomsten = !empty($xml->Inkomsten)?doubleval($xml->Inkomsten):0.00;
                            
                            // get latest row
                            $select = 'select * from m_tasklist_fields where TASK_ID="'.$DB->ForSQL($task['ID']).'" order by ID desc limit 1';
                            
                            $rsRow = $DB->Query($select);
                            if ($row = $rsRow->Fetch()) {
                                
                                $update = 'update m_tasklist_fields set BETAALD="'.$DB->ForSQL($betaald).'", KOSTEN_DERDEN="'.$DB->ForSQL($kosten_derden).'", KOSTEN_LIFT="'.$DB->ForSQL($kosten_lift).'", INKOMSTEN="'.$DB->ForSQL($inkomsten).'" where ID="'.$DB->ForSQL($row['ID']).'" LIMIT 1';
                                if ($DB->Query($update))
                                    $tasklistUpdate = true;
                                
                            } else {
                                $insert = 'insert into m_tasklist_fields';
                                $insert .= ' (`TASK_ID`, `ADDED_DESCRIPTION`, `INVOICE_AMOUNT`, `PERCENT_DONE`, `OTHER_COMMENTS`, `BETAALD`, `KOSTEN_DERDEN`, `KOSTEN_LIFT`, `INKOMSTEN`)';
                                $insert .= ' VALUES';
                                $insert .= ' ("'.$DB->ForSQL($task['ID']).'", " ", "'.$DB->ForSQL($betaald).'", "1", " ", "'.$DB->ForSQL($betaald).'", "'.$DB->ForSQL($kosten_derden).'", "'.$DB->ForSQL($kosten_lift).'", "'.$DB->ForSQL($inkomsten).'")';

                                if ($DB->Query($insert))
                                    $tasklistUpdate = true;
                            }

                            if ($tasklistUpdate) {
                                // get the task's parent deal;
                                $select = 'select * from b_utm_tasks_task where VALUE_ID="'.$DB->ForSQL($task['ID']).'" order by ID desc';
                                $result = $DB->Query($select);
                                while ($utmTasks = $result->Fetch()) {

                                    if (stripos($utmTasks['VALUE'], 'd_')!==false) {
                                        $dealid = str_ireplace('D_', '', $utmTasks['VALUE']);
                                        $dealid = intval($dealid);

                                        $mod_project_task = new mod_project_task($dealid, $task['ID']);
                                        $mod_project_task->setTaskData('THIRD_PARTY_COST', $kosten_derden, 'ACTUAL');
                                        $mod_project_task->setTaskData('COST_LIFT', $kosten_lift, 'ACTUAL');
                                        $mod_project_task->setTaskData('REVENUE', $inkomsten, 'ACTUAL');
                                        $mod_project_task->setTaskData('PAID', $betaald, 'ACTUAL');

                                        // update the project's Gefactureerd value under Werkelijke;
                                        $select = 'select * from m_project_task_actual where DEAL_ID="'.$DB->ForSQL($dealid).'" and TASK_ID="'.$DB->ForSQL($task['ID']).'" order by ACTUAL_ID desc limit 1';
                                        $result = $DB->Query($select);
                                        if ($actual = $result->Fetch()) {

                                            // $billed = $actual['BILLED']+$xml->Gefactureerd;
                                            $billed = $xml->Gefactureerd;
                                            $billed = doubleval($billed);

                                            $update = 'update m_project_task_actual set BILLED="'.$billed.'" where ACTUAL_ID="'.$DB->ForSQL($actual['ACTUAL_ID']).'" limit 1';
                                            $DB->Query($update);
                                        }
                                    }
                                }
                            }
                        
                        } else {

                        }
                    }
                }
            }
            LocalRedirect('/crm/tasks/?imported=Y');
        }
    }
}

LocalRedirect('/crm/tasks/?imported=N');
