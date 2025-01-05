import{_ as a,j as i}from"./vendor-tTApdY0Y.bundle.js";class m extends ReportBaseViewController{constructor(e={}){a.defaults(e,{return_type_array:null,exempt_payment_array:null,state_array:null,province_array:null}),super(e)}initReport(e){this.script_name="Form940Report",this.viewId="Form940Report",this.context_menu_name=i.i18n._("Form 940"),this.navigation_label=i.i18n._("Saved Report"),this.view_file="Form940ReportView.html",this.api=TTAPI.APIForm940Report,this.api_paystub=TTAPI.APIPayStubEntryAccount,this.include_form_setup=!0}initOptions(e){var _=this,o=[{option_name:"page_orientation"},{option_name:"font_size"},{option_name:"chart_display_mode"},{option_name:"chart_type"},{option_name:"templates"},{option_name:"setup_fields"},{option_name:"return_type"},{option_name:"exempt_payment"},{option_name:"state"},{option_name:"auto_refresh"}];this.initDropDownOptions(o,function(t){TTAPI.APICompany.getOptions("province","US",{onResult:function(n){_.province_array=Global.buildRecordArray(n.getResult()),e(t)}})})}getCustomContextMenuModel(){var e={groups:{form:{label:i.i18n._("Form"),id:this.viewId+"Form"}},exclude:[],include:[{label:i.i18n._("Form"),id:"view_print",action_group_header:!0,action_group:"view_form",group:"form",menu_align:"right",icon:"view-35x35.png",type:2},{label:i.i18n._("View"),id:"view_form",action_group:"view_form",group:"form",menu_align:"right"},{label:i.i18n._("Save Setup"),id:"save_setup",action_group:"view_form",group:"form",menu_align:"right"}]};return e}buildFormSetupUI(){var e=this,_=this.edit_view_tab.find("#tab_form_setup"),o=_.find(".first-column");this.edit_view_tabs[3]=[],this.edit_view_tabs[3].push(o);var t,n;t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t=t.AComboBox({field:"return_type",set_empty:!0,allow_multiple_selection:!0,layout_name:"global_option_column",key:"value"}),t.setSourceData(e.return_type_array),this.addEditFieldToColumn(i.i18n._("Type of Return"),t,o,""),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t=t.AComboBox({field:"exempt_payment",set_empty:!0,allow_multiple_selection:!0,layout_name:"global_option_column",key:"value"}),t.setSourceData(e.exempt_payment_array),this.addEditFieldToColumn(i.i18n._("Exempt Payment Types"),t,o);var r=i("<div class='v-box'></div>");t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"exempt_payments_include_pay_stub_entry_account"}),n=this.putInputToInsideFormItem(t,i.i18n._("Include")),r.append(n),r.append("<div class='clear-both-div'></div>");var l=Global.loadWidgetByName(FormItemType.AWESOME_BOX);l.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"exempt_payments_exclude_pay_stub_entry_account"}),n=this.putInputToInsideFormItem(l,i.i18n._("Exclude")),r.append(n),this.addEditFieldToColumn(i.i18n._(`Exempt Payments (Line 4)
( Must already be excluded in Tax/Deduction settings )`),[t,l],o,"",r,!1,!0),t=Global.loadWidgetByName(FormItemType.CHECKBOX),t.TCheckbox({field:"line_9"}),this.addEditFieldToColumn(i.i18n._("Were ALL taxable FUTA wages excluded from State UI? (Line 9)"),t,o),t=Global.loadWidgetByName(FormItemType.TEXT_INPUT),t.TTextInput({field:"line_10"}),this.addEditFieldToColumn(i.i18n._("Wages Excluded From State Unemployement Tax (Line 10)"),t,o),t=Global.loadWidgetByName(FormItemType.TEXT_INPUT),t.TTextInput({field:"tax_deposited"}),this.addEditFieldToColumn(i.i18n._("FUTA Tax Deposited for the Year (Line 13)")+" $",t,o)}getFormSetupData(){var e={};return e.exempt_payments={include_pay_stub_entry_account:this.current_edit_record.exempt_payments_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.exempt_payments_exclude_pay_stub_entry_account},e.return_type=this.current_edit_record.return_type,e.exempt_payment=this.current_edit_record.exempt_payment,e.line_9=this.current_edit_record.line_9,e.line_10=this.current_edit_record.line_10,e.tax_deposited=this.current_edit_record.tax_deposited,e}setFormSetupData(e){if(!e)this.show_empty_message=!0;else{let _=this.processFormSetupDataAndAddToBatch(e,[{data:a.get(e,"exempt_payments"),field_key:"exempt_payments",api:this.api_paystub,api_method:"getPayStubEntryAccount"},{data:a.get(e,"return_type"),field_key:"return_type",api:null},{data:a.get(e,"exempt_payment"),field_key:"exempt_payment",api:null},{data:a.get(e,"line_9"),field_key:"line_9",api:null},{data:a.get(e,"line_10"),field_key:"line_10",api:null},{data:a.get(e,"line_11"),field_key:"line_11",api:null},{data:a.get(e,"tax_deposited"),field_key:"tax_deposited",api:null}]);this.getBatchedRealFormDataFromAPI(_)}}}export{m as Form940ReportViewController};
//# sourceMappingURL=Form940ReportViewController-CZh6ZT0l.bundle.js.map
