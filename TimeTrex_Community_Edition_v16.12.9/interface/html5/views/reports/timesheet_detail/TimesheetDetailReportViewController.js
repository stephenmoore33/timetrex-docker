export class TimesheetDetailReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'TimesheetDetailReport';
		this.viewId = 'TimesheetDetailReport';
		this.context_menu_name = $.i18n._( 'TimeSheet Detail' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'TimesheetDetailReportView.html';
		this.api = TTAPI.APITimesheetDetailReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				timesheet: {
					label: $.i18n._( 'TimeSheet' ),
					id: this.viewId + 'TimeSheet'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Print TimeSheet' ),
					id: 'print_timesheet',
					action_group_header: true,
					action_group: 'timesheet',
					menu_align: 'right',
					permission_result: true,
					permission: true
				},
				{
					label: $.i18n._( 'Summary' ),
					id: 'pdf_timesheet',
					action_group: 'timesheet',
					menu_align: 'right'
				},
				{
					label: $.i18n._( 'Detailed' ),
					id: 'pdf_timesheet_detail',
					action_group: 'timesheet',
					menu_align: 'right',
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id, context_btn ) {
		switch ( id ) {
			case 'pdf_timesheet':
			case 'pdf_timesheet_detail':
				this.onReportMenuClick( id );
				break;
		}
	}

	onReportMenuClick( id ) {
		this.onViewClick( id );
	}
}
