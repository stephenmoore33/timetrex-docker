import{_ as d,j as s}from"./vendor-tTApdY0Y.bundle.js";class u extends BaseViewController{constructor(e={}){d.defaults(e,{el:"#notification_view_container",is_viewing:null,status_id_array:null,type_id_array:null}),super(e)}init(e){this.edit_view_tpl="NotificationEditView.html",this.permission_id="notification",this.viewId="Notification",this.script_name="NotificationView",this.table_name_key="Notification",this.navigate_link="",this.selected_payload={},this.context_menu_name=s.i18n._("Notifications"),this.navigation_label=s.i18n._("Notification"),this.api=TTAPI.APINotification,this.is_viewing=!1,this.render(),this.buildContextMenu(),this.initData()}initOptions(){this.initDropDownOption("type","type_id",this.api),this.initDropDownOption("status","status_id",this.api)}onGridDblClickRow(e){ProgressBar.showOverlay(),this.onViewClick(),this.setDefaultMenu(!0)}getFilterColumnsFromDisplayColumns(){var e={};e.id=!0,e.object_id=!0,e.payload_data=!0,e.status_id=!0;var t=[];if(this.grid&&(t=this.grid.getGridParam("colModel")),t)for(var i=t.length,a=0;a<i;a++){var n=t[a];e[n.name]=!0}return e}setGridCellBackGround(){if(this.grid){var e=this.grid.getGridParam("data");if(e)for(var t=e.length,i=0;i<t;i++){var a=e[i];a.status_id==10&&s("tr[id='"+a.id+"'] td").css("font-weight","bold")}}}getCustomContextMenuModel(){var e={groups:{other:{label:s.i18n._("Other"),id:this.script_name+"other",sort_order:9e3},mark:{label:s.i18n._("Mark"),id:this.viewId+"mark",sort_order:8e3}},exclude:["save_and_continue","save_and_next","save_and_new","save_and_copy","save","copy","copy_as_new","edit","new_add","add","mass_edit","export_excel","delete_and_next"],include:["view",{label:s.i18n._("Jump To"),id:"navigate",vue_icon:"tticon tticon-north_east_black_24dp",menu_align:"right",permission_result:!0,permission:8200,sort_order:8100},{label:"",id:"other_header",menu_align:"right",action_group:"other",action_group_header:!0,vue_icon:"tticon tticon-more_vert_black_24dp"},{label:s.i18n._("Mark: Read"),id:"read",menu_align:"left",action_group:"mark",permission_result:!0,permission:null,sort_order:8e3},{label:s.i18n._("Mark: UnRead"),id:"unread",menu_align:"left",action_group:"mark",permission_result:!0,permission:null,sort_order:8100}]};return e}onCustomContextClick(e,t){switch(e){case"close_misc":case"cancel":this.onCancelClick();break;case"read":this.onReadClick();break;case"unread":this.onUnReadClick();break;case"navigate":this.onNavigateClick();break}}oncancelClick(){this.removeEditView()}onReadClick(){var e=[];if(this.is_viewing&&this.current_edit_record?e.push(this.current_edit_record.id):e=this.getGridSelectIdArray(),e.length>0){var t=this;this.api.setNotificationStatus(e,20,{onResult:function(i){t.is_viewing&&t.removeEditView(),t.search(!1)}})}}onUnReadClick(){var e=[];if(this.is_viewing&&this.current_edit_record?e.push(this.current_edit_record.id):e=this.getGridSelectIdArray(),e.length>0){var t=this;this.api.setNotificationStatus(e,10,{onResult:function(i){t.is_viewing&&t.removeEditView(),t.search(!1)}})}}onNavigateClick(){if(this.navigate_link!=="")if(this.is_viewing==!0&&this.onCancelClick(),this.navigate_link==="open_view"){for(let e=0;e<this.selected_payload.timetrex.event.length;e++)if(this.selected_payload.timetrex.event[e].type==="open_view"||this.selected_payload.timetrex.event[e].type==="open_view_immediate"){NotificationConsumer.openViewLinkedToNotification(this.selected_payload.timetrex.event[e]);break}}else this.selected_payload.link_target&&this.selected_payload.link_target==="_blank"?window.open(this.navigate_link,"_blank"):window.location=this.navigate_link}setNavigateLink(){if(this.navigate_link="",!!this.grid){var e=this.grid.getGridParam("data");if(!e)return!1;var t=[];if(this.current_edit_record&&this.current_edit_record.id?t.push(this.current_edit_record.id):t=this.getGridSelectIdArray(),t.length===1)for(var i=e.length,a=0;a<i;a++){var n=e[a];if(n.id===t[0]){if(n.payload_data.timetrex!==void 0&&n.payload_data.timetrex.event!==void 0){for(let r=0;r<n.payload_data.timetrex.event.length;r++)if(n.payload_data.timetrex.event[r].type==="open_view"||n.payload_data.timetrex.event[r].type==="open_view_immediate")return this.navigate_link="open_view",this.selected_payload=n.payload_data,!0}else if(n.payload_data.link!==void 0&&n.payload_data.link!=="")return this.navigate_link=n.payload_data.link,this.selected_payload=n.payload_data,!0}}return this.navigate_link="",this.selected_payload={},!1}}initEditView(){if(this.current_edit_record&&this.current_edit_record.status_id==10){var e=this;e.current_edit_record.status_id=20,this.api.setNotificationStatus([this.current_edit_record.id],20,{onResult:function(t){e.search(!1)}})}super.initEditView()}setCustomDefaultMenuIcon(e,t,i){switch(e){case"read":this.setDefaultMenuReadIcon(t,i);break;case"unread":this.setDefaultMenuUnReadIcon(t,i);break;case"navigate":this.setDefaultMenuNavigateIcon(t,i);break}}setCustomEditMenuIcon(e,t){switch(e){case"navigate":this.setDefaultMenuNavigateIcon(t);break;case"read":this.setEditMenuReadIcon(t);break;case"unread":this.setEditMenuUnReadIcon(t);break}}setDefaultMenuDeleteAndNextIcon(e,t,i){this.is_viewing?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuDeleteAndNextIcon(e,t,i){this.is_viewing?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuDeleteIcon(e,t,i){t>=1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuDeleteIcon(e,t,i){this.is_viewing?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuViewIcon(e,t,i){this.is_viewing==!1&&t===1?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuCancelIcon(e,t){this.is_viewing?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuReadIcon(e,t,i){if(t>=1){for(var a=this.getSelectedItems(),n=0;n<a.length;n++)if(a[n]!==null&&a[n].status_id==10){ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0);return}ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}else ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuUnReadIcon(e,t,i){if(t>=1){for(var a=this.getSelectedItems(),n=0;n<a.length;n++)if(a[n]!==null&&a[n].status_id==20){ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0);return}ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}else ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setDefaultMenuNavigateIcon(e,t,i){this.is_viewing==!0||t===1?this.setNavigateLink()==!0?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuReadIcon(e){this.current_edit_record&&this.current_edit_record.status_id==10?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}setEditMenuUnReadIcon(e){this.current_edit_record&&this.current_edit_record.status_id==20?ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!0):ContextMenuManager.disableMenuItem(this.determineContextMenuMountAttributes().id,e.id,!1)}buildEditViewUI(){super.buildEditViewUI();var e=this,t={tab_notification:{label:s.i18n._("Notification")}};this.setTabModel(t);var i;this.navigation.AComboBox({api_class:TTAPI.APINotification,id:this.script_name+"_navigation",allow_multiple_selection:!1,layout_name:"global_notification",navigation_mode:!0,show_search_inputs:!0}),this.setNavigation();var a=s("#tab_notification"),n=a.find(".first-column");this.edit_view_tabs[0]=[],this.edit_view_tabs[0].push(n),i=Global.loadWidgetByName(FormItemType.COMBO_BOX),i.TComboBox({field:"type_id"}),i.setSourceData(Global.addFirstItemToArray(e.type_array)),this.addEditFieldToColumn(s.i18n._("Type"),i,n,""),i=Global.loadWidgetByName(FormItemType.TEXT),i.TText({field:"created_date"}),this.addEditFieldToColumn(s.i18n._("Date"),i,n,""),i=Global.loadWidgetByName(FormItemType.TEXT),i.TText({field:"title_long"}),this.addEditFieldToColumn(s.i18n._("Title"),i,n,""),i=Global.loadWidgetByName(FormItemType.TEXT),i.TText({field:"body_long_text"}),i.off("click").on("click",function(){e.onNavigateClick()}),this.addEditFieldToColumn(s.i18n._("Message"),i,n,"")}buildSearchFields(){super.buildSearchFields(),this.search_fields=[new SearchField({label:s.i18n._("Status"),in_column:1,field:"status_id",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:s.i18n._("Type"),in_column:1,field:"type_id",multiple:!0,basic_search:!0,adv_search:!1,layout_name:"global_option_column",form_item_type:FormItemType.AWESOME_BOX}),new SearchField({label:s.i18n._("Title"),in_column:1,field:"title_long",multiple:!0,basic_search:!0,adv_search:!1,form_item_type:FormItemType.TEXT_INPUT})]}search(e,t,i,a){super.search(e,t,i,a),Global.UNIT_TEST_MODE==!1&&NotificationConsumer.getUnreadNotifications()}}export{u as NotificationViewController};
//# sourceMappingURL=NotificationViewController-BA6S3EU9.bundle.js.map
