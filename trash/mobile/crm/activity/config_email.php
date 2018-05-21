<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.config.user_email',
	'',
	array('UID' => 'mobile_crm_config_user_email')
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
