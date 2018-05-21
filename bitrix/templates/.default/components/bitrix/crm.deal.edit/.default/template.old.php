<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
$APPLICATION->AddHeadScript("/bitrix/js/crm/crm.js");
$APPLICATION->AddHeadScript('/bitrix/templates/bitrix24/js/jquery-3.2.1.min.js');
// $APPLICATION->AddHeadScript('/bitrix/templates/bitrix24/jquery-ui.js');
// $APPLICATION->AddHeadScript('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js');
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");

// for the project search auto-suggest
$APPLICATION->AddHeadScript('/bitrix/templates/bitrix24/js/auto-complete.min.js');
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/auto-complete.css');

if(isset($arResult['CONVERSION_LEGEND'])):
	?><div class="crm-view-message"><?=$arResult['CONVERSION_LEGEND']?></div><?
endif;

$arTabs = array();
$arTabs[] = array(
	'id' => 'tab_1',
	'name' => GetMessage('CRM_TAB_1'),
	'title' => GetMessage('CRM_TAB_1_TITLE'),
	'icon' => '',
	'fields'=> $arResult['FIELDS']['tab_1']
);

$productFieldset = array();
foreach($arTabs[0]['fields'] as $k => &$field):
	if($field['id'] === 'section_product_rows'):
		$productFieldset['NAME'] = $field['name'];
		unset($arTabs[0]['fields'][$k]);
	endif;

	if($field['id'] === 'PRODUCT_ROWS'):
		$productFieldset['HTML'] = $field['value'];
		unset($arTabs[0]['fields'][$k]);
		break;
	endif;
endforeach;
unset($field);

$elementID = isset($arResult['ELEMENT']['ID']) ? $arResult['ELEMENT']['ID'] : 0;

$arResult['CRM_CUSTOM_PAGE_TITLE'] =
	$elementID > 0
	? GetMessage('CRM_DEAL_EDIT_TITLE',
		array(
			'#ID#' => $elementID,
			'#TITLE#' => isset($arResult['ELEMENT']['TITLE']) ? $arResult['ELEMENT']['TITLE'] : ''
		)
	)
	: GetMessage('CRM_DEAL_CREATE_TITLE');

$arFormButtons = array(
	'back_url' => $arResult['BACK_URL'],
	'custom_html' => '<input type="hidden" name="deal_id" value="'.$elementID.'"/>'
);

if($arResult['CATEGORY_ID'] > 0)
{
	$arFormButtons['custom_html'] .= '<input type="hidden" name="category_id" value="'.$arResult['CATEGORY_ID'].'"/>';
}

if($arResult['CALL_LIST_ID'] > 0)
{
	$arFormButtons['custom_html'] .= '<input type="hidden" name="call_list_id" value="'.(int)$arResult['CALL_LIST_ID'].'"/>';
	$arFormButtons['custom_html'] .= '<input type="hidden" name="call_list_element" value="'.(int)$arResult['CALL_LIST_ELEMENT'].'"/>';
}
$projectId = isset($_REQUEST['PROJECT_ID'])?$DB->ForSQL($_REQUEST['PROJECT_ID']):'';
$arFormButtons['custom_html'] .= '<input type="hidden" name="PROJECT_ID" value="'.$projectId.'"/>';

if(isset($arResult['LEAD_ID']) && $arResult['LEAD_ID'] > 0)
{
	$arFormButtons['standard_buttons'] = false;
	$arFormButtons['wizard_buttons'] = true;
	$arFormButtons['custom_html'] = '<input type="hidden" name="lead_id" value="'.$arResult['LEAD_ID'].'"/>';
}
elseif(isset($arResult['QUOTE_ID']) && $arResult['QUOTE_ID'] > 0)
{
	$arFormButtons['standard_buttons'] = false;
	$arFormButtons['wizard_buttons'] = true;
	$arFormButtons['custom_html'] .= '<input type="hidden" name="quote_id" value="'.$arResult['QUOTE_ID'].'"/>';
}
elseif(isset($arResult['EXTERNAL_CONTEXT']) && $arResult['EXTERNAL_CONTEXT'] !== '')
{
	$arFormButtons['standard_buttons'] = false;
	$arFormButtons['dialog_buttons'] = true;
	$arFormButtons['wizard_buttons'] = false;
	$arFormButtons['custom_html'] .= '<input type="hidden" name="external_context" value="'.htmlspecialcharsbx($arResult['EXTERNAL_CONTEXT']).'"/>';
}
else
{
	$arFormButtons['standard_buttons'] = true;
	$arFormButtons['wizard_buttons'] = false;
}

$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.form',
	'edit',
	array(
		'FORM_ID' => $arResult['FORM_ID'],
		'GRID_ID' => $arResult['GRID_ID'],
		'TABS' => $arTabs,
		'FIELD_SETS' => array($productFieldset),
		'BUTTONS' => $arFormButtons,
		'IS_NEW' => $elementID <= 0,
		'USER_FIELD_ENTITY_ID' => CCrmDeal::$sUFEntityID,
		'USER_FIELD_SERVICE_URL' => '/bitrix/components/bitrix/crm.config.fields.edit/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
		'TITLE' => $arResult['CRM_CUSTOM_PAGE_TITLE'],
		'ENABLE_TACTILE_INTERFACE' => 'Y',
		'DATA' => $arResult['ELEMENT'],
		'SHOW_SETTINGS' => 'Y'
	)
);
?>
<script type="text/javascript">

	// window.CrmProductRowSetLocation = function(){ BX.onCustomEvent('CrmProductRowSetLocation', ['LOC_CITY']); };

	/*BX.ready(
		function()
		{
			var formID = 'form_' + '<?= $arResult['FORM_ID'] ?>';
			var form = BX(formID);

			var currencyEl = BX.findChild(form, { 'tag':'select', 'attr':{ 'name': 'CURRENCY_ID' } }, true, false);
			var opportunityEl = BX.findChild(form, { 'tag':'input', 'attr':{ 'name': 'OPPORTUNITY' } }, true, false);

			var prodEditor = BX.CrmProductEditor.getDefault();
			if(opportunityEl)
			{
				opportunityEl.disabled = prodEditor.getProductCount() > 0;

				BX.addCustomEvent(
					prodEditor,
					'productAdd',
					function(params)
					{
						opportunityEl.disabled = prodEditor.getProductCount() > 0;
					}
				);

				BX.addCustomEvent(
					prodEditor,
					'productRemove',
					function(params)
					{
						opportunityEl.disabled = prodEditor.getProductCount() > 0;
					}
				);

				BX.addCustomEvent(
					prodEditor,
					'sumTotalChange',
					function(ttl)
					{
						opportunityEl.value = ttl;
					}
				);

				if(currencyEl)
				{
					BX.bind(
						currencyEl,
						'change',
						function()
						{
							var currencyId = currencyEl.value;
							var prevCurrencyId = prodEditor.getCurrencyId();

							prodEditor.setCurrencyId(currencyId);

							var oportunity = opportunityEl.value.length > 0 ? parseFloat(opportunityEl.value) : 0;
							if(isNaN(oportunity))
							{
								oportunity = 0;
							}

							if(prodEditor.getProductCount() == 0 && oportunity !== 0)
							{
								prodEditor.convertMoney(
									parseFloat(opportunityEl.value),
									prevCurrencyId,
									currencyId,
									function(sum)
									{
										opportunityEl.value = sum;
									}
								);
							}
						}
					);
				}
			}

			var el = BX("LOC_CITY_val");
			if (el)
				BX.addClass(el, "bx-crm-edit-input");
		}
	);*/
</script>
<?
if($arResult['CONVERSION_PERMITTED'] && $arResult['CAN_CONVERT'] && isset($arResult['CONVERSION_CONFIG'])):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmEntityType.captions =
				{
					"<?=CCrmOwnerType::LeadName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Lead)?>",
					"<?=CCrmOwnerType::ContactName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Contact)?>",
					"<?=CCrmOwnerType::CompanyName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Company)?>",
					"<?=CCrmOwnerType::DealName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Deal)?>",
					"<?=CCrmOwnerType::InvoiceName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Invoice)?>",
					"<?=CCrmOwnerType::QuoteName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Quote)?>"
				};

				BX.CrmDealConversionScheme.messages =
					<?=CUtil::PhpToJSObject(\Bitrix\Crm\Conversion\DealConversionScheme::getJavaScriptDescriptions(false))?>;

				BX.CrmDealConverter.messages =
				{
					accessDenied: "<?=GetMessageJS("CRM_DEAL_CONV_ACCESS_DENIED")?>",
					generalError: "<?=GetMessageJS("CRM_DEAL_CONV_GENERAL_ERROR")?>",
					dialogTitle: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_TITLE")?>",
					syncEditorLegend: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_SYNC_LEGEND")?>",
					syncEditorFieldListTitle: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_SYNC_FILED_LIST_TITLE")?>",
					syncEditorEntityListTitle: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_SYNC_ENTITY_LIST_TITLE")?>",
					continueButton: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_CONTINUE_BTN")?>",
					cancelButton: "<?=GetMessageJS("CRM_DEAL_CONV_DIALOG_CANCEL_BTN")?>"
				};
				BX.CrmDealConverter.permissions =
				{
					invoice: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_INVOICE'])?>,
					quote: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_QUOTE'])?>
				};
				BX.CrmDealConverter.settings =
				{
					serviceUrl: "<?='/bitrix/components/bitrix/crm.deal.show/ajax.php?action=convert&'.bitrix_sessid_get()?>",
					config: <?=CUtil::PhpToJSObject($arResult['CONVERSION_CONFIG']->toJavaScript())?>
				};
			}
		);
	</script>
<?endif;?>

<?php
define('FIELD_PROJECT_ID', CUF_D_PROJECT_ID);
define('FIELD_PROJECT_NAME', CUF_D_PROJECT_NAME);

if ($arResult['ELEMENT']['ID']==0 && (empty($_REQUEST[FIELD_PROJECT_NAME]) || empty($_REQUEST['PROJECT_ID'])) && empty($arResult['ERROR_MESSAGE'])):
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/customjs/crm/activity.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/interface_grid.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/customjs/crm/interface_grid.js');
?>
<?php/*<script type="text/javascript" src="/crm/deal/popup.js"></script>*/?>
<script type="text/javascript">
BX.loadCSS('/bitrix/components/bitrix/crm.interface.form.tactile/templates/.default/bitrix/main.interface.form/tactile/style.css'); 
var surl = '/crm/deal/popup.php';

// var windowWidth = ($(window).width() * 0.60);
// var windowHeight = ($(window).height() * 0.78);
var windowWidth = 350;
var windowHeight = 285;
var dealDialog = new BX.CDialog({ 
        content_url: surl, 
        width: windowWidth, 
        height: windowHeight, 
        resizable: false,
        title: 'Nieuwe offerte'
    });

<?php if (!isset($_REQUEST['project'])):?>
dealDialog.Show();
<?php endif;?>

function initProjectPopup()
{
    $('#project_select').on('click', function() {
        $('.popup_project .bx-core-adm-dialog-head-inner').text('Toevoegen aan project');
        $('.popup_step1').hide();
        $('.popup_step2').show();
        $('.popup_project .bx-core-adm-dialog-content').css({height:'325px'})
    });
    $('#back_step1').on('click', function() {
        $('.popup_project .bx-core-adm-dialog-head-inner').text('Nieuwe offerte');
        $('.popup_step1').show();
        $('.popup_step2').hide();
        $('.popup_project .bx-core-adm-dialog-content').css({height:'285px'}) 
    });

    $('#project_id_input').on('keyup', function() {
        var projectidInput = $(this);
        var posturl = '/crm/deal/action.php';
        var assignedName = document.querySelector('#project_assign');
        if (projectidInput.val()!='' && projectidInput.val()!=' ') {
            jQuery.ajax({
                type     : 'GET',
                url      : posturl,
                dataType : 'json',
                data     : {
                    qproject : projectidInput.val(),
                    action     : 'select',
                    ajax       : true,
                },
                success : function(response) {
                    if (response.success) {
                        assignedName.innerText = '+ ['+response.project.ID+'] '+response.project_name;
                    } else {
                        assignedName.innerText = '+ SELECT PROJECT';
                    }
                }    
            });
        } else {
            assignedName.innerText = '+ SELECT PROJECT';
        }
        
    });
    $( '#project_id_input' ).keypress(function( event ) {
        if ( event.which == 13 ) {
            event.preventDefault();
            $('#project_assign').trigger('click');
        }
    });

    $('#project_assign').on('click', function() {
        var projectidInput = $('#project_id_input');
        if (projectidInput.val()!='' && projectidInput.val()!=' ') {
            var posturl = '/crm/deal/action.php';
            jQuery.ajax({
                type     : 'GET',
                url      : posturl,
                dataType : 'json',
                data     : {
                    project : projectidInput.val(),
                    action     : 'new',
                    ajax       : true,
                },
                success : function(response) {
                    if (response.success) {

                        if (response.company_id > 0 || response.contact_id > 0) {
                            var reload = window.location.pathname+'?company_id='+response.company_id+'&contact_id='+response.contact_id+'&project='+response.project.ID;
                            window.location.href = reload;
                            dealDialog.Close();
                        } else {
                            $('input#project_name').val(response.project_name);
                            $('input#project_name_disabled').val(projectidInput.val()+': '+response.project_name);
                            $('input#project_id').val(projectidInput.val());
                            dealDialog.Close();    
                        }
                        
                    } else {
                        projectidInput.val('');
                        projectidInput.css('border','1px solid red');
                    }
                }    
            });
        } else {
            projectidInput.css('border','1px solid red');
        }
    });
    $( 'input[name="project_title"]' ).keypress(function( event ) {
        if ( event.which == 13 ) {
            event.preventDefault();
            $('#project_new').trigger('click');
        }
    });
    $('#project_new').on('click', function() {
        var inputTitle = $('input[name="project_title"]');
        var projectname = inputTitle.val();
        if (projectname!='' && projectname!=' ') {
            var posturl = '/crm/deal/action.php';
            jQuery.ajax({
                type     : 'POST',
                url      : posturl,
                dataType : 'json',
                data     : {
                    title : projectname,
                    action     : 'new',
                    ajax       : true,
                },
                success : function(response) {
                    if (response.success) {
                        $('input#project_name').val(projectname);
                        $('input#project_name_disabled').val(response.last_id+': '+projectname);
                        $('input#project_id').val(response.last_id);
                        dealDialog.Close();
                    } else {
                        inputTitle.val('');
                        inputTitle.css('border','1px solid red');
                    }
                }    
            });
        } else {
            inputTitle.css('border','1px solid red');
        }
    });
}

$(function() {
    setTimeout(function() {
        $('#bx-admin-prefix').addClass('popup_project');
        initProjectPopup();
        setBedrag();
    }, 700);
});

</script>
<?php endif; ?>
<script>
function setBedrag()
{
    // totalcost = totalcost.toFixed(2);
    // totalcost = totalcost.replace('.',',');
    // totalcost = parseFloat(totalcost);
    // totalcost = totalcost.toLocaleString('de-DE');

    var totalcost=0; 
    $('input.cost_fields_hidden').each(function() {
        var cost = $(this).val();
        if (!!cost.trim()) {
            if (isNaN(cost)) {
                cost = 0.00;
            }
        }

        if ($(this).data('computed') == 'N') {
            totalcost += parseFloat(cost);
        }
        if ($(this).data('computed') == 'Y') {
            totalcost -= parseFloat(cost);
        }
        // cost = parseFloat(cost);
        // cost = cost.toFixed(2);
        // $(this).val(cost.replace('.',','));
    });
    totalcost = (totalcost).toLocaleString("de-DE", {minimumFractionDigits: 2}) ;

    // totalcost = totalcost.toFixed(2);
    // totalcost = totalcost.replace('.',',');
    document.querySelector('input.crm-offer-item-inp.readonly').value = totalcost;
}

function setCostFormatting(elem)
{
    var cost = elem.val();
    if (cost.indexOf('.')!==-1) {
        cost = cost.replace('.','');
    }
    if (cost.indexOf(',')!==-1) {
        cost = cost.replace(',','.');
    }
    cost = parseFloat(cost);
    
    var target = elem.data('target');
    $('input[name="'+target+'"]').val(cost);

    if (!isNaN(cost)) {
        // cost = cost.toFixed(2);
        // cost = cost.replace('.',',');
        
        cost = (cost).toLocaleString("de-DE", {minimumFractionDigits: 2, currency: 'EUR', currencyDisplay: 'symbol'}) ;
        elem.val(cost);
    }
}
function setEvents()
{
    $('input.cost_field.cost_field.integer').on('blur', function() {
        setCostFormatting($(this));
        setBedrag();
    });
    // $('input.cost_field.cost_field.integer').on('keyup', function() {
    //     setBedrag();
    // });
}
$(function() {
    $('input.cost_field').each(function() {
        setCostFormatting($(this));
    });

    setBedrag();

    setEvents();
});
</script>
