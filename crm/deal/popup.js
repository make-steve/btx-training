
var popupWindow = null;
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
        'buttons': this.prepareButtons(action),
    }
);

popupWindow.content = 'test';
popupWindow.show();

function prepareButtons(action)
{
    return BX.CrmPopupWindowHelper.prepareButtons(
    [
        {
            type: 'button',
            settings:
            {
                text: 'Okay',
                className: 'popup-window-button-accept',
                events:
                {
                    click : function()
                    {
                    

                        
                    }
                }
            }
        },
        {
            type: 'button',
            settings:
            {
                text: 'Cancel',
                className: 'popup-window-button-link-cancel',
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
}