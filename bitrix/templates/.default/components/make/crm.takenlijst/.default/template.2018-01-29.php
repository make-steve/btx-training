<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}
if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
	CBitrix24::initLicenseInfoPopupJS();
}

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/activity.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/interface_grid.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/autorun_proc.js');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/autorun_proc.css');

$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
// CJSCore::Init($jsCoreInit);
// CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
// CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/crm.js');
// CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/communication_search.js');
// CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');


define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('T_REFERENTIE_KLANT', CUF_T_REF_CLIENT); // referentie klant
define('T_INKOMSTEN', CUF_T_INKOMSTEN); // inkomsten
define('T_KOSTEN_UREN', CUF_T_KOSTEN_UREN_IF); // kosten uren if
define('T_KOSTEN_DERDEN', CUF_T_KOSTEN_DERDEN); // kosten derden
define('T_KOSTEN_LIFT', CUF_T_KOSTEN_LIFT); // kosten lift
define('T_RENDEMENT', CUF_T_RENDEMENT); // rendement

$isInternal = $arResult['INTERNAL'];
$callListUpdateMode = $arResult['CALL_LIST_UPDATE_MODE'];
$allowWrite = $arResult['PERMS']['WRITE'];
$allowDelete = $arResult['PERMS']['DELETE'];
$currentUserID = $arResult['CURRENT_USER_ID'];

$gridManagerID = $arResult['GRID_ID'].'_MANAGER';
$gridManagerCfg = array(
    	'ownerType' => 'DEAL',
    	'gridId' => $arResult['GRID_ID'],
    	'formName' => "form_{$arResult['GRID_ID']}",
    	'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
    	'activityEditorId' => $activityEditorID,
    	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
    	'filterFields' => array()
    );

$prefix = $arResult['GRID_ID'];
$prefixLC = strtolower($arResult['GRID_ID']);

$arResult['GRID_DATA'] = array();
$arColumns = array();
// foreach ($arResult['HEADERS'] as $arHead)
// 	$arColumns[$arHead['id']] = false;


foreach($arResult['TASK'] as $sKey =>  $arTask) {
    $arColumns = array();

    list($x, $dealId) = explode('_', $arTask['REL_ID']);

	$arActivityMenuItems = array();
	$arActivitySubMenuItems = array();
	$arActions = array();

    // hide view action
    /*$arActions[] =  array(
        'ICONCLASS' => 'view',
        'TITLE' => GetMessage('CRM_TASK_SHOW_TITLE'),
        'TEXT' => GetMessage('CRM_TASK_SHOW'),
        'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arTask['PATH_TO_TASK_SHOW'])."');",
        'DEFAULT' => true
    );*/

    // hide edit action
    $arActions[] =  array(
            'ICONCLASS' => 'edit',
            'TITLE' => GetMessage('CRM_TASK_EDIT_TITLE'),
            'TEXT' => GetMessage('CRM_TASK_EDIT'),
            'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arTask['PATH_TO_TASK_EDIT'])."');"
        );

    // $arActions[] = array('SEPARATOR' => true);

    if(isTaskAllowed2Delete($dealId, $arTask['ID']))
    {
        $arActions[] =  array(
            'ICONCLASS' => 'delete',
            'TITLE' => GetMessage('CRM_TASK_DELETE_TITLE'),
            'TEXT' => GetMessage('CRM_TASK_DELETE'),
            'ONCLICK' => "crm_activity_task_delete_grid('".CUtil::JSEscape(GetMessage('CRM_TASK_DELETE_TITLE'))."', '".CUtil::JSEscape(GetMessage('CRM_TASK_DELETE_CONFIRM'))."', '".CUtil::JSEscape(GetMessage('CRM_TASK_DELETE'))."', '".CUtil::JSEscape($arTask['PATH_TO_TASK_DELETE'])."')"
        );
    }
    
    // if($userID == $arTask["CREATED_BY_ID"] || $userID == $arTask["RESPONSIBLE_ID"])
    // {
        if($arTask['CUSTOM_FIELDS'][CUF_T_LOCKED] == 0)
        {
            $arActions[] =  array(
                'ICONCLASS' => 'locked',
                'TITLE' => GetMessage('CRM_TASK_LOCK_TITLE'),
                'TEXT' => GetMessage('CRM_TASK_LOCK'),
                'ONCLICK' => "lockTaskAction(" . $arTask['ID'] . ", 1);"
            );
        }
        else
        {
            $arActions[] =  array(
                'ICONCLASS' => 'unlock',
                'TITLE' => GetMessage('CRM_TASK_UNLOCK_TITLE'),
                'TEXT' => GetMessage('CRM_TASK_UNLOCK'),
                'ONCLICK' => "lockTaskAction(" . $arTask['ID'] . ", 0);"
            );
        }
    // }
	
	// $arColumns['CHECK_ROW'] = '<input type="checkbox" name="exported[]" class="exported" value="'.$arTask['ID'].'" />';

    if ($arResult['ACTIVITY_ENTITY_LINK'] == 'Y')
    {
        $arColumns['ENTITY_TYPE'] = !empty($arTask['ENTITY_TYPE'])? GetMessage('CRM_ENTITY_TYPE_'.$arTask['ENTITY_TYPE']): '';
        $arColumns['ENTITY_TITLE'] = !empty($arTask['ENTITY_TITLE'])?
            '<a href="'.$arTask['ENTITY_LINK'].'" id="balloon_'.$arResult['GRID_ID'].'_I_'.$arTask['REL_ID'].'">'.$arTask['ENTITY_TITLE'].'</a>'.
            '<script type="text/javascript">BX.tooltip("'.$arTask['ENTITY_TYPE'].'_'.$arTask['ENTITY_ID'].'", "balloon_'.$arResult['GRID_ID'].'_I_'.$arTask['REL_ID'].'", "/bitrix/components/bitrix/crm.'.strtolower($arTask['ENTITY_TYPE']).'.show/card.ajax.php", "crm_balloon'.($arTask['ENTITY_TYPE'] == 'LEAD' || $arTask['ENTITY_TYPE'] == 'DEAL' || $arTask['ENTITY_TYPE'] == 'QUOTE' ? '_no_photo': '_'.strtolower($arTask['ENTITY_TYPE'])).'", true);</script>'
            : '';
    }
    else
    {
        unset($arTask['ENTITY_TYPE']);
        unset($arTask['ENTITY_TITLE']);
    }

    // custom project data
    $arColumns['UF_PROJECT_NR'] = !empty($arTask['PROJECT'])?$arTask['PROJECT']['ID']:'';
    $value = $arTask['PROJECT']['ID'];
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][project_id]" value="'.$value.'">';
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][deal_id]" value="'.$dealId.'">';
    
    // add the project title
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][project_title]" value="'.$arTask['PROJECT']['TITLE'].'">';
    // add the project start date
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][project_date_start]" value="'.date('Y-m-d', strtotime($arTask['PROJECT']['DATE_APPROVED'])).'">';

    // add the company client exact ID
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][clientid_exact]" value="'.$arTask['PROJECT']['COMPANY']['CLIENTID_EXACT'].'">';
    
    // add the task start date; per Nadya, should be date when task was created
    $arTask['CREATED_DATE'] = trim($arTask['CREATED_DATE']);
    if (empty($arTask['CREATED_DATE'])) {
        $dateStart = date('Y-m-d');
    } else {
        // $dateStart = date('Y-m-d', $arTask['CREATED_DATE']);
        $dateStart = ConvertDateTime($arTask['CREATED_DATE'],'Y-m-d');
    }
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][date_start]" value="'.$dateStart.'">';
    
    // add the task referentie klant
    $arColumns['UF_PROJECT_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][reference_customer]" value="'.$arTask['CUSTOM_FIELDS'][T_REFERENTIE_KLANT].'">';

    
    /*$arColumns['UF_TASK_NR'] = '<a target="_self" href="'.$arTask['PATH_TO_TASK_SHOW'].'">'."{$dealId}.{$arTask['ID']}".'</a>';
    $value = (!empty($arTask['TASKLIST_FIELDS']['TASK_ID']))?$arTask['TASKLIST_FIELDS']['TASK_ID']:$arTask['ID'];
    $arColumns['UF_TASK_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][id]" value="'.$value.'" />';*/

    $arColumns['UF_TASK_NR'] = '<a target="_self" href="'.$arTask['PATH_TO_TASK_SHOW'].'">'."{$arTask['XML_ID']}".'</a>';
    $value = (!empty($arTask['XML_ID']))?$arTask['XML_ID']:'';
    $arColumns['UF_TASK_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][id]" value="'.$value.'" />';
    $arColumns['UF_TASK_NR'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][task_realid]" value="'.$arTask['ID'].'" />';
    $arColumns['ID'] = $arColumns['UF_TASK_NR'];


    $arColumns['UF_PROJECT_LEAD'] = !empty($arTask['CREATOR'])?$arTask['CREATOR']['NAME'].' '.$arTask['CREATOR']['LAST_NAME']:'';
    $value = (!empty($arTask['CREATOR']))?$arTask['CREATOR']['NAME'].' '.$arTask['CREATOR']['LAST_NAME']:'';
    $arColumns['UF_PROJECT_LEAD'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][project_lead]" value="'.$value.'" />';


    $arColumns['UF_CLIENT'] = !empty($arTask['PROJECT']['COMPANY']['COMPANY_NAME'])?$arTask['PROJECT']['COMPANY']['COMPANY_NAME']:'';
    // $arColumns['UF_TASK_TITLE'] = '<a target="_self" href="'.$arTask['PATH_TO_TASK_SHOW'].'">'.$arTask['TITLE'].'</a>';
    $value = !empty($arTask['PROJECT']['COMPANY']['COMPANY_NAME'])?$arTask['PROJECT']['COMPANY']['COMPANY_NAME']:'';
    $arColumns['UF_CLIENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][client]" value="'.$value.'" />';
    $arColumns['UF_CLIENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][company_id]" value="'.$arTask['PROJECT']['COMPANY']['COMPANY_ID'].'" />';
    
    $arColumns['UF_DEALNAAM'] = $arResult['ALL_DEALS'][$dealId]['TITLE'];
    $value = !empty($arResult['ALL_DEALS'][$dealId]['TITLE'])?$arResult['ALL_DEALS'][$dealId]['TITLE']:'';
    // $value = '';
    $arColumns['UF_DEALNAAM'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][deal_title]" value="'.$value.'" />';
    

    $arColumns['UF_TASK_TITLE'] = $arTask['TITLE'];
    $value = !empty($arTask['TITLE'])?$arTask['TITLE']:'';
    $arColumns['UF_TASK_TITLE'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][task_title]" value="'.$value.'" />';

    $arColumns['UF_REF_CLIENT'] = !empty($arTask['CUSTOM_FIELDS'][CUF_T_REF_CLIENT])?$arTask['CUSTOM_FIELDS'][CUF_T_REF_CLIENT]:' - ';
    $value = !empty($arTask['CUSTOM_FIELDS'][CUF_T_REF_CLIENT])?$arTask['CUSTOM_FIELDS'][CUF_T_REF_CLIENT]:'';
    $arColumns['UF_REF_CLIENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][ref_client]" value="'.$value.'" />';

    $arColumns['UF_TOELECHTING_FACTURATIE'] = !empty($arTask['CUSTOM_FIELDS'][CUF_T_FACTUUR_MOMENTEN])?$arTask['CUSTOM_FIELDS'][CUF_T_FACTUUR_MOMENTEN]:' - ';
    $value = !empty($arTask['CUSTOM_FIELDS'][CUF_T_FACTUUR_MOMENTEN])?$arTask['CUSTOM_FIELDS'][CUF_T_FACTUUR_MOMENTEN]:'';
    $arColumns['UF_TOELECHTING_FACTURATIE'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][description]" value="'.$value.'" />';

    $arColumns['UF_LOCKED'] = ($arTask['CUSTOM_FIELDS'][CUF_T_LOCKED] == 0) ? 'No' : 'Yes';
    $value = !empty($arTask['CUSTOM_FIELDS'][CUF_T_LOCKED])?$arTask['CUSTOM_FIELDS'][CUF_T_LOCKED]:'';
    $arColumns['UF_LOCKED'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][task_locked]" value="'.$value.'" />';


    $arTask['TASKLIST_FIELDS']['ADDED_DESCRIPTION'] = trim($arTask['TASKLIST_FIELDS']['ADDED_DESCRIPTION']);
    $value = (!empty($arTask['TASKLIST_FIELDS']['ADDED_DESCRIPTION']))?$arTask['TASKLIST_FIELDS']['ADDED_DESCRIPTION']:'';
    $arColumns['UF_EXTRA_OMSCRIJVING'] = '<textarea class="taskaddeddescription" rel="' . $arTask['ID'] . '" type="text" name="task['.$arTask['ID'].'][added_description]" placeholder="Voeg commentaar toe" rows="3">'.$value.'</textarea>';

    // $arColumns['UF_BUDGET'] = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_720314405573'])?number_format($arTask['CUSTOM_FIELDS']['UF_AUTO_720314405573'],2):"0.00";
    // $value = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_720314405573'])?number_format($arTask['CUSTOM_FIELDS']['UF_AUTO_720314405573'],2):"0.00";
    // budget will now come from inkomsten werkelijke (inkomsten in import)
    
    $budget = 0.00;
    if (!empty($arTask['CUSTOM_FIELDS'][T_INKOMSTEN]))
        $budget = $arTask['CUSTOM_FIELDS'][T_INKOMSTEN];
    $arColumns['UF_BUDGET'] = number_format($budget,2,',','.');
    // $value = !empty($arTask['CUSTOM_FIELDS'][T_INKOMSTEN])?number_format($arTask['CUSTOM_FIELDS'][T_INKOMSTEN],2,'.',','):"0,00";
    $totalBudget = !empty($arTask['CUSTOM_FIELDS'][T_INKOMSTEN])?$arTask['CUSTOM_FIELDS'][T_INKOMSTEN]:"0,00";
    $arColumns['UF_BUDGET'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][budget]" value="'.$budget.'" /><input class="total_budget tid_tb_'.$arTask['ID'].'" type="hidden" value="'.$totalBudget.'" />';
    
    $value = "0,00";
    // $kostenTotal = ($arTask['CUSTOM_FIELDS'][T_KOSTEN_UREN]+$arTask['CUSTOM_FIELDS'][T_KOSTEN_DERDEN]+$arTask['CUSTOM_FIELDS'][T_KOSTEN_LIFT]);
    $kostenTotal = ($arTask['TASKLIST_FIELDS']['KOSTEN_UREN']+$arTask['TASKLIST_FIELDS']['KOSTEN_DERDEN']+$arTask['TASKLIST_FIELDS']['KOSTEN_LIFT']);
    
    $arColumns['UF_TOTAAL'] = number_format($kostenTotal,2,',','.');
    $arColumns['UF_TOTAAL'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][total]" value="'.$kostenTotal.'" />';
    

    $arColumns['UF_KOSTEN_UREN'] = "0,00";
    if (!empty($arTask['TASKLIST_FIELDS']['KOSTEN_UREN']))
        $arColumns['UF_KOSTEN_UREN'] = number_format($arTask['TASKLIST_FIELDS']['KOSTEN_UREN'],2,',','.');
    
    $value = !empty($arTask['TASKLIST_FIELDS']['KOSTEN_UREN'])?number_format($arTask['TASKLIST_FIELDS']['KOSTEN_UREN'],2,'.',','):"0,00";
    $arColumns['UF_KOSTEN_UREN'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][cost_hours]" value="'.$value.'" />';


    $arColumns['UF_KOSTEN_DERDEN'] = "0,00";
    if (!empty($arTask['TASKLIST_FIELDS']['KOSTEN_DERDEN']))
        $arColumns['UF_KOSTEN_DERDEN'] = number_format($arTask['TASKLIST_FIELDS']['KOSTEN_DERDEN'],2,',','.');
    
    $value = !empty($arTask['TASKLIST_FIELDS']['KOSTEN_DERDEN'])?number_format($arTask['TASKLIST_FIELDS']['KOSTEN_DERDEN'],2,'.',','):"0,00";
    $arColumns['UF_KOSTEN_DERDEN'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][cost_thirdparty]" value="'.$value.'" />';


    $costExtras = 0.00; // kosten lift
    if (!empty($arTask['TASKLIST_FIELDS']['KOSTEN_LIFT']))
        $costExtras = $arTask['TASKLIST_FIELDS']['KOSTEN_LIFT'];

    $arColumns['UF_KOSTEN_LIFT'] = number_format($costExtras,2,',','.');
    $arColumns['UF_KOSTEN_LIFT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][cost_extras]" value="'.$costExtras.'" />';


    $omzet = 0.00; // omzet
    if (!empty($arTask['TASKLIST_FIELDS']['INKOMSTEN']))
        $omzet = $arTask['TASKLIST_FIELDS']['INKOMSTEN'];
    
    $arColumns['UF_OMZET'] = number_format($omzet,2,',','.');
    $arColumns['UF_OMZET'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][revenue]" value="'.$omzet.'" /><input type="hidden" class="tid_omzet_'.$arTask['ID'].'" value="'.$omzet.'" />';


    $value = '0,00';
    $arColumns['UF_BETAALD'] = $value;
    if (!empty($arTask['TASKLIST_FIELDS']['BETAALD'])) {
        $arColumns['UF_BETAALD'] = number_format($arTask['TASKLIST_FIELDS']['BETAALD'], 2, ',', '.');
        $value = trim($arTask['TASKLIST_FIELDS']['BETAALD']);
    }
    $arColumns['UF_BETAALD'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][paid]" value="'.$value.'" />';

    $taskTypes = array('regie', 'vast', 'intern');
    $content = '<select name="task['.$arTask['ID'].'][task_type]">';
    foreach ($$taskTypes as $typeOpt) {
        $selected = '';
        // if ($arTask[])
        $tmp = '<option value="'.strtolower($typeOpt).'" '.$selected.'>'.ucfirst($typeOpt).'</option>';
        $content .= $tmp;
    }
    $content .= '</select>';
    $arColumns['UF_TASK_TYPE'] .= $content;

    $invoiceAmount = ''; // bedrag te factureren
    if (!empty($arTask['TASKLIST_FIELDS']['INVOICE_AMOUNT']))
        $invoiceAmount = $arTask['TASKLIST_FIELDS']['INVOICE_AMOUNT'];

    // $invoiceAmount = number_format($invoiceAmount,2,',');
    $invoiceAmount = str_ireplace('.',',',$invoiceAmount);
    $arColumns['UF_BEDRAG_FACTUREREN'] = '<input rel="' . $arTask['ID'] . '" type="text" name="task['.$arTask['ID'].'][invoice_amount]" class="invoice_amount" placeholder="0,00" value="'.$invoiceAmount.'">';

    $percentDone = 0; // % gereed
    if (!empty($arTask['TASKLIST_FIELDS']['PERCENT_DONE']))
        $percentDone = $arTask['TASKLIST_FIELDS']['PERCENT_DONE'];

    $arColumns['UF_GEREED'] .= '<input rel="' . $arTask['ID'] . '" type="text" class="percent_done" data-tid="'.$arTask['ID'].'" name="task['.$arTask['ID'].'][percent_done]" placeholder="0" value="'.(($percentDone)?$percentDone:'').'" />';

    if ($percentDone >= 1) {
        $percentDone = ($percentDone/100);
    }
    $owh = ($percentDone*$totalBudget)-$omzet; // OHW
    $arColumns['UF_OHW'] = '<span class="tid_ohw_'.$arTask['ID'].'">'.number_format($owh,2,',','.').'</span>';
    $arColumns['UF_OHW'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][ohw]" value="'.$owh.'" class="tid_ohw_'.$arTask['ID'].'" />';
    

    // totaal verwachte kosten
    $expectedCost = ($kostenTotal/$percentDone);
    if ($percentDone<=0) {
        $expectedCost = 0.00;
    }
    $arColumns['UF_VERWACHTE_KOSTEN'] = '<span class="tid_ec_'.$arTask['ID'].'">'
        .number_format($expectedCost, 2,',','.').'</span>'
        .'<input type="hidden" class="kosten_total tid_kt_'.$arTask['ID'].'" value="'.$kostenTotal.'" />';
    $arColumns['UF_VERWACHTE_KOSTEN'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][expected_costs]" value="'.$expectedCost.'" class="tid_ec_'.$arTask['ID'].'" />';


    // verwachte rendement
    $expectedReturn = doubleval($totalBudget)-doubleval($expectedCost); 
    // if ($percentDone<=0) {
    //     $expectedReturn = 0.00;
    // }
    $arColumns['UF_VERWACHTE_RENDEMENT'] = '<span class="tid_er_'.$arTask['ID'].'">'
        .number_format($expectedReturn,2,',','.').'</span>';
    $arColumns['UF_VERWACHTE_RENDEMENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][expected_returns]" value="'.$expectedReturn.'" class="tid_er_'.$arTask['ID'].'" />';


    // opmerkingen voor F&C
    $otherComments = ''; 
    if (!empty($arTask['TASKLIST_FIELDS']['OTHER_COMMENTS'])) {
        $otherComments = stripslashes($arTask['TASKLIST_FIELDS']['OTHER_COMMENTS']);
        $otherComments = trim($otherComments);
    }
    
    $arColumns['UF_OPMERKINGEN_VOOR'] = '<textarea rel="' . $arTask['ID'] . '" type="text" name="task['.$arTask['ID'].'][other_comments]" placeholder="Voeg commentaar toe" rows="3">'.$otherComments.'</textarea>';
	
	$resultItem = array(
            'id' => $arTask['ID'],
            'actions' => $arActions,
            'data' => $arTask,
            'editable' => false,
    		'columns' => $arColumns
    	);

	$arResult['GRID_DATA'][] = $resultItem;
	unset($resultItem);
}

?>
<style>
.menu-popup-item .menu-popup-item-icon {
    height: 20px;
    width: 20px;
}
.menu-popup-item.edit .menu-popup-item-icon {
    background-image: url('/bitrix/templates/.default/components/bitrix/main.interface.grid/.default/images/popup/edit.gif');
}
.menu-popup-item.delete .menu-popup-item-icon {
    background-image: url('/bitrix/templates/.default/components/bitrix/main.interface.grid/.default/images/popup/delete.gif');
}
.menu-popup-item.locked .menu-popup-item-icon {
    background-image: url('/bitrix/templates/.default/components/bitrix/main.interface.grid/.default/images/popup/lock.png');
}
.menu-popup-item.unlock .menu-popup-item-icon {
    background-image: url('/bitrix/templates/.default/components/bitrix/main.interface.grid/.default/images/popup/unlock.png');
}
</style>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'titleflex',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ROWS' => $arResult['GRID_DATA'],
		'FORM_ID' => $arResult['FORM_ID'],
		'TAB_ID' => $arResult['TAB_ID'],
		'AJAX_ID' => $arResult['AJAX_ID'],
		'AJAX_OPTION_HISTORY' => $arResult['AJAX_OPTION_HISTORY'],
		'AJAX_LOADER' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null,
        // 'FILTER' => $arResult['FILTER'],
		// 'FILTER' => array(),
        // 'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		// 'FILTER_PRESETS' => array(),
		'ENABLE_LIVE_SEARCH' => false,
		'ACTION_PANEL' => $controlPanel,
		'AJAX_OPTION_JUMP' => "N",
		'PAGINATION' => isset($arResult['PAGINATION']) && is_array($arResult['PAGINATION'])
			? $arResult['PAGINATION'] : array(),
		'ENABLE_ROW_COUNT_LOADER' => true,
		'PRESERVE_HISTORY' => $arResult['PRESERVE_HISTORY'],
		'MESSAGES' => $messages,
		'NAVIGATION_BAR' => array(),
		'IS_EXTERNAL_FILTER' => $arResult['IS_EXTERNAL_FILTER'],
		'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
		'EXTENSION' => array(
			'ID' => $gridManagerID,
			'CONFIG' => array(
				'ownerTypeName' => CCrmOwnerType::DealName,
				'gridId' => $arResult['GRID_ID'],
				'activityEditorId' => $activityEditorID,
				'activityServiceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'taskCreateUrl'=> isset($arResult['TASK_CREATE_URL']) ? $arResult['TASK_CREATE_URL'] : '',
				'serviceUrl' => '/bitrix/components/make/crm.takenlijst/list.ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'loaderData' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null
			),
			'MESSAGES' => array(
				'deletionDialogTitle' => GetMessage('CRM_DEAL_DELETE_TITLE'),
				'deletionDialogMessage' => GetMessage('CRM_DEAL_DELETE_CONFIRM'),
				'deletionDialogButtonTitle' => GetMessage('CRM_DEAL_DELETE'),
				'moveToCategoryDialogTitle' => GetMessage('CRM_DEAL_MOVE_TO_CATEGORY_DLG_TITLE'),
				'moveToCategoryDialogMessage' => GetMessage('CRM_DEAL_MOVE_TO_CATEGORY_DLG_SUMMARY')
			)
		),
	),
	$component
);
?>
<script>
jQuery(document).ready(function() {

    jQuery('.invoice_amount').on('keyup', function() {
        var amtinput = jQuery(this);
        var amt = amtinput.val();
        amtinput.val(amt.replace('.',','));
        // amtinput.val(amt.replace(/[^0-9\.]/g,''));
    });

    jQuery('.percent_done').on('keyup', function() {
        var percentDone = $(this).val();
        var reftid = $(this).data('tid');
        var totalbudget = jQuery('.total_budget.tid_tb_'+reftid).val();
        totalBudget = parseFloat(totalbudget);
        var omzet = jQuery('.tid_omzet_'+reftid).val();
        omzet = parseFloat(omzet);
        var kostenTotal = jQuery('.tid_kt_'+reftid).val();
        kostenTotal = parseFloat(kostenTotal);

        if (percentDone >= 1) {
            percentDone = (percentDone/100);
            
            var owh = 0.00
            if (totalbudget > 0 && omzet > 0)
                owh = ( (percentDone*totalbudget)-omzet );
            owh = parseFloat(owh);

            var expectedCost = 0;
            // if(kostenTotal > 0)
                expectedCost = (kostenTotal/percentDone);
            expectedCost = parseFloat(expectedCost);
            
            var expectedReturn = 0.00;
            // if (expectedCost > 0 && totalbudget > 0) {
                // expectedReturn = (expectedCost-totalbudget);
                expectedReturn = (totalbudget-expectedCost);
            // }
            expectedReturn = parseFloat(expectedReturn);
        } else {

            // isNaN(percentDone) || !percentDone || percentDone<1 || percentDone == ' ' ; all true
            owh = 0.00;
            expectedCost = 0.00;
            expectedReturn = 0.00;
        }
        

        // ohw
        jQuery('input.tid_ohw_'+reftid).val(owh);
        owhv = (owh).toLocaleString("de-DE", {minimumFractionDigits: 2, currency: 'EUR', currencyDisplay: 'symbol'}) ;
        jQuery('span.tid_ohw_'+reftid).html(owhv);

        // totaal verwachte kosten
        jQuery('input.tid_ec_'+reftid).val(expectedCost);
        expectedCostv = (expectedCost).toLocaleString("de-DE", {minimumFractionDigits: 2, currency: 'EUR', currencyDisplay: 'symbol'}) ;
        jQuery('span.tid_ec_'+reftid).html(expectedCostv);

        // verwachte rendement
        jQuery('input.tid_er_'+reftid).val(expectedReturn);
        expectedReturnv = (expectedReturn).toLocaleString("de-DE", {minimumFractionDigits: 2, currency: 'EUR', currencyDisplay: 'symbol'}) ;
        jQuery('span.tid_er_'+reftid).html(expectedReturnv);
    });
    jQuery('.submit_btn').on('click', function() {
        jQuery('#task_export').val(jQuery(this).attr('data-export'));
        if (jQuery('.exported:checked').length > 0) {
            var r = confirm("Weet je zeker dat het ingevulde bedrag te factureren door F&C gefactureerd mag worden?");
            if (r == true) {
                jQuery('#form_tasklist').submit();
            }
            
        } else {
            alert('Please select atleast 1 task to proceed.');
        }
        
    });
    jQuery('#check_all').on('change', function() {
        $('input:checkbox.exported').prop('checked', this.checked);
    });

    jQuery('.taskaddeddescription').charLimit({limit: 200});
});

function lockTaskAction(element, set)
{
    var getTaskID = element;
    var url = '/bitrix/templates/.default/components/bitrix/crm.interface.form/show.project/bitrix/main.interface.form/crm.view.project/actions.php';
    var lockButtons = BX.CrmPopupWindowHelper.prepareButtons(
    [
        {
            type: 'button',
            settings:
            {
                text: 'Yes, continue',
                className: 'popup-window-button-accept',
                events:
                {
                    click : function()
                    {
                        BX.ajax({
                        url: url,
                        method: 'post',
                        async: true,
                        emulateOnload: true,
                        data: { 'taskID': getTaskID, 'set': set },
                        cache: false,
                        onsuccess: function(result)
                        {
                            var resOpt = {};
                            if(result == "true")
                                location.reload();
                            else {
                                resOpt = {
                                    SUCCESS: false,
                                    ERROR: [{CODE: 'INTERNAL_ERROR', MESSAGE: "Can't set task to " + set, TYPE: 'FATAL'}],
                                    ASSET: [],
                                    DATA: {}
                                };
                                this.fireEvent('executed', [resOpt]);
                            }
                        }
                    });
                    }
                }
            }
        },
        {
            type: 'button',
            settings:
            {
                text: 'Cancel',
                className: 'popup-window-button-link',
                events:
                {
                    click : function()
                    {
                        var popupwindow = BX.PopupWindowManager.getCurrentPopup();
                        popupwindow.close();
                        popupwindow.destroy();
                    }
                }
            }
        }
    ]);

    var popupWindow = null;
    var setText = "";
    var setEnable = "";
    if(set == "0") {
        setText = '<span style="color:green; font-weight:bold; font-size: 15px;">unlock</span>';
        setEnable = 'This will enable the logging of hours';
    }
    else {
        setText = '<span style="color:red; font-weight:bold; font-size: 15px;">lock</span>';
        setEnable = 'This will disable the logging of hours';
    }


    var content = '<div style="padding: 16px 12px; max-width: 400px; max-height: 400px; overflow: hidden; text-align: center">Are you sure you want to ' + setText + ' task? <br/>' + setEnable + '</div>';
    popupWindow = BX.PopupWindowManager.create(
        'lead-dialog-container',
        BX('menu-popup-item-text'),
        {
            'darkMode': false,
            'closeByEsc': true,
            'closeIcon': false,
            'content':  content,
            'className': 'dialog-box',
            'autoHide': false,
            'lightShadow' : false,
            'offsetLeft': 0,
            'offsetTop': 0,
            'overlay': true,
            'zIndex': BX.WindowManager ? BX.WindowManager.GetZIndex() + 10 : 0,
            'buttons': lockButtons,
        }
    );

    popupWindow.show();
}

(function(jQuery) {
jQuery.fn.charLimit = function(options) {
    if(options === undefined || options.limit === undefined || typeof options.limit !== 'number') {
        $.error('Option limit must be defined and must be a number.');
    }

    return this.each(function() {
        var self = $(this);
        var charLimit = options.limit;

        function _truncate(ev) {
            var caretPos;
            if (ev.target.selectionStart !== undefined) {
                caretPos = ev.target.selectionEnd;
            } else if(document.selection) {
                ev.target.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -ev.target.value.length);
                caretPos = range.text.length;
            }

            self.val(self.val().substring(0, charLimit));
            _setCaretPos(ev, caretPos);
        }

        function _setCaretPos(ev, pos) {
            if ($(ev.target).get(0).setSelectionRange) {
                $(ev.target).get(0).setSelectionRange(pos, pos);
            } else if ($(ev.target).get(0).createTextRange) {
                var range = $(ev.target).get(0).createTextRange();
                range.collapse(true);
                range.moveEnd('character', pos);
                range.moveStart('character', pos);
                range.select();
            }
        }

        self.keypress(function(ev) {
            var charCount = self.val().length;
            var selected;
            if (ev.target.selectionStart !== undefined) {
                selected = !(ev.target.selectionStart==ev.target.selectionEnd);
            } else if(document.selection) {
                ev.target.focus();
                var range = document.selection.createRange();
                selected = (range.text.length > 0);
            }

            if(charCount > charLimit-1 && !selected) {
                return false;
            }
            setTimeout(function() {
                _truncate(ev);
            }, 1);
        });

        self.bind('paste', function(ev) {
            setTimeout(function() {
                _truncate(ev);
            }, 1);
        });

    });
};
})( jQuery );

</script>
