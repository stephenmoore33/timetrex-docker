import{_ as c,j as r}from"./vendor-tTApdY0Y.bundle.js";import{t as g,y as T}from"./QuickPunchLoginViewController-DZo_BDAl.bundle.js";import"./main_ui-z76rxVqJ.entry.bundle.js";class w extends BaseViewController{constructor(e={}){c.defaults(e,{type_array:null,job_api:null,job_item_api:null,punch_tag_api:null,user_api:null,department_api:null,system_job_queue_api:null,default_punch_tag:[],previous_punch_tag_selection:[],old_type_status:{},show_job_ui:!1,show_job_item_ui:!1,show_punch_tag_ui:!1,show_branch_ui:!1,show_department_ui:!1,show_good_quantity_ui:!1,show_bad_quantity_ui:!1,show_transfer_ui:!1,show_node_ui:!1,original_note:!1,new_note:!1,do_not_prevalidate:!0}),super(e)}init(e){Global.setUINotready(!0),this.permission_id="punch",this.viewId="InOut",this.script_name="InOutView",this.table_name_key="punch",this.context_menu_name=r.i18n._("In/Out"),this.api=TTAPI.APIPunch,this.system_job_queue_api=TTAPI.APISystemJobQueue,this.event_bus=new TTEventBus({view_id:this.viewId}),Global.getProductEdition()>=20&&(this.job_api=TTAPI.APIJob,this.job_item_api=TTAPI.APIJobItem,this.punch_tag_api=TTAPI.APIPunchTag,this.user_api=TTAPI.APIUser,this.department_api=TTAPI.APIDepartment),this.render(),this.initPermission(),this.initData(),this.is_changed=!0}getCustomContextMenuModel(){var e={exclude:["default"],include:["save","cancel"]};return e}addPermissionValidate(e){return Global.isSet(e)||(e=this.permission_id),!!(e==="report"||PermissionManager.validate(e,"punch_in_out"))}jobUIValidate(){return!!(PermissionManager.validate("job","enabled")&&PermissionManager.validate("punch","edit_job"))}jobItemUIValidate(){return!!PermissionManager.validate("punch","edit_job_item")}punchTagUIValidate(){return!!PermissionManager.validate("punch","edit_punch_tag")}branchUIValidate(){return!!PermissionManager.validate("punch","edit_branch")}departmentUIValidate(){return!!PermissionManager.validate("punch","edit_department")}goodQuantityUIValidate(){return!!PermissionManager.validate("punch","edit_quantity")}badQuantityUIValidate(){return!!(PermissionManager.validate("punch","edit_quantity")&&PermissionManager.validate("punch","edit_bad_quantity"))}transferUIValidate(){return!!PermissionManager.validate("punch","edit_transfer")}noteUIValidate(){return!!PermissionManager.validate("punch","edit_note")}initPermission(){this.jobUIValidate()?this.show_job_ui=!0:this.show_job_ui=!1,this.jobItemUIValidate()?this.show_job_item_ui=!0:this.show_job_item_ui=!1,this.punchTagUIValidate()?this.show_punch_tag_ui=!0:this.show_punch_tag_ui=!1,this.branchUIValidate()?this.show_branch_ui=!0:this.show_branch_ui=!1,this.departmentUIValidate()?this.show_department_ui=!0:this.show_department_ui=!1,this.goodQuantityUIValidate()?this.show_good_quantity_ui=!0:this.show_good_quantity_ui=!1,this.badQuantityUIValidate()?this.show_bad_quantity_ui=!0:this.show_bad_quantity_ui=!1,this.transferUIValidate()?this.show_transfer_ui=!0:this.show_transfer_ui=!1,this.noteUIValidate()?this.show_node_ui=!0:this.show_node_ui=!1;var e=!1,s=TTAPI.APICompany;s&&c.isFunction(s.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled)&&(e=s.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled({async:!1})),e?(e=e.getResult(),e.branch||(this.show_branch_ui=!1),e.department||(this.show_department_ui=!1),e.job||(this.show_job_ui=!1),e.job_item||(this.show_job_item_ui=!1),e.punch_tag||(this.show_punch_tag_ui=!1)):(this.show_branch_ui=!1,this.show_department_ui=!1,this.show_job_ui=!1,this.show_job_item_ui=!1,this.show_punch_tag_ui=!1),!this.show_job_ui&&!this.show_job_item_ui&&(this.show_bad_quantity_ui=!1,this.show_good_quantity_ui=!1)}render(){super.render()}initOptions(e){var s=[{option_name:"type"},{option_name:"status"}];this.initDropDownOptions(s,function(i){e&&e(i)})}getUserPunch(e){var s=this,i=Global.getStationID(),a=TTAPI.APIStation;i?a.getCurrentStation(i,"10",{onResult:function(n){t(n)}}):(g("exclude",["audio","canvas","webgl","system.browser.version"]),T().then(n=>{Debug.Text("Browser Fingerprint: "+n.hash,"InOutViewController.js","InOutViewController","getUserPunch",10),n&&n.hash!=""&&(i="BFP-"+n.hash),a.getCurrentStation(i,"10",{onResult:function(_){t(_)}})}).catch(n=>{a.getCurrentStation("","10",{onResult:function(_){t(_)}})}));function t(n){if(!(!s.api||typeof s.api.getUserPunch!="function")){if(!n.isValid()){TAlertManager.showErrorAlert(n),s.onCancelClick(!0);return}var _=n.getResult();Global.setStationID(_),s.api.getUserPunch({onResult:function(u){var o=u.getResult();if(Global.UNIT_TEST_MODE===!0&&(o.punch_date="UNITTEST",o.punch_time="UNITTEST"),!u.isValid()){TAlertManager.showErrorAlert(u),s.onCancelClick(!0);return}Global.isSet(o)?e(o):s.onCancelClick()}})}}}onCancelClick(e){this.is_changed=!0,super.onCancelClick(e)}openEditView(){var e=this;this.edit_only_mode&&this.api&&this.initOptions(function(s){e.edit_view||(e.initEditViewUI("InOut","InOutEditView.html"),e.buildContextMenu()),e.getUserPunch(function(i){e.current_edit_record=i,Global.UNIT_TEST_MODE===!0&&(e.current_edit_record.punch_date="UNITTEST",e.current_edit_record.punch_time="UNITTEST"),e.initEditView()})})}onFormItemChange(e,s){var i=this;this.setIsChanged(e),this.setMassEditingFieldsWhenFormChange(e);var a=e.getField(),t=e.getValue();switch(this.current_edit_record[a]=t,s=this.do_not_prevalidate,a){case"transfer":this.onTransferChanged();break;case"job_id":Global.getProductEdition()>=20&&(this.edit_view_ui_dic.job_quick_search.setValue(e.getValue(!0)&&e.getValue(!0).manual_id?e.getValue(!0).manual_id:""),this.setJobItemValueWhenJobChanged(e.getValue(!0),"job_item_id",{status_id:10,job_id:this.current_edit_record.job_id}),this.edit_view_ui_dic.job_quick_search.setCheckBox(!0),this.setPunchTagValuesWhenCriteriaChanged(this.getPunchTagFilterData(),"punch_tag_id"));break;case"job_item_id":Global.getProductEdition()>=20&&(this.edit_view_ui_dic.job_item_quick_search.setValue(e.getValue(!0)&&e.getValue(!0).manual_id?e.getValue(!0).manual_id:""),this.edit_view_ui_dic.job_item_quick_search.setCheckBox(!0),this.setPunchTagValuesWhenCriteriaChanged(this.getPunchTagFilterData(),"punch_tag_id"));break;case"punch_tag_id":Global.getProductEdition()>=20&&(t!==TTUUID.zero_id&&t!==!1&&t.length>0?this.setPunchTagQuickSearchManualIds(e.getSelectItems()):this.edit_view_ui_dic.punch_tag_quick_search.setValue(""),i.previous_punch_tag_selection=t,this.edit_view_ui_dic.punch_tag_id.setSourceData(null));break;case"branch_id":Global.getProductEdition()>=20&&(this.setPunchTagValuesWhenCriteriaChanged(this.getPunchTagFilterData(),"punch_tag_id"),this.setJobValueWhenCriteriaChanged("job_id",{status_id:10,user_id:this.current_edit_record.user_id,punch_branch_id:this.current_edit_record.branch_id,punch_department_id:this.current_edit_record.department_id}),this.setDepartmentValueWhenBranchChanged(e.getValue(!0),"department_id",{branch_id:this.current_edit_record.branch_id,user_id:this.current_edit_record.user_id}));break;case"user_id":Global.getProductEdition()>=20&&this.setPunchTagValuesWhenCriteriaChanged(this.getPunchTagFilterData(),"punch_tag_id");break;case"department_id":Global.getProductEdition()>=20&&(this.setPunchTagValuesWhenCriteriaChanged(this.getPunchTagFilterData(),"punch_tag_id"),this.setJobValueWhenCriteriaChanged("job_id",{status_id:10,user_id:this.current_edit_record.user_id,punch_branch_id:this.current_edit_record.branch_id,punch_department_id:this.current_edit_record.department_id}));break;case"job_quick_search":case"job_item_quick_search":Global.getProductEdition()>=20&&(this.onJobQuickSearch(a,t),TTPromise.wait("BaseViewController","onJobQuickSearch",function(){i.setPunchTagValuesWhenCriteriaChanged(i.getPunchTagFilterData(),"punch_tag_id")}),s=!0);break;case"punch_tag_quick_search":Global.getProductEdition()>=20&&(this.onPunchTagQuickSearch(t,this.getPunchTagFilterData(),"punch_tag_id"),s=!0);break}s||this.validate()}onTransferChanged(e){var s=!1;this.edit_view_ui_dic&&this.edit_view_ui_dic.transfer&&this.edit_view_ui_dic.transfer.getValue()==!0&&(s=!0);var i=this.edit_view_ui_dic.type_id,a=this.edit_view_ui_dic.status_id;s&&i&&a?(i.setEnabled(!1),a.setEnabled(!1),this.old_type_status.type_id=i.getValue(),this.old_type_status.status_id=a.getValue(),i.setValue(10),a.setValue(10),this.current_edit_record.type_id=10,this.current_edit_record.status_id=10):i&&a&&(i.setEnabled(!0),a.setEnabled(!0),this.old_type_status.hasOwnProperty("type_id")&&(i.setValue(this.old_type_status.type_id),a.setValue(this.old_type_status.status_id),this.current_edit_record.type_id=this.old_type_status.type_id,this.current_edit_record.status_id=this.old_type_status.status_id)),s==!0?(this.auto_fill_data&&this.auto_fill_data.note?(this.new_note=this.auto_fill_data.note,this.edit_view_ui_dic.note.setValue(this.new_note),this.current_edit_record.note=this.new_note):this.original_note==""?this.original_note=this.current_edit_record.note:this.original_note=this.edit_view_ui_dic.note.getValue(),this.edit_view_ui_dic.note.setValue(this.new_note?this.new_note:""),this.current_edit_record.note=this.new_note?this.new_note:""):(typeof e>"u"||e===!1)&&(this.new_note=this.edit_view_ui_dic.note.getValue(),this.edit_view_ui_dic.note.setValue(this.original_note?this.original_note:""),this.current_edit_record.note=this.original_note?this.original_note:"")}validate(){var e=this,s={};if(this.is_mass_editing){for(var i in this.edit_view_ui_dic)if(this.edit_view_ui_dic.hasOwnProperty(i)){var a=this.edit_view_ui_dic[i];Global.isSet(a.isChecked)&&a.isChecked()&&a.getEnabled()&&(s[i]=a.getValue())}}else s=this.current_edit_record;s=this.uniformVariable(s),this.api.setUserPunch(s,!0,{onResult:function(t){e.validateResult(t)}})}onSaveClick(e,s){return super.onSaveClick(e,!0)}doSaveAPICall(e,s,i){var a=this.getCurrentAPI();return i||(i={onResult:(function(t){this.onSaveResult(t)}).bind(this)}),a.setIsIdempotent(!0),a.setUserPunch(e,!1,s,i)}convertSetUserPunchToGetPunch(e){return e.id=TTUUID.not_exist_id,e.longitude=null,e.latitude=null,e.position_accuracy=null,e.pay_period_id=null,e.punch_control_id=e.punch_control_id?e.punch_control_id:null,e.tainted=!1,e.has_image=!1,e}onSaveResult(e){let s=c.clone(this.current_edit_record);if(super.onSaveResult(e),e&&e.isValid()&&s!=null){LocalCacheData.setLastPunchTime(new Date().getTime());var i=e.getAttributeInAPIDetails("system_job_queue");i&&(LocalCacheData.setJobQueuePunchData(this.convertSetUserPunchToGetPunch(s)),this.event_bus.emit("tt_topbar","toggle_job_queue_spinner",{show:!0,get_job_data:!0})),LocalCacheData.current_open_primary_controller&&LocalCacheData.current_open_primary_controller.viewId==="TimeSheet"&&LocalCacheData.current_open_primary_controller.search()}else this.do_not_prevalidate=!1}setErrorMenu(){for(var e=ContextMenuManager.getMenuModelByMenuId(this.determineContextMenuMountAttributes().id),s=e.length,i=0;i<s;i++){let a=e[i],t=e[i].id;switch(ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,a.id,!0),t){case"cancel":break;default:ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,a.id,!1);break}}}getCustomFieldReferenceField(){return"note"}buildEditViewUI(){super.buildEditViewUI();var e=this,s={tab_punch:{label:r.i18n._("Punch")},tab_audit:!1};this.setTabModel(s);var i=this.edit_view_tab.find("#tab_punch"),a=i.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(a);var t,n;if(t=Global.loadWidgetByName(FormItemType.TEXT),t.TText({field:"user_id_readonly"}),this.addEditFieldToColumn(r.i18n._("Employee"),t,a,""),t=Global.loadWidgetByName(FormItemType.TIME_PICKER),t.TTimePicker({field:"punch_time"}),this.addEditFieldToColumn(r.i18n._("Time"),t,a),t=Global.loadWidgetByName(FormItemType.DATE_PICKER),t.TDatePicker({field:"punch_date"}),this.addEditFieldToColumn(r.i18n._("Date"),t,a),t=Global.loadWidgetByName(FormItemType.CHECKBOX),t.TCheckbox({field:"transfer"}),this.addEditFieldToColumn(r.i18n._("Transfer"),t,a,"",null,!0),this.show_transfer_ui||this.detachElement("transfer"),t=Global.loadWidgetByName(FormItemType.COMBO_BOX),t.TComboBox({field:"type_id"}),t.setSourceData(e.type_array),this.addEditFieldToColumn(r.i18n._("Punch Type"),t,a),t=Global.loadWidgetByName(FormItemType.COMBO_BOX),t.TComboBox({field:"status_id"}),t.setSourceData(e.status_array),this.addEditFieldToColumn(r.i18n._("In/Out"),t,a),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIBranch,allow_multiple_selection:!1,layout_name:"global_branch",show_search_inputs:!0,set_empty:!0,field:"branch_id"}),this.addEditFieldToColumn(r.i18n._("Branch"),t,a,"",null,!0),this.show_branch_ui||this.detachElement("branch_id"),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIDepartment,allow_multiple_selection:!1,layout_name:"global_department",show_search_inputs:!0,set_empty:!0,field:"department_id"}),this.addEditFieldToColumn(r.i18n._("Department"),t,a,"",null,!0),this.show_department_ui||this.detachElement("department_id"),Global.getProductEdition()>=20){t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIJob,allow_multiple_selection:!1,layout_name:"global_job",show_search_inputs:!0,set_empty:!0,always_include_columns:["group_id"],setRealValueCallBack:function(d){d&&_.setValue(d.manual_id)},field:"job_id"}),n=r("<div class='widget-h-box'></div>");var _=Global.loadWidgetByName(FormItemType.TEXT_INPUT);_.TTextInput({field:"job_quick_search",disable_keyup_event:!0}),_.addClass("job-coder"),n.append(_),n.append(t),this.addEditFieldToColumn(r.i18n._("Job"),[t,_],a,"",n,!0),this.show_job_ui||this.detachElement("job_id"),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIJobItem,allow_multiple_selection:!1,layout_name:"global_job_item",show_search_inputs:!0,set_empty:!0,always_include_columns:["group_id"],setRealValueCallBack:function(d){d&&u.setValue(d.manual_id)},field:"job_item_id"}),n=r("<div class='widget-h-box'></div>");var u=Global.loadWidgetByName(FormItemType.TEXT_INPUT);u.TTextInput({field:"job_item_quick_search",disable_keyup_event:!0}),u.addClass("job-coder"),n.append(u),n.append(t),this.addEditFieldToColumn(r.i18n._("Task"),[t,u],a,"",n,!0),this.show_job_item_ui||this.detachElement("job_item_id"),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIPunchTag,allow_multiple_selection:!0,layout_name:"global_punch_tag",show_search_inputs:!0,set_empty:!0,get_real_data_on_multi:!0,setRealValueCallBack:(d,b)=>{d&&this.setPunchTagQuickSearchManualIds(d,b)},field:"punch_tag_id"}),n=r("<div class='widget-h-box'></div>");var o=Global.loadWidgetByName(FormItemType.TEXT_INPUT);o.TTextInput({field:"punch_tag_quick_search",disable_keyup_event:!0}),o.addClass("job-coder"),n.append(o),n.append(t),this.addEditFieldToColumn(r.i18n._("Tags"),[t,o],a,"",n,!0),this.show_punch_tag_ui||this.detachElement("punch_tag_id")}if(Global.getProductEdition()>=20){var l=Global.loadWidgetByName(FormItemType.TEXT_INPUT);l.TTextInput({field:"quantity",width:40}),l.addClass("quantity-input");var m=r("<span class='widget-right-label'>"+r.i18n._("Good")+": </span>"),h=Global.loadWidgetByName(FormItemType.TEXT_INPUT);h.TTextInput({field:"bad_quantity",width:40}),h.addClass("quantity-input");var f=r("<span class='widget-right-label'>/ "+r.i18n._("Bad")+": </span>");n=r("<div class='widget-h-box'></div>"),n.append(m),n.append(l),n.append(f),n.append(h),this.addEditFieldToColumn(r.i18n._("Quantity"),[l,h],a,"",n,!0),!this.show_bad_quantity_ui&&!this.show_good_quantity_ui?this.detachElement("quantity"):(this.show_bad_quantity_ui||(f.hide(),h.hide()),this.show_good_quantity_ui||(m.hide(),l.hide()))}t=Global.loadWidgetByName(FormItemType.TEXT_AREA),t.TTextArea({field:"note",width:"100%"}),this.addEditFieldToColumn(r.i18n._("Note"),t,a,"",null,!0,!0),t.parent().width("45%"),this.show_node_ui||this.detachElement("note")}setCurrentEditRecordData(){var e=this;this.old_type_status={};for(var s in this.current_edit_record)if(this.current_edit_record.hasOwnProperty(s)){var i=this.edit_view_ui_dic[s];if(Global.isSet(i))switch(s){case"user_id_readonly":i.setValue(this.current_edit_record.first_name+" "+this.current_edit_record.last_name);break;case"job_id":if(Global.getProductEdition()>=20){var a={};a.filter_data={status_id:10,user_id:this.current_edit_record.user_id,punch_branch_id:this.current_edit_record.branch_id,punch_department_id:this.current_edit_record.department_id},i.setDefaultArgs(a),i.setValue(this.current_edit_record[s])}break;case"job_item_id":if(Global.getProductEdition()>=20){var a={};a.filter_data={status_id:10,job_id:this.current_edit_record.job_id},i.setDefaultArgs(a),i.setValue(this.current_edit_record[s])}break;case"punch_tag_id":if(Global.getProductEdition()>=20){i.setValue(this.current_edit_record[s]),this.previous_punch_tag_selection=this.current_edit_record[s];var t=i;TTPromise.wait(null,null,function(){var n={};n.filter_data=e.getPunchTagFilterData(),t.setDefaultArgs(n)})}break;case"branch_id":if(Global.getProductEdition()>=20){var a={};a.filter_data={status_id:10,user_id:this.current_edit_record.user_id},i.setDefaultArgs(a)}i.setValue(this.current_edit_record[s]);break;case"department_id":if(Global.getProductEdition()>=20){var a={};a.filter_data={status_id:10,user_id:this.current_edit_record.user_id,branch_id:this.current_edit_record.branch_id},i.setDefaultArgs(a)}i.setValue(this.current_edit_record[s]);break;case"job_quick_search":break;case"job_item_quick_search":break;case"punch_tag_quick_search":break;case"transfer":break;case"punch_time":case"punch_date":i.setEnabled(!1),i.setValue(this.current_edit_record[s]);break;default:i.setValue(this.current_edit_record[s]);break}}this.show_transfer_ui&&this.edit_view_ui_dic.transfer&&this.edit_view_ui_dic.transfer.setValue(this.current_edit_record.transfer),this.onTransferChanged(!0),this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}setEditViewDataDone(){super.setEditViewDataDone(),this.confirm_on_exit=!0}}w.loadView=function(){Global.loadViewSource("InOut","InOutView.html",function(p){var e={},s=c.template(p);Global.contentContainer().html(s(e))})};export{w as InOutViewController};
//# sourceMappingURL=InOutViewController-BYGHLFCi.bundle.js.map
