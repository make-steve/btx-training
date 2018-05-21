    <?php
    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

    /** @var CMain $APPLICATION */
    $APPLICATION->IncludeComponent(
        'bitrix:crm.control_panel',
        '',
        array(
            'ID' => 'TASK',
            'ACTIVE_ITEM_ID' => 'TASK',
            'PATH_TO_LIST_LIST' => isset($arResult['PATH_TO_LIST_LIST']) ? $arResult['PATH_TO_LIST_LIST'] : '',
            'PATH_TO_TASK_EDIT' => isset($arResult['PATH_TO_TASK_EDIT']) ? $arResult['PATH_TO_TASK_EDIT'] : '',
        ),
        $component
    );

    $APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
    $APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
    $APPLICATION->AddHeadScript('/bitrix/templates/bitrix24/js/jquery-3.2.1.min.js');
    $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    $APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css');
    $APPLICATION->SetAdditionalCSS('/bitrix/templates/bitrix24/css/pikaday.css');
    $APPLICATION->AddHeadScript('/bitrix/templates/bitrix24/js/pikaday.js');

    $jsCoreInit = array('date', 'popup', 'ajax', 'crm_activity_planner', 'crm_visit_tracker');

    CJSCore::Init($jsCoreInit);
    CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
    CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/crm.js');
    CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/communication_search.js');
    CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

    define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
    define('T_REFERENTIE_KLANT', 'UF_AUTO_192582141226');
    define('T_INKOMSTEN', 'UF_AUTO_276127427586'); // inkomsten
    define('T_KOSTEN_UREN', 'UF_AUTO_775103888970'); // kosten uren if
    define('T_KOSTEN_DERDEN', 'UF_AUTO_834260910111'); // kosten derden
    define('T_KOSTEN_LIFT', 'UF_AUTO_347562553598'); // kosten lift
    define('T_RENDEMENT', 'UF_AUTO_347562553598'); // rendement

    global $USER, $DB;
    ?>
    <script type="text/javascript">
    function crm_activity_task_delete_grid(title, message, btnTitle, path)
    {
        var d;
        d = new BX.CDialog({
            title: title,
            head: '',
            content: message,
            resizable: false,
            draggable: true,
            height: 70,
            width: 300
        });

        var _BTN = [
            {
                title: btnTitle,
                id: 'crmOk',
                'action': function ()
                {
                    window.location.href = path;
                    BX.WindowManager.Get().Close();
                }
            },
            BX.CDialog.btnCancel
        ];
        d.ClearButtons();
        d.SetButtons(_BTN);
        d.Show();
    }
    </script>
    <style>
    form[name="form_CRM_ACTIVITY_TASK_LIST"] input[rel="' . $arTask['ID'] . '" type="text"] {
        font: 13px "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #555;
        background-color: #fff;
        border: 1px solid #d9d9d9;
        box-sizing: border-box;
        margin-left: -2px;
        outline: none;
        padding: 0 5px;
        height: 27px;
        width: 100%;
    }
    .download {
        color: #535c69;
        margin: 0 20px;
        display: inline-block;
        height: 20px;
        margin: 10px; float: left;
    }
    </style>
    <?
        $userID = $USER->GetID();

        for ($i=0, $ic=sizeof($arResult['FILTER']); $i < $ic; $i++)
        {
            if ($arResult['FILTER'][$i]['type'] === 'user')
            {
                $userID = (isset($_REQUEST[$arResult['FILTER'][$i]['id']]))?intval($_REQUEST[$arResult['FILTER'][$i]['id']][0]):0;
                if ($userID === 0 || (isset($_REQUEST['clear_filter']) && $_REQUEST['clear_filter'] == 'Y'))
                {
                    $userID = '';
                    $userName = '';
                }
                else
                    $userName = __format_user4search($userID);

                ob_start();
                $APPLICATION->IncludeComponent('bitrix:intranet.user.selector', 'minimized', array(
                    'INPUT_NAME' => $arResult['FILTER'][$i]['id'],
                    'INPUT_NAME_STRING' => $arResult['FILTER'][$i]['id'].'_name',
                    'INPUT_VALUE' => $userID,
                    'INPUT_VALUE_STRING' => htmlspecialcharsback($userName),
                    'EXTERNAL' => 'I',
                    'MULTIPLE' => 'N',
                    'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE']
                    ), $component);
                $val = ob_get_clean();

                $arResult["FILTER"][$i]["type"] = "custom";
                $arResult['FILTER'][$i]['value'] = $val;
            }
        }

        

        $arResult['GRID_DATA'] = array();
        foreach($arResult['TASK'] as $sKey =>  $arTask) {

            list($x, $dealId) = explode('_', $arTask['REL_ID']);
            
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
            
            if($userID == $arTask["CREATED_BY_ID"] || $userID == $arTask["RESPONSIBLE_ID"])
            {
                if($arTask['CUSTOM_FIELDS']['UF_AUTO_713286985141'] == 0)
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
            }
            

            

            $arColumns = array(
                // 'TITLE' => '<a target="_self" href="'.$arTask['PATH_TO_TASK_SHOW'].'">'.$arTask['TITLE'].'</a>',
                // 'CREATED_DATE' => FormatDate('x', MakeTimeStamp($arTask['CREATED_DATE'])),
                // 'CHANGED_DATE' => FormatDate('x', MakeTimeStamp($arTask['CHANGED_DATE'])),
                // 'DATE_START' => !empty($arTask['DATE_START']) ? FormatDate('x', MakeTimeStamp($arTask['DATE_START'])) : '',
                // 'CLOSED_DATE' => !empty($arTask['CLOSED_DATE']) ? FormatDate('x', MakeTimeStamp($arTask['CLOSED_DATE'])) : '',
                // 'REAL_STATUS' => GetMessage('TASKS_STATUS_'.$arTask['REAL_STATUS']),
                // 'PRIORITY' => GetMessage('TASKS_PRIORITY_'.$arTask['PRIORITY']),
                // 'RESPONSIBLE_ID' => $arTask['~RESPONSIBLE_ID'] > 0 ?
                //  '<a href="'.$arTask['PATH_TO_USER_PROFILE'].'" id="balloon_'.$arResult['GRID_ID'].'_'.$arTask['ID'].'">'.$arTask['RESPONSIBLE_FORMATTED_NAME'].'</a>'.
                //  '<script type="text/javascript">BX.tooltip('.$arTask['~RESPONSIBLE_ID'].', "balloon_'.$arResult['GRID_ID'].'_'.$arTask['ID'].'", "");</script>'
                //  : ''
            );

            $arColumns['CHECK_ROW'] = '<input type="checkbox" name="exported[]" class="exported" value="'.$arTask['ID'].'" />';

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
            
            // $query = 'select * from b_utm_tasks_task where VALUE="D_'.$DB->ForSQL($dealProjRow['DEAL_ID']).'"';
            // $rsUtmTask = $DB->Query($query);

            

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

            $arColumns['UF_REF_CLIENT'] = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_192582141226'])?$arTask['CUSTOM_FIELDS']['UF_AUTO_192582141226']:' - ';
            $value = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_192582141226'])?$arTask['CUSTOM_FIELDS']['UF_AUTO_192582141226']:'';
            $arColumns['UF_REF_CLIENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][ref_client]" value="'.$value.'" />';

            $arColumns['UF_TOELECHTING_FACTURATIE'] = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_371316967809'])?$arTask['CUSTOM_FIELDS']['UF_AUTO_371316967809']:' - ';
            $value = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_371316967809'])?$arTask['CUSTOM_FIELDS']['UF_AUTO_371316967809']:'';
            $arColumns['UF_TOELECHTING_FACTURATIE'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][description]" value="'.$value.'" />';

            $arColumns['UF_LOCKED'] = ($arTask['CUSTOM_FIELDS']['UF_AUTO_713286985141'] == 0) ? 'No' : 'Yes';
            $value = !empty($arTask['CUSTOM_FIELDS']['UF_AUTO_713286985141'])?$arTask['CUSTOM_FIELDS']['UF_AUTO_713286985141']:'';
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


            $invoiceAmount = ''; // bedrag te factureren
            if (!empty($arTask['TASKLIST_FIELDS']['INVOICE_AMOUNT']))
                $invoiceAmount = $arTask['TASKLIST_FIELDS']['INVOICE_AMOUNT'];

            $invoiceAmount = number_format($invoiceAmount,2,',','.');
            $arColumns['UF_BEDRAG_FACTUREREN'] = '<input rel="' . $arTask['ID'] . '" type="text" name="task['.$arTask['ID'].'][invoice_amount]" placeholder="0,00" value="'.$invoiceAmount.'">';

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

            
            /*$expectedCost = 0.00; // totaal verwachte kosten
            foreach ($arTask['TASKLIST_FIELDS'] as $key => $value) {
                if (in_array($key, array('KOSTEN_UREN', 'KOSTEN_DERDEN', 'KOSTEN_LIFT'))) {
                    $expectedCost += doubleval($value);
                }
            }*/

            // $kostenTotal = (doubleval($arTask['CUSTOM_FIELDS'][T_KOSTEN_UREN])+doubleval($arTask['CUSTOM_FIELDS'][T_KOSTEN_DERDEN])+doubleval($arTask['CUSTOM_FIELDS'][T_KOSTEN_LIFT]));

            $expectedCost = ($kostenTotal/$percentDone);
            if ($percentDone<=0) {
                $expectedCost = 0.00;
            }
            $arColumns['UF_VERWACHTE_KOSTEN'] = '<span class="tid_ec_'.$arTask['ID'].'">'
                .number_format($expectedCost, 2,',','.').'</span>'
                .'<input type="hidden" class="kosten_total tid_kt_'.$arTask['ID'].'" value="'.$kostenTotal.'" />';
            $arColumns['UF_VERWACHTE_KOSTEN'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][expected_costs]" value="'.$expectedCost.'" class="tid_ec_'.$arTask['ID'].'" />';


            $expectedReturn = doubleval($totalBudget)-doubleval($expectedCost); // verwachte rendement
            // if ($percentDone<=0) {
            //     $expectedReturn = 0.00;
            // }
            $arColumns['UF_VERWACHTE_RENDEMENT'] = '<span class="tid_er_'.$arTask['ID'].'">'
                .number_format($expectedReturn,2,',','.').'</span>';
            $arColumns['UF_VERWACHTE_RENDEMENT'] .= '<input rel="' . $arTask['ID'] . '" type="hidden" name="task['.$arTask['ID'].'][expected_returns]" value="'.$expectedReturn.'" class="tid_er_'.$arTask['ID'].'" />';


            $otherComments = ''; // opmerkingen voor F&C
            if (!empty($arTask['TASKLIST_FIELDS']['OTHER_COMMENTS'])) {
                $otherComments = stripslashes($arTask['TASKLIST_FIELDS']['OTHER_COMMENTS']);
                $otherComments = trim($otherComments);
            }
            
            $arColumns['UF_OPMERKINGEN_VOOR'] = '<textarea rel="' . $arTask['ID'] . '" type="text" name="task['.$arTask['ID'].'][other_comments]" placeholder="Voeg commentaar toe" rows="3">'.$otherComments.'</textarea>';

            $arResult['GRID_DATA'][] = array(
                    'id' => $arTask['ID'],
                    'actions' => $arActions,
                    'data' => $arTask,
                    'editable' => false,
                    'columns' => $arColumns
                );
        }
        
        $APPLICATION->IncludeComponent('bitrix:main.user.link',
            '',
            array(
                'AJAX_ONLY' => 'Y',
            ),
            false,
            array('HIDE_ICONS' => 'Y')
        );

        // $formAction = POST_FORM_ACTION_URI;
        $formAction = '/crm/tasks/import.php';
        ?>
        <div class="import_block">
            <h2>Import XML</h2>
            <form method="post" action="<?php echo $formAction;?>" enctype="multipart/form-data">
                <input type="file" name="importfile">
                <button type="submit" name="submit" class="btn_anchor">Submit</button>
            </form>
        </div>
        <br>
        <?php if (isset($_REQUEST['exported']) && 'y' == strtolower($_REQUEST['exported'])):?>
            <?php
            $exported = array();
            if ('csv' == strtolower($_REQUEST['format'])) {
                $path = "/crm/tasks/export/takenlijst_".date("Y-m-d").".csv";
                if (file_exists(DOCUMENT_ROOT.$path)) {
                    $exported['csv'] = $path;
                }
            }
            if ('xml' == strtolower($_REQUEST['format'])) {
                $paths = array(
                    'projects' => "/crm/tasks/export/projects_".date("Y-m-d").".xml",
                    'factuur' => "/crm/tasks/export/factuur_".date("Y-m-d").".xml",
                    // 'old-xml' => "/crm/tasks/export/takenlijst_".date("Y-m-d").".xml",
                    'csv' => "/crm/tasks/export/takenlijst_".date("Y-m-d").".csv"
                );
                foreach ($paths as $key => $path) {
                    if (file_exists(DOCUMENT_ROOT.$path)) {
                        $exported[$key] = $path;
                    }
                }
            }
            
            /*if (!empty($exported)) {
                ?>
                <div class="success_message task-list-status">
                    <div class="task-list-status-content">Export of tasks done! Please click here to download file(s) 
                    <br>
                    <?php
                    foreach ($exported as $key => $path) {
                        ?><a class="download" href="<?php echo $path;?>" download><?php echo strtoupper($key);?></a><?php
                    }
                    ?>
                    </div>
                </div>
                <?php
            }*/
            ?>
        <?php endif;?>
        <?php if (isset($_REQUEST['imported']) && 'y' == strtolower($_REQUEST['imported'])):?>
        <div class="success_message task-list-status">
            <div class="task-list-status-content">Import done. Tasks have been updated.</div>
        </div>
        <?php endif;?>
        <br>
        <?php /*<div class="row">
            <div class="col-lg-12">
                <span>Filter by completion date:</span>
                <form method="post" action="" enctype="multipart/form-data">
                <span class="col-lg-3 crm-offer-info-data-wrap"><input data-toggle="datepicker" id="datepicker" name="date_complete" value="<?php echo $_POST['date_complete'];?>" class="" 
                    style="height: 35px; border: 1px solid #d9d9d9; background-color: #FFF; text-indent: 10px; font-size: 1.2em; padding: 10px 5px;"></span>
                <input type="submit" class="btn_anchor" name="filter_on" value="Submit">
                <input type="submit" class="webform-small-button-text" name="filter_off" value="Clear" style="margin-left: 20px;">
                </form>
            </div>
        </div>*/ ?>
    <br>

        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:main.interface.grid',
            '',
            array(
                'GRID_ID' => $arResult['GRID_ID'],
                'HEADERS' => $arResult['HEADERS'],
                'SORT' => $arResult['SORT'],
                'SORT_VARS' => $arResult['SORT_VARS'],
                'ROWS' => $arResult['GRID_DATA'],
                'FOOTER' => array(array('title' => GetMessage('CRM_ALL'), 'value' => $arResult['ROWS_COUNT'])),
                'EDITABLE' => 'N',
                'ACTIONS' => array(
                    'delete' => true
                ),
                'ACTION_ALL_ROWS' => true,
                'NAV_OBJECT' => $arResult['DB_LIST'],
                'FORM_ID' => $arResult['FORM_ID'],
                'TAB_ID' => $arResult['TAB_ID'],
                'AJAX_MODE' => $arResult['INTERNAL'] ? 'N' : 'Y',
                'FILTER' => $arResult['FILTER'],
                'FILTER_PRESETS' => $arResult['FILTER_PRESETS']
            ),
            $component
        );
    ?>
    <style type="text/css">
    .main-grid-more {
        position: relative;
        background: #ffffff;
        text-align: left;
        border-top: 1px #eef2f4 solid;
        padding-left: 2%;
    }
    .main-grid-more-btn {
        display: inline-block;
        text-align: center;
        padding-left: 44px;
        padding-right: 44px;
        padding-top: 7px;
        padding-bottom: 7px;
        cursor: pointer;
        color: #8e9697;
        font-size: 13px;
        font-weight: normal;
        line-height: 34px;
        vertical-align: middle;
        border: 1px solid #c6cdd3;
        border-radius: 2px;
        margin-top: 12px;
        margin-bottom: 12px;
        -webkit-transition: background 200ms;
        -moz-transition: background 200ms;
        -ms-transition: background 200ms;
        -o-transition: background 200ms;
        transition: background 200ms;
        background-color: #bbed21;
        font-weight: bold;
    }

    .main-grid-more-btn::after {
        background: #eef2f4;
        position: absolute;
        display: block;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        content: '';
    }

    .main-grid-more-btn:hover {
        background: #f5f5f5;
    }

    .main-grid-more-btn:active {
        background: #f5f5f5;
        -webkit-box-shadow:inset 0 -4px 3px -5px rgba(0,0,0,.7);
        box-shadow:inset 0 -4px 3px -5px rgba(0,0,0,.7);
    }

    .main-grid-more-btn.load {
        background: #f5f5f5;
        padding-right: 36px;
    }

    .main-grid-more-btn .main-grid-more-load-text,
    .main-grid-more-btn.load .main-grid-more-text {
        display: none;
    }

    .main-grid-more-btn.load .main-grid-more-load-text {
        display: inline-block;
    }

    .main-grid-more-btn.load .main-grid-more-icon {
        background: url(images/loader.gif) 50% 50% no-repeat;
        position: relative;
        top: -2px;
        width: 20px;
        height: 20px;
        display: inline-block;
        vertical-align: middle;
        margin:0 5px;
        opacity:.6;
    }

    .main-grid-more-btn.load .main-grid-more-icon {
        background: url(images/loader.png) no-repeat 0 0;
        background-size: 100%;
        height: 20px;
        width: 20px;
        -webkit-transition: opacity 0.2s linear;
        transition: opacity 0.2s linear;
    }

    .main-grid-more-btn.load .main-grid-more-icon {
        -webkit-animation: grid-load 2s linear infinite;
        animation: grid-load 2s linear infinite;
        opacity: 1;
    }
    </style>
    <div class="main-grid-more task_buttons">
        <a href="" class="btn_deliver"></a>
        <a href="" class="btn_export"></a>
        <a href="#" class="main-grid-more-btn submit_btn export_csv" data-export="xml"><span class="main-grid-more-text">VERZEND NAAR ADMINSTRATIE</span><span class="main-grid-more-load-text">Loading data</span><span class="main-grid-more-icon"></span></a>
        <a href="#" class="main-grid-more-btn submit_btn export_xml" data-export="csv"><span class="main-grid-more-text">EXPORT CSV</span><span class="main-grid-more-load-text">Loading data</span><span class="main-grid-more-icon"></span></a>
    </div>
    <script>
    jQuery(document).ready(function() {
        jQuery('.percent_done').on('keyup', function() {
            var percentDone = $(this).val();
            var reftid = $(this).data('tid');
            var totalbudget = jQuery('.total_budget.tid_tb_'+reftid).val();
            totalBudget = parseFloat(totalbudget);
            var omzet = jQuery('.tid_omzet_'+reftid).val();
            omzet = parseFloat(omzet);
            var kostenTotal = jQuery('.tid_kt_'+reftid).val();
            kostenTotal = parseFloat(kostenTotal);
            console.log('kostenTotal is '+kostenTotal);

            if (percentDone >= 1) {
                percentDone = (percentDone/100);

                console.log('percentDone is '+percentDone);
                
                var owh = 0.00
                if (totalbudget > 0 && omzet > 0)
                    owh = ( (percentDone*totalbudget)-omzet );
                owh = parseFloat(owh);

                var expectedCost = 0;
                // if(kostenTotal > 0)
                    expectedCost = (kostenTotal/percentDone);
                expectedCost = parseFloat(expectedCost);

                console.log('expectedCost is '+expectedCost);
                
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
                var r = confirm("Are you sure that these task you want to invoice?");
                if (r == true) {
                    jQuery('#form_tasklist').submit();
                }
                
            } else {
                alert('Please select atleast 1 task to proceed.');
            }
            
        });
        jQuery('#check_all').on('change', function() {
            // $('input:checkbox').not(this).prop('checked', this.checked);
            $('input:checkbox.exported').prop('checked', this.checked);
        });

        var picker = new Pikaday(
        {
            field: document.getElementById('datepicker'),
            firstDay: 1,
            // minDate: new Date(),
            maxDate: new Date(2020, 12, 31),
            yearRange: [2000,2020],
            format: 'D/M/YYYY',
            toString(date, format) {
                // you should do formatting based on the passed format,
                // but we will just return 'D/M/YYYY' for simplicity
                const day = date.getDate();
                const month = date.getMonth() + 1;
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }
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
