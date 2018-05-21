{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TaskDetailPartsProjDep","Item","Util","ItemSet","extend","sys","code","methods","construct","this","callConstruct","vars","data","option","bindEvents","bindDelegateControl","passCtx","onRelationLeftChange","onRelationRightChange","value","VALUE","display","DISPLAY","destruct","remove","scope","ctrls","node","control","getLinkTypeByEnds","onDeleteClick","fireEvent","left","right","LINK_TYPE_START_START","LINK_TYPE_START_FINISH","LINK_TYPE_FINISH_START","LINK_TYPE_FINISH_FINISH","PopupItemSet","options","itemFx","itemFxHoverDelete","instances","calendar","selector","window","load","callMethod","arguments","toggleContainer","openAddForm","B24","licenseInfoPopup","show","message","assignCalendar","bindFormEvents","addCustomEvent","delegate","itemsChanged","bindEvent","onItemDestroy","cont","itemCount","addItem","parameters","createItem","DEPENDS_ON_TITLE","SE_DEPENDS_ON","TITLE","L_START","TYPE","L_FINISH","R_START","R_FINISH","getNodeByTemplate","append","item","parent","getPopupAttachTo","applySelectionChange","k","temporalItems","TASK_ID","DEPENDS_ON_ID","id","name","close","extractItemValue","extractItemDisplay"],"mappings":"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"}