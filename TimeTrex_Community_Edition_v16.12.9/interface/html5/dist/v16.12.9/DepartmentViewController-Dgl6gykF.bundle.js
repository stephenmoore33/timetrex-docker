import{_ as p,j as t}from"./vendor-tTApdY0Y.bundle.js";class h extends BaseViewController{constructor(i={}){p.defaults(i,{el:"#department_view_container",user_group_selection_type_id_array:null,user_title_selection_type_id_array:null,user_punch_branch_selection_type_id_array:null,user_default_department_selection_type_id_array:null,user_group_array:null,status_array:null}),super(i)}init(i){this.edit_view_tpl="DepartmentEditView.html",this.permission_id="department",this.viewId="Department",this.script_name="DepartmentView",this.table_name_key="department",this.context_menu_name=t.i18n._("Departments"),this.navigation_label=t.i18n._("Department"),this.api=TTAPI.APIDepartment,Global.getProductEdition()>=20&&(this.user_group_api=TTAPI.APIUserGroup),this.render(),this.buildContextMenu(),this.initData()}initOptions(){var i=this;let o=[{option_name:"status",api:this.api}];Global.getProductEdition()>=20&&(o.push({option_name:"user_group_selection_type_id",api:this.api},{option_name:"user_title_selection_type_id",api:this.api},{option_name:"user_punch_branch_selection_type_id",api:this.api},{option_name:"user_default_department_selection_type_id",api:this.api}),this.user_group_api.getUserGroup("",!1,!1,{onResult:function(n){n=n.getResult(),n=Global.buildTreeRecord(n),i.user_group_array=n}})),this.initDropDownOptions(o)}onFormItemChange(i,o){this.setIsChanged(i),this.setMassEditingFieldsWhenFormChange(i);var n=i.getField();this.current_edit_record[n]=i.getValue(),Global.getProductEdition()>=20&&(n==="user_group_selection_type_id"&&this.onEmployeeGroupSelectionTypeChange(),n==="user_title_selection_type_id"&&this.onEmployeeTitleSelectionTypeChange(),n==="user_punch_branch_selection_type_id"&&this.onEmployeePunchBranchSelectionTypeChange(),n==="user_default_department_selection_type_id"&&this.onEmployeeDefaultDepartmentSelectionTypeChange()),o||this.validate()}onEmployeeGroupSelectionTypeChange(){this.current_edit_record.user_group_selection_type_id==10?this.edit_view_ui_dic.user_group_ids.setEnabled(!1):this.edit_view_ui_dic.user_group_ids.setEnabled(!0)}onEmployeeTitleSelectionTypeChange(){this.current_edit_record.user_title_selection_type_id==10?this.edit_view_ui_dic.user_title_ids.setEnabled(!1):this.edit_view_ui_dic.user_title_ids.setEnabled(!0)}onEmployeePunchBranchSelectionTypeChange(){this.current_edit_record.user_punch_branch_selection_type_id==10?this.edit_view_ui_dic.user_punch_branch_ids.setEnabled(!1):this.edit_view_ui_dic.user_punch_branch_ids.setEnabled(!0)}onEmployeeDefaultDepartmentSelectionTypeChange(){this.current_edit_record.user_default_department_selection_type_id==10?this.edit_view_ui_dic.user_default_department_ids.setEnabled(!1):this.edit_view_ui_dic.user_default_department_ids.setEnabled(!0)}buildEditViewUI(){super.buildEditViewUI();var i=this,o={tab_department:{label:t.i18n._("Department")},tab_employee_criteria:{label:t.i18n._("Employee Criteria"),init_callback:"initSubEmployeeCriteriaView",html_template:this.getDepartmentEmployeeCriteriaTabHtml()},tab_audit:!0};this.setTabModel(o),this.navigation.AComboBox({api_class:TTAPI.APIDepartment,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_department",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var n=this.edit_view_tab.find("#tab_department"),r=n.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(r);var e=Global.loadWidgetByName(FormItemType.COMBO_BOX);if(e.TComboBox({field:"status_id"}),e.setSourceData(i.status_array),this.addEditFieldToColumn(t.i18n._("Status"),e,r,""),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn(t.i18n._("Name"),e,r),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"manual_id",width:65}),this.addEditFieldToColumn(t.i18n._("Code"),e,r),Global.getProductEdition()>=20&&(e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APIGEOFence,allow_multiple_selection:!0,layout_name:"global_geo_fence",show_search_inputs:!0,set_empty:!0,field:"geo_fence_ids"}),this.addEditFieldToColumn(t.i18n._("Allowed GEO Fences"),e,r)),e=Global.loadWidgetByName(FormItemType.TAG_INPUT),e.TTagInput({field:"tag",object_type_id:120}),this.addEditFieldToColumn(t.i18n._("Tags"),e,r,"",null,null,!0),Global.getProductEdition()>=20){var u=this.edit_view_tab.find("#tab_employee_criteria"),d=u.find(".first-column");this.edit_view_tabs[1]=[],this.edit_view_tabs[1].push(d);var a=t("<div class='v-box'></div>");e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"user_group_selection_type_id"}),e.setSourceData(i.user_group_selection_type_id_array);var l=this.putInputToInsideFormItem(e,t.i18n._("Selection Type"));a.append(l),a.append("<div class='clear-both-div'></div>");var s=Global.loadWidgetByName(FormItemType.AWESOME_BOX);s.AComboBox({tree_mode:!0,allow_multiple_selection:!0,layout_name:"global_tree_column",set_empty:!0,field:"user_group_ids"}),s.setSourceData(i.user_group_array),l=this.putInputToInsideFormItem(s,t.i18n._("Selection")),a.append(l),this.addEditFieldToColumn(t.i18n._("Employee Groups"),[e,s],d,"",a,!1,!0),a=t("<div class='v-box'></div>"),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"user_title_selection_type_id",set_empty:!1}),e.setSourceData(i.user_title_selection_type_id_array),l=this.putInputToInsideFormItem(e,t.i18n._("Selection Type")),a.append(l),a.append("<div class='clear-both-div'></div>"),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.UserTitle,allow_multiple_selection:!0,layout_name:"global_user_title",show_search_inputs:!0,set_empty:!0,field:"user_title_ids"}),l=this.putInputToInsideFormItem(s,t.i18n._("Selection")),a.append(l),this.addEditFieldToColumn(t.i18n._("Employee Titles"),[e,s],d,"",a,!1,!0),a=t("<div class='v-box'></div>"),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"user_punch_branch_selection_type_id",set_empty:!1}),e.setSourceData(i.user_punch_branch_selection_type_id_array),l=this.putInputToInsideFormItem(e,t.i18n._("Selection Type")),a.append(l),a.append("<div class='clear-both-div'></div>"),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.Branch,allow_multiple_selection:!0,layout_name:"global_user_punch_branch",show_search_inputs:!0,set_empty:!0,field:"user_punch_branch_ids"}),l=this.putInputToInsideFormItem(s,t.i18n._("Selection")),a.append(l),this.addEditFieldToColumn(t.i18n._("Punch Branch"),[e,s],d,"",a,!1,!0),a=t("<div class='v-box'></div>"),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"user_default_department_selection_type_id",set_empty:!1}),e.setSourceData(i.user_default_department_selection_type_id_array),l=this.putInputToInsideFormItem(e,t.i18n._("Selection Type")),a.append(l),a.append("<div class='clear-both-div'></div>"),s=Global.loadWidgetByName(FormItemType.AWESOME_BOX),s.AComboBox({api_class:TTAPI.Department,allow_multiple_selection:!0,layout_name:"global_user_default_department",show_search_inputs:!0,set_empty:!0,field:"user_default_department_ids"}),l=this.putInputToInsideFormItem(s,t.i18n._("Selection")),a.append(l);var _=Global.loadWidgetByName(FormItemType.CHECKBOX);_.TCheckbox({field:"include_user_default_department_id"}),l=this.putInputToInsideFormItem(_,t.i18n._("Include This Department")),a.append(l),this.addEditFieldToColumn(t.i18n._("Default Department"),[e,s,_],d,"",a,!1,!0),e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!0,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"include_user_ids"}),this.addEditFieldToColumn(t.i18n._("Include Employees"),e,d),e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!0,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"exclude_user_ids"}),this.addEditFieldToColumn(t.i18n._("Exclude Employees"),e,d,"")}}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:t.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:t.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:t.i18n._("Tags"),field:"tag",basic_search:!0,in_column:1,form_item_type:FormItemType.TAG_INPUT}),new SearchField({label:t.i18n._("Code"),field:"manual_id",basic_search:!0,in_column:2,object_type_id:120,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:t.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:t.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}getCustomContextMenuModel(){var i={exclude:[],include:[{label:t.i18n._("Import"),id:"import_icon",menu_align:"right",action_group:"import_export",group:"other",vue_icon:"tticon tticon-file_download_black_24dp",permission_result:PermissionManager.checkTopLevelPermission("ImportCSVDepartment"),sort_order:9010}]};return i}setDefaultMenuImportIcon(i,o,n){PermissionManager.checkTopLevelPermission("ImportCSVDepartment")===!0?ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,i.id,!0):ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,i.id,!1)}onCustomContextClick(i){switch(i){case"import_icon":this.onImportClick();break}}onImportClick(){var i=this;IndexViewController.openWizard("ImportCSVWizard","Department",function(){i.search()})}setEditViewDataDone(){super.setEditViewDataDone(),Global.getProductEdition()>=20&&(this.onEmployeeGroupSelectionTypeChange(),this.onEmployeeTitleSelectionTypeChange(),this.onEmployeePunchBranchSelectionTypeChange(),this.onEmployeeDefaultDepartmentSelectionTypeChange())}initSubEmployeeCriteriaView(){Global.getProductEdition()>=20?(this.edit_view_tab.find("#tab_employee_criteria").find(".first-column").css("display","block"),this.edit_view.find(".permission-defined-div").css("display","none")):(this.edit_view_tab.find("#tab_employee_criteria").find(".first-column").css("display","none"),this.edit_view.find(".permission-defined-div").css("display","block"),this.edit_view.find(".permission-message").html(Global.getUpgradeMessage()))}getDepartmentEmployeeCriteriaTabHtml(){return`
		<div id="tab_employee_criteria" class="edit-view-tab-outside">
			<div class="edit-view-tab" id="tab_employee_criteria_content_div">
				<div class="first-column full-width-column"></div>
				<div class="save-and-continue-div permission-defined-div">
					<span class="message permission-message"></span>
				</div>
			</div>
		</div>`}}export{h as DepartmentViewController};
//# sourceMappingURL=DepartmentViewController-Dgl6gykF.bundle.js.map
