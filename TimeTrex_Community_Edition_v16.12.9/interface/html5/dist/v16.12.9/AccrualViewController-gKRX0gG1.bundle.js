import{_ as o,j as r}from"./vendor-tTApdY0Y.bundle.js";class c extends BaseViewController{constructor(e={}){o.defaults(e,{el:"#accrual_view_container",type_array:null,user_group_api:null,user_group_array:null,user_type_array:null,system_type_array:null,delete_type_array:null,date_api:null,edit_enabled:!1,delete_enabled:!1,is_trigger_add:!1,sub_view_grid_data:null,hide_search_field:!1,api_accrual_balance:null}),super(e)}init(e){this.edit_view_tpl="AccrualEditView.html",this.permission_id="accrual",this.viewId="Accrual",this.script_name="AccrualView",this.table_name_key="accrual",this.context_menu_name=r.i18n._("Accruals"),this.navigation_label=r.i18n._("Accrual"),this.api=TTAPI.APIAccrual,this.api_accrual_balance=TTAPI.APIAccrualBalance,this.initPermission(),this.render(),this.sub_view_mode?this.buildContextMenu(!0):this.buildContextMenu(),this.sub_view_mode||this.initData(),TTPromise.resolve("AccrualViewController","init")}initPermission(){super.initPermission(),PermissionManager.validate(this.permission_id,"view")||PermissionManager.validate(this.permission_id,"view_child")?this.hide_search_field=!1:this.hide_search_field=!0}initOptions(){var e=this;this.initDropDownOption("user_type",null,null,function(t){var i=t.getResult();e.user_type_array=i}),this.initDropDownOption("delete_type",null,null,function(t){var i=t.getResult();e.delete_type_array=i}),this.initDropDownOption("type",null,null,function(t){var i=t.getResult();e.system_type_array=i,!e.sub_view_mode&&e.basic_search_field_ui_dic.type_id&&e.basic_search_field_ui_dic.type_id.setSourceData(Global.buildRecordArray(i))}),TTAPI.APIUserGroup.getUserGroup("",!1,!1,{onResult:function(t){t=t.getResult(),t=Global.buildTreeRecord(t),!e.sub_view_mode&&e.basic_search_field_ui_dic.group_id&&e.basic_search_field_ui_dic.group_id.setSourceData(t),e.user_group_array=t}})}buildEditViewUI(){super.buildEditViewUI();var e=this,t={tab_accrual:{label:r.i18n._("Accrual")},tab_audit:!0};this.setTabModel(t),this.navigation.AComboBox({api_class:TTAPI.APIAccrual,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_accrual_accrual",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var i=this.edit_view_tab.find("#tab_accrual"),l=i.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(l);var a,n;if(this.sub_view_mode&&(this.parent_edit_record===void 0||o.isEmpty(this.parent_edit_record)===!1))a=Global.loadWidgetByName(FormItemType.TEXT),a.TText({field:"full_name"}),this.addEditFieldToColumn(r.i18n._("Employee"),a,l,"");else{a=Global.loadWidgetByName(FormItemType.AWESOME_BOX),a.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!0,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"user_id"});var _={};_.permission_section="accrual",a.setDefaultArgs(_),this.addEditFieldToColumn(r.i18n._("Employee"),a,l,"")}this.sub_view_mode&&(this.parent_edit_record===void 0||o.isEmpty(this.parent_edit_record)===!1)?(a=Global.loadWidgetByName(FormItemType.TEXT),a.TText({field:"accrual_policy_account"}),this.addEditFieldToColumn(r.i18n._("Accrual Account"),a,l)):(a=Global.loadWidgetByName(FormItemType.AWESOME_BOX),a.AComboBox({api_class:TTAPI.APIAccrualPolicyAccount,allow_multiple_selection:!1,layout_name:"global_accrual_policy_account",show_search_inputs:!0,set_empty:!0,field:"accrual_policy_account_id"}),this.addEditFieldToColumn(r.i18n._("Accrual Account"),a,l)),a=Global.loadWidgetByName(FormItemType.COMBO_BOX),a.TComboBox({field:"type_id"}),a.setSourceData(e.user_type_array),this.addEditFieldToColumn(r.i18n._("Type"),a,l),a=Global.loadWidgetByName(FormItemType.TEXT_INPUT),a.TTextInput({field:"amount",width:120,mode:"time_unit"});var n=r("<div class='widget-h-box'></div>"),u=r("<input id='release-balance-button' class='t-button' style='margin-left: 5px' type='button' value='"+r.i18n._("Available Balance")+"'>");u.click(function(){e.getAvailableBalance()}),this.is_viewing&&u.css("display","none"),n.append(a),n.append(u),this.addEditFieldToColumn(r.i18n._("Amount"),a,l,"",n),a=Global.loadWidgetByName(FormItemType.DATE_PICKER),a.TDatePicker({field:"time_stamp"}),this.addEditFieldToColumn(r.i18n._("Date"),a,l,"",null),a=Global.loadWidgetByName(FormItemType.TEXT_AREA),a.TTextArea({field:"note"}),this.addEditFieldToColumn(r.i18n._("Note"),a,l,"",null,null,!0)}onFormItemChange(e,t){this.setIsChanged(e),this.setMassEditingFieldsWhenFormChange(e);var i=e.getField(),l=e.getValue();if(this.current_edit_record){switch(this.current_edit_record[i]=l,i){case"amount":this.current_edit_record[i]=Global.parseTimeUnit(l);break}t||this.validate()}}buildSearchFields(){super.buildSearchFields();var e={};e.permission_section="accrual",this.search_fields=[new SearchField({label:r.i18n._("Employee"),field:"user_id",in_column:1,default_args:e,layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Accrual Account"),field:"accrual_policy_account_id",in_column:1,layout_name:"global_accrual_policy_account",api_class:TTAPI.APIAccrualPolicyAccount,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Type"),in_column:1,field:"type_id",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Group"),in_column:1,multiple:!0,field:"group_id",layout_name:"global_tree_column",tree_mode:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Default Branch"),in_column:2,field:"default_branch_id",layout_name:"global_branch",api_class:TTAPI.APIBranch,multiple:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Default Department"),in_column:2,field:"default_department_id",layout_name:"global_department",api_class:TTAPI.APIDepartment,multiple:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:r.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!this.hide_search_field,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}setEditViewData(){if(this.is_viewing?this.edit_view_ui_dic.type_id.setSourceData(this.system_type_array):this.edit_view_ui_dic.type_id.setSourceData(this.user_type_array),super.setEditViewData(),!this.sub_view_mode){var e=this.edit_view_ui_dic.user_id;(!this.current_edit_record||!this.current_edit_record.id)&&!this.is_mass_editing?e.setAllowMultipleSelection(!0):e.setAllowMultipleSelection(!1)}}uniformVariable(e){var t=[];if(r.type(e.user_id)==="array"){if(e.user_id.length===0)return e.user_id=!1,e;for(var i in e.user_id){var l=Global.clone(e);l.user_id=e.user_id[i],t.push(l)}}return t.length>0&&(e=t),e}setCurrentEditRecordData(){for(var e in this.current_edit_record){var t=this.edit_view_ui_dic[e];if(Global.isSet(t))switch(e){case"full_name":this.current_edit_record.first_name&&t.setValue(this.current_edit_record.first_name+" "+this.current_edit_record.last_name);break;case"amount":var i=Global.getTimeUnit(this.current_edit_record[e]);t.setValue(i),this.is_viewing||r("#release-balance-button").css("display","");break;default:t.setValue(this.current_edit_record[e]);break}}this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}getFilterColumnsFromDisplayColumns(){var e={};return e.type_id=!0,this.sub_view_mode&&(e.accrual_policy_account=!0,e.accrual_policy_account_id=!0,e.user_id=!0),this._getFilterColumnsFromDisplayColumns(e,!0)}onGridSelectAll(){this.edit_enabled=this.editEnabled(),this.delete_enabled=this.deleteEnabled(),this.setDefaultMenu()}deleteEnabled(){var e=this.getGridSelectIdArray();if(e.length>0)for(var t=e.length-1;t>=0;t--){var i=this.getRecordFromGridById(e[t]);if(Global.isSet(this.delete_type_array[i.type_id]))return!0}return!1}editEnabled(){var e=this.getGridSelectIdArray();if(e.length>0)for(var t=e.length-1;t>=0;t--){var i=this.getRecordFromGridById(e[t]);if(Global.isSet(this.user_type_array[i.type_id]))return!0}return!1}onGridSelectRow(){var e=this.getGridSelectIdArray(),t=e.length;t>0&&(this.getRecordFromGridById(e[0]),this.edit_enabled=this.editEnabled(),this.delete_enabled=this.deleteEnabled()),this.setDefaultMenu()}setDefaultMenuEditIcon(e,t,i){(!this.editPermissionValidate(i)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t===1&&this.editOwnerOrChildPermissionValidate(i)?this.edit_enabled?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1):(ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t!==0&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1))}setDefaultMenuMassEditIcon(e,t,i){(!this.editPermissionValidate(i)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t>1?this.edit_enabled?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1):(ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1))}setDefaultMenuDeleteIcon(e,t,i){(!this.deletePermissionValidate(i)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),t>=1&&this.deleteOwnerOrChildPermissionValidate(i)?this.delete_enabled?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuEditIcon(e,t){(!this.editPermissionValidate(t)||this.edit_only_mode||this.is_mass_editing)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),this.edit_enabled&&this.editOwnerOrChildPermissionValidate(t)?(ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0),this.is_viewing||ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuDeleteIcon(e,t){(!this.deletePermissionValidate(t)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),this.delete_enabled&&this.deleteOwnerOrChildPermissionValidate(t)?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuDeleteAndNextIcon(e,t){(!this.deletePermissionValidate(t)||this.edit_only_mode)&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1),this.delete_enabled&&this.deleteOwnerOrChildPermissionValidate(t)?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}getCustomContextMenuModel(){var e={exclude:["save_and_continue"],include:[{label:r.i18n._("Jump To"),id:"jump_to_header",menu_align:"right",action_group:"jump_to",action_group_header:!0,permission_result:!1},{label:r.i18n._("TimeSheet"),id:"timesheet",menu_align:"right",action_group:"jump_to",group:"navigation"},{label:r.i18n._("Import"),id:"import_icon",menu_align:"right",action_group:"import_export",group:"other",vue_icon:"tticon tticon-file_download_black_24dp",sort_order:9010}]};return e}getGridSetup(){var e=this,t={container_selector:this.sub_view_mode?".edit-view-tab":".view",sub_grid_mode:this.sub_view_mode,onSelectRow:function(){e.onGridSelectRow()},onCellSelect:function(){e.onGridSelectRow()},onSelectAll:function(){e.onGridSelectAll()},ondblClickRow:function(i){e.onGridDblClickRow(i)},onRightClickRow:function(i){var l=e.getGridSelectIdArray();l.indexOf(i)<0&&(e.grid.grid.resetSelection(),e.grid.grid.setSelection(i),e.onGridSelectRow())}};return t}onCustomContextClick(e){switch(e){case"timesheet":this.onNavigationClick();break;case"import_icon":this.onImportClick();break}}onImportClick(){var e=this;IndexViewController.openWizard("ImportCSVWizard","Accrual",function(){e.search()})}reSelectLastSelectItems(){super.reSelectLastSelectItems(),this.edit_enabled=this.editEnabled(),this.delete_enabled=this.deleteEnabled(),this.edit_view||this.setDefaultMenu()}setCustomDefaultMenuIcon(e,t,i){switch(e){case"timesheet":this.setDefaultMenuViewIcon(t,i,"punch");break}}setCustomEditMenuIcon(e,t){switch(e){case"timesheet":this.setDefaultMenuViewIcon(t,"punch");break}}onNavigationClick(){var e=this,t={filter_data:{}},i=this.sub_view_mode?r.i18n._("Accrual Balances"):r.i18n._("Accruals");if(Global.isSet(this.current_edit_record))t.user_id=this.current_edit_record.user_id,t.base_date=this.current_edit_record.time_stamp,Global.addViewTab(this.viewId,i,window.location.href),IndexViewController.goToView("TimeSheet",t);else{var l={},a=this.getGridSelectIdArray(),_=a.length;if(_>0){var n=a[0];l.filter_data={},l.filter_data.id=[n],TTAPI.APIAccrual.getAccrual(l,{onResult:function(u){var s=u.getResult();s||(s=[]),s=s[0],t.user_id=s.user_id,t.base_date=s.time_stamp,Global.addViewTab(e.viewId,i,window.location.href),IndexViewController.goToView("TimeSheet",t)}})}}}getSubViewFilter(e){return this.parent_edit_record&&this.parent_edit_record.user_id&&this.parent_edit_record.accrual_policy_account_id&&(e.user_id=this.parent_edit_record.user_id,e.accrual_policy_account_id=this.parent_edit_record.accrual_policy_account_id),e}onAddResult(e){var t=this,i=e.getResult();i||(i=[]),i.company=LocalCacheData.current_company.name,t.sub_view_mode&&(i.user_id=t.parent_edit_record.user_id,i.first_name=t.parent_edit_record.first_name,i.last_name=t.parent_edit_record.last_name,i.accrual_policy_account_id=t.parent_edit_record.accrual_policy_account_id,i.accrual_policy_account=t.parent_edit_record.accrual_policy_account),t.current_edit_record=i,t.initEditView()}searchDone(){var e=this;if(Global.isSet(e.is_trigger_add)&&e.is_trigger_add&&(e.onAddClick(),e.is_trigger_add=!1),this.sub_view_mode){TTPromise.resolve("initSubAccrualView","init");var t=this.grid.getGridParam("data");(!Global.isArray(t)||t.length<1)&&(this.onCancelClick(),this.parent_view_controller&&this.parent_view_controller.search())}super.searchDone()}getAvailableBalance(){if(!this.is_viewing){var e=this;this.api_accrual_balance.getAccrualBalanceAndRelease(this.current_edit_record.accrual_policy_account_id,this.current_edit_record.user_id,this.current_edit_record.type_id,{onResult:function(t){e.releaseBalance(t.getResult())}})}}releaseBalance(e){Global.parseTimeUnit('"'+Global.getTimeUnit(e)+'"')==e?this.edit_view_ui_dic.amount.setValue(Global.getTimeUnit(e)):this.edit_view_ui_dic.amount.setValue('"'+Global.getTimeUnit(e,12)+'"'),this.edit_view_ui_dic.amount.trigger("change")}}c.loadView=function(){Global.loadViewSource("Accrual","AccrualView.html",function(d){TTPromise.wait("BaseViewController","initialize",function(){var e={},t=o.template(d);Global.contentContainer().html(t(e))})})};c.loadSubView=function(d,e,t){Global.loadViewSource("Accrual","SubAccrualView.html",function(i){var l={},a=o.template(i);Global.isSet(e)&&e(),Global.isSet(d)&&(d.html(a(l)),Global.isSet(t)&&(TTPromise.add("AccrualViewController","init"),TTPromise.wait("AccrualViewController","init",function(){t(sub_accrual_view_controller)})))})};export{c as AccrualViewController};
//# sourceMappingURL=AccrualViewController-gKRX0gG1.bundle.js.map
