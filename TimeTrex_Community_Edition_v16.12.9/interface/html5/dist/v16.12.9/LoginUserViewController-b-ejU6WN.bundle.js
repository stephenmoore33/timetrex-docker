import{_ as l,j as i}from"./vendor-tTApdY0Y.bundle.js";class o extends BaseViewController{constructor(e={}){l.defaults(e,{el:"#login_user_view_container",status_array:null,sex_array:null,user_group_array:null,country_array:null,province_array:null,company_api:null}),super(e)}init(e){this.permission_id="user",this.viewId="LoginUser",LoginUserWizardController.type==="Client"?this.script_name="ClientLoginPopUpEmployeeView":this.script_name="CompamyLoginPopUpEmployeeView",this.api=TTAPI.APIUser,this.api.key_name="CompanyUser",this.company_api=TTAPI.APICompany,this.render(),this.initData()}initRightClickMenu(e){}showNoResultCover(e){super.showNoResultCover(!1)}getGridSetup(){var e=this,a={container_selector:".login-user-view",sub_grid_mode:!1};return a.setGridSize=function(){e.baseViewSubTabGridResize(".login-user-view")},a.onResizeGrid=function(){e.baseViewSubTabGridResize(".login-user-view")},a.onCellSelect=function(){e.onGridSelectRow()},a}setCurrentViewPosition(){var e=this.search_panel.find(".layout-selector-div"),a=this.search_panel.find("a[ref='saved_layout']").parent(),t=a.offset().left-(Global.bodyWidth()/2-500);e.css("left",t+a.width()+20)}initOptions(e){var a=[{option_name:"status",field_name:null,api:null},{option_name:"sex",field_name:null,api:null},{option_name:"country",field_name:"country",api:this.company_api}];this.initDropDownOptions(a,function(t){e&&e(t)})}onSetSearchFilterFinished(){if(this.search_panel.getSelectTabIndex()===1){var e=this.adv_search_field_ui_dic.country,a=e.getValue();this.setProvince(a)}}onBuildAdvUIFinished(){this.adv_search_field_ui_dic.country.change(i.proxy(function(){var e=this.adv_search_field_ui_dic.country,a=e.getValue();this.setProvince(a),this.adv_search_field_ui_dic.province.setValue(null)},this))}setProvince(e,a){var t=this;!e||e==="-1"||e==="0"?(t.province_array=[],this.adv_search_field_ui_dic.province.setSourceData([])):this.company_api.getOptions("province",e,{onResult:function(r){r=r.getResult(),r||(r=[]),t.province_array=Global.buildRecordArray(r),t.adv_search_field_ui_dic.province.setSourceData(t.province_array)}})}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:i.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("First Name"),in_column:1,field:"first_name",basic_search:!0,adv_search:!0,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Last Name"),field:"last_name",basic_search:!0,adv_search:!0,in_column:1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Home Phone"),field:"home_phone",basic_search:!1,adv_search:!0,in_column:1,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Tags"),field:"tag",basic_search:!0,adv_search:!0,in_column:1,form_item_type:FormItemType.TAG_INPUT}),new SearchField({label:i.i18n._("Employee Number"),field:"employee_number",basic_search:!1,adv_search:!0,in_column:2,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("Sex"),in_column:2,field:"sex_id",multiple:!0,basic_search:!1,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Group"),in_column:2,multiple:!0,field:"group_id",layout_name:"global_tree_column",tree_mode:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Default Branch"),in_column:2,field:"default_branch_id",layout_name:"global_branch",api_class:TTAPI.APIBranch,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Default Department"),field:"default_department_id",in_column:2,layout_name:"global_department",api_class:TTAPI.APIDepartment,multiple:!0,basic_search:!0,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Title"),field:"title_id",in_column:3,layout_name:"global_job_title",api_class:TTAPI.APIUserTitle,multiple:!0,basic_search:!1,adv_search:!0,form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("Country"),in_column:3,field:"country",multiple:!0,basic_search:!1,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.COMBO_BOX}),new SearchField({label:i.i18n._("Province/State"),in_column:3,field:"province",multiple:!0,basic_search:!1,adv_search:!0,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:i.i18n._("City"),field:"city",basic_search:!1,adv_search:!0,in_column:3,form_item_type:FormItemType.TEXT_INPUT}),new SearchField({label:i.i18n._("SIN/SSN"),field:"sin",basic_search:!1,adv_search:!0,in_column:3,form_item_type:FormItemType.TEXT_INPUT})]}}export{o as LoginUserViewController};
//# sourceMappingURL=LoginUserViewController-b-ejU6WN.bundle.js.map
