import { WizardStep } from '@/global/widgets/wizard/WizardStep';

export class PayrollRemittanceAgencyEventWizardStepPublish extends WizardStep {
	constructor( options = {} ) {
		_.defaults( options, {
			name: 'publish',
			api: null,
			el: $( '.wizard.process_transactions_wizard' )
		} );

		super( options );
	}

	init() {
		this.api = TTAPI.APIPayrollRemittanceAgencyEvent;
		this.render();
	}

	getPreviousStepName() {
		return 'submit';
	}

	_render() {
		this.setTitle( $.i18n._( 'Publish Information for Employees' ) );
		this.setInstructions( $.i18n._( 'Publish forms for employees to access online' ) + ': ' );

		var $this = this;
		this.getWizardObject().getPayrollRemittanceAgencyEventById( this.getWizardObject().selected_remittance_agency_event_id, null, function( result ) {
			$this.getWizardObject().selected_remittance_agency_event = result;
			$this.getWizardObject().buildEventDataBlock( 'payroll_remittance_agency_event_wizard-publish-event_details', result );
			$this.initCardsBlock();

			switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
				case 'T4':
					$this.addButton( 'printIcon',
						'payroll_remittance_agency-35x35.png',
						$.i18n._( 'Publish' ),
						$.i18n._( 'Publish T4 forms for employees to access online with their own login under Payroll -> Government Documents.' )
					);

					$this.addButton( 'EmployeeT4',
						'print-35x35.png',
						$.i18n._( 'Employee T4 Forms' ),
						$.i18n._( 'Print employee T4 forms for distribution to employees by hand or mail.' )
					);
					break;
				case 'T4A':
					$this.addButton( 'printIcon',
						'payroll_remittance_agency-35x35.png',
						$.i18n._( 'Publish' ),
						$.i18n._( 'Publish T4A forms for employees to access online with their own login under Payroll -> Government Documents.' )
					);

					$this.addButton( 'EmployeeT4A',
						'print-35x35.png',
						$.i18n._( 'Employee T4A Forms' ),
						$.i18n._( 'Print employee T4A forms for distribution to employees by hand or mail.' )
					);
					break;

				//US
				case 'FW2':
					$this.addButton( 'printIcon',
						'payroll_remittance_agency-35x35.png',
						$.i18n._( 'Publish' ),
						$.i18n._( 'Publish W2 forms for employees to access online with their own login under Payroll -> Government Documents.' )
					);

					$this.addButton( 'EmployeeW2',
						'print-35x35.png',
						$.i18n._( 'Print employee W2 Forms' ) + ' (' + $.i18n._( 'Optional' ) + ') ',
						$.i18n._( 'Print employee W2 forms for distribution to employees by hand or mail.' )
					);
					break;
				case 'F1099NEC':
					$this.addButton( 'printIcon',
						'payroll_remittance_agency-35x35.png',
						$.i18n._( 'Publish' ),
						$.i18n._( 'Publish 1099-NEC forms for recipients to access online with their own login under Payroll -> Government Documents.' )
					);

					$this.addButton( 'Employee1099Nec',
						'print-35x35.png',
						$.i18n._( 'Print employee 1099-NEC Forms' ) + ' (' + $.i18n._( 'Optional' ) + ') ',
						$.i18n._( 'Print employee 1099-NEC forms for distribution to recipients by hand or mail.' )
					);
					break;
			}

			$this.getWizardObject().enableButtons();
		} );
	}

	_onNavigationClick( icon ) {
		var $this = this;
		switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
			//Canada
			case 'T4':
				switch ( icon ) {
					case 'printIcon':
						this.getWizardObject().disableForCommunity( function() {
							$this.publishReportToEmployee();
						} );
						break;
					case 'EmployeeT4':
						Global.loadScript( 'views/reports/t4_summary/T4SummaryReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
				}
				break;
			case 'T4A':
				switch ( icon ) {
					case 'printIcon':
						this.getWizardObject().disableForCommunity( function() {
							$this.publishReportToEmployee();
						} );
						break;
					case 'EmployeeT4A':
						Global.loadScript( 'views/reports/t4a_summary/T4ASummaryReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
				}
				break;

			//US
			case 'FW2':
				switch ( icon ) {
					case 'printIcon':
						this.getWizardObject().disableForCommunity( function() {
							$this.publishReportToEmployee();
						} );
						break;
					case 'EmployeeW2':
						Global.loadScript( 'views/reports/formw2/FormW2ReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
				}
				break;
			case 'F1099NEC':
				switch ( icon ) {
					case 'printIcon':
						this.getWizardObject().disableForCommunity( function() {
							$this.publishReportToEmployee();
						} );

						break;
					case 'Employee1099Nec':
						Global.loadScript( 'views/reports/form1099/Form1099NecReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
				}
				break;

		}
	}

	publishReportToEmployee() {
		this.api.getReportData( this.getWizardObject().selected_remittance_agency_event_id, 'pdf_form_publish_employee', {
			onResult: function( result ) {
				var retval = result.getResult();

				if ( retval.api_retval ) {
					UserGenericStatusWindowController.open( retval.api_retval, LocalCacheData.getLoginUser().id, function() {
					} );
				} else {
					TAlertManager.showAlert( $.i18n._( 'No results found.' ), $.i18n._( 'Warning' ), function() {
					} );
				}
			}
		} );
	}

	getPDFForm( scriptPath ) {
		Global.loadScript( scriptPath, function() {
			$this.getWizardObject().getReport( 'pdf_form' );
		} );
	}
}