import{_ as i,j as t}from"./vendor-tTApdY0Y.bundle.js";class _ extends BaseTreeViewController{constructor(e={}){i.defaults(e,{el:"#job_group_view_container",tree_mode:null,grid_table_name:null,grid_select_id_array:null}),super(e)}init(e){this.edit_view_tpl="JobGroupEditView.html",this.permission_id="job",this.viewId="JobGroup",this.script_name="JobGroupView",this.table_name_key="job_group",this.context_menu_name=t.i18n._("Job Groups"),this.grid_table_name=t.i18n._("Job Group"),this.navigation_label=t.i18n._("Job Group"),this.tree_mode=!0,this.primary_tab_label=t.i18n._("Job Group"),this.primary_tab_key="tab_job_group",this.api=TTAPI.APIJobGroup,this.grid_select_id_array=[],this.render(),this.buildContextMenu(),this.initData()}getCustomContextMenuModel(){var e={exclude:["copy","mass_edit","delete_and_next","save_and_continue","save_and_next","export_excel"],include:[]};return e}}export{_ as JobGroupViewController};
//# sourceMappingURL=JobGroupViewController-8fTDAbmE.bundle.js.map
