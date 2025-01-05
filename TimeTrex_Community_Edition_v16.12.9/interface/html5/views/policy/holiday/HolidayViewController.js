export class HolidayViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#holiday_view_container',

		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'HolidayEditView.html';
		this.permission_id = 'holiday_policy';
		this.viewId = 'Holiday';
		this.script_name = 'HolidayView';
		this.table_name_key = 'holidays';
		this.context_menu_name = $.i18n._( 'Holiday' );
		this.navigation_label = $.i18n._( 'Holiday' );
		this.api = TTAPI.APIHoliday;
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['mass_edit'],
			include: []
		};

		return context_menu_model;
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_holiday': { 'label': $.i18n._( 'Holiday' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIHoliday,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_holiday_holoday',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_holiday = this.edit_view_tab.find( '#tab_holiday' );

		var tab_holiday_column1 = tab_holiday.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_holiday_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_holiday_column1, '' );

		form_item_input.parent().width( '45%' );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_holiday_column1, '' );

	}

	onSaveResult( result ) {
		super.onSaveResult( result );
		if ( result && result.isValid() ) {
			var system_job_queue = result.getAttributeInAPIDetails( 'system_job_queue' );
			if ( system_job_queue ) {
				this.event_bus.emit( 'tt_topbar', 'toggle_job_queue_spinner', {
					show: true,
					get_job_data: true
				} );
			}
		}
	}
}

HolidayViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'Holiday', 'SubHolidayView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_holiday_view_controller );
				} );
			}
		}
	} );
};