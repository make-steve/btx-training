function crm_deal_delete_grid(title, message, btnTitle, path)
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

BX.addCustomEvent("CrmProgressControlAfterSaveSucces", function(progressControl, params)
{
	var getParams = params;

	if(getParams.TYPE == "DEAL" && getParams.VALUE == "WON")
	{
		var d;
		d = new BX.CDialog({
			title: 'Warning!!!',
			head: '',
			content: 'Client ID Exact is missing!',
			resizable: false,
			draggable: true,
			height: 70,
			width: 300
		});

		var data = params;

		var query_data = {
			'method': 'POST',
			'url': '/bitrix/templates/.default/components/bitrix/crm.entity.quickpanelview/.default/checkClientIDExact.php',
			'data':  BX.ajax.prepareData(data),
			'dataType': 'json',
			'onsuccess': function(data) {
				if(data.success == false) 
					popupCheckCompanyClientIDExact(params);
				else
				{
					var query_data2 = {
						'method': 'POST',
						'url': '/bitrix/templates/.default/components/bitrix/crm.entity.quickpanelview/.default/checkproject.php',
						'data':  BX.ajax.prepareData(params),
						'dataType': 'json',
						'onsuccess': function(data) {
							if(data.success == true)
								popupRedirectProjectCard(data, d);
						},
					};

					BX.ajax(query_data2);
				}
			},
		};

		BX.ajax(query_data);

	}
});

function popupCheckCompanyClientIDExact(params)
{
	var d;
	d = new BX.CDialog({
		title: 'Warning!!!',
		head: '',
		content: 'Client ID Exact is missing!',
		resizable: false,
		draggable: true,
		height: 70,
		width: 300
	});
	
	var _BTN = [	
		{
			title: 'Continue',
			id: 'crmOk',
			'action': function () 
			{
				var data = params;

				var query_data = {
					'method': 'POST',
					'url': '/bitrix/templates/.default/components/bitrix/crm.entity.quickpanelview/.default/checkproject.php',
					'data':  BX.ajax.prepareData(data),
					'dataType': 'json',
					'onsuccess': function(data) {
						if(data.success == true)
							popupRedirectProjectCard(params, d);
					},
				};
				
				BX.ajax(query_data);
			}
		}
	];	
	d.ClearButtons();
	d.SetButtons(_BTN);
	d.Show();
}

function popupRedirectProjectCard(params, dialog)
{
	dialog.Hide();
	window.location.href = "/crm/project/show/" + params.ID + "/";

	/*dialog.Hide();
	var d;
	d = new BX.CDialog({
		title: 'Deal Successfuly Updated',
		head: '',
		content: 'Would you like to go the project details?',
		resizable: false,
		draggable: true,
		height: 70,
		width: 300
	});
	
	var _BTN = [	
		{
			title: 'Yes',
			id: 'crmOk',
			'action': function () 
			{
				window.location.href = "/crm/project/show/" + params.ID + "/";
			}
		},
		BX.CDialog.btnCancel
	];	
	d.ClearButtons();
	d.SetButtons(_BTN);
	d.Show();*/
}