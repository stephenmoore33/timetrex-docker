export class ScheduleSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'ScheduleSummaryReport';
		this.viewId = 'ScheduleSummaryReport';
		this.context_menu_name = $.i18n._( 'Schedule Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'ScheduleSummaryReportView.html';
		this.api = TTAPI.APIScheduleSummaryReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				schedule: {
					label: $.i18n._( 'Schedule' ),
					id: this.script_name + 'Schedule'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Print Summary' ),
					id: 'print',
					action_group_header: true,
					action_group: 'schedule',
					menu_align: 'right',
					permission_result: true,
					permission: true
				},
				{
					label: $.i18n._( 'Individual Schedules' ),
					id: 'pdf_schedule',
					action_group: 'schedule',
					menu_align: 'right'
				},
				{
					label: $.i18n._( 'Group - Combined' ),
					id: 'pdf_schedule_group_combined',
					action_group: 'schedule',
					menu_align: 'right'
				},
				{
					label: $.i18n._( 'Group - Separated' ),
					id: 'pdf_schedule_group',
					action_group: 'schedule',
					menu_align: 'right'
				},
				{
					label: $.i18n._( 'Group - Separated (Page Breaks)' ),
					id: 'pdf_schedule_group_pagebreak',
					action_group: 'schedule',
					menu_align: 'right'
				}
			]
		};

		return context_menu_model;
	}

	// Overriding empty ReportBaseViewController.processFilterField() called from base.openEditView to provide view specific logic.
	processFilterField() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'status_id' ) {
				item.value = 'filter';
			}
		}
	}

	onCustomContextClick( id, context_btn ) {
		switch ( id ) {
			case 'pdf_schedule':
			case 'pdf_schedule_group_combined':
			case 'pdf_schedule_group':
			case 'pdf_schedule_group_pagebreak':
				this.onReportMenuClick( id );
				break;
		}
	}

	onReportMenuClick( id ) {
		this.onViewClick( id );
	}

	setFilterValue( widget, value ) {
		widget.setValue( value.status_id );
	}

	onFormItemChangeProcessFilterField( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	}

}