const __vite__fileDeps=["./main_ui-CaF8IcSp.css","./leaflet-timetrex-DIHfnHxo.css"],__vite__mapDeps=i=>i.map(i=>__vite__fileDeps[i]);
import{_ as h}from"./main_ui-z76rxVqJ.entry.bundle.js";import{_ as u,j as i}from"./vendor-tTApdY0Y.bundle.js";class y extends BaseViewController{constructor(t={}){u.defaults(t,{el:"#client_contact_view_container",status_array:null,type_array:null,country_array:null,province_array:null,e_province_array:null,district_array:null,company_api:null,area_policy_api:null,sub_document_view_controller:null,document_object_type_id:null}),super(t)}init(t){this.edit_view_tpl="ClientContactEditView.html",this.permission_id="client",this.viewId="ClientContact",this.script_name="ClientContactView",this.table_name_key="client_contact",this.document_object_type_id=85,this.context_menu_name=i.i18n._("Client Contacts"),this.navigation_label=i.i18n._("Client Contact"),this.api=TTAPI.APIClientContact,this.company_api=TTAPI.APICompany,this.area_policy_api=TTAPI.APIAreaPolicy,this.render(),this.buildContextMenu(),this.sub_view_mode||this.initData()}initOptions(){var t=[{option_name:"status",api:this.api},{option_name:"type",api:this.api},{option_name:"country",field_name:"country",api:this.company_api}];this.initDropDownOptions(t)}getFilterColumnsFromDisplayColumns(){var t={};t.client_id=!0,this._getFilterColumnsFromDisplayColumns(t,!0)}getCustomContextMenuModel(){var t={exclude:[],include:[{label:i.i18n._("Map"),id:"map",menu_align:"right",group:"other",vue_icon:"tticon tticon-map_black_24dp",sort_order:8e3}]};return this.sub_view_mode?t.exclude.push("export_excel"):t.include.push({label:i.i18n._("Jump To"),id:"jump_to_header",menu_align:"right",action_group:"jump_to",action_group_header:!0,permission_result:!1,sort_order:9050},{label:i.i18n._("Edit Client"),id:"edit_client",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:i.i18n._("Invoices"),id:"invoice",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:i.i18n._("Transactions"),id:"transaction",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:i.i18n._("Payment Methods"),id:"payment_method",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},"export_excel"),t.include.push({label:"",id:"other_header",menu_align:"right",action_group:"other",action_group_header:!0,vue_icon:"tticon tticon-more_vert_black_24dp"},{label:i.i18n._("Add Company"),id:"add_company",menu_align:"right",action_group:"other"}),t}setDefaultMenuAddCompanyIcon(t,a){(!PermissionManager.validate("company","enabled")||!PermissionManager.validate("company","add"))&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,t.id,!1),a===1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!1)}setCustomDefaultMenuIcon(t,a,o){switch(t){case"edit_client":this.setDefaultMenuEditClientIcon(a,o);break;case"add_company":this.setDefaultMenuAddCompanyIcon(a,o);break}}setCustomEditMenuIcon(t,a){switch(t){case"edit_client":this.setEditMenuEditClientIcon(a);break;case"add_company":this.setEditMenuAddCompanyIcon(a);break}}setEditMenuAddCompanyIcon(t,a){this.current_edit_record&&this.current_edit_record.id?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!1)}setEditMenuEditClientIcon(t,a){ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!0),this.is_mass_editing&&ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!1)}setDefaultMenuEditClientIcon(t,a,o){a===1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,t.id,!1)}onMapClick(){if(Global.getProductEdition()>=15){this.is_viewing=!1,ProgressBar.showProgressBar();var t={filter_columns:{id:!0,client:!0,first_name:!0,last_name:!0,type:!0,address1:!0,address2:!0,city:!0,province:!0,country:!0,postal_code:!0,latitude:!0,longitude:!0}},a=this.getGridSelectIdArray();t.filter_data=Global.convertLayoutFilterToAPIFilter(this.select_layout),a.length>0&&(t.filter_data.id=a);var o=this.api.getClientContact(t,{async:!1}).getResult();this.is_mass_editing||h(()=>import("./leaflet-timetrex-D9Z7_Af6.bundle.js"),__vite__mapDeps([0,1]),import.meta.url).then(l=>{var e=l.TTConvertMapData.processBasicFromGenericViewController(o);IndexViewController.openEditView(this,"Map",e)}).catch(Global.importErrorHandler)}}onCustomContextClick(t){switch(t){case"edit_client":case"invoice":case"transaction":case"payment_method":this.onNavigationClick(t);break;case"add_company":this.onAddCompanyClick();break;case"map":this.onMapClick();break}}onAddCompanyClick(){var t=this,a="";this.edit_view?a=this.current_edit_record.id:a=this.getGridSelectIdArray()[0],this.api.addCompany(a,{onResult:function(o){o.isValid()?t.search():TAlertManager.showErrorAlert(o)}})}onNavigationClick(t){var a=this,o,l={filter_data:{}},e=[];switch(a.edit_view&&a.current_edit_record.id?e.push(a.current_edit_record.client_id):(o=this.getGridSelectIdArray(),i.each(o,function(n,s){var r=a.getRecordFromGridById(s);e.push(r.client_id)})),l.filter_data.client_id=e,t){case"edit_client":e.length>0&&IndexViewController.openEditView(this,"Client",e[0]);break;case"invoice":Global.addViewTab(a.viewId,i.i18n._("Client Contacts"),window.location.href),IndexViewController.goToView("Invoice",l);break;case"transaction":Global.addViewTab(a.viewId,i.i18n._("Client Contacts"),window.location.href),IndexViewController.goToView("InvoiceTransaction",l);break;case"payment_method":Global.addViewTab(a.viewId,i.i18n._("Client Contacts"),window.location.href),IndexViewController.goToView("ClientPayment",l);break}}buildEditViewUI(){super.buildEditViewUI();var t=this,a={tab_contact_information:{label:i.i18n._("Contact Information"),is_multi_column:!0},tab_policy:{label:i.i18n._("Policy")},tab_portal:{label:i.i18n._("Portal")},tab_attachment:!0,tab_audit:!0};this.setTabModel(a),this.navigation.AComboBox({api_class:TTAPI.APIUserTitle,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_client_contact",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var o=this.edit_view_tab.find("#tab_contact_information"),l=o.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(l);var e=Global.loadWidgetByName(FormItemType.AWESOME_BOX);e.AComboBox({api_class:TTAPI.APIClient,allow_multiple_selection:!1,layout_name:"global_client",show_search_inputs:!0,set_empty:!0,field:"client_id"}),this.addEditFieldToColumn(i.i18n._("Client"),e,l,""),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"status_id"}),e.setSourceData(t.status_array),this.addEditFieldToColumn(i.i18n._("Status"),e,l),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"type_id"}),e.setSourceData(t.type_array),this.addEditFieldToColumn(i.i18n._("Type"),e,l),e=Global.loadWidgetByName(FormItemType.CHECKBOX),e.TCheckbox({field:"is_default"}),this.addEditFieldToColumn(i.i18n._("Default"),e,l),e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APICurrency,allow_multiple_selection:!1,layout_name:"global_currency",show_search_inputs:!0,field:"currency_id"}),this.addEditFieldToColumn(i.i18n._("Currency"),e,l),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"first_name",width:150}),this.addEditFieldToColumn(i.i18n._("First Name"),e,l),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"last_name",width:150}),this.addEditFieldToColumn(i.i18n._("Last Name"),e,l),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"address1",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Address(Line 1)"),e,l),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"address2",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Address(Line 2)"),e,l),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"address3",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Address(Line 3)"),e,l),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.TAG_INPUT),e.TTagInput({field:"tag",object_type_id:810}),this.addEditFieldToColumn(i.i18n._("Tags"),e,l,"",null,null,!0);var n=o.find(".second-column");this.edit_view_tabs[0].push(n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"city",width:150}),this.addEditFieldToColumn(i.i18n._("City"),e,n,""),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"country",set_empty:!0}),e.setSourceData(t.country_array),this.addEditFieldToColumn(i.i18n._("Country"),e,n),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"province"}),e.setSourceData([]),this.addEditFieldToColumn(i.i18n._("Province / State"),e,n),e.change(i.proxy(function(){var m=this.edit_view_ui_dic.country.getValue(),p=this.edit_view_ui_dic.province.getValue();this.setDistrict(m,p)},this)),e=Global.loadWidgetByName(FormItemType.COMBO_BOX),e.TComboBox({field:"invoice_district_id",set_empty:!0}),e.setSourceData([]),this.addEditFieldToColumn(i.i18n._("District"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"postal_code",width:150}),this.addEditFieldToColumn(i.i18n._("Postal/ZIP Code"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"work_phone",width:150}),this.addEditFieldToColumn(i.i18n._("Work Phone"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"mobile_phone",width:200}),this.addEditFieldToColumn(i.i18n._("Mobile Phone"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"fax_phone",width:150}),this.addEditFieldToColumn(i.i18n._("Fax"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"email",width:220}),this.addEditFieldToColumn(i.i18n._("Email"),e,n),e=Global.loadWidgetByName(FormItemType.TEXT_AREA),e.TTextArea({field:"note",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Note"),e,n,"",null,null,!0),e.parent().width("45%");var s=this.edit_view_tab.find("#tab_policy"),r=s.find(".first-column");this.edit_view_tabs[1]=[],this.edit_view_tabs[1].push(r),e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APITaxPolicy,allow_multiple_selection:!0,layout_name:"global_tax_policy",show_search_inputs:!0,set_empty:!0,field:"include_tax_policy"}),this.addEditFieldToColumn(i.i18n._("Include Tax Policy"),e,r,""),e=Global.loadWidgetByName(FormItemType.AWESOME_BOX),e.AComboBox({api_class:TTAPI.APITaxPolicy,allow_multiple_selection:!0,layout_name:"global_tax_policy",show_search_inputs:!0,set_empty:!0,field:"exclude_tax_policy"}),this.addEditFieldToColumn(i.i18n._("Exclude Tax Policy"),e,r,"");var c=this.edit_view_tab.find("#tab_portal"),d=c.find(".first-column");this.edit_view_tabs[2]=[],this.edit_view_tabs[2].push(d),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"user_name",width:150}),this.addEditFieldToColumn(i.i18n._("User Name"),e,d,""),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"password",width:150}),this.addEditFieldToColumn(i.i18n._("Password"),e,d),e=Global.loadWidgetByName(FormItemType.TEXT_INPUT),e.TTextInput({field:"password2",width:150}),this.addEditFieldToColumn(i.i18n._("Password (Confirm)"),e,d,"")}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:i.i18n._("Client"),in_column:1,field:"client_id",layout_name:"global_client",api_class:TTAPI.APIClient,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Type"),in_column:1,field:"type_id",multiple:!0,basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("First Name"),in_column:1,field:"first_name",multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Last Name"),in_column:1,field:"last_name",multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Tags"),field:"tag",basic_search:!0,adv_search:!0,in_column:1,object_type_id:810,form_item_type:FormItemType.TAG_INPUT}),new SearchField({label:i.i18n._("City"),field:"city",basic_search:!1,adv_search:!0,in_column:2,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Country"),in_column:2,field:"country",multiple:!0,basic_search:!1,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.COMBO_BOX}),new SearchField({label:i.i18n._("Province/State"),in_column:2,field:"province",multiple:!0,basic_search:!1,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Phone"),field:"any_phone",basic_search:!0,adv_search:!0,in_column:2,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Email"),field:"email",basic_search:!0,adv_search:!0,in_column:2,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX})]}setSelectLayout(){this.sub_view_mode?super.setSelectLayout(["client"]):super.setSelectLayout()}onFormItemChange(t,a){this.setIsChanged(t),this.setMassEditingFieldsWhenFormChange(t);var o=t.getField();switch(this.current_edit_record[o]=t.getValue(),o){case"country":var l=this.edit_view_ui_dic.province;l.setValue(null);break;case"province":l=this.edit_view_ui_dic.invoice_district_id,l.setValue(null);break}if(o==="country"){this.onCountryChange();return}a||this.validate()}onGridDblClickRow(){this.onEditClick()}setCurrentEditRecordData(){for(var t in this.current_edit_record){var a=this.edit_view_ui_dic[t];if(Global.isSet(a))switch(t){case"country":this.setCountryValue(a,t);break;case"province":this.setDistrict(this.current_edit_record.country,this.current_edit_record[t]),a.setValue(this.current_edit_record[t]);break;default:a.setValue(this.current_edit_record[t]);break}}this.collectUIDataToCurrentEditRecord(),this.setEditViewDataDone()}onSetSearchFilterFinished(){if(this.search_panel.getSelectTabIndex()===1){var t=this.adv_search_field_ui_dic.country,a=t.getValue();this.setProvince(a)}}setProvince(t,a){var o=this;!t||t==="-1"||t==="0"?(o.province_array=[],this.adv_search_field_ui_dic.province.setSourceData([])):this.company_api.getOptions("province",t,{onResult:function(l){l=l.getResult(),l||(l=[]),o.province_array=Global.buildRecordArray(l),o.adv_search_field_ui_dic.province.setSourceData(o.province_array)}})}eSetProvince(t,a){var o=this,l=o.edit_view_ui_dic.province;!t||t==="-1"||t==="0"?(o.e_province_array=[],l.setSourceData([])):this.company_api.getOptions("province",t,{onResult:function(e){e=e.getResult(),e||(e=[]),o.e_province_array=Global.buildRecordArray(e),a&&o.e_province_array.length>0&&(o.current_edit_record.province=o.e_province_array[0].value,l.setValue(o.current_edit_record.province)),l.setSourceData(o.e_province_array)}})}setDistrict(t,a){var o=this,l=o.edit_view_ui_dic.invoice_district_id;!Global.isSet(t)||t==="-1"||t==="0"||!Global.isSet(a)||a==="-1"||a==="0"?(o.district_array=[],l.setSourceData([])):this.area_policy_api.getDistrictOptions(t,a,{onResult:function(e){e=e.getResult(),e||(e=[]),o.district_array=Global.buildRecordArray(e),l.setSourceData(o.district_array)}})}onBuildAdvUIFinished(){this.adv_search_field_ui_dic.country.change(i.proxy(function(){var t=this.adv_search_field_ui_dic.country,a=t.getValue();this.setProvince(a),this.adv_search_field_ui_dic.province.setValue(null)},this))}removeEditView(){super.removeEditView(),this.sub_client_contact_view_controller=null}}y.loadSubView=function(_,t,a){Global.loadViewSource("ClientContact","SubClientContactView.html",function(o){var l={},e=u.template(o);Global.isSet(t)&&t(),Global.isSet(_)&&(_.html(e(l)),Global.isSet(a)&&a(sub_client_contact_view_controller))})};export{y as ClientContactViewController};
//# sourceMappingURL=ClientContactViewController-BwG1JHOL.bundle.js.map
