<?
if ('csv' == strtolower($_REQUEST['format'])) {
    $path = "/crm/tasks/export/takenlijst_".date("Y-m-d").".csv";
    if (file_exists($_SERVER["DOCUMENT_ROOT"].$path)) {
        $exported['csv'] = $path;
        $url = "/crm/tasks/export/takenlijst_".date("Y-m-d").".csv";
        header("Content-type: text/csv");
        // header("Location: " . $path );
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        header('Content-Disposition: attachment; filename="takenlijst_'.date("Y-m-d").'.csv"');
        echo readfile($_SERVER["DOCUMENT_ROOT"].$path);
        unlink($_SERVER["DOCUMENT_ROOT"].$path);
        exit();
    }
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle('Factuuroverzicht');
/*?><?$APPLICATION->IncludeComponent(
	"make:crm.activity.task.list",
	"",
    Array(
        "SEF_MODE" => "Y",
        "ELEMENT_ID" => $_REQUEST["task_id"],
        "SEF_FOLDER" => "/crm/project/",
        "SEF_URL_TEMPLATES" => Array(
            "index" => "index.php",
            "list" => "list/",
            "edit" => "edit/#task_id#/",
            "show" => "show/#task_id#/"
        ),
        "VARIABLE_ALIASES" => Array(
            "index" => Array(),
            "list" => Array(),
            "edit" => Array(),
            "show" => Array(),
        )
    )
);?>
<?php
*/
/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
    'bitrix:crm.control_panel',
    '',
    array(
        'ID' => 'TASK_LIST',
        'ACTIVE_ITEM_ID' => 'TASK',
        'PATH_TO_PROJECT_LIST' => isset($arResult['PATH_TO_PROJECT_LIST']) ? $arResult['PATH_TO_PROJECT_LIST'] : '',
        'PATH_TO_PROJECT_EDIT' => isset($arResult['PATH_TO_PROJECT_EDIT']) ? $arResult['PATH_TO_PROJECT_EDIT'] : '',
    ),
    $component
);
$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';

$APPLICATION->ShowViewContent('crm-grid-filter');

$APPLICATION->IncludeComponent(
    'make:crm.takenlijst',
    '',
    array(
        'DEAL_COUNT' => '20',
        'PATH_TO_PROJECT_LIST' => $arResult['PATH_TO_PROJECT_LIST'],
        'PATH_TO_PROJECT_SHOW' => $arResult['PATH_TO_PROJECT_SHOW'],
        'PATH_TO_PROJECT_EDIT' => $arResult['PATH_TO_PROJECT_EDIT'],
        'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
        'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID'],
        'GRID_ID_SUFFIX' => "IF",
        // 'CATEGORY_ID' => $categoryID
    ),
    $component
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>