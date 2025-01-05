import{_ as l,j as n}from"./vendor-tTApdY0Y.bundle.js";class s extends BaseViewController{constructor(e={}){l.defaults(e,{el:"#client_view_container",status_array:null,industry_array:null,client_group_array:null,client_group_api:null,sub_client_contact_view_controller:null,sub_client_payment_view_controller:null,sub_document_view_controller:null,sub_transaction_view_controller:null,sub_invoice_view_controller:null,document_object_type_id:null}),super(e)}init(e){this.edit_view_tpl="ClientEditView.html",this.permission_id="client",this.viewId="Client",this.script_name="ClientView",this.table_name_key="client",this.context_menu_name=n.i18n._("Clients"),this.navigation_label=n.i18n._("Client"),this.document_object_type_id=80,this.api=TTAPI.APIClient,this.client_group_api=TTAPI.APIClientGroup,this.render(),this.buildContextMenu(),this.initData()}removeEditView(){super.removeEditView(),this.sub_client_contact_view_controller=null,this.sub_invoice_view_controller=null,this.sub_client_payment_view_controller=null,this.sub_transaction_view_controller=null}initOptions(e){var i=this;this.initDropDownOption("industry"),this.initDropDownOption("status","status_id",this.api,function(){i.client_group_api.getClientGroup("",!1,!1,{onResult:function(o){o=o.getResult(),o=Global.buildTreeRecord(o),i.edit_only_mode||!i.sub_view_mode&&i.basic_search_field_ui_dic.group_id&&(i.basic_search_field_ui_dic.group_id.setSourceData(o),i.adv_search_field_ui_dic.group_id.setSourceData(o)),i.client_group_array=o,e&&e()}})})}getClientData(e,i){var o={};o.filter_data={},o.filter_data.id=[e],this.api["get"+this.api.key_name](o,{onResult:function(r){var t=r.getResult();t||(t=[]),t=t[0],i(t)}})}openEditView(e){Global.setUINotready(),TTPromise.add("init","init"),TTPromise.wait();var i=this;i.edit_only_mode?i.initOptions(function(o){i.edit_view||i.initEditViewUI(i.viewId,i.edit_view_tpl),i.getClientData(e,function(r){i.current_edit_record=r,i.initEditView()})}):this.edit_view||this.initEditViewUI(this.viewId,this.edit_view_tpl)}getCustomContextMenuModel(){var e={exclude:["copy"],include:[{label:n.i18n._("Sign In"),id:"login",menu_align:"right",group:"navigation",vue_icon:"tticon tticon-login_black_24dp"},{label:n.i18n._("Import"),id:"import_icon",menu_align:"right",action_group:"import_export",group:"other",vue_icon:"tticon tticon-file_download_black_24dp",sort_order:9001},{label:n.i18n._("Jump To"),id:"jump_to_header",menu_align:"right",action_group:"jump_to",action_group_header:!0,permission_result:!1,sort_order:9050},{label:n.i18n._("Client Contacts"),id:"client_contact",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:n.i18n._("Invoices"),id:"invoice",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:n.i18n._("Transactions"),id:"transaction",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050},{label:n.i18n._("Payment Methods"),id:"payment_method",menu_align:"right",action_group:"jump_to",group:"navigation",sort_order:9050}]};return e}onCustomContextClick(e){switch(e){case"import_icon":IndexViewController.openWizard("ImportCSVWizard","Client",function(){$this.search()});break;case"client_contact":case"invoice":case"transaction":case"payment_method":case"in_out":this.onNavigationClick(e);break;case"login":this.onLoginClick();break}}onLoginClick(){var e=this,i={};i.client_id=this.getGridSelectIdArray()[0],LocalCacheData.extra_filter_for_next_open_view={},LocalCacheData.extra_filter_for_next_open_view.filter_data=i,IndexViewController.openWizard("LoginUserWizard",e.viewId,function(o){Global.NewSession(o,e.getGridSelectIdArray()[0])})}onNavigationClick(e){var i=this,o={};if(o.filter_data={},o.filter_data.client_id=[],i.edit_view&&i.current_edit_record.id)o.filter_data.client_id.push(i.current_edit_record.id);else{var r=this.getGridSelectIdArray();n.each(r,function(t,a){o.filter_data.client_id.push(a)})}switch(e!="in_out"&&Global.addViewTab(i.viewId,n.i18n._("Clients"),window.location.href),e){case"in_out":IndexViewController.openEditView(LocalCacheData.current_open_primary_controller,"InOut");break;case"client_contact":IndexViewController.goToView("ClientContact",o);break;case"invoice":IndexViewController.goToView("Invoice",o);break;case"transaction":IndexViewController.goToView("InvoiceTransaction",o);break;case"payment_method":IndexViewController.goToView("ClientPayment",o);break}}buildEditViewUI(){super.buildEditViewUI();var e=this,i={tab_client:{label:n.i18n._("Client")},tab_client_contacts:{label:n.i18n._("Client Contacts"),init_callback:"initSubClientContactView",display_on_mass_edit:!1},tab_payment_methods:{label:n.i18n._("Payment Methods"),init_callback:"initSubClientPaymentView",display_on_mass_edit:!1},tab_invoices:{label:n.i18n._("Invoices"),init_callback:"initSubInvoiceView",display_on_mass_edit:!1},tab_transactions:{label:n.i18n._("Transactions"),init_callback:"initSubTransactionView",display_on_mass_edit:!1},tab_attachment:!0,tab_audit:!0};this.setTabModel(i),this.edit_only_mode||(this.navigation.AComboBox({api_class:TTAPI.APIClient,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_client",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation());var o=this.edit_view_tab.find("#tab_client"),r=o.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(r);var t=Global.loadWidgetByName(FormItemType.AWESOME_BOX);t.AComboBox({api_class:TTAPI.APIClient,allow_multiple_selection:!1,layout_name:"global_client",show_search_inputs:!0,set_empty:!0,field:"parent_id"}),this.addEditFieldToColumn(n.i18n._("Parent"),t,r,""),t=Global.loadWidgetByName(FormItemType.COMBO_BOX),t.TComboBox({field:"status_id"}),t.setSourceData(e.status_array),this.addEditFieldToColumn(n.i18n._("Status"),t,r),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({tree_mode:!0,allow_multiple_selection:!1,layout_name:"global_tree_column",set_empty:!0,field:"group_id"}),t.setSourceData(e.client_group_array),this.addEditFieldToColumn(n.i18n._("Group"),t,r),t=Global.loadWidgetByName(FormItemType.TEXT_INPUT),t.TTextInput({field:"company_name",width:"100%"}),this.addEditFieldToColumn(n.i18n._("Client Name"),t,r),t.parent().width("45%"),t=Global.loadWidgetByName(FormItemType.TEXT_INPUT),t.TTextInput({field:"company_dba_name",width:430}),this.addEditFieldToColumn(n.i18n._("Doing Business As"),t,r),t=Global.loadWidgetByName(FormItemType.COMBO_BOX),t.TComboBox({field:"industry_id"}),t.setSourceData(e.industry_array),this.addEditFieldToColumn(n.i18n._("Industry"),t,r),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!1,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"sales_contact_id"}),this.addEditFieldToColumn(n.i18n._("Sales Contact"),t,r),t=Global.loadWidgetByName(FormItemType.AWESOME_BOX),t.AComboBox({api_class:TTAPI.APIUser,allow_multiple_selection:!1,layout_name:"global_user",show_search_inputs:!0,set_empty:!0,field:"support_contact_id"}),this.addEditFieldToColumn(n.i18n._("Support Contact"),t,r),t=Global.loadWidgetByName(FormItemType.TEXT_INPUT),t.TTextInput({field:"website",width:360}),this.addEditFieldToColumn(n.i18n._("Website"),t,r),t=Global.loadWidgetByName(FormItemType.TEXT_AREA),t.TTextArea({field:"note",width:"100%"}),this.addEditFieldToColumn(n.i18n._("Note"),t,r,"",null,null,!0),t.parent().width("45%"),t=Global.loadWidgetByName(FormItemType.TAG_INPUT),t.TTagInput({field:"tag",object_type_id:800}),this.addEditFieldToColumn(n.i18n._("Tags"),t,r,"last",null,null,!0)}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:n.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Group"),in_column:1,multiple:!0,field:"group_id",layout_name:"global_tree_column",tree_mode:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Company Name"),in_column:1,field:"company_name",multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:n.i18n._("Website"),in_column:1,field:"website",multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:n.i18n._("Note"),in_column:1,field:"note",multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:n.i18n._("Tags"),field:"tag",basic_search:!0,adv_search:!0,in_column:2,object_type_id:800,form_item_type:FormItemType.TAG_INPUT}),new SearchField({label:n.i18n._("Sales Contact"),in_column:2,field:"sales_contact_id",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Support Contact"),in_column:2,field:"support_contact_id",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:n.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX})]}setCustomEditMenuIcon(e,i){switch(e){case"login":this.setEditMenuLoginIcon(i);break;case"import_icon":case"client_contact":case"invoice":case"transaction":case"payment_method":this.setNavigationMenuIcons(i);break}}setNavigationMenuIcons(e){this.edit_only_mode&&ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuLoginIcon(e){if(!PermissionManager.validate("company","login_other_user")){ContextMenuManager.hideMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1);return}ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}initSubClientContactView(){var e=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_client_contact_view_controller){this.sub_client_contact_view_controller.buildContextMenu(!0),this.sub_client_contact_view_controller.setDefaultMenu(),e.sub_client_contact_view_controller.parent_value=e.current_edit_record.id,e.sub_client_contact_view_controller.parent_edit_record=e.current_edit_record,e.sub_client_contact_view_controller.initData();return}Global.loadScript("views/invoice/client_contact/ClientContactViewController.js",function(){var r=e.edit_view_tab.find("#tab_client_contacts"),t=r.find(".first-column-sub-view");Global.trackView("SubClientContactView"),ClientContactViewController.loadSubView(t,i,o)});function i(){}function o(r){e.sub_client_contact_view_controller=r,e.sub_client_contact_view_controller.parent_key="client_id",e.sub_client_contact_view_controller.parent_value=e.current_edit_record.id,e.sub_client_contact_view_controller.parent_edit_record=e.current_edit_record,e.sub_client_contact_view_controller.parent_view_controller=e,e.sub_client_contact_view_controller.postInit=function(){this.initData()}}}initSubClientPaymentView(){var e=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_client_payment_view_controller){this.sub_client_payment_view_controller.buildContextMenu(!0),this.sub_client_payment_view_controller.setDefaultMenu(),e.sub_client_payment_view_controller.parent_value=e.current_edit_record.id,e.sub_client_payment_view_controller.parent_edit_record=e.current_edit_record,e.sub_client_payment_view_controller.initData();return}Global.loadScript("views/invoice/client_payment/ClientPaymentViewController.js",function(){var r=e.edit_view_tab.find("#tab_payment_methods"),t=r.find(".first-column-sub-view");Global.trackView("SubClientPaymentView"),ClientPaymentViewController.loadSubView(t,i,o)});function i(){}function o(r){e.sub_client_payment_view_controller=r,e.sub_client_payment_view_controller.parent_key="client_id",e.sub_client_payment_view_controller.parent_value=e.current_edit_record.id,e.sub_client_payment_view_controller.parent_edit_record=e.current_edit_record,e.sub_client_payment_view_controller.parent_view_controller=e,e.sub_client_payment_view_controller.postInit=function(){this.initData()}}}initSubInvoiceView(){var e=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_invoice_view_controller){e.sub_invoice_view_controller.buildContextMenu(!0),e.sub_invoice_view_controller.setDefaultMenu(),e.sub_invoice_view_controller.parent_key="client_id",e.sub_invoice_view_controller.parent_value=e.current_edit_record.id,e.sub_invoice_view_controller.parent_edit_record=e.current_edit_record,e.sub_invoice_view_controller.initData();return}Global.loadScript("views/invoice/invoice/InvoiceViewController.js",function(){var r=e.edit_view_tab.find("#tab_invoices"),t=r.find(".first-column-sub-view");Global.trackView("SubInvoiceView"),InvoiceViewController.loadSubView(t,i,o)});function i(){}function o(r){e.sub_invoice_view_controller=r,e.sub_invoice_view_controller.parent_key="client_id",e.sub_invoice_view_controller.parent_value=e.current_edit_record.id,e.sub_invoice_view_controller.parent_edit_record=e.current_edit_record,e.sub_invoice_view_controller.parent_view_controller=e,e.sub_invoice_view_controller.postInit=function(){this.initData()}}}initSubTransactionView(){var e=this;if(!this.current_edit_record.id){TTPromise.resolve("BaseViewController","onTabShow");return}if(this.sub_transaction_view_controller){this.sub_transaction_view_controller.buildContextMenu(!0),this.sub_transaction_view_controller.setDefaultMenu(),e.sub_transaction_view_controller.parent_value=e.current_edit_record.id,e.sub_transaction_view_controller.parent_edit_record=e.current_edit_record,e.sub_transaction_view_controller.getSubViewFilter=function(t){return r(t)},e.sub_transaction_view_controller.initData();return}Global.loadScript("views/invoice/invoice_transaction/InvoiceTransactionViewController.js",function(){var t=e.edit_view_tab.find("#tab_transactions"),a=t.find(".first-column-sub-view");Global.trackView("SubInvoiceTransactionView"),InvoiceTransactionViewController.loadSubView(a,i,o)});function i(){}function o(t){e.sub_transaction_view_controller=t,e.sub_transaction_view_controller.parent_key="client_id",e.sub_transaction_view_controller.parent_value=e.current_edit_record.id,e.sub_transaction_view_controller.parent_edit_record=e.current_edit_record,e.sub_transaction_view_controller.getSubViewFilter=function(a){return r(a)},e.sub_transaction_view_controller.parent_view_controller=e,e.sub_transaction_view_controller.postInit=function(){this.initData()}}function r(t,a){return t.client_id=e.current_edit_record.id,t.invoice_status_id=[10,20,30,35,40,50,55,70,80,90,95,97,98,100],t}}}export{s as ClientViewController};
//# sourceMappingURL=ClientViewController-BH_jxYaJ.bundle.js.map
