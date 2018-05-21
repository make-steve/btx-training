BX.ready(function(){
	/*BX.bind(BX('_qpv_toggle_btn', true), 'click', function()
	{
		console.log('test');
		var checkSettings = this.className;

		if(checkSettings.indexOf("crm-lead-header-contact-btn-close") > -1)
			BX('bx-crm-project-form').style.display = "none";
		else
			BX('bx-crm-project-form').style.display = "block";
	});

	var getMainSetting = BX('_qpv_toggle_btn').className;
		
	if(getMainSetting.indexOf("crm-lead-header-contact-btn-open") > -1)
		BX('bx-crm-project-form').style.display = "none";
	else
		BX('bx-crm-project-form').style.display = "block";*/

});

$(function(){

	$("span#deal_finan_showhide").on('click', function() {

		if($(this).hasClass('finanhide')) {
			$('tr#project_financial_container').hide();
			$(this).removeClass('finanhide').addClass('finanshow');
		}
		else {
			$('tr#project_financial_container').show();
			$(this).removeClass('finanshow').addClass('finanhide');
		}
	});
});

function showDealTasks(element)
{
	var url = '/bitrix/templates/.default/components/bitrix/crm.interface.form/show.project/bitrix/main.interface.form/crm.view.project/settings.php';
	var checkSettings = element.className;
	var getProjectDealID = element.getAttribute('rel');

	var splitIDS = getProjectDealID.split("-");
	var projectID = splitIDS[0];
	var dealID = splitIDS[1];
	var showTasks = "Y";

	var getDealContainers = document.getElementsByClassName("crm_project_deal_" + dealID + "_container");

	if(checkSettings.indexOf("crm-lead-header-contact-btn-close") > -1)
	{
		showTasks = "N";
		checkSettings = checkSettings.replace("btn-close", "btn-open");
		element.className = checkSettings;
	}
	else
	{
		showTasks = "Y";
		checkSettings = checkSettings.replace("btn-open", "btn-close");
		element.className = checkSettings;
	}

	BX.ajax({
        url: url,
        method: 'post',
        async: true,
        emulateOnload: true,
        data: { 'projectID': projectID, 'dealID': dealID, 'showTasks': showTasks },
        cache: false,
        onsuccess: function(result)
        {
        	if(result == "true")
        	{
        		for (var i = 0; i < getDealContainers.length; i++) {
				    if(showTasks == "Y")
				    	getDealContainers[i].style.display = "table-row";
				    else
				    	getDealContainers[i].style.display = "none";
				}
        	}
        }
    });
}

function lockTaskAction(element, set)
{
	var getTaskID = element.getAttribute("rel");
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

function delTaskAction(element)
{
	var getTaskID = element.getAttribute("rel");
	var url = '/bitrix/components/bitrix/tasks.base/ajax.php';
	var delButtons = BX.CrmPopupWindowHelper.prepareButtons(
	[
		{
			type: 'button',
			settings:
			{
				text: 'Continue',
				className: 'popup-window-button-accept',
				events:
				{
					click : function()
					{
						var batch = [{ARGUMENTS: {id: parseInt(getTaskID)}, OPERATION: "task.delete", PARAMETERS: {code: "op_0"}}];

						BX.ajax({
		                    url: url,
		                    method: 'post',
		                    dataType: 'json',
		                    async: true,
		                    processData: true,
		                    emulateOnload: true,
		                    start: true,
		                    data: {
			                    'sessid': BX.bitrix_sessid(), // make security filter feel happy, call variable "sessid" instead of "csrf"
		                        'SITE_ID': BX.message('SITE_ID'),
			                    'EMITTER': '',
		                        'ACTION': batch
		                    },
		                    cache: false,
		                    onsuccess: function(result)
		                    {
		                    	var resOpt = {};
		                        try // prevent falling through onfailure section in case of some exceptions inside onsuccess
		                        {
			                        if(!result)
			                        {
				                        resOpt = {
					                        SUCCESS: false,
					                        ERROR: [{CODE: 'INTERNAL_ERROR', MESSAGE: BX.message('TASKS_ASSET_QUERY_EMPTY_RESPONSE'), TYPE: 'FATAL'}],
					                        ASSET: [],
					                        DATA: {}
				                        };
			                        }
			                        else
			                        {
			                        	var popupwindow = BX.PopupWindowManager.getCurrentPopup();
										popupwindow.close();
										popupwindow.destroy();
										window.location.reload();
			                        }
		                        }
		                        catch(e)
		                        {
			                        resOpt = {
				                        success: 				false,
				                        clientProcessErrors: 	[{CODE: 'INTERNAL_ERROR', MESSAGE: BX.message('TASKS_ASSET_QUERY_QUERY_FAILED_EXCEPTION'), TYPE: 'FATAL'}],
				                        serverProcessErrors: 	[],
				                        data: 					{}
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
	var content = '<div style="padding: 16px 12px; max-width: 400px; max-height: 400px; overflow: hidden;">Are you sure you want to delete task?</div>';
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
            'buttons': delButtons,
        }
    );

    popupWindow.show();
}