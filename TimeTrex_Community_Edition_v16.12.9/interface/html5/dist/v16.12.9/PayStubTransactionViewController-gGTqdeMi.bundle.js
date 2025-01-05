import{_ as m,j as n}from"./vendor-tTApdY0Y.bundle.js";class y extends BaseViewController{constructor(e={}){m.defaults(e,{el:"#pay_stub_transaction_view_container",status_array:null,currency_array:null,user_status_array:null,user_group_array:null,type_array:null,user_api:null,user_group_api:null,company_api:null,pay_stub_entry_api:null,include_entries:!0}),super(e)}init(){this.edit_view_tpl="PayStubTransactionEditView.html",this.permission_id="pay_stub",this.viewId="PayStubTransaction",this.script_name="PayStubTransactionView",this.table_name_key="pay_stub_transaction",this.context_menu_name=n.i18n._("Pay Stub Transaction"),this.navigation_label=n.i18n._("Pay Stub Transactions"),this.api=TTAPI.APIPayStubTransaction,this.currency_api=TTAPI.APICurrency,this.remittance_source_account_api=TTAPI.APIRemittanceSourceAccount,this.remittance_destination_account_api=TTAPI.APIRemittanceDestinationAccount,this.user_api=TTAPI.APIUser,this.pay_stub_entry_api=TTAPI.APIPayStubEntry,this.user_group_api=TTAPI.APIUserGroup,this.company_api=TTAPI.APICompany,this.pay_period_api=TTAPI.APIPayPeriod,this.initPermission(),this.render(),this.buildContextMenu(),this.initData()}initPermission(){super.initPermission(),PermissionManager.validate(this.permission_id,"view")||PermissionManager.validate(this.permission_id,"view_child")?this.show_search_tab=!0:this.show_search_tab=!1}initOptions(e){this.initDropDownOption("status","transaction_status_id")}getCustomContextMenuModel(){var e={exclude:["default"],include:["view","edit","mass_edit","save","save_and_continue","save_and_next","cancel",{label:n.i18n._("Jump To"),id:"jump_to_header",menu_align:"right",action_group:"jump_to",action_group_header:!0,permission_result:!1},{label:n.i18n._("TimeSheet"),id:"timesheet",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:n.i18n._("Schedule"),id:"schedule",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:n.i18n._("Pay Stubs"),id:"pay_stub",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:n.i18n._("Pay Stub Amendments"),id:"pay_stub_amendment",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:n.i18n._("Edit Employee"),id:"edit_employee",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:n.i18n._("Edit Pay Period"),id:"edit_pay_period",menu_align:"right",action_group:"jump_to",group:"navigation"}]};return e}setCustomDefaultMenuIcon(e,t,i){switch(e){case"pay_stub_transaction":case"pay_stub":this.setDefaultMenuViewIcon(t,i);break;case"timesheet":this.setDefaultMenuViewIcon(t,i,"punch");break;case"schedule":this.setDefaultMenuViewIcon(t,i,"schedule");break;case"pay_stub_amendment":this.setDefaultMenuViewIcon(t,i,"pay_stub_amendment");break;case"edit_employee":this.setDefaultMenuEditEmployeeIcon(t,i,"user");break;case"edit_pay_period":this.setDefaultMenuEditPayPeriodIcon(t,i);break}}setDefaultMenuEditPayPeriodIcon(e,t,i){this.editPermissionValidate("pay_period_schedule")||ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t===1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuEditEmployeeIcon(e,t){this.editChildPermissionValidate("user")||ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t===1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuViewIcon(e,t,i){i==="punch"||i==="schedule"||i==="pay_stub_amendment"?super.setDefaultMenuViewIcon(e,t,i):((!this.viewPermissionValidate(i)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t>0&&this.viewOwnerOrChildPermissionValidate()?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1))}setCustomEditMenuIcon(e,t){switch(e){case"import_icon":this.setEditMenuImportIcon(t);break;case"timesheet":this.setEditMenuViewIcon(t,"punch");break;case"schedule":this.setEditMenuViewIcon(t,"schedule");break;case"pay_stub_transaction":this.setEditMenuViewIcon(t,"pay_stub_transaction");break;case"pay_stub_amendment":this.setEditMenuViewIcon(t,"pay_stub_amendment");break;case"edit_employee":this.setEditMenuViewIcon(t,"user");break;case"edit_pay_period":this.setEditMenuViewIcon(t,"pay_period_schedule");break}}setDefaultMenuGeneratePayStubIcon(e,t,i){t>0?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setCurrentEditRecordData(){this.include_entries=!0;for(var e in this.current_edit_record){var t=this.edit_view_ui_dic[e];if(Global.isSet(t))switch(e){default:t.setValue(this.current_edit_record[e]);break}}this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}setEditViewDataDone(){super.setEditViewDataDone(),this.edit_view_ui_dic.user_id.setEnabled(!1),this.edit_view_ui_dic.remittance_source_account_id.setEnabled(!1),this.edit_view_ui_dic.remittance_destination_account_id.setEnabled(!1),this.edit_view_ui_dic.currency_id.setEnabled(!1),this.edit_view_ui_dic.amount.setEnabled(!1),this.edit_view_ui_dic.confirmation_number.setEnabled(!1)}onSaveClick(e){this.is_mass_editing&&(this.include_entries=!1),super.onSaveClick(e)}onSaveAndContinue(e){var t=this;Global.isSet(e)||(e=!1),this.is_changed=!1,this.is_add=!1,LocalCacheData.current_doing_context_action="save_and_continue";var i=this.current_edit_record;i=this.uniformVariable(i),this.api["set"+this.api.key_name](i,!1,e,{onResult:function(a){t.onSaveAndContinueResult(a)}})}onSaveAndContinueResult(e){var t=this;if(e&&e.isValid()){var i=e.getResult();i===!0?t.refresh_id=t.current_edit_record.id:TTUUID.isUUID(i)&&i!=TTUUID.zero_id&&i!=TTUUID.not_exist_id&&(t.refresh_id=i),t.search(!1),t.onSaveAndContinueDone(e)}else t.setErrorTips(e),t.setErrorMenu()}getFilterColumnsFromDisplayColumns(){var e={};e.pay_stub_transaction_date=!0,e.pay_stub_start_date=!0,e.pay_stub_end_date=!0,e.id=!0,e.status_id=!0,e.is_owner=!0,e.user_id=!0,e.pay_stub_id=!0,e.pay_period_id=!0,e.pay_stub_run_id=!0,e.currency_id=!0,e.remittance_source_account_type_id=!0;var t=[];if(this.grid&&(t=this.grid.getGridParam("colModel")),t)for(var i=0;i<t.length;i++)e[t[i].name]=!0;return e}onFormItemChange(e,t){this.setIsChanged(e),this.setMassEditingFieldsWhenFormChange(e);var i=e.getField(),a=e.getValue();this.current_edit_record[i]=a,t||this.validate()}validate(){var e=this,t={};if(this.is_mass_editing){for(var i in this.edit_view_ui_dic)if(this.edit_view_ui_dic.hasOwnProperty(i)){var a=this.edit_view_ui_dic[i];Global.isSet(a.isChecked)&&a.isChecked()&&a.getEnabled()&&(t[i]=a.getValue())}}else t=this.current_edit_record;t=this.uniformVariable(t),this.api["validate"+this.api.key_name](t,{onResult:function(s){e.validateResult(s)}})}buildEditViewUI(){super.buildEditViewUI();var e=this,t={tab_pay_stub_transaction:{label:n.i18n._("Pay Stub Transaction")},tab_audit:!0};this.setTabModel(t),this.navigation.AComboBox({api_class:TTAPI.APIPayStub,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_pay_stub",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i=this.edit_view_tab.find("#tab_pay_stub_transaction"),a=i.find(".first-column"),s;this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(a),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!1,layout_name:"global_user",show_search_inputs:!1,set_empty:!1,field:"user_id"}),this.addEditFieldToColumn(n.i18n._("Employee"),s,a,"",null,!0),s=Global.loadWidgetByName(FormItemType.COMBO_BOX),s.TComboBox({field:"status_id",set_empty:!1}),s.setSourceData(e.status_array),this.addEditFieldToColumn(n.i18n._("Status"),s,a),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.APIRemittanceSourceAccount,allow_multiple_selection:!1,layout_name:"global_remittance_source_account",show_search_inputs:!1,set_empty:!1,field:"remittance_source_account_id"}),this.addEditFieldToColumn(n.i18n._("Source Account"),s,a,"",null,!0),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.APIRemittanceDestinationAccount,allow_multiple_selection:!1,layout_name:"global_remittance_destination_account",show_search_inputs:!1,set_empty:!1,field:"remittance_destination_account_id"}),this.addEditFieldToColumn(n.i18n._("Destination Account"),s,a,"",null,!0),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({field:"currency_id",set_empty:!1,layout_name:"global_currency",allow_multiple_selection:!1,show_search_inputs:!1,api_class:TTAPI.APICurrency}),this.addEditFieldToColumn(n.i18n._("Currency"),s,a),s=Global.loadWidgetByName(FormItemType.TEXT_INPUT),s.TTextInput({field:"amount",width:300}),this.addEditFieldToColumn(n.i18n._("Amount"),s,a),s=Global.loadWidgetByName(FormItemType.DATE_PICKER),s.TDatePicker({field:"transaction_date"}),this.addEditFieldToColumn(n.i18n._("Transaction Date"),s,a),s=Global.loadWidgetByName(FormItemType.TEXT_INPUT),s.TTextInput({field:"confirmation_number",width:300}),this.addEditFieldToColumn(n.i18n._("Confirmation #"),s,a),s=Global.loadWidgetByName(FormItemType.TEXT_AREA),s.TTextArea({field:"note",width:300}),this.addEditFieldToColumn(n.i18n._("Note"),s,a)}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:n.i18n._("Status"),in_column:1,field:"transaction_status_id",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Source Account"),in_column:2,field:"remittance_source_account_id",layout_name:"global_remittance_source_account",api_class:TTAPI.APIRemittanceSourceAccount,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Pay Period"),in_column:1,field:"pay_period_id",layout_name:"global_Pay_period",api_class:TTAPI.APIPayPeriod,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Employee"),in_column:1,field:"user_id",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_user",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Currency"),in_column:2,field:"currency_id",api_class:TTAPI.APICurrency,multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_currency",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Transaction Date"),in_column:2,field:"transaction_date",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.DATE_PICKER})]}onCustomContextClick(e){switch(e){case"timesheet":case"schedule":case"pay_stub_amendment":case"edit_employee":case"generate_pay_stub":case"pay_stub_transaction":case"edit_pay_period":case"pay_stub":this.onNavigationClick(e);break}}onViewClick(e,t){this.onNavigationClick("view")}onNavigationClick(e){var t=this,i,a={},s=[],o=[],u,_=[],d=[];switch(t.edit_view&&t.current_edit_record.id?(s.push(t.current_edit_record.id),o.push(t.current_edit_record.user_id),_.push(t.current_edit_record.pay_period_id),d.push(t.current_edit_record.pay_stub_id),u=t.current_edit_record.pay_stub_start_date):(i=this.getGridSelectIdArray(),n.each(i,function(b,l){var r=t.getRecordFromGridById(l);s.push(r.id),o.push(r.user_id),_.push(r.pay_period_id),d.push(r.pay_stub_id),u=r.pay_stub_start_date})),e){case"pay_stub":a.filter_data={},a.filter_data.id={value:d},a.select_date=u,Global.addViewTab(this.viewId,n.i18n._("Pay Stub Transactions"),window.location.href),IndexViewController.goToView("PayStub",a);break;case"edit_employee":o.length>0&&IndexViewController.openEditView(this,"Employee",o[0]);break;case"edit_pay_period":_.length>0&&IndexViewController.openEditView(this,"PayPeriods",_[0]);break;case"timesheet":o.length>0&&(a.user_id=o[0],a.base_date=u,Global.addViewTab(t.viewId,n.i18n._("Pay Stub Transactions"),window.location.href),IndexViewController.goToView("TimeSheet",a));break;case"schedule":a.filter_data={};var h={value:o};a.filter_data.include_user_ids=h,a.select_date=u,Global.addViewTab(this.viewId,n.i18n._("Pay Stub Transactions"),window.location.href),IndexViewController.goToView("Schedule",a);break;case"pay_stub_amendment":a.filter_data={},a.filter_data.user_id=o[0],a.filter_data.pay_period_id=_[0],Global.addViewTab(this.viewId,n.i18n._("Pay Stub Transactions"),window.location.href),IndexViewController.goToView("PayStubAmendment",a);break;case"view":this.setCurrentEditViewState("view"),this.openEditView(),a.filter_data={};var i=this.getGridSelectIdArray(),p=i[0];a.filter_data.id=[p],this.api["get"+this.api.key_name](a,{onResult:function(l){var r=l.getResult();if(r||(r=[]),r=r[0],!r){TAlertManager.showAlert(n.i18n._("Record does not exist")),t.onCancelClick();return}t.sub_view_mode&&t.parent_key&&(r[t.parent_key]=t.parent_value),t.current_edit_record=r,t.initEditView()}});break;case"pay_stub_transaction":IndexViewController.openEditView(this,"PayStubTransaction",o[0]);break}}}y.loadView=function(){Global.loadViewSource("PayStubTransaction","PayStubTransactionView.html",function(c){var e={},t=m.template(c,e);Global.contentContainer().html(t)})};export{y as PayStubTransactionViewController};
//# sourceMappingURL=PayStubTransactionViewController-gGTqdeMi.bundle.js.map
