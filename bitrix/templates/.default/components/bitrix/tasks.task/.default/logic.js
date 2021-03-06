'use strict';

BX.namespace('Tasks.Component');

(function(){

	if(typeof BX.Tasks.Component.Task != 'undefined')
	{
		return;
	}

	BX.Tasks.Component.Task = BX.Tasks.Util.Widget.extend({
		options: {
			removeTemplates: false, // temporal, until the bug fixed
			registerDispatcher: true,
			data: {}
		},
		constants: {
			PRIORITY_AVERAGE: 1,
			PRIORITY_HIGH: 2
		},
		sys: {
			code: 'task-edit'
		},
		methods: {
			construct: function()
			{
				this.callConstruct(BX.Tasks.Util.Widget);

				this.instances.calendar = false;
				this.instances.query = false;
				this.instances.helpWindow = false;

				this.fireTaskEvent();

				if(this.option('doInit'))
				{
					this.bindEvents();

					this.initParentTask();
					this.initRelatedTask();
					this.initReminder();
					this.initProjectDependence();
					this.initProjectPlan();
					this.initState();

					this.doSomeTricks();
				}
			},

			getUser: function()
			{
				return this.option('auxData').USER;
			},

			restrictMemberSelectors: function()
			{
				if(this.getUser().IS_SUPER_USER)
				{
					return;
				}

				this.vars.responsible = null;
				this.vars.originator = null;

				BX.Tasks.Util.Dispatcher.find(this.option('id')+'-responsible').then(function(responsible){
					this.vars.responsible = responsible;
					return BX.Tasks.Util.Dispatcher.find(this.option('id') + '-originator');
				}.bind(this)).then(function(originator){

					this.vars.originator = originator;
					var responsible = this.vars.responsible;

					this.vars.responsibleRestrLock = false;
					this.vars.originatorRestrLock = false;

					originator.bindEvent('change', this.restrictResponsible.bind(this));
					responsible.bindEvent('change', this.restrictOriginator.bind(this));

				}.bind(this));
			},

			restrictResponsible: function()
			{
				if(this.vars.responsibleRestrLock)
				{
					return;
				}
				this.vars.originatorRestrLock = true;

				var responsible = this.vars.responsible;
				var originator = this.vars.originator;

				var user = this.getUser().DATA;
				var values = originator.value();
				var valueOrig = false;
				if(typeof values != 'undefined' && typeof values[0] != 'undefined')
				{
					valueOrig = values[0];
				}

				// other originator. then set responsible to current user and make it read-only
				if(valueOrig)
				{
					values = responsible.value();
					var valueResp = false;
					if(typeof values != 'undefined' && typeof values[0] != 'undefined')
					{
						valueResp = values[0];
					}

					if(valueOrig != 'U'+user.ID)
					{
						if(valueResp != user.ID)
						{
							responsible.replaceItem(valueResp, user);
						}
						responsible.readOnly(true);
					}
					else
					{
						responsible.readOnly(false);
					}
				}

				this.vars.originatorRestrLock = false;
			},

			restrictOriginator: function()
			{
				if(this.vars.originatorRestrLock)
				{
					return;
				}
				this.vars.responsibleRestrLock = true;

				var originator = this.vars.originator;
				var responsible = this.vars.responsible;

				if(originator)
				{
					// multiple responsibles. show originator, set to current user and make read-only
					if(responsible.count() > 1)
					{
						var user = this.getUser().DATA;
						var values = originator.value();
						var value = false;
						if(typeof values != 'undefined' && typeof values[0] != 'undefined')
						{
							value = values[0];
						}

						if(value)
						{
							originator.replaceItem(value, user);

							if(BX.hasClass(this.control('originator'), 'invisible'))
							{
								this.toggleBlock('originator');
							}
						}

						originator.readOnly(true);
					}
					else
					{
						originator.readOnly(false);
					}
				}

				this.vars.responsibleRestrLock = false;
			},

			disableHints: function()
			{
				BX.Tasks.Util.hintManager.disableSeveral(this.option('auxData').HINT_STATE);
			},

			fireTaskEvent: function()
			{
				var eType = this.option('componentData').EVENT_TYPE.toString().toUpperCase();
				var task = this.option('data').EVENT_TASK;
				var uglyTask = this.option('data').EVENT_TASK_UGLY;

				if(eType && (task || uglyTask))
				{
					BX.Tasks.Util.fireGlobalTaskEvent(eType, task, this.option('componentData').EVENT_OPTIONS, uglyTask);
				}
			},

			doSomeTricks: function()
			{
				this.disableHints();
				this.replaceCmdBtn();

				// fix replication checkbox when user press "back" button in browser
				var cb = this.control('flag-replication');
				if(cb.checked)
				{
					BX.removeClass(this.control('replication-panel'), 'invisible');
				}
			},

			replaceCmdBtn: function()
			{
				if(BX.browser.IsMac())
				{
					var cmd = this.control('cmd');
					if(cmd)
					{
						cmd.innerHTML = "&#8984;"
					}
				}
			},

			bindEditorEvents: function(editor, handler)
			{
				// to make form hotkeys work even if focus is in editor
				BX.addCustomEvent(editor, 'OnIframeKeyup', handler);
				BX.addCustomEvent(editor, 'OnTextareaKeyup', handler);
			},

			setFocusOnTitle: function(editor)
			{
				setTimeout(function(){

					var input = this.control('title');

					if(input)
					{
						editor.Focus(false);
						input.focus();
						input.selectionStart = input.value.length;
						BX.focus();
					}
				}.bind(this), 500);
			},

			isEditMode: function()
			{
				return this.option('template').EDIT_MODE;
			},

			bindEvents: function()
			{
				if(!this.isEditMode())
				{
					// editor events
					BX.ready(BX.delegate(function(){

						var handler = BX.delegate(this.onFormKeyDown, this);

						BX.bind(
							document,
							'keydown',
							handler
						);

						var editorId = this.option('template').ID;
						var editor = BXHtmlEditor.Get(editorId);

						if(editor) // already initialized
						{
							this.bindEditorEvents(editor, handler);
							this.setFocusOnTitle(editor, handler);
						}
						else
						{
							BX.addCustomEvent(
								window,
								'OnEditorInitedAfter',
								BX.delegate(function(eventEditor){

									if(eventEditor != null && eventEditor.id == editorId)
									{
										this.bindEditorEvents(eventEditor, handler);
										this.setFocusOnTitle(editor, handler);
									}
								}, this)
							);
						}

					}, this));
				}

				// all block togglers
				this.bindDelegateControl('toggler', 'click', this.passCtx(this.onToggleBlock));

				// all flag togglers
				this.bindDelegateControl('flag', 'click', this.passCtx(this.onToggleFlag));

				// all block choosers
				this.bindDelegateControl('chooser', 'click', this.passCtx(this.onChooseBlock));

				// additional
				this.bindDelegateControl('additional-header', 'click', this.passCtx(this.onToggleAdditionalBlock));

				// priority button
				this.bindDelegateControl('priority-cb', 'change', this.passCtx(this.onPriorityChange));

				this.bindDelegateControl('pin-footer', 'click', BX.delegate(this.onPinFooterClick, this));

				this.bindControl('cancel-button', 'click', BX.delegate(this.onCancelButtonClick, this));

				var elements = this.scope().getElementsByClassName("js-id-wg-optbar-flag-match-work-time");
				if (elements.length)
				{
					BX.bind(elements[0], "change", this.passCtx(this.onWorktimeChange));
				}

				this.bindControl('form', 'submit', BX.delegate(this.onForumSubmit, this));
				this.bindDelegateControl('submit', 'click', this.passCtx(this.onSubmitClick));

				this.bindNestedControls();

				this.bindSliderEvents();

				BX.Tasks.Util.hintManager.bindHelp(this.control('options'));
			},

			bindNestedControls: function()
			{
				// multiple responsibe hint
				BX.Tasks.Util.Dispatcher.bindEvent(this.option('id')+'-responsible', 'change', this.onResponsibleChange.bind(this));
				BX.Tasks.Util.Dispatcher.bindEvent(this.option('id')+'-originator', 'change', this.onOriginatorChange.bind(this));

				this.restrictMemberSelectors();

				// option toggle
				this.getDispatcher().bindEvent('options-'+this.option('id'), 'toggle', this.processToggleFlag.bind(this));
			},

			bindSliderEvents: function()
			{
				BX.addCustomEvent("BX.Bitrix24.PageSlider:onOpen", this.setEditorBeforeUnloadEvent.bind(this, true));
				BX.addCustomEvent("BX.Bitrix24.PageSlider:onClose", this.setEditorBeforeUnloadEvent.bind(this, false));
			},

			setEditorBeforeUnloadEvent: function(flag)
			{
				var editorId = this.option("template").ID;
				var editor = BXHtmlEditor.Get(editorId);

				if (editor)
				{
					flag ? editor.AllowBeforeUnloadHandler() : editor.DenyBeforeUnloadHandler();
				}
			},

			getTaskData: function()
			{
				return this.option('data').TASK;
			},
			getTaskActions: function()
			{
				return this.getTaskData().ACTION;
			},

			initProjectDependence: function()
			{
				var inst = BX.Tasks.Util.Dispatcher.get('projectdependence-'+this.id());

				inst.assignCalendar(this.getCalendar());
				inst.option('task', {data: this.getTaskData()});
				inst.load(
					this.getTaskData().SE_PROJECTDEPENDENCE,
					this.getTaskActions().SE_PROJECTDEPENDENCE
				);
			},

			initProjectPlan: function()
			{
				this.instances.projectPlan = new BX.Tasks.Shared.Form.ProjectPlan({
					scope: this.control('date-plan-manager'),
					parent: this,
					matchWorkTime: this.getTaskData().MATCH_WORK_TIME == 'Y'
				});
				this.instances.projectPlan.bindEvent('change-deadline', BX.delegate(function(stamp){
					// fire event on reminder block, if any
					BX.Tasks.Util.Dispatcher.fireEvent(
						'reminder-'+this.id(),
						'setTaskDeadLine',
						[stamp]
					);
				}, this));
			},

			initParentTask: function()
			{
				var ctrlName = 'parenttask';
				var parent = new BX.Tasks.Component.Task.TaskItemSet({
					id: ctrlName+'-'+this.id(),
					max: 1,
					selectorCode: ctrlName,
					itemFx: 'horizontal',
					itemFxHoverDelete: true,
					parent: this
				});
				parent.bindEvent('change', BX.delegate(function(items){

					this.control('parent-input').value = items.length > 0 ? parseInt(items[0]) : '';

				}, this));
				if(this.getTaskData().SE_PARENTTASK)
				{
					parent.load([this.getTaskData().SE_PARENTTASK]);
				}

				this.instances[ctrlName] = parent;
			},

			initRelatedTask: function()
			{
				this.instances['dependson'] = new BX.Tasks.Component.Task.TaskItemSet({
					id: 'dependson-'+this.id(),
					selectorCode: 'dependson',
					itemFx: 'horizontal',
					itemFxHoverDelete: true,
					parent: this
				});

				if(typeof this.getTaskData().SE_RELATEDTASK != 'undefined')
				{
					this.instances['dependson'].load(this.getTaskData().SE_RELATEDTASK);
				}
			},

			initReminder: function()
			{
				var reminder = BX.Tasks.Util.Dispatcher.get('reminder-'+this.id());
				if(reminder !== null)
				{
					reminder.load(
						this.getTaskData().SE_REMINDER,
						this.getTaskActions().SE_REMINDER
					);
					reminder.setTaskId(this.getTaskData().ID);
					reminder.setTaskDeadLine(this.getTaskData().DEADLINE);
				}
			},

			initState: function()
			{
				this.vars.state = BX.clone(this.option('state'));
				this.redrawState();
			},

			onPinFooterClick: function()
			{
				var pinned = !this.vars.state.FLAGS.FORM_FOOTER_PIN;
				var footer = this.control('footer');

				if(footer)
				{
					BX[pinned ? 'addClass' : 'removeClass'](footer, 'pinned');
				}
				this.setState('FLAGS', 'FORM_FOOTER_PIN', false, pinned);
			},

			onPriorityChange: function(node)
			{
				var input = this.control('priority');
				if(BX.type.isElementNode(input))
				{
					input.value = node.checked ? this.PRIORITY_HIGH : this.PRIORITY_AVERAGE;
				}
			},

			onForumSubmit: function()
			{
				var csrf = this.control('csrf');
				if(csrf)
				{
					csrf.value = BX.bitrix_sessid(); // prevent sending expired csrf
				}

				this.vars.submitting = true;
			},

			onSubmitClick: function(node, e)
			{
				if(this.vars.submitting)
				{
					BX.PreventDefault(e);
					return;
				}

				BX.addClass(node, 'webform-small-button-wait');
				this.vars.submitting = true;
			},

			submit: function()
			{
				this.control('form').submit();
			},

			onFormKeyDown: function(e)
			{
				e = e || window.event;

				var prevent = false;
				if(BX.Tasks.Util.isEnter(e))
				{
					if(e.ctrlKey || e.metaKey)
					{
						this.submit();
						prevent = true;
					}
				}

				if(prevent)
				{
					BX.PreventDefault(e);
				}
			},

			onChooseBlock: function(node)
			{
				var chosenContainer = this.control('chosen-blocks');
				var unChosenContainer = this.control('unchosen-blocks');

				if(!BX.type.isElementNode(chosenContainer) || !BX.type.isElementNode(unChosenContainer))
				{
					return;
				}

				var target = BX.data(node, 'target');
				if(typeof target != 'undefined' && BX.type.isNotEmptyString(target))
				{
					var node = this.control(target);
					var blockName = BX.data(node, 'block-name');

					if(BX.type.isNotEmptyString(blockName) && BX.type.isElementNode(node))
					{
						var stateBlock = this.vars.state['BLOCKS'][blockName];

						if(typeof stateBlock.C != 'undefined')
						{
							var toPin = !stateBlock.C;

							// find block exact place
							var to = this.control(target+'-place', toPin ? chosenContainer : unChosenContainer);
							var from = this.control(target+'-place', toPin ? unChosenContainer : chosenContainer);
							if(to) // if there is an exact place, relocate to it
							{
								BX.Tasks.Util.fadeSlideToggleByClass(from, 200, function(){
									BX.addClass(to, 'invisible');
									BX.append(node, to);
									BX.Tasks.Util.fadeSlideToggleByClass(to, 200);

									BX.removeClass(from, 'invisible');
								});
							}
							else // static block, then just pin it
							{
								BX.toggleClass(node, 'pinned');
							}

							// update state
							this.setState('BLOCKS', blockName, 'C', !stateBlock.C);
						}
					}
				}
			},

			onToggleAdditionalBlock: function(node)
			{
				var opened = BX.hasClass(node, 'opened');
				BX.toggleClass(node, 'opened');

				this.toggleBlock('unchosen-blocks');
			},

			onToggleBlock: function(node)
			{
				var target = BX.data(node, 'target');

				if(typeof target != 'undefined' && BX.type.isNotEmptyString(target))
				{
					var way = this.toggleBlock(target);

					if(way && target == 'checklist') // pre-open checklist add form on empty checklist
					{
						BX.Tasks.Util.Dispatcher.find(this.id()+'-checklist').then(function(ctrl){

							if(!ctrl.count())
							{
								ctrl.openForm();
							}

						}.bind(this));
					}
				}
			},

			toggleBlock: function(target, duration)
			{
				return BX.Tasks.Util.fadeSlideToggleByClass(this.control(target), duration || 100);
			},

			toggleOption: function(code, way)
			{
				var ctrl = this.getOptionNode(code);
				if(ctrl)
				{
					ctrl.checked = !!way;
					this.onToggleFlag(ctrl);
				}
			},

			switchOption: function(code, way)
			{
				var ctrl = this.getOptionNode(code);
				if(ctrl)
				{
					ctrl.disabled = !!way;
				}
			},

			getOptionNode: function(code)
			{
				code = code.toLowerCase().replace(/_/g, '-');

				return this.control('flag-'+code);
			},

			onToggleFlag: function(node)
			{
				var target = BX.data(node, 'target');
				if(typeof target != 'undefined' && BX.type.isNotEmptyString(target))
				{
					var flagNode = this.control(target);
					var flagName = BX.data(node, 'flag-name');

					if(BX.type.isElementNode(flagNode))
					{
						flagNode.value = node.checked ? 'Y' : 'N';
					}

					this.processToggleFlag(flagName, flagNode.value == 'Y');
				}
			},

			processToggleFlag: function(name, value)
			{
				if(name == 'REPLICATE')
				{
					var panel = this.control('replication-panel');

					if (value) // checkbox was just checked
					{
						// make invisible
						BX.Tasks.Util.fadeSlideToggleByClass(panel);
					}
					else // checkbox was just UNchecked
					{
						BX.Tasks.Util.fadeSlideToggleByClass(
							panel
						);
					}

					this.toggleOption('SAVE_AS_TEMPLATE', value);
					this.switchOption('SAVE_AS_TEMPLATE', value);
				}
				if(name == 'TASK_PARAM_1')
				{
					this.toggleDateParameters(value);
				}

				this.setState('FLAGS', name, false, value);
			},

			toggleDateParameters: function(flag)
			{
				// date inputs
				BX[flag ? 'addClass' : 'removeClass'](this.control('date-plan'), 'disabled-block');

				// match work time
				BX.Tasks.Util.Dispatcher.find('options-'+this.option('id')).then(function(ctrl){

					ctrl.switchOption('MATCH_WORK_TIME', !flag);

				}.bind(this));
			},

			onOriginatorChange: function()
			{
				BX.Tasks.Util.Dispatcher.find(this.option('id')+'-originator').then(function(ctrl){

					if(ctrl.count() && ctrl.value())
					{
						var userMatch = 'U'+this.getUser().DATA.ID.toString() == ctrl.value()[0].toString();

						BX.Tasks.Util.Dispatcher.find('options-'+this.option('id')).then(function(optCtrl){

							optCtrl.switchOption('ADD_TO_TIMEMAN', userMatch);

						}.bind(this));
					}

				}.bind(this));
			},

			onResponsibleChange: function()
			{
				BX.Tasks.Util.Dispatcher.find(this.option('id')+'-responsible').then(function(ctrl){

					if(ctrl.count() > 1)
					{
						BX.Tasks.Util.hintManager.showDisposable(
							ctrl.scope(),
							BX.message('TASKS_TASK_COMPONENT_TEMPLATE_MULTIPLE_RESPONSIBLE_NOTICE'),
							'TASK_EDIT_MULTIPLE_RESPONSIBLES'
						);
					}
					else
					{
						BX.Tasks.Util.hintManager.hide('TASK_EDIT_MULTIPLE_RESPONSIBLES');
					}

				}.bind(this));
			},

			onCancelButtonClick: function(e)
			{
				if(this.option('cancelActionIsEvent')) // let iframe popup close window, dont go to url
				{
					BX.Tasks.Util.fireGlobalTaskEvent('NOOP', {}, {STAY_AT_PAGE: false});
					BX.PreventDefault(e);
				}
			},

			onWorktimeChange: function(node)
			{
				this.instances.projectPlan.setMatchWorkTime(node.checked);
			},

			getCalendar: function()
			{
				if(this.instances.calendar == false)
				{
					this.instances.calendar = new BX.Tasks.Calendar(BX.Tasks.Calendar.adaptSettings(this.option('auxData').COMPANY_WORKTIME));
				}

				return this.instances.calendar;
			},

			getState: function(type, name, actionName)
			{
				if (type == 'BLOCKS') {
					return this.vars.state[type][name][actionName];
				}
				if (type == 'FLAGS') {
					return this.vars.state[type][name];
				}
			},

			setState: function(type, name, actionName, value)
			{
				if(!BX.type.isNotEmptyString(name))
				{
					return;
				}

				if(type == 'FLAGS')
				{
					var allowed = {
						'ALLOW_TIME_TRACKING': true,
						'TASK_CONTROL': true,
						'ALLOW_CHANGE_DEADLINE': true,
						'MATCH_WORK_TIME': true,
						'FORM_FOOTER_PIN': true
					};

					if(!(name in allowed))
					{
						return;
					}
				}

				if(typeof this.vars.state[type] == 'undefined')
				{
					this.vars.state[type] = {};
				}
				if(typeof this.vars.state[type][name] == 'undefined')
				{
					this.vars.state[type][name] = {};
				}

				if(type == 'BLOCKS')
				{
					this.vars.state[type][name][actionName] = value;
				}
				if(type == 'FLAGS')
				{
					this.vars.state[type][name] = value;
				}

				this.submitState();
				this.redrawState(); // for submitting with form
			},

			submitState: function()
			{
				if(!this.instances.query)
				{
					this.instances.query = new BX.Tasks.Util.Query({
						url : this.option('template').COMPONENTURL,
						autoExec: true,
						autoExecDelay: 1500
					});
				}

				var st = BX.clone(this.vars.state);

				// send FORM_FOOTER_PIN, but dont send other flags in this manner, it looks pretty awkward
				// other flags will be saved when form actually submitted
				var fp = st.FLAGS.FORM_FOOTER_PIN;
				delete(st.FLAGS);
				st.FLAGS = {
					FORM_FOOTER_PIN: fp
				};

				this.instances.query.add('this.setstate', {state: st});
			},

			redrawState: function()
			{
				var container = this.control('state');
				if(BX.type.isElementNode(container))
				{
					var html = '';

					if(typeof this.vars.state['BLOCKS'] != 'undefined')
					{
						for(var bName in this.vars.state['BLOCKS'])
						{
							var opened = this.vars.state['BLOCKS'][bName]['O'];
							var chosen = this.vars.state['BLOCKS'][bName]['C'];

							if(typeof opened != 'undefined')
							{
								html += this.getHTMLByTemplate('state-block', {
									NAME: bName,
									TYPE: 'O',
									VALUE: opened === true || opened === 'true' ? '1' : '0'
								});
							}
							if(typeof chosen != 'undefined')
							{
								html += this.getHTMLByTemplate('state-block', {
									NAME: bName,
									TYPE: 'C',
									VALUE: chosen === true || chosen === 'true' ? '1' : '0'
								});
							}
						}
					}

					if(typeof this.vars.state['FLAGS'] != 'undefined')
					{
						for(var fName in this.vars.state['FLAGS'])
						{
							var checked = this.vars.state['FLAGS'][fName];

							html += this.getHTMLByTemplate('state-flag', {
								NAME: fName,
								VALUE: checked === true || checked === 'true' ? '1' : '0'
							});
						}
					}

					container.innerHTML = html;
				}
			}
		}
	});

	BX.Tasks.Component.Task.UserItemSet = BX.Tasks.UserItemSet.extend({
		methods: {

			onSearchBlurred: function()
			{
				if(this.callMethod(BX.Tasks.UserItemSet, 'onSearchBlurred'))
				{
					this.restoreKept();
				}
			},

			restoreKept: function()
			{
				if(this.vars.toDelete)
				{
					this.addItem(this.vars.toDelete, {checkRestrictions: false});
					this.vars.toDelete = false;
				}
			},

			onSelectorItemSelected: function(data)
			{
				var value = this.extractItemValue(data);

				if(!this.hasItem(value))
				{
					var max = this.option('max');

					this.addItem(data);
					this.vars.toDelete = false;

					if(max == 1)
					{
						this.instances.selector.close();
						this.onSearchBlurred();
					}
				}

				this.resetInput();
			},

			openAddForm: function(node, e, keepValue)
			{
				var min = this.option('min');
				var max = this.option('max');

				if(keepValue || (max == 1 && (min == 0 || min == 1)))
				{
					var first = this.getItemFirst();
					if(first)
					{
						this.vars.toDelete = first.data();
						this.callMethod(BX.Tasks.UserItemSet, 'deleteItem', [first.value(), {checkRestrictions: false}]);
					}
				}

				this.callMethod(BX.Tasks.UserItemSet, 'openAddForm');
			},

			deleteItem: function(value)
			{
				if(!this.callMethod(BX.Tasks.UserItemSet, 'deleteItem', arguments))
				{
					this.openAddForm(false, false, true);
					return false;
				}

				return true;
			}
		}
	});

	BX.Tasks.Component.Task.GroupItemSet = BX.Tasks.Component.Task.UserItemSet.extend({
		sys: {
			code: 'group-item-set'
		},
		methods: {
			extractItemDisplay: function(data)
			{
				return data.NAME || BX.util.htmlspecialcharsback(data.nameFormatted); // socnetlogdest returns escaped name, we want unescaped
			},
			getNSMode: function()
			{
				return 'group';
			}
		}
	});

	// legacy popup - task selector
	BX.Tasks.Component.Task.TaskItemSet = BX.Tasks.PopupItemSet.extend({
		sys: {
			code: 'task-item-set'
		},
		options: {
			itemFx: 'horizontal'
		},
		methods: {
			construct: function()
			{
				this.callConstruct(BX.Tasks.PopupItemSet);

				this.instances.selector = window['O_'+this.option('selectorCode')];
			},
			extractItemDisplay: function(data)
			{
				if(typeof data.DISPLAY != 'undefined')
				{
					return data.DISPLAY;
				}

				if(typeof data.name != 'undefined')
				{
					return data.name;
				}

				return data.TITLE;
			},
			extractItemValue: function(data)
			{
				return (typeof data.ID == 'undefined' ? data.id : data.ID);
			},
			bindFormEvents: function()
			{
				if(typeof this.instances.selector != 'undefined' && this.instances.selector != null && this.instances.selector != false)
				{
					BX.addCustomEvent(this.instances.selector, 'on-change', BX.delegate(this.itemsChanged, this));

					if(typeof this.instances.window != 'undefined')
					{
						var selectorCtrl = this.instances.selector;
						BX.addCustomEvent(this.instances.window, "onAfterPopupShow", function(){
							setTimeout(function(){
								selectorCtrl.searchInput.focus();
							}, 100);
						});
					}
				}
			},
			deleteItem: function(value, parameters)
			{
				// todo: in some cases we got numeric in value, in other cases - object. re-check it and unify
				var taskId = (BX.type.isNumber(value) || BX.type.isString(value)) ? value : value.value();

				if(this.callMethod(BX.Tasks.PopupItemSet, 'deleteItem', arguments))
				{
					this.instances.selector.unselect(taskId);
				}
			}
		}
	});

}).call(this);