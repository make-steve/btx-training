<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (isset($_REQUEST['title']) && !empty($_REQUEST['title'])) {
    // setProject() @ /php_interface/make/functions.php
    echo setProject(array('title' => $_REQUEST['title']));
}

if (isset($_REQUEST['project']) && !empty($_REQUEST['project'])) {
    $param = trim($_REQUEST['project']);
    if (stripos($param, ']')!==false) {
        $explode = explode(']', $param);
        $param = str_ireplace('[', '', $explode[0]);
        $param = intval($param);
    }
    
    $mproject = new MProject;
    $result = $mproject->getByID($param);
    if ($row = $result->Fetch()) {
        if ($row['ACTIVE'] == 'N') {
            echo json_encode(array('success' => false, 'project' => false));
            return;
        }
        
        // get project company/contact
        $company = $mproject->company($row['ID']);
        $contact = $mproject->contact($row['ID']);
        
        $contactCompany = array(
                'company_id' => !empty($company['COMPANY_ID'])?intval($company['COMPANY_ID']):0, 
                'contact_id' => !empty($contact['CONTACT_ID'])?intval($contact['CONTACT_ID']):0, 
            );

        $json = array('success' => true, 'project_name' => $row['TITLE'], 'project' => $row);
        $json = array_merge($json, $contactCompany);
        echo json_encode($json);
        return;
    }
    echo json_encode(array('success' => false, 'project' => false));
}

if (isset($_REQUEST['qproject']) && !empty($_REQUEST['qproject'])) {
    $mproject = new MProject;
    $query = trim($_REQUEST['qproject']);
    
    if (is_numeric($query)) {
        // $result = $mproject->getByID($query);
        $result = $mproject->searchID($query);
    } else {
        $result = $mproject->getByName($query);
    }

    $results = array();
    while ($row = $result->Fetch()) {

        if ($row['ACTIVE'] == 'N') {
            continue;
            // echo json_encode(array('success' => false, 'project' => false));
            // return;
        }
        
        // get project company/contact
        $company = $mproject->company($row['ID']);
        $contact = $mproject->contact($row['ID']);
        
        $contactCompany = array(
                'company_id' => !empty($company['COMPANY_ID'])?intval($company['COMPANY_ID']):0, 
                'contact_id' => !empty($contact['CONTACT_ID'])?intval($contact['CONTACT_ID']):0, 
            );

        // $json = array('success' => true, 'project_name' => $row['TITLE'], 'project' => $row);
        $row = array_merge($row, $contactCompany);

        $results[] = $row;
    }

    if (!empty($results)) {
        $json = array('success' => true, 'results' => $results);
        echo json_encode($json);
        return;
    }

    echo json_encode(array('success' => false, 'results' => false));
}
