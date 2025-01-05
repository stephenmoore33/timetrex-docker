import{_ as l,j as t}from"./vendor-tTApdY0Y.bundle.js";class u extends BaseViewController{constructor(e={}){l.defaults(e,{el:"#currency_view_container",status_array:null,iso_codes_array:null,round_decimal_places_array:null,sub_currency_rate_view_controller:null}),super(e)}init(e){this.edit_view_tpl="CurrencyEditView.html",this.permission_id="currency",this.viewId="Currency",this.script_name="CurrencyView",this.table_name_key="currency",this.context_menu_name=t.i18n._("Currencies"),this.navigation_label=t.i18n._("Currency"),this.api=TTAPI.APICurrency,this.render(),this.buildContextMenu(),this.initData()}initOptions(){var e=this;this.initDropDownOption("status"),this.initDropDownOption("round_decimal_places","round_decimal_places"),this.api.getISOCodesArray("",!1,!1,{onResult:function(r){r=r.getResult(),r=Global.buildRecordArray(r),e.basic_search_field_ui_dic.iso_code.setSourceData(r),e.iso_codes_array=r}})}buildEditViewUI(){super.buildEditViewUI();var e=this,r={tab_currency:{label:t.i18n._("Currency")},tab_rates:{label:t.i18n._("Rates"),init_callback:"initSubCurrencyRateView",display_on_mass_edit:!1},tab_audit:!0};this.setTabModel(r),this.navigation.AComboBox({api_class:TTAPI.APICurrency,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_currency",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var _=this.edit_view_tab.find("#tab_currency"),a=_.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(a);var i,o;i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"status_id"}),i.setSourceData(e.status_array),this.addEditFieldToColumn(t.i18n._("Status"),i,a,""),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"iso_code"}),i.setSourceData(e.iso_codes_array),this.addEditFieldToColumn(t.i18n._("ISO Currency"),i,a),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn(t.i18n._("Name"),i,a),i.parent().width("45%"),i=Global.loadWidgetByName(FormItemType.CHECKBOX),i.TCheckbox({field:"is_base"}),this.addEditFieldToColumn(t.i18n._("Base Currency"),i,a),i=Global.loadWidgetByName(FormItemType.TEXT_INPUT),i.TTextInput({field:"conversion_rate",width:114}),o=t("<div class=''></div>");var n=t("<span id='conversion_rate_clarification_box'></span>");o.append(i),o.append(n),this.addEditFieldToColumn(t.i18n._("Conversion Rate"),[i],a,"",o,!1,!0),i=Global.loadWidgetByName(FormItemType.CHECKBOX),i.TCheckbox({field:"is_default"}),this.addEditFieldToColumn(t.i18n._("Default Currency"),i,a),i=Global.loadWidgetByName(FormItemType.CHECKBOX),i.TCheckbox({field:"auto_update"}),this.addEditFieldToColumn(t.i18n._("Auto Update"),i,a),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"round_decimal_places"}),i.setSourceData(e.round_decimal_places_array),this.addEditFieldToColumn(t.i18n._("Decimal Places"),i,a,"")}removeEditView(){super.removeEditView(),this.sub_currency_rate_view_controller=null}initSubCurrencyRateView(){var e=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_currency_rate_view_controller){this.sub_currency_rate_view_controller.buildContextMenu(!0),this.sub_currency_rate_view_controller.setDefaultMenu(),e.sub_currency_rate_view_controller.parent_key="currency_id",e.sub_currency_rate_view_controller.parent_value=e.current_edit_record.id,e.sub_currency_rate_view_controller.parent_edit_record=e.current_edit_record,e.sub_currency_rate_view_controller.initData();return}Global.loadScript("views/company/currency/CurrencyRateViewController.js",function(){var a=e.edit_view_tab.find("#tab_rates"),i=a.find(".first-column-sub-view");Global.trackView("SubCurrencyRateView",LocalCacheData.current_doing_context_action),CurrencyRateViewController.loadSubView(i,r,_)});function r(){}function _(a){e.sub_currency_rate_view_controller=a,e.sub_currency_rate_view_controller.parent_key="currency_id",e.sub_currency_rate_view_controller.parent_value=e.current_edit_record.id,e.sub_currency_rate_view_controller.parent_edit_record=e.current_edit_record,e.sub_currency_rate_view_controller.parent_view_controller=e,e.sub_currency_rate_view_controller.initData()}}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:t.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:t.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:t.i18n._("ISO Currency"),in_column:1,field:"iso_code",multiple:!0,basic_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:t.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:t.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}onFormItemChange(e,r){e.getField()=="conversion_rate"&&this.setConversionRateExampleText(e.getValue(),this.edit_view_ui_dic.iso_code.getValue()),super.onFormItemChange(e,r)}initEditView(e,r){super.initEditView(),this.setConversionRateExampleText(this.edit_view_ui_dic.conversion_rate.getValue(),this.edit_view_ui_dic.iso_code.getValue())}}export{u as CurrencyViewController};
//# sourceMappingURL=CurrencyViewController-DmdRCgMp.bundle.js.map
