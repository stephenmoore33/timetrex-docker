import{_ as n,j as t}from"./vendor-tTApdY0Y.bundle.js";class s extends ReportBaseViewController{constructor(e={}){n.defaults(e,{}),super(e)}initReport(e){this.script_name="RemittanceSummaryReport",this.viewId="RemittanceSummaryReport",this.context_menu_name=t.i18n._("Remittance Summary"),this.navigation_label=t.i18n._("Saved Report"),this.view_file="RemittanceSummaryReportView.html",this.api=TTAPI.APIRemittanceSummaryReport,this.api_paystub=TTAPI.APIPayStubEntryAccount,this.include_form_setup=!0}getCustomContextMenuModel(){var e={groups:{form:{label:t.i18n._("Form"),id:this.viewId+"Form"}},exclude:[],include:[{label:t.i18n._("Form"),id:"view_print",action_group_header:!0,action_group:"view_form",group:"form",menu_align:"right",icon:"view-35x35.png",type:2},{label:t.i18n._("Save Setup"),id:"save_setup",action_group:"view_form",group:"form",menu_align:"right"}]};return e}buildFormSetupUI(){var e=this.edit_view_tab.find("#tab_form_setup"),u=e.find(".first-column");this.edit_view_tabs[3]=[],this.edit_view_tabs[3].push(u);var _,l,c;_=Global.loadWidgetByName(FormItemType.TEXT_INPUT),_.TTextInput({field:"this_payment"}),l=t("<div class='widget-h-box'></div>"),c=t("<span class='widget-right-label'>"+t.i18n._("(Leave blank to not override)")+"</span>"),l.append(_),l.append(c),this.addEditFieldToColumn(t.i18n._("This Payment (Override)"),_,u,"",l);var a=t("<div class='v-box'></div>"),_=Global.loadWidgetByName(FormItemType.AWESOME_BOX);_.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"gross_payroll_include_pay_stub_entry_account"});var o=this.putInputToInsideFormItem(_,t.i18n._("Include"));a.append(o),a.append("<div class='clear-both-div'></div>");var i=Global.loadWidgetByName(FormItemType.AWESOME_BOX);i.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"gross_payroll_exclude_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(i,t.i18n._("Exclude")),a.append(o),this.addEditFieldToColumn(t.i18n._("Gross Payroll")+`
*`+t.i18n._("Must Match T4 Box 14"),[_,i],u,"",a,!1,!0),a=t("<div class='v-box'></div>"),_=Global.loadWidgetByName(FormItemType.AWESOME_BOX),_.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"ei_include_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(_,t.i18n._("Include")),a.append(o),a.append("<div class='clear-both-div'></div>"),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"ei_exclude_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(i,t.i18n._("Exclude")),a.append(o),this.addEditFieldToColumn(t.i18n._("Employee/Employer EI"),[_,i],u,"",a,!1,!0),a=t("<div class='v-box'></div>"),_=Global.loadWidgetByName(FormItemType.AWESOME_BOX),_.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"cpp_include_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(_,t.i18n._("Include")),a.append(o),a.append("<div class='clear-both-div'></div>"),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"cpp_exclude_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(i,t.i18n._("Exclude")),a.append(o),this.addEditFieldToColumn(t.i18n._("Employee/Employer CPP"),[_,i],u,"",a,!1,!0),a=t("<div class='v-box'></div>"),_=Global.loadWidgetByName(FormItemType.AWESOME_BOX),_.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"cpp2_include_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(_,t.i18n._("Include")),a.append(o),a.append("<div class='clear-both-div'></div>"),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"cpp2_exclude_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(i,t.i18n._("Exclude")),a.append(o),this.addEditFieldToColumn(t.i18n._("Employee/Employer CPP2"),[_,i],u,"",a,!1,!0),a=t("<div class='v-box'></div>"),_=Global.loadWidgetByName(FormItemType.AWESOME_BOX),_.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"tax_include_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(_,t.i18n._("Include")),a.append(o),a.append("<div class='clear-both-div'></div>"),i=Global.loadWidgetByName(FormItemType.AWESOME_BOX),i.AComboBox({api_class:TTAPI.APIPayStubEntryAccount,allow_multiple_selection:!0,layout_name:"global_PayStubAccount",show_search_inputs:!0,set_empty:!0,field:"tax_exclude_pay_stub_entry_account"}),o=this.putInputToInsideFormItem(i,t.i18n._("Exclude")),a.append(o),this.addEditFieldToColumn(t.i18n._("Federal/Provincial Income Tax"),[_,i],u,"",a,!1,!0)}getFormSetupData(){var e={};return e.this_payment=this.current_edit_record.this_payment,e.gross_payroll={include_pay_stub_entry_account:this.current_edit_record.gross_payroll_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.gross_payroll_exclude_pay_stub_entry_account},e.cpp={include_pay_stub_entry_account:this.current_edit_record.cpp_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.cpp_exclude_pay_stub_entry_account},e.cpp2={include_pay_stub_entry_account:this.current_edit_record.cpp2_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.cpp2_exclude_pay_stub_entry_account},e.ei={include_pay_stub_entry_account:this.current_edit_record.ei_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.ei_exclude_pay_stub_entry_account},e.tax={include_pay_stub_entry_account:this.current_edit_record.tax_include_pay_stub_entry_account,exclude_pay_stub_entry_account:this.current_edit_record.tax_exclude_pay_stub_entry_account},e}setFormSetupData(e){if(!e)this.show_empty_message=!0;else{let u=this.processFormSetupDataAndAddToBatch(e,[{data:n.get(e,"this_payment"),field_key:"this_payment",api:null},{data:n.get(e,"gross_payroll"),field_key:"gross_payroll",api:this.api_paystub,api_method:"getPayStubEntryAccount"},{data:n.get(e,"cpp"),field_key:"cpp",api:this.api_paystub,api_method:"getPayStubEntryAccount"},{data:n.get(e,"cpp2"),field_key:"cpp2",api:this.api_paystub,api_method:"getPayStubEntryAccount"},{data:n.get(e,"ei"),field_key:"ei",api:this.api_paystub,api_method:"getPayStubEntryAccount"},{data:n.get(e,"tax"),field_key:"tax",api:this.api_paystub,api_method:"getPayStubEntryAccount"}]);this.getBatchedRealFormDataFromAPI(u)}}}export{s as RemittanceSummaryReportViewController};
//# sourceMappingURL=RemittanceSummaryReportViewController-PwgkLS5C.bundle.js.map
