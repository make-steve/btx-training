<?php
@define('NOT_CHECK_PERMISSIONS', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__).'/../../');
if (!defined('DOCUMENT_ROOT')) {
    define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));
}

require(DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_before.php");
require DOCUMENT_ROOT.'/vendor/league/csv/autoload.php';
use League\Csv\Reader;
use League\Csv\Writer;

global $DB;

// echo '<pre>';
// print_r($_FILES);
// echo '</pre>';
// exit()


$reader = Reader::createFromPath($_FILES['importfile']['tmp_name'], 'r');
// $reader->setDelimiter(';');
$reader->setDelimiter(',');
$results = $reader->setOffset(1)->fetch();
foreach ($results as $row) {
    // echo '<pre>';
    // print_r($row);
    // echo '</pre>';
    // exit();
    // $exactID = intval($row[20]);
    $exactID = intval($row[14]);
    // $company = trim($row[2]);
    $company = trim($row[1]);
    if ($exactID && !empty($company)) {
        if (stripos($company,'BV')!==false) {
            $company = str_ireplace('BV','',$company);
        }
        if (stripos($company,'B.V.')!==false) {
            $company = str_ireplace('B.V.','',$company);
        }
        $company = trim($company);

        // search company, then update its Exact ID value
        $query = 'select * from b_crm_company where TITLE="'.$DB->ForSql($company).'" limit 1';
        echo '<pre>$query is ';
        print_r($query);
        echo '</pre>';
        $result = $DB->Query($query);
        if ($row = $result->Fetch()) {
            $update = 'update b_uts_crm_company set '.CUF_CM_CLIENTID_EXACT.'="'.$DB->ForSql($exactID).'" where VALUE_ID="'.$DB->ForSql($row['ID']).'" limit 1';
            // var_dump($update);
            // exit();
            $DB->Query($update);
        }    
    }    
}
