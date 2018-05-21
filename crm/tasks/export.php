<?php
@define('NOT_CHECK_PERMISSIONS', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../');
if (!defined('DOCUMENT_ROOT'))
    define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));

require(DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_before.php");
global $DB;

if (!empty($_POST['task']) && !empty($_POST['ID'])) {
    $postTasks = array();
    foreach ($_POST['ID'] as $tid) {
        if (isset($_POST['task'][$tid]) && !empty($_POST['task'][$tid])) {
            $postTasks[$tid] = $_POST['task'][$tid];
        }
    }
    
    $tmp = current($postTasks);
    $fieldnames = array_keys($tmp);

    $file = null;

    $headerTranslate = array(
            'project_id' => 'PROJECT NR.',
            'id' => 'TAAK NR.',
            'project_lead' => 'PROJECTLEIDER',
            'client' => 'KLANT',
            'deal_title' => 'DEAL NAAM',
            'task_title' => 'TAAK OMSCHRIJVING',
            'task_locked' => 'LOCKED',
            'ref_client' => 'REFERENTIE KLANT',
            'description' => 'TOELICHTING TBV FACTURATIE',
            'added_description' => 'EXTRA OMSCHRIJVING OP FACTUUR',
            'budget' => 'BUDGET',
            'total' => 'TOTALE KOSTEN',
            'cost_hours' => 'KOSTEN UREN IF',
            'cost_thirdparty' => 'KOSTEN DERDEN',
            'cost_extras' => 'KOSTEN LIFT',
            'revenue' => 'OMZET',
            'paid' => 'BETAALD',
            'task_type' => 'TAAK TYPE',
            'invoice_amount' => 'BEDRAG TE FACTUREREN',
            'percent_done' => '% GEREED',
            'ohw' => 'OHW',
            'expected_costs' => 'TOTAAL VERWACHTE KOSTEN',
            'expected_returns' => 'VERWACHTE RENDEMENT',
            'other_comments' => 'OPMERKINGEN VOOR F&C',
        );
    $amountCols = array(
            'budget',
            'total',
            'cost_hours',
            'cost_thirdparty',
            'cost_extras',
            'revenue',
            'paid',
            'invoice_amount',
            'percent_done',
            'ohw',
            'expected_costs',
            'expected_returns',
        );
    
    
    $exclude = array(
            // 'project_id',
            'deal_id',
            'project_title',
            'project_date_start',
            'clientid_exact',
            'date_start',
            'reference_customer',
            // 'id',
            'task_realid',
            // 'project_lead',
            // 'client',
            'company_id',
            // 'deal_title',
            // 'task_title',
            // 'description',
            // 'task_locked',
            // 'added_description',
            // 'ref_client',
            // 'budget',
            // 'total',
            // 'cost_hours',
            // 'cost_thirdparty',
            // 'cost_extras',
            // 'revenue',
            // 'paid',
            // 'invoice_amount',
            // 'percent_done',
            // 'ohw',
            // 'expected_costs',
            // 'expected_returns',
            // 'other_comments'
        );
    $headers = array();
    foreach ($fieldnames as $header) {
        if (!in_array($header, $exclude)) {
            $headers[] = trim($headerTranslate[$header]);
        }
    }

    $data = array();
    $data[] = array_values($headers);

    reset($postTasks);
    foreach ($postTasks as $key => $ptask) {
        $arvalues = array();
        foreach ($ptask as $pkey => $pval) {
            if (in_array($pkey, $exclude)) {
                continue;
            }

            if (in_array($pkey, $amountCols)) {
                // $pval = str_ireplace(',', '.', $pval);
                // $pval = doubleval($pval);
                // $pval = number_format($pval,2,',','');
            }
            if ('added_description' == $pkey) {
                // $pval = nl2br($pval);
                // $pval = str_ireplace('<br>', '|', $pval);
                $pval = str_replace(["\r\n", "\r", "\n"], ' | ', $pval);
            }
            if ('task_locked' == $pkey) {
                $pval = ($pval==1)?'Y':'N';
            }
            $arvalues[] = trim($pval);
        }

        // $data[] = implode(';', $ptask);
        // $data[] = array_values($ptask);
        $data[] = $arvalues;
    }

    $csvFile = DOCUMENT_ROOT."/crm/tasks/export/takenlijst_".date("Y-m-d").".csv";
    if (file_exists($csvFile))
        unlink($csvFile);
    
    $delimiter = ";";
    $fp = fopen($csvFile, "a+");
    foreach ($data as $fields) {
        fputcsv($fp, $fields, $delimiter);
    }
    fclose($fp);

    if ('xml' == $_POST['export_type']) {
        $exclude = array(
                // 'project_id', // project nr.
                // 'deal_id', // deal nr.
                // 'id', // taak nr.
                'project_lead', // projectleider
                'client', // klant naam
                // 'company_id', // klant ID
                'deal_title', // deal title
                // 'task_title', // taak omschrijving
                // 'ref_client', // referentie klant
                'description', // toelichting tbv facturatie
                // 'added_description', // extra omschrijving op factuur
                // 'budget', // budget
                // 'total', // kosten totaal
                // 'cost_hours', // kosten uren 
                'cost_thirdparty', // kosten derden
                'cost_extras', // kosten lift
                'revenue', // omzet
                'paid', // betaald
                // 'invoice_amount', // -bedrag te factureren
                'percent_done', // % gereed
                'ohw', // ohw
                'expected_costs', // totaal verwachte kosten
                'expected_returns', // verwachte rendement
                'other_comments' // opmerkingen voor f&c
            );

        // no longer needed
        // create xml
        /*$xml = new DOMDocument("1.0");
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $root = $xml->createElement("task_list");
        $xml->appendChild($root);

        foreach ($postTasks as $key => $ptask) {

            $nodeTask = $xml->createElement("task");
            foreach ($ptask as $pkey => $pval) {
                if (in_array($pkey, $exclude)) {
                    continue;
                }

                $node = $xml->createElement($pkey);
                $node->appendChild($xml->createTextNode($pval));
                $nodeTask->appendChild($node);
            }
            $root->appendChild($nodeTask);
        }
        if (isset($_REQUEST['echo_only']) && $_REQUEST['echo_only'] == 'y') {
            // echo only
        } else {
            $xmlFile = DOCUMENT_ROOT."/crm/tasks/export/takenlijst_".date("Y-m-d").".xml";
            if (isset($xmlFile)) {
                unlink($xmlFile);
            }
            $xml->save($xmlFile) or die("Error");
        }*/
    }

    reset($postTasks);

    if ('xml' == $_POST['export_type']) {

        // create projects XML
        $xml = '';
        /*$xml .= '<?xml version="1.0" ?>'.PHP_EOL;*/
        $xml .= '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-Schema.xsd">'.PHP_EOL;
            $xml .= '<Projects>'.PHP_EOL;
            foreach ($postTasks as $key => $ptask) {
                $xml .= '<Project code="'.$ptask['id'].'" type="I" status="A" >   '.PHP_EOL;
                    $xml .= '<Description>'.$ptask['task_title'].'</Description>'.PHP_EOL;
                    $xml .= '<DateStart>'.$ptask['date_start'].'</DateStart>'.PHP_EOL;
                    $xml .= '<DateEnd>2199-12-31</DateEnd>'.PHP_EOL;
                    $xml .= '<ParentProject code="'.$ptask['project_id'].'" type="I" status="A" >'.PHP_EOL;
                        $xml .= '<Description>'.$ptask['project_title'].'</Description>'.PHP_EOL;
                        $xml .= '<DateStart>'.$ptask['project_date_start'].'</DateStart>'.PHP_EOL;
                        $xml .= '<DateEnd>2199-12-31</DateEnd>'.PHP_EOL;
                    $xml .= '</ParentProject>'.PHP_EOL;
                $xml .= '</Project>'.PHP_EOL;
            }
            $xml .= '</Projects>'.PHP_EOL;
        $xml .= '</eExact>'.PHP_EOL;
        reset($postTasks);

        if (isset($_REQUEST['echo_only']) && $_REQUEST['echo_only'] == 'y') {
            // echo only
        } else {
            $projectFile = DOCUMENT_ROOT."/crm/tasks/export/projects_".date("Y-m-d").".xml";
            if (isset($projectFile)) {
                unlink($projectFile);
            }
            file_put_contents($projectFile, $xml) or die("Error");
        }
        reset($postTasks);

        // create the invoice XML
        $details = current($postTasks);
        $xml = '';
        $xml .= '<?xml version="1.0" ?>'.PHP_EOL;
        $xml .= '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-Schema.xsd">'.PHP_EOL;
            $xml .= '<Invoices> '.PHP_EOL;
                    
                    foreach ($postTasks as $key => $ptask) {

                        $count = 1;

                        $xml .= '<Invoice type="V" code="03">'.PHP_EOL;
                            $xml .= '<Description>'.$ptask['deal_title'].'</Description>'.PHP_EOL;
                            $xml .= '<YourRef>'.$ptask['reference_customer'].'</YourRef>'.PHP_EOL;
                            $xml .= '<Currency code="EUR" />'.PHP_EOL;
                            $xml .= '<OrderedBy>'.PHP_EOL;
                                $xml .= '<Debtor code="'.$ptask['clientid_exact'].'" number="'.$ptask['clientid_exact'].'" />'.PHP_EOL;
                            $xml .= '</OrderedBy>'.PHP_EOL;

                            $descriptions = array();
                            if (!empty($ptask['added_description'])) {
                                $descriptions = str_replace(["\r\n", "\r", "\n"], '^', $ptask['added_description']);
                                $descriptions = explode('^', $descriptions);
                            }

                            $xml .= '<InvoiceLine lineNo="'.$count.'">'.PHP_EOL;
                                $xml .= '<Item code="FACTUUR" />'.PHP_EOL;
                                $xml .= '<Quantity>1</Quantity>'.PHP_EOL;
                                $xml .= '<Price type="S">'.PHP_EOL;
                                    $xml .= '<Currency code="EUR" />'.PHP_EOL;
                                    // $xml .= '<Value>'.$ptask['total'].'</Value>'.PHP_EOL;

                                    // EXACT expects numbers to be in doubleval format, not dutch currency as earlier requested
                                    // $ptask['invoice_amount'] = doubleval($ptask['invoice_amount']);
                                    // $ptask['invoice_amount'] = number_format($ptask['invoice_amount'],2,'.',','); // revert dutch notation to normal notation
                                    $ptask['invoice_amount'] = str_ireplace(',', '.', $ptask['invoice_amount']);
                                    $ptask['invoice_amount'] = doubleval($ptask['invoice_amount']); // revert notation to plain doublevals

                                    $xml .= '<Value>'.sprintf('%0.2f', $ptask['invoice_amount']).'</Value>'.PHP_EOL;
                                $xml .= '</Price>'.PHP_EOL;
                                $xml .= '<Project code="'.$ptask['id'].'" />'.PHP_EOL;
                                $xml .= '<Text>'.$ptask['task_title'].' : '.current($descriptions).'</Text>'.PHP_EOL;
                            $xml .= '</InvoiceLine>'.PHP_EOL;

                            $count++;
                            if (count($descriptions)>1) {
                                // $count = 3;
                                foreach ($descriptions as $key => $desc) {
                                    if (!$key)
                                        continue;

                                    $desc = trim($desc);
                                    if (!empty($desc)) {
                                        $xml .= '<InvoiceLine lineNo="'.$count.'">'.PHP_EOL;
                                            $xml .= '<Text>'.$desc.'</Text>'.PHP_EOL;
                                        $xml .= '</InvoiceLine>'.PHP_EOL;
                                        $count++;
                                    }
                                }
                            }
                        $xml .= '</Invoice>'.PHP_EOL;
                    }
            $xml .= '</Invoices>'.PHP_EOL;
        $xml .= '</eExact>'.PHP_EOL;

        if (isset($_REQUEST['echo_only']) && $_REQUEST['echo_only'] == 'y') {
            // echo only
        } else {
            $factuurFile = DOCUMENT_ROOT."/crm/tasks/export/factuur_".date("Y-m-d").".xml";
            if (isset($factuurFile)) {
                unlink($factuurFile);
            }
            file_put_contents($factuurFile, $xml) or die("Error");
        }
    }
    reset($postTasks);

    // now update the task field row(s)
    $message = "Task(s) have been exported : " . PHP_EOL. PHP_EOL;
    $count = 1;
    foreach ($postTasks as $ptask) {
        $ptask['invoice_amount'] = str_ireplace(',', '.', $ptask['invoice_amount']);
        $query = 'insert into m_tasklist_fields (`TASK_ID`, `ADDED_DESCRIPTION`, `INVOICE_AMOUNT`, `PERCENT_DONE`, `OTHER_COMMENTS`) VALUES ("'.$DB->ForSQL($ptask['task_realid']).'", "'.$DB->ForSQL($ptask['added_description']).'", "'.$DB->ForSQL($ptask['invoice_amount']).'", "'.$DB->ForSQL($ptask['percent_done']).'", "'.$DB->ForSQL($ptask['other_comments']).'")';
        $DB->Query($query);

        $message .= "{$count}. {$ptask['id']} : " . $ptask["task_title"] . PHP_EOL;
        $count++;
    }
    reset($postTasks);

    if ('xml' == $_POST['export_type']) {
        
        // $textmail = nl2br( $message . PHP_EOL );
        $textmail = $message;

        // email the usergroup
        // define('UGROUP_TASKADMIN', 21);
        define('UGROUP_TASKADMIN', 22);
        $from = COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME);
        $subject = 'Task(s) export - '.date('m D Y');
        
        // $file = DOCUMENT_ROOT."/crm/tasks/export/takenlijst_".date("Y-m-d").".".$_POST['export_type'];
        // $attachment = null;
        // if (!is_null($file) && file_exists($file)) {
        //     $attachment = $file;
        // }

        $attachment = array(
                $csvFile,
                $projectFile,
                $factuurFile
            );

        $result = CUSER::GetList($by='id', $order='asc', array('GROUPS_ID' => UGROUP_TASKADMIN));
        while ($urow = $result->Fetch()) {
            ifmail($urow['EMAIL'],$from,$subject,$textmail,$htmlmail = null, $attachment);
        }
    }
}

LocalRedirect('/crm/tasks/?exported=Y&format='.$_POST['export_type']);
