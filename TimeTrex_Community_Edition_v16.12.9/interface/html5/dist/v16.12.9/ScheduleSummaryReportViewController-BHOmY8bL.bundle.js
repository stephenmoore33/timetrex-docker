import{_ as u,j as t}from"./vendor-tTApdY0Y.bundle.js";class s extends ReportBaseViewController{constructor(e={}){u.defaults(e,{}),super(e)}initReport(e){this.script_name="ScheduleSummaryReport",this.viewId="ScheduleSummaryReport",this.context_menu_name=t.i18n._("Schedule Summary"),this.navigation_label=t.i18n._("Saved Report"),this.view_file="ScheduleSummaryReportView.html",this.api=TTAPI.APIScheduleSummaryReport}getCustomContextMenuModel(){var e={groups:{schedule:{label:t.i18n._("Schedule"),id:this.script_name+"Schedule"}},exclude:[],include:[{label:t.i18n._("Print Summary"),id:"print",action_group_header:!0,action_group:"schedule",menu_align:"right",permission_result:!0,permission:!0},{label:t.i18n._("Individual Schedules"),id:"pdf_schedule",action_group:"schedule",menu_align:"right"},{label:t.i18n._("Group - Combined"),id:"pdf_schedule_group_combined",action_group:"schedule",menu_align:"right"},{label:t.i18n._("Group - Separated"),id:"pdf_schedule_group",action_group:"schedule",menu_align:"right"},{label:t.i18n._("Group - Separated (Page Breaks)"),id:"pdf_schedule_group_pagebreak",action_group:"schedule",menu_align:"right"}]};return e}processFilterField(){for(var e=0;e<this.setup_fields_array.length;e++){var r=this.setup_fields_array[e];r.value==="status_id"&&(r.value="filter")}}onCustomContextClick(e,r){switch(e){case"pdf_schedule":case"pdf_schedule_group_combined":case"pdf_schedule_group":case"pdf_schedule_group_pagebreak":this.onReportMenuClick(e);break}}onReportMenuClick(e){this.onViewClick(e)}setFilterValue(e,r){e.setValue(r.status_id)}onFormItemChangeProcessFilterField(e,r){var i=e.getValue();this.visible_report_values[r]={status_id:i}}}export{s as ScheduleSummaryReportViewController};
//# sourceMappingURL=ScheduleSummaryReportViewController-BHOmY8bL.bundle.js.map
