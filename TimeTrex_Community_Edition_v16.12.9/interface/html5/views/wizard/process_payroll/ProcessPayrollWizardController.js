export class ProcessPayrollWizardController extends BaseWizardController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.wizard-bg',

			all_columns: null,
			pay_stub_transaction_columns: null,

			api_pay_period: null,
			api_pay_stub: null,

			api_pay_stub_transaction: null,

			alert_message: $.i18n._( 'Please select one or more pay periods in the list above to enable icons.' ),

			transaction_source_data: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );

		this.title = $.i18n._( 'Payroll Processing Wizard' );
		this.steps = 9;
		this.current_step = 1;
		this.script_name = 'wizard_process_payroll';
		this.wizard_id = 'ProcessPayrollWizard';
		this.api_pay_period = TTAPI.APIPayPeriod;
		this.api_pay_stub = TTAPI.APIPayStub;
		this.api_pay_stub_transaction = TTAPI.APIPayStubTransaction;

		this.render();
	}

	render() {
		super.render();

		this.initUserGenericData();
	}

	setButtonsStatus() {

		Global.setWidgetEnabled( this.done_btn, false );
		Global.setWidgetEnabled( this.close_btn, true );

		if ( this.current_step === 1 ) {
			Global.setWidgetEnabled( this.back_btn, false );
		} else {
			Global.setWidgetEnabled( this.back_btn, true );
		}

		if ( this.current_step !== this.steps ) {
			Global.setWidgetEnabled( this.done_btn, false );
			Global.setWidgetEnabled( this.next_btn, true );
			//Error: TypeError: this.stepsWidgetDic[1] is undefined in interface/html5/framework/jquery.min.js?v=9.0.0-20150918-155419 line 2 > eval line 45
			if ( this.stepsWidgetDic[1] && ( !this.stepsWidgetDic[1].pay_period_id.getValue() || this.stepsWidgetDic[1].pay_period_id.getValue().length < 1 ) ) {
				Global.setWidgetEnabled( this.next_btn, false );
			}
		} else {
			Global.setWidgetEnabled( this.done_btn, true );
			Global.setWidgetEnabled( this.next_btn, false );
		}
	}

	//Create each page UI
	buildCurrentStepUI() {

		var $this = this;
		this.content_div.empty();
		switch ( this.current_step ) {
			case 1:

				var label = this.getLabel();
				label.text( $.i18n._( 'Select one or more pay periods to process payroll for' ) );
				var a_combobox = this.getAComboBox( TTAPI.APIPayPeriod, true, 'global_Pay_period', 'pay_period_id' );
				var div = $( '<div class=\'wizard-acombobox-div\'></div>' );
				div.append( a_combobox );

				a_combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function() {
					$this.setButtonsStatus();
				} );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );
				break;
			case 2:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm all requests are authorized' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				var grid_id = 'pending_request';
				var grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				var ribbon_button_box = this.getRibbonButtonBox();
				var request_button = this.getRibbonButton( 'request', Global.getRibbonIconRealPath( 'requests-35x35.png' ), $.i18n._( 'Requests' ) );

				request_button.unbind( 'click' ).bind( 'click', function() {

					$this.onNavigationClick( 'request' );
				} );

				ribbon_button_box.children().eq( 0 ).append( request_button );
				this.content_div.append( ribbon_button_box );

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm that no critical exceptions exist' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'exceptions';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				this.removeResizableGrids();

				ribbon_button_box = this.getRibbonButtonBox();
				var ribbon_btn = this.getRibbonButton( 'exceptions', Global.getRibbonIconRealPath( 'exceptions-35x35.png' ), $.i18n._( 'Exceptions' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'exceptions' );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				this.content_div.append( ribbon_button_box );
				break;
			case 4:
				label = this.getLabel();
				label.text( $.i18n._( 'Confirm timesheets are verified' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'timesheet';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				this.removeResizableGrids();

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( 'timesheet_reports', Global.getRibbonIconRealPath( 'timesheet_reports-35x35.png' ), $.i18n._( 'TimeSheet<br>Summary' ) );
				var ribbon_btn2 = this.getRibbonButton( 'authorization_timesheet', Global.getRibbonIconRealPath( 'authorize_timesheet-35x35.png' ), $.i18n._( 'TimeSheet<br>Authorizations' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'timesheet_reports' );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'authorization_timesheet' );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				this.content_div.append( ribbon_button_box );
				break;
			case 5:
				label = this.getLabel();
				label.text( $.i18n._( 'Lock pay periods to prevent changes' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'lock_pay_period';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( 'lock', Global.getRibbonIconRealPath( 'lock-35x35.png' ), $.i18n._( 'Lock' ) );
				ribbon_btn2 = this.getRibbonButton( 'unlock', Global.getRibbonIconRealPath( 'unlock-35x35.png' ), $.i18n._( 'UnLock' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( 'lock' );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( 'unlock' );
				} );

				this.stepsWidgetDic[this.current_step].lock = ribbon_btn;
				this.stepsWidgetDic[this.current_step].unlock = ribbon_btn2;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				this.content_div.append( ribbon_button_box );
				break;
			case 6:
				label = this.getLabel();
				label.text( $.i18n._( 'Create any necessary pay stub amendments' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_amendments';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( 'pay_stub_amendment', Global.getRibbonIconRealPath( 'pay_stub_amendments-35x35.png' ), $.i18n._( 'Pay Stub<br>Amendments' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'pay_stub_amendment' );
				} );

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				this.content_div.append( ribbon_button_box );
				break;
			case 7:
				label = this.getLabel();
				label.text( $.i18n._( 'Generate pay stubs' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_generate';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( 'generate_pay_stub', Global.getRibbonIconRealPath( 'calculate_paystubs-35x35.png' ), $.i18n._( 'Generate<br>Pay Stubs' ) );
				ribbon_btn2 = this.getRibbonButton( 'pay_stub', Global.getRibbonIconRealPath( 'pay_stubs-35x35.png' ), $.i18n._( 'Pay<br>Stubs' ) );
				var ribbon_btn3 = this.getRibbonButton( 'pay_stub_summary', Global.getRibbonIconRealPath( 'pay_stubs_accounts-35x35.png' ), $.i18n._( 'Pay Stub<br>Summary' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );

						return;
					}
					$this.onNavigationClick( 'generate_pay_stub' );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'pay_stub' );
				} );

				ribbon_btn3.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'pay_stub_summary' );
				} );

				this.stepsWidgetDic[this.current_step].generate_pay_stub = ribbon_btn;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn3 );

				this.content_div.append( ribbon_button_box );
				break;
			case 8:
				label = this.getLabel();
				label.text( $.i18n._( 'Process transactions' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				$( '.wizard .content' ).css( 'opacity', 0 );
				TTPromise.add( 'wizard', 'step8' );
				TTPromise.wait( 'wizard', 'step8', function() {
					$( '.wizard .content' ).css( 'opacity', 1 );
				} );

				grid_id = 'pay_stub_transfer';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div> <hr>' );
				this.setGrid( grid_id, grid_div, true );

				grid_id = 'pay_stub_transaction';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, false ).setGridHeight( Math.floor( ( this.content_div.height() - 150 ) / 2 ) );

				ribbon_button_box = this.getRibbonButtonBox();
				var ribbon_btn1 = this.getRibbonButton( 'direct_deposit', Global.getRibbonIconRealPath( 'direct_deposit-35x35.png' ), $.i18n._( 'Process<br>Transactions' ) );
				ribbon_btn2 = this.getRibbonButton( 'pay_stub_transaction_summary', Global.getRibbonIconRealPath( 'payroll_reports-35x35.png' ), $.i18n._( 'Pay Stub<br>Transaction Summary' ) );
				ribbon_btn3 = this.getRibbonButton( 'payroll_export_report', Global.getRibbonIconRealPath( 'payroll_reports-35x35.png' ), $.i18n._( 'Payroll Export' ) );

				ribbon_btn1.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'direct_deposit' );
				} );

				ribbon_btn2.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'pay_stub_transaction_summary' );
				} );
				ribbon_btn3.unbind( 'click' ).bind( 'click', function() {
					$this.onNavigationClick( 'payroll_export_report' );
				} );

				//this is the display order for buttons on step 8
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn1 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn2 );
				ribbon_button_box.children().eq( 0 ).append( ribbon_btn3 );

				this.content_div.append( ribbon_button_box );
				break;
			case 9:
				label = this.getLabel();
				label.text( $.i18n._( 'Close pay period' ) );

				this.content_div.append( label );
				this.stepsWidgetDic[this.current_step] = {};

				grid_id = 'pay_stub_close';
				grid_div = $( '<div class=\'grid-div wizard-grid-div\'> <table id=\'' + grid_id + '\'></table></div>' );
				this.setGrid( grid_id, grid_div, true );

				ribbon_button_box = this.getRibbonButtonBox();
				ribbon_btn = this.getRibbonButton( 'close_pay_period', Global.getRibbonIconRealPath( 'close_pay_period-35x35.png' ), $.i18n._( 'Close' ) );

				ribbon_btn.unbind( 'click' ).bind( 'click', function() {
					if ( $( this ).hasClass( 'disable-image' ) ) {
						TAlertManager.showAlert( $this.alert_message );
						return;
					}
					$this.onNavigationClick( 'close_pay_period' );
				} );

				this.stepsWidgetDic[this.current_step].close = ribbon_btn;

				ribbon_button_box.children().eq( 0 ).append( ribbon_btn );

				this.content_div.append( ribbon_button_box );
				break;

		}
	}

	onNextClick() {
		this.saveCurrentStep();

		if ( this.current_step === 1 ) {
			var current_step_ui = this.stepsWidgetDic[this.current_step];
			var pay_period_id = current_step_ui.pay_period_id.getValue();
			if ( !pay_period_id || pay_period_id.length == 0 || ( pay_period_id == TTUUID.zero_id ) || ( pay_period_id.length == 1 && pay_period_id[0] == TTUUID.zero_id ) ) {
				TAlertManager.showAlert( $.i18n._( 'Please choose a Pay Period' ) );
				return;
			}
		}

		this.current_step = this.current_step + 1;
		this.initCurrentStep();
	}

	onDoneClick() {
		this.cleanStepsData();
		LocalCacheData.current_open_wizard_controllers = LocalCacheData.current_open_wizard_controllers.filter( wizard => wizard.wizard_id !== this.wizard_id );
		this.saveAllStepsToUserGenericData( function() {

		} );

		if ( this.call_back ) {
			this.call_back();
		}

		$( this.el ).remove();

		$().TFeedback( {
			source: this.wizard_id
		} );
	}

	getGridColumns( gridId, callBack ) {

		var $this = this;

		if ( gridId == 'pay_stub_transaction' ) {
			var result = this.api_pay_stub_transaction.getOptions( 'columns', { 'payroll_wizard': true }, { async: false } );
			if ( result ) {
				var result_data = result.getResult();

				$this.pay_stub_transaction_columns = Global.buildColumnArray( result_data );

				var column_info_array = [];
				for ( var x in $this.pay_stub_transaction_columns ) {
					var column_data = $this.pay_stub_transaction_columns[x];

					if ( column_data.value == 'remittance_source_account' ||
						column_data.value == 'remittance_source_account_type' ||
						column_data.value == 'currency' ||
						column_data.value == 'total_transactions' ||
						column_data.value == 'total_amount' ) {
						var column_info = {
							resizable: false,
							name: column_data.value,
							index: column_data.value,
							label: column_data.label,
							width: 100,
							sortable: false,
							title: false
						};
						column_info_array.push( column_info );
					}
				}

				callBack( column_info_array );

				return;
			}
		}

		if ( this.all_columns ) {
			doNext();
		} else {
			var result = this.api_pay_period.getOptions( 'columns', { async: false } );
			var result_data = result.getResult();
			$this.all_columns = Global.buildColumnArray( result_data );
			doNext();
		}

		function doNext() {

			var len = $this.all_columns.length;
			var column_info_array = [];

			switch ( gridId ) {
				case 'pending_request':

					for ( var i = 0; i < len; i++ ) {
						var column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pending_requests' ) {
							var column_info = {
								resizable: false,
								name: column_data.value,
								index: column_data.value,
								label: column_data.label,
								width: 100,
								sortable: false,
								title: false
							};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'exceptions':

					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'exceptions_high' ||
							column_data.value == 'exceptions_medium' ||
							column_data.value == 'exceptions_low' ||
							column_data.value == 'exceptions_critical'
						) {

							if ( column_data.value == 'exceptions_high' ||
								column_data.value == 'exceptions_medium' ||
								column_data.value == 'exceptions_low' ||
								column_data.value == 'exceptions_critical' ) {

								column_info = {
									resizable: false,
									name: column_data.value,
									index: column_data.value,
									label: column_data.label,
									width: 50,
									sortable: false,
									title: false
								};
								column_info_array.push( column_info );

							} else {
								column_info = {
									resizable: false,
									name: column_data.value,
									index: column_data.value,
									label: column_data.label,
									width: 100,
									sortable: false,
									title: false
								};
								column_info_array.push( column_info );
							}

						}

					}

					break;

				case 'timesheet':

					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'verified_timesheets' ||
							column_data.value == 'pending_timesheets' ||
							column_data.value == 'total_timesheets'
						) {

							if ( column_data.value == 'verified_timesheets' ||
								column_data.value == 'pending_timesheets' ||
								column_data.value == 'total_timesheets' ) {

								column_info = {
									resizable: false,
									name: column_data.value,
									index: column_data.value,
									label: column_data.label,
									width: 50,
									sortable: false,
									title: false
								};
								column_info_array.push( column_info );

							} else {
								column_info = {
									resizable: false,
									name: column_data.value,
									index: column_data.value,
									label: column_data.label,
									width: 100,
									sortable: false,
									title: false
								};
								column_info_array.push( column_info );
							}

						}

					}

					break;
				case 'lock_pay_period':
					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'status'
						) {

							column_info = {
								resizable: false,
								name: column_data.value,
								index: column_data.value,
								label: column_data.label,
								width: 100,
								sortable: false,
								title: false
							};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'pay_stub_amendments':
					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'ps_amendments'
						) {

							column_info = {
								resizable: false,
								name: column_data.value,
								index: column_data.value,
								label: column_data.label,
								width: 100,
								sortable: false,
								title: false
							};
							column_info_array.push( column_info );

						}

					}

					break;
				case 'pay_stub_generate':
				case 'pay_stub_transfer':
					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pay_stubs_open'
						) {

							column_info = {
								resizable: false,
								name: column_data.value,
								index: column_data.value,
								label: column_data.label,
								width: 100,
								sortable: false,
								title: false
							};
							column_info_array.push( column_info );
						}
					}

					break;
				case 'pay_stub_close':
					for ( var i = 0; i < len; i++ ) {
						column_data = $this.all_columns[i];

						if ( column_data.value == 'status' ||
							column_data.value == 'start_date' ||
							column_data.value == 'end_date' ||
							column_data.value == 'transaction_date' ||
							column_data.value == 'pay_stubs_open'
						) {

							column_info = {
								resizable: false,
								name: column_data.value,
								index: column_data.value,
								label: column_data.label,
								width: 100,
								sortable: false,
								title: false
							};
							column_info_array.push( column_info );

						}

					}

					break;

			}

			callBack( column_info_array );
		}
	}

	removeResizableGrids() {
		//Issue #3214 - Exception triggered when page is resized while on Exception or TimeSheet wizard steps
		//This is a related to a previous fix found in this.setGridGroupColumns().
		//To avoid this we now remove the two error causing grids from the resizeable grid list.
		//This solution was chosen as the exception is deep within TTGrid and not easily avoidable.
		//Resizing is not requires for normal use and only causes display issues with very small resolutions <1024px.
		//In addition, only happens in the specific case where the end user resizes their browser on those two wizard steps.

		if ( Array.isArray( LocalCacheData.resizeable_grids ) ) {
			LocalCacheData.resizeable_grids = LocalCacheData.resizeable_grids.filter( function( grid ) {
				return grid !== null && grid.ui_id !== 'exceptions' && grid.ui_id !== 'timesheet';
			} );
		}
	}

	setGridGroupColumns( gridId ) {
		//Short circuit this function if we aren't creating any spanning cells for exceptions/timesheets.
		//  As having "group-column-tr" without anything in it causes jqGrid to trigger JS exception: Uncaught TypeError: Cannot read property 'style' of undefined
		//  when running: $( '#contentContainer' ).trigger( 'resize' ) from the console.
		if ( gridId !== 'exceptions' && gridId !== 'timesheet' ) {
			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + gridId + ']' )[0] );
		var table_width_target = $( $( this.el ).find( 'table[id=' + gridId + ']' )[0] );

		var new_tr = $( '<tr>' + '</tr>' );

		var new_th = $( '<th class="group-column-th">' +
			'<span style="font-weight: bold"></span>' +
			'</th>' );

		switch ( gridId ) {
			case 'exceptions':
				var ths = table_width_target.children( 0 ).children( 0 ).children();

				var default_th = new_th.clone();
				default_th.attr( 'colspan', '1' );
				default_th.width( ths.eq( 0 ).width() );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 1 ).width() * 3 );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '4' );
				default_th.width( ths.eq( 4 ).width() * 4 );

				default_th.children( 0 ).text( $.i18n._( 'Exceptions' ) );

				break;
			case 'timesheet':
				ths = table_width_target.children( 0 ).children( 0 ).children();

				default_th = new_th.clone();
				default_th.attr( 'colspan', '1' );
				default_th.width( ths.eq( 0 ).width() );
				new_tr.append( default_th );

				default_th = new_th.clone();
				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 1 ).width() * 3 );
				new_tr.append( default_th );

				default_th = new_th.clone();

				default_th.attr( 'colspan', '3' );
				default_th.width( ths.eq( 4 ).width() * 3 );

				default_th.children( 0 ).text( $.i18n._( 'Timesheets' ) );
				break;
		}

		new_tr.append( default_th );
		table.find( 'thead' ).prepend( new_tr );
	}

	onNavigationClick( iconName ) {
		var $this = this;
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid;
		var ids;
		var data_array;
		var filter;
		switch ( iconName ) {
			case 'exceptions':
				filter = { filter_data: {} };
				grid = current_step_ui.exceptions;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );

				var pay_period_ids = { value: ids };
				filter.filter_data.pay_period_id = pay_period_ids;

				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();
				IndexViewController.goToView( 'Exception', filter );

				break;
			case 'request':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );

				IndexViewController.goToView( 'Request' );

				this.onCloseClick();
				break;
			case 'timesheet_reports':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();

				LocalCacheData.default_filter_for_next_open_view = { template: 'by_pay_period_by_employee+verified_time_sheet' };

				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'TimesheetSummaryReport' );

				break;
			case 'lock':
				grid = current_step_ui.lock_pay_period;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( var i = 0; i < ids.length; i++ ) {
					var data = {};
					data.id = ids[i];
					data.status_id = 12;
					data_array.push( data );
				}
				ProgressBar.showOverlay();
				this.api_pay_period.setPayPeriod( data_array, {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							$this.buildCurrentStepData();
						} else {
							TAlertManager.showErrorAlert( result );
						}
					}
				} );
				break;
			case 'unlock':
				grid = current_step_ui.lock_pay_period;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( var i = 0; i < ids.length; i++ ) {
					data = {};
					data.id = ids[i];
					data.status_id = 10;
					data_array.push( data );
				}
				ProgressBar.showOverlay();
				this.api_pay_period.setPayPeriod( data_array, {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							$this.buildCurrentStepData();
						} else {
							TAlertManager.showErrorAlert( result );
						}
					}
				} );
				break;
			case 'pay_stub_amendment':

				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();

				grid = current_step_ui.pay_stub_amendments;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				filter = { filter_data: { pay_period_id: { value: ids } } };
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case 'generate_pay_stub':

				grid = current_step_ui.pay_stub_generate;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				ProgressBar.showOverlay();

				TTAPI.APIPayStub.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
				TTAPI.APIPayStub.generatePayStubs( ids, {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );

							if ( user_generic_status_batch_id && TTUUID.isUUID( user_generic_status_batch_id ) && user_generic_status_batch_id != TTUUID.zero_id && user_generic_status_batch_id != TTUUID.not_exist_id ) {
								UserGenericStatusWindowController.open( user_generic_status_batch_id, [], function() {
									$this.buildCurrentStepData();
								} );
							}
						} else {
							TAlertManager.showErrorAlert( result );
						}
					}
				} );

				break;
			case 'pay_stub':

				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();

				if ( this.current_step === 7 ) {
					grid = current_step_ui.pay_stub_generate;
				} else {
					grid = current_step_ui.pay_stub_transfer;
				}
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				filter = { filter_data: { pay_period_id: { value: ids } } };
				IndexViewController.goToView( 'PayStub', filter );
				break;
			case 'pay_stub_summary':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();
				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'PayStubSummaryReport' );

				break;
			case 'pay_stub_transaction_summary':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();
				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'PayStubTransactionSummaryReport' );

				break;
			case 'payroll_export_report':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				this.onCloseClick();
				IndexViewController.openReport( LocalCacheData.current_open_primary_controller, 'PayrollExportReport' );

				break;
			case 'close_pay_period':
				grid = current_step_ui.pay_stub_close;
				ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				data_array = [];
				for ( var i = 0; i < ids.length; i++ ) {
					data = {};
					data.id = ids[i];
					data.status_id = 20;
					data_array.push( data );
				}
				ProgressBar.showOverlay();
				this.api_pay_period.setPayPeriod( data_array, {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							$this.buildCurrentStepData();
						} else {
							TAlertManager.showErrorAlert( result );
						}
					}
				} );
				break;
			case 'authorization_timesheet':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );
				IndexViewController.goToView( 'TimeSheetAuthorization' );
				this.onCloseClick();
				break;

			//Process Payments Button Click
			case 'direct_deposit':
				Global.addViewTab( this.wizard_id, $.i18n._( 'Process Payroll' ), window.location.href );

				var pay_stub_transfer_grid = current_step_ui.pay_stub_transfer;
				var data = {
					transaction_source_data: $this.transaction_source_data,
					filter_data: {
						pay_period_id: pay_stub_transfer_grid.grid.jqGrid( 'getGridParam', 'selarrrow' )
					}
				};
				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', data );
				this.onCloseClick();
				break;
		}
	}

	onGridSelectRow( e ) {
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var grid;
		var ids;

		if ( this.current_step === 5 ) {
			grid = current_step_ui.lock_pay_period;
			ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );

			if ( ids.length < 1 ) {
				current_step_ui.lock.addClass( 'disable-image' );
				current_step_ui.unlock.addClass( 'disable-image' );
			} else {
				current_step_ui.lock.removeClass( 'disable-image' );
				current_step_ui.unlock.removeClass( 'disable-image' );
			}

		} else if ( this.current_step === 7 ) {
			grid = current_step_ui.pay_stub_generate;
			ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
			if ( ids.length < 1 ) {
				current_step_ui.generate_pay_stub.addClass( 'disable-image' );
			} else {
				current_step_ui.generate_pay_stub.removeClass( 'disable-image' );
			}
		} else if ( this.current_step === 8 ) {
			var pay_stub_transfer_grid = current_step_ui.pay_stub_transfer;
			var pay_stub_transaction_grid = current_step_ui.pay_stub_transaction;

			var pay_period_ids = pay_stub_transfer_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
			if ( pay_period_ids.length < 1 ) {
				this.content_div.find( '#directDepositIcon' ).addClass( 'disable-image' );

				var grid = current_step_ui.pay_stub_transaction;
				grid.grid.trigger( 'reloadGrid' );
				this.setGridAutoHeight( grid, this.transaction_source_data.length );
				this.showNoResultCover( grid.grid.parents( '.grid-div' ) );
				pay_stub_transaction_grid.grid.show();

			} else {
				this.content_div.find( '#directDepositIcon' ).removeClass( 'disable-image' );
				var $this = this;
				var grid = current_step_ui.pay_stub_transaction;

				// A similar call to this api function is in the Process Transactions Popup
				this.api_pay_stub_transaction.getPayPeriodTransactionSummary( { pay_period_id: pay_period_ids }, {
					onResult: function( result ) {
						$this.transaction_source_data = result.getResult();
						if ( $this.transaction_source_data.length > 0 ) {
							$this.removeNoResultCover( grid.grid.parents( '.grid-div' ) );

							grid.setData( $this.transaction_source_data );
							$this.setGridAutoHeight( grid, $this.transaction_source_data.length );
						} else {
							$this.showNoResultCover( grid );
						}

						pay_stub_transaction_grid.grid.show();
					}
				} );
			}

		} else if ( this.current_step === 9 ) {
			grid = current_step_ui.pay_stub_close;
			ids = grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
			if ( ids.length < 1 ) {
				current_step_ui.close.addClass( 'disable-image' );
			} else {
				current_step_ui.close.removeClass( 'disable-image' );
			}

		}
	}

	setGridAutoHeight( grid, length ) {
		if ( length > 0 && length < 10 ) {
			grid.grid.setGridHeight( length * 23 );
		} else if ( length > 10 ) {
			grid.grid.setGridHeight( 230 );
		}
	}

	buildCurrentStepData() {

		if ( !this.stepsDataDic[1] ) {
			return;
		}

		var pay_period_id = this.stepsDataDic[1].pay_period_id;

		if ( !pay_period_id || pay_period_id.length == 0 ) {
			pay_period_id = [0];
		}
		var args = {};
		var grid;
		var $this = this;
		var source_data;

		var current_step_ui = this.stepsWidgetDic[this.current_step];

		switch ( this.current_step ) {
			case 2:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pending_requests = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.pending_request;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );

						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;
			case 3:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.exceptions_high = true;
				args.filter_columns.exceptions_medium = true;
				args.filter_columns.exceptions_low = true;
				args.filter_columns.exceptions_critical = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.exceptions;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );
						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;
			case 4:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.verified_timesheets = true;
				args.filter_columns.pending_timesheets = true;
				args.filter_columns.total_timesheets = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.timesheet;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );

						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;
			case 5:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.status = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.lock_pay_period;
						grid.setData( source_data );
						$this.setGridSelection( grid, source_data );

						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;
			case 6:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.ps_amendments = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.pay_stub_amendments;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );
						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;
			case 7:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs_open = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.pay_stub_generate;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );
						$this.setGridAutoHeight( grid, source_data.length );
					}
				} );
				break;
			case 8:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs_open = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				//this if statement is only used for step 8 to prevent populating the transactions grid
				if ( TTUUID.isUUID( pay_period_id ) && pay_period_id != TTUUID.zero_id ) {
					this.api_pay_period.getPayPeriod( args, true, {
						onResult: function( result ) {
							source_data = result.getResult();
							grid = current_step_ui.pay_stub_transfer;
							grid.setData( source_data );

							$this.setGridSelection( grid, source_data );
							$this.setGridAutoHeight( grid, source_data.length );

							var pay_period_ids = [];
							for ( var i in source_data ) {
								pay_period_ids.push( source_data[i].id );
							}

							$this.api_pay_stub_transaction.getPayPeriodTransactionSummary( { pay_period_id: pay_period_ids }, {
								onResult: function( result ) {
									$this.transaction_source_data = result.getResult();
									grid = current_step_ui.pay_stub_transaction;
									if ( typeof $this.transaction_source_data == 'object' && $this.transaction_source_data.length > 0 ) {
										grid.setData( $this.transaction_source_data );
									} else {
										grid.setData( [] );
										$this.showNoResultCover( grid.grid.parents( '.grid-div' ) );
									}
									$this.setGridAutoHeight( grid, $this.transaction_source_data.length );
									grid.grid.find( '.cbox' ).prop( 'checked', true );

									TTPromise.resolve( 'wizard', 'step8' );
								}
							} );

						}
					} );
				}

				break;
			case 9:
				args.filter_columns = {};
				args.filter_columns.id = true;
				args.filter_columns.status = true;
				args.filter_columns.start_date = true;
				args.filter_columns.end_date = true;
				args.filter_columns.transaction_date = true;
				args.filter_columns.pay_stubs_open = true;
				args.filter_data = {};
				args.filter_data.id = pay_period_id;

				this.api_pay_period.getPayPeriod( args, true, {
					onResult: function( result ) {
						source_data = result.getResult();
						grid = current_step_ui.pay_stub_close;
						grid.setData( source_data );

						$this.setGridSelection( grid, source_data );
						$this.setGridAutoHeight( grid, source_data.length );

					}
				} );
				break;

		}
	}

	setCurrentStepValues() {

		if ( !this.stepsDataDic[this.current_step] ) {
			if ( this.current_step === 1 ) {
				this.setLastPayPeriod();
			}
			return;
		} else {
			var current_step_data = this.stepsDataDic[this.current_step];
			var current_step_ui = this.stepsWidgetDic[this.current_step];
		}

		switch ( this.current_step ) {
			case 1:
				if ( current_step_data.pay_period_id ) {
					if ( current_step_data.pay_period_id === TTUUID.zero_id || current_step_data.pay_period_id[0] === TTUUID.zero_id ) {
						this.setLastPayPeriod();
					} else {
						current_step_ui.pay_period_id.setValue( current_step_data.pay_period_id );
					}
				}
				break;
			case 2:
				break;
			case 3:
				break;
		}
	}

	setGridSelection( grid, source_data ) {
		if ( source_data ) {
			for ( var i = 0; i < source_data.length; i++ ) {
				var content = source_data[i];
				grid.grid.jqGrid( 'setSelection', content['id'], false );
			}
		}
	}

	setLastPayPeriod() {
		var $this = this;
		this.api_pay_period.getLastPayPeriod( {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( result_data && result_data[0] && result_data[0].id ) {
					$this.stepsWidgetDic[1].pay_period_id.setValue( result_data );
				} else {
					$this.stepsWidgetDic[1].pay_period_id.setValue( TTUUID.zero_id );
				}
			}
		} );
	}

	saveCurrentStep() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		//Error: TypeError: current_step_ui is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20150126-115958 line 2 > eval line 989
		if ( !current_step_ui ) {
			return;
		}
		switch ( this.current_step ) {
			case 1:
				current_step_data.pay_period_id = current_step_ui.pay_period_id.getValue();
				break;
			case 2:
				break;
		}
	}

	setDefaultDataToSteps() {

		if ( !this.default_data ) {
			return null;
		}

//		  this.stepsDataDic[2] = {};
//		  this.stepsDataDic[3] = {};
//
//		  if ( this.getDefaultData( 'user_id' ) ) {
//			  this.stepsDataDic[3].user_id = this.getDefaultData( 'user_id' );
//		  }
//
//		  if ( this.getDefaultData( 'pay_period_id' ) ) {
//			  this.stepsDataDic[2].pay_period_id = this.getDefaultData( 'pay_period_id' );
//		  }
	}

}