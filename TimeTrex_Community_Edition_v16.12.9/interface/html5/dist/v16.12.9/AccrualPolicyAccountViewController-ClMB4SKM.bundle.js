import{_ as c,j as i}from"./vendor-tTApdY0Y.bundle.js";class o extends BaseViewController{constructor(a={}){c.defaults(a,{el:"#wage_group_view_container"}),super(a)}init(a){this.edit_view_tpl="AccrualPolicyAccountEditView.html",this.permission_id="accrual_policy",this.viewId="AccrualPolicyAccount",this.script_name="AccrualPolicyAccountView",this.table_name_key="accrual_policy_account",this.context_menu_name=i.i18n._("Accrual Account"),this.navigation_label=i.i18n._("Accrual Account"),this.api=TTAPI.APIAccrualPolicyAccount,this.render(),this.buildContextMenu(),this.initData()}getCustomContextMenuModel(){var a={exclude:["mass_edit"],include:[]};return a}buildEditViewUI(){super.buildEditViewUI();var a={tab_accrual_account:{label:i.i18n._("Accrual Account")},tab_audit:!0};this.setTabModel(a),this.navigation.AComboBox({api_class:TTAPI.APIAccrualPolicyAccount,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_accrual_policy_account",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var l=this.edit_view_tab.find("#tab_accrual_account"),t=l.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(t);var e=Global.loadWidgetByName(FormItemType.TEXT_INPUT);e.TTextInput({field:"name",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Name"),e,t,"first_last"),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.TEXT_AREA),e.TTextArea({field:"description",width:"100%"}),this.addEditFieldToColumn(i.i18n._("Description"),e,t,"",null,null,!0),e.parent().width("45%"),e=Global.loadWidgetByName(FormItemType.CHECKBOX),e.TCheckbox({field:"enable_pay_stub_balance_display"}),this.addEditFieldToColumn(i.i18n._("Display Balance on Pay Stub"),e,t)}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:i.i18n._("Name"),in_column:1,field:"name",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Created By"),in_column:2,field:"created_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Updated By"),in_column:2,field:"updated_by",layout_name:"global_user",api_class:TTAPI.APIUser,multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.AWESOME_BOX})]}}o.html_template=`
<div class="view wage-group-view" id="wage_group_view_container">
	<div class="clear-both-div"></div>
	<div class="grid-div">
		<div class="total-number-div">
			<span class="total-number-span"></span>
		</div>
		<div class="grid-top-border"></div>
		<table id="grid"></table>
		<div class="bottom-div">
			<div class="grid-bottom-border"></div>
		</div>
	</div>
</div>`;export{o as AccrualPolicyAccountViewController};
//# sourceMappingURL=AccrualPolicyAccountViewController-ClMB4SKM.bundle.js.map
