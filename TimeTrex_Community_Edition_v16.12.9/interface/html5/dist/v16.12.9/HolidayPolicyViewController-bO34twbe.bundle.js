import{_ as f,j as e}from"./vendor-tTApdY0Y.bundle.js";class I extends BaseViewController{constructor(a={}){f.defaults(a,{el:"#holiday_policy_view_container",type_array:null,default_schedule_status_array:null,shift_on_holiday_type_array:null,worked_scheduled_days_array:null,date_api:null,sub_holiday_view_controller:null}),super(a)}init(a){this.edit_view_tpl="HolidayPolicyEditView.html",this.permission_id="holiday_policy",this.viewId="HolidayPolicy",this.script_name="HolidayPolicyView",this.table_name_key="holiday_policy",this.context_menu_name=e.i18n._("Holiday Policy"),this.navigation_label=e.i18n._("Holiday Policy"),this.api=TTAPI.APIHolidayPolicy,this.date_api=TTAPI.APITTDate,this.render(),this.buildContextMenu(),this.initData()}initOptions(){var a=this,s=[{option_name:"type",api:this.api},{option_name:"average_time_frequency_type",api:this.api},{option_name:"default_schedule_status",api:this.api},{option_name:"shift_on_holiday_type",api:this.api}];this.initDropDownOptions(s),this.initDropDownOption("scheduled_day","worked_scheduled_days",null,function(d){d=d.getResult(),a.worked_scheduled_days_array=e.extend({},d)})}buildEditViewUI(){super.buildEditViewUI();var a=this,s={tab_holiday_policy:{label:e.i18n._("Holiday Policy")},tab_eligibility:{label:e.i18n._("Eligibility")},tab_holiday_time:{label:e.i18n._("Holiday Time")},tab_holidays:{label:e.i18n._("Holidays"),init_callback:"initSubHolidayView",display_on_mass_edit:!1},tab_audit:!0};this.setTabModel(s),this.navigation.AComboBox({api_class:TTAPI.APIHolidayPolicy,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_holiday",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var d=this.edit_view_tab.find("#tab_holiday_policy"),l=d.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(l);var i,t,p;i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn(e.i18n._("Name"),i,l,""),i.parent().width("45%"),i=Global.loadWidgetByName(FormItemType.TEXT_AREA),i.TTextArea({field:"description",width:"100%"}),this.addEditFieldToColumn(e.i18n._("Description"),i,l,"",null,null,!0),i.parent().width("45%"),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"type_id",set_empty:!1}),i.setSourceData(a.type_array),this.addEditFieldToColumn(e.i18n._("Type"),i,l),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"default_schedule_status_id"}),i.setSourceData(a.default_schedule_status_array),this.addEditFieldToColumn(e.i18n._("Default Schedule Status"),i,l),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIRecurringHoliday,allow_multiple_selection:!0,layout_name:"global_recurring_holiday",show_search_inputs:!0,set_empty:!0,field:"recurring_holiday_id"}),this.addEditFieldToColumn(e.i18n._("Recurring Holidays"),i,l,""),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"holiday_display_days",width:50}),t=e("<div class='widget-h-box'></div>"),p=e("<span class='widget-right-label'> "+e.i18n._("(Days in Advance)")+"</span>"),t.append(i),t.append(p),this.addEditFieldToColumn(e.i18n._("Display Holidays"),i,l,"",t);var w=this.edit_view_tab.find("#tab_eligibility"),m=w.find(".first-column");this.edit_view_tabs[1]=[],this.edit_view_tabs[1].push(m),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"minimum_employed_days",width:50}),this.addEditFieldToColumn(e.i18n._("Minimum Employed Days"),i,m,"");var n=Global.loadWidgetByName(FormItemType.TEXT_INPUT);n.TTextInput({field:"minimum_worked_days",width:30});var r=Global.loadWidgetByName(FormItemType.TEXT_INPUT);r.TTextInput({field:"minimum_worked_period_days",width:30});var _=Global.loadWidgetByName(FormItemType.COMBO_BOX);_.TComboBox({field:"worked_scheduled_days"}),_.setSourceData(a.worked_scheduled_days_array),t=e("<div class='widget-h-box'></div>");var u=e("<span class='widget-right-label'> "+e.i18n._("of the")+" </span>"),h=e("<span class='widget-right-label'> "+e.i18n._("prior to the holiday")+" </span>"),y=e("<span class='widget-right-label'>   </span>");t.append(n),t.append(u),t.append(r),t.append(y),t.append(_),t.append(h),this.addEditFieldToColumn(e.i18n._("Employee Must Work at Least"),[n,r,_],m,"",t,!0),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"shift_on_holiday_type_id"}),i.setSourceData(a.shift_on_holiday_type_array),this.addEditFieldToColumn(e.i18n._("On the Holiday, the Employee"),i,m,"",null,!0),n=Global.loadWidgetByName(FormItemType.TEXT_INPUT),n.TTextInput({field:"minimum_worked_after_days",width:30}),r=Global.loadWidgetByName(FormItemType.TEXT_INPUT),r.TTextInput({field:"minimum_worked_after_period_days",width:30}),_=Global.loadWidgetByName(FormItemType.COMBO_BOX),_.TComboBox({field:"worked_after_scheduled_days"}),_.setSourceData(a.worked_scheduled_days_array),t=e("<div class='widget-h-box'></div>"),u=e("<span class='widget-right-label'> "+e.i18n._("of the")+" </span>"),h=e("<span class='widget-right-label'> "+e.i18n._("following the holiday")+" </span>"),y=e("<span class='widget-right-label'>   </span>"),t.append(n),t.append(u),t.append(r),t.append(y),t.append(_),t.append(h),this.addEditFieldToColumn(e.i18n._("Employee Must Work at Least"),[n,r,_],m,"",t,!0),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIContributingShiftPolicy,allow_multiple_selection:!1,layout_name:"global_contributing_shift_policy",show_search_inputs:!0,set_empty:!0,field:"eligible_contributing_shift_policy_id"}),this.addEditFieldToColumn(e.i18n._("Contributing Shift Policy"),i,m,"",null,!0);var g=this.edit_view_tab.find("#tab_holiday_time"),o=g.find(".first-column");this.edit_view_tabs[2]=[],this.edit_view_tabs[2].push(o),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"average_time_days",width:30});var c=Global.loadWidgetByName(FormItemType.COMBO_BOX);c.TComboBox({field:"average_time_frequency_type_id"}),c.setSourceData(a.average_time_frequency_type_array),t=e("<div class='widget-h-box'></div>"),t.append(i),t.append(c),this.addEditFieldToColumn(e.i18n._("Total Time Over"),[i,c],o,"",t,!0);var T=Global.loadWidgetByName(FormItemType.CHECKBOX);T.TCheckbox({field:"average_time_worked_days"});var b=Global.loadWidgetByName(FormItemType.TEXT_INPUT);b.TTextInput({field:"average_days",width:30}),t=e("<div class='widget-h-box '></div>"),u=e("<span class='widget-right-label'> "+e.i18n._("Worked Days Only")+" </span>"),h=e("<span class='widget-right-label'> "+e.i18n._("or")+" </span>"),y=e("<span class='widget-right-label'> "+e.i18n._("days")+" </span>"),t.append(u),t.append(T),t.append(h),t.append(b),t.append(y),this.average_days_widgets=[h,b,y],this.addEditFieldToColumn(e.i18n._("Average Time Over"),[T,b],o,"",t,!0),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIContributingShiftPolicy,allow_multiple_selection:!1,layout_name:"global_contributing_shift_policy",show_search_inputs:!0,set_empty:!0,field:"contributing_shift_policy_id"}),this.addEditFieldToColumn(e.i18n._("Contributing Shift Policy"),i,o,"",null,!0),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"minimum_time",mode:"time_unit",need_parser_sec:!0}),this.addEditFieldToColumn(e.i18n._("Holiday Time"),i,o,"",null,!0),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"maximum_time",mode:"time_unit",need_parser_sec:!0}),this.addEditFieldToColumn(e.i18n._("Maximum Time"),i,o,"",null,!0),i=Global.loadWidgetByName(FormItemType.CHECKBOX),i.TCheckbox({field:"force_over_time_policy"}),t=e("<div class='widget-h-box'></div>"),p=e("<span class='widget-right-label '> ("+e.i18n._("Even if they are not eligible for holiday pay")+")</span>"),t.append(i),t.append(p),this.addEditFieldToColumn(e.i18n._("Always Apply Over Time/Premium Policies"),i,o,"",t,!0),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIRoundIntervalPolicy,allow_multiple_selection:!1,layout_name:"global_round_interval",show_search_inputs:!0,set_empty:!0,field:"round_interval_policy_id"}),this.addEditFieldToColumn(e.i18n._("Rounding Policy"),i,o,"",null,!0),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIAbsencePolicy,allow_multiple_selection:!1,layout_name:"global_absences",show_search_inputs:!0,set_empty:!0,field:"absence_policy_id"}),this.addEditFieldToColumn(e.i18n._("Absence Policy"),i,o,"")}onFormItemChange(a,s){this.setIsChanged(a),this.setMassEditingFieldsWhenFormChange(a);var d=a.getField(),l=a.getValue();switch(this.current_edit_record[d]=l,d){case"type_id":this.onTypeChange();break;case"average_time_worked_days":this.onWorkedDaysChange();break}s||this.validate()}setEditViewDataDone(){super.setEditViewDataDone(),this.onTypeChange(),this.onWorkedDaysChange()}onWorkedDaysChange(){this.current_edit_record.average_time_worked_days===!0?(this.average_days_widgets[0].hide(),this.average_days_widgets[1].hide(),this.average_days_widgets[2].hide()):(this.average_days_widgets[0].show(),this.average_days_widgets[1].show(),this.average_days_widgets[2].show())}onTypeChange(){this.current_edit_record.type_id==10?(this.detachElement("minimum_worked_days"),this.detachElement("shift_on_holiday_type_id"),this.detachElement("minimum_worked_after_days"),this.detachElement("average_time_days"),this.detachElement("average_time_worked_days"),this.edit_view_form_item_dic.minimum_time.find(".edit-view-form-item-label").text(e.i18n._("Holiday Time")),this.detachElement("maximum_time"),this.detachElement("force_over_time_policy"),this.detachElement("round_interval_policy_id"),this.detachElement("eligible_contributing_shift_policy_id"),this.detachElement("contributing_shift_policy_id")):this.current_edit_record.type_id==20?(this.attachElement("minimum_worked_days"),this.attachElement("shift_on_holiday_type_id"),this.attachElement("minimum_worked_after_days"),this.detachElement("average_time_days"),this.detachElement("average_time_worked_days"),this.edit_view_form_item_dic.minimum_time.find(".edit-view-form-item-label").text(e.i18n._("Holiday Time")),this.detachElement("maximum_time"),this.detachElement("force_over_time_policy"),this.detachElement("round_interval_policy_id"),this.attachElement("eligible_contributing_shift_policy_id"),this.detachElement("contributing_shift_policy_id")):this.current_edit_record.type_id==30&&(this.attachElement("minimum_worked_days"),this.attachElement("shift_on_holiday_type_id"),this.attachElement("minimum_worked_after_days"),this.attachElement("average_time_days"),this.attachElement("average_time_worked_days"),this.edit_view_form_item_dic.minimum_time.find(".edit-view-form-item-label").text(e.i18n._("Minimum Time")),this.attachElement("maximum_time"),this.attachElement("force_over_time_policy"),this.attachElement("round_interval_policy_id"),this.attachElement("eligible_contributing_shift_policy_id"),this.attachElement("contributing_shift_policy_id")),this.editFieldResize()}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:e.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:e.i18n._("Type"),in_column:1,field:"type_id",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:e.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:e.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}initSubHolidayView(){var a=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_holiday_view_controller){this.sub_holiday_view_controller.buildContextMenu(!0),this.sub_holiday_view_controller.setDefaultMenu(),a.sub_holiday_view_controller.parent_value=a.current_edit_record.id,a.sub_holiday_view_controller.parent_edit_record=a.current_edit_record,a.sub_holiday_view_controller.initData();return}Global.loadScript("views/policy/holiday/HolidayViewController.js",function(){var l=a.edit_view_tab.find("#tab_holidays"),i=l.find(".first-column-sub-view");Global.trackView("SubHolidayView"),HolidayViewController.loadSubView(i,s,d)});function s(){}function d(l){a.sub_holiday_view_controller=l,a.sub_holiday_view_controller.parent_key="holiday_policy_id",a.sub_holiday_view_controller.parent_value=a.current_edit_record.id,a.sub_holiday_view_controller.parent_edit_record=a.current_edit_record,a.sub_holiday_view_controller.parent_view_controller=a,a.sub_holiday_view_controller.initData()}}removeEditView(){super.removeEditView(),this.sub_holiday_view_controller=null}}export{I as HolidayPolicyViewController};
//# sourceMappingURL=HolidayPolicyViewController-bO34twbe.bundle.js.map
