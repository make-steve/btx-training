<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.deal.view',
	'',
	array(
		'UID' => 'mobile_crm_deal_view',
		'DEAL_SHOW_URL_TEMPLATE' => '/mobile/crm/deal/view.php?deal_id=#deal_id#',		
		'DEAL_EDIT_URL_TEMPLATE' => '/mobile/crm/deal/edit.php?deal_id=#deal_id#',			
		'ACTIVITY_LIST_URL_TEMPLATE' => '/mobile/crm/activity/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'ACTIVITY_EDIT_URL_TEMPLATE' => '/mobile/crm/activity/edit.php?owner_type=#owner_type#&owner_id=#owner_id#&type_id=#type_id#',
		'INVOICE_EDIT_URL_TEMPLATE' => '/mobile/crm/invoice/edit.php?contact_id=#contact_id#&company_id=#company_id#&deal_id=#deal_id#',
		'COMMUNICATION_LIST_URL_TEMPLATE' => '/mobile/crm/comm/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#&type_id=#type_id#',		
		'EVENT_LIST_URL_TEMPLATE' => '/mobile/crm/event/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'PRODUCT_ROW_LIST_URL_TEMPLATE' => '/mobile/crm/product_row/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'COMPANY_SHOW_URL_TEMPLATE' => '/mobile/crm/company/view.php?company_id=#company_id#',
		'CONTACT_SHOW_URL_TEMPLATE' => '/mobile/crm/contact/view.php?contact_id=#contact_id#',	
		'DEAL_STAGE_SELECTOR_URL_TEMPLATE' => '/mobile/crm/progress_bar/list.php?mode=selector&entity_type=deal&context_id=#context_id#',
		'USER_PROFILE_URL_TEMPLATE' => '/mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
