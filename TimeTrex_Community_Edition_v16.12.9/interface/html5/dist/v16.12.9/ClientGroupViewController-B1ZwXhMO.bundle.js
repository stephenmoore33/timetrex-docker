import{_ as i,j as t}from"./vendor-tTApdY0Y.bundle.js";class _ extends BaseTreeViewController{constructor(e={}){i.defaults(e,{el:"#client_group_view_container",tree_mode:null,grid_table_name:null,grid_select_id_array:null}),super(e)}init(e){this.edit_view_tpl="ClientGroupEditView.html",this.permission_id="client",this.viewId="ClientGroup",this.script_name="ClientGroupView",this.table_name_key="client_group",this.context_menu_name=t.i18n._("Client Groups"),this.grid_table_name=t.i18n._("Client Group"),this.navigation_label=t.i18n._("Client Group"),this.tree_mode=!0,this.primary_tab_label=t.i18n._("Client Group"),this.primary_tab_key="tab_client_group",this.api=TTAPI.APIClientGroup,this.grid_select_id_array=[],this.render(),this.buildContextMenu(),this.initData()}getCustomContextMenuModel(){var e={exclude:["copy","mass_edit","delete_and_next","save_and_continue","save_and_next","export_excel"],include:[]};return e}}export{_ as ClientGroupViewController};
//# sourceMappingURL=ClientGroupViewController-B1ZwXhMO.bundle.js.map
