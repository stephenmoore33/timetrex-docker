import { WizardStep } from '@/global/widgets/wizard/WizardStep';

export class PayrollRemittanceAgencyEventWizardStepSubmit extends WizardStep {
	constructor( options = {} ) {
		_.defaults( options, {
			name: 'summary',
			api: null,
			el: $( '.wizard.process_transactions_wizard' ),

			report_paths: {
				'FormW2ReportViewController': 'views/reports/formw2/FormW2ReportViewController'
			}

		} );

		super( options );
	}

	init() {
		this.render();

		this.api = TTAPI.APIPayrollRemittanceAgencyEvent;
	}

	getPreviousStepName() {
		return 'review';
	}

	getNextStepName() {
		var retval = false;
		switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
			//Canada
			case 'T4':
			case 'T4A':
				retval = 'publish';
				break;

			//US
			case 'FW2':
				//Only have a publish step when its a federal W2, since it includes copies of the W2s for State/Local.
				if ( this.getWizardObject().selected_remittance_agency_event.payroll_remittance_agency_obj.type_id == 10 ) { //10=Federal
					retval = 'publish';
				} else {
					retval = false;
				}

				break;

			case 'F1099NEC':
				retval = 'publish';
				break;

			default:
				retval = false;
				break;
		}

		return retval;
	}

	isRequiredButtonsClicked() {
		//Check to see if every button on this step has been clicked.
		//$this.getWizardObject().selected_remittance_agency_event.type_id
		if ( Object.keys( this.clicked_buttons ).length >= Object.keys( this.buttons ).length ) {
			return true;
		}

		return false;
	}

	_render() {
		this.setTitle( $.i18n._( 'Submit Verified Information' ) );
		this.setInstructions( $.i18n._( 'Submit verified information to the agency' ) + ': ' );

		var $this = this;
		this.getWizardObject().getPayrollRemittanceAgencyEventById( this.getWizardObject().selected_remittance_agency_event_id, null, function( result ) {
			$this.getWizardObject().selected_remittance_agency_event = result;
			$this.getWizardObject().buildEventDataBlock( 'payroll_remittance_agency_event_wizard-review-event_details', result );
			$this.initCardsBlock();

			if ( $this.getWizardObject().selected_remittance_agency_event.status_id == 15 ) { //15=Full Service
				switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
					//Canada
					case 'T4SD':
						$this.addButton( 'paymentMethodIcon',
							'payment_methods-35x35.png',
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Transmit payment with TimeTrex Payment Services automatically.' )
						);
						break;
					case 'T4':
					case 'T4A':
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'eFile' ),
							$.i18n._( 'Transmit forms to the CRA with TimeTrex Payment Services automatically.' )
						);
						break;
					default:
						break;
				}
			} else {
				switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
					//Canada
					case 'T4':
					case 'T4A':
						$this.addButton( 'efileDownload',
							'export_to_efile-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;
					case 'ROE':
						$this.addButton( 'efileDownload',
							'export-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;

					//US
					case 'FW2':
						$this.addButton( 'efileDownload',
							'export_to_efile-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;
					case 'F1099NEC':
						$this.addButton( 'Government1099Nec',
							'print-35x35.png',
							$.i18n._( '1099-NEC Forms' ),
							$.i18n._( 'Generate the government 1099-NEC forms to print and file manually.' )
						);
						break;
					case 'F940':
						$this.addButton( 'Government940',
							'view_detail-35x35.png',
							$.i18n._( '940 Form' ),
							$.i18n._( 'Generate the 940 form to print and file manually.' )
						);
						$this.addButton( 'paymentMethodIcon',
							'payment_methods-35x35.png',
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
						);
						break;
					case 'F941':
						$this.addButton( 'Government941',
							'view_detail-35x35.png',
							$.i18n._( '941 Form' ),
							$.i18n._( 'Generate the 941 form to print and file manually.' )
						);
						$this.addButton( 'paymentMethodIcon',
							'payment_methods-35x35.png',
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
						);
						break;
					case 'PBJ':
						$this.addButton( 'efileDownload',
							'export-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;
					case 'EEO1':
					case 'EEOCA':
					case 'EEO4':
						$this.addButton( 'efileDownload',
							'export_to_efile-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;
					case 'RETIREMENT':
						$this.addButton( 'efileDownload',
							'export_to_efile-35x35.png',
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
						);
						break;
					case 'NEWHIRE':
						$this.addButton( 'taxReportsIcon',
							'tax_reports-35x35.png',
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report to file manually.' )
						);
						$this.addButton( 'efileDownload',
							'export-35x35.png',
							$.i18n._( 'Download Excel/CSV File (Optional)' ),
							$.i18n._( 'Download the Excel/CSV file to your computer for eFiling in the below step.' )
						);
						$this.addButton( 'eFileIcon',
							'export_to_efile-35x35.png',
							$.i18n._( 'File with Agency' ),
							$.i18n._( 'Navigate to the agency\'s website to file the necessary information.' )
						);
						break;

					//Generic
					case 'AUDIT':
					case 'REPORT':
						// $this.addButton( 'taxReportsIcon',
						// 	'tax_reports-35x35.png',
						// 	$.i18n._( 'Report' ),
						// 	$.i18n._( 'Generate the report to file manually.' )
						// );

						$this.addButton( 'printIcon',
							'print-35x35.png',
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report in PDF format to file manually.' )
						);

						$this.addButton( 'exportExcelIcon',
							'export_to_excel-35x35.png',
							$.i18n._( 'Excel Report' ),
							$.i18n._( 'Generate the report in Excel/CSV format to file manually.' )
						);

						$this.addButton( 'paymentMethodIcon',
							'payment_methods-35x35.png',
							$.i18n._( 'File Report' ),
							$.i18n._( 'Navigate to the agency\'s website to file the report manually.' )
						);
						break;
					case 'PAYMENT':
					case 'PAYMENT+REPORT':
						// $this.addButton( 'taxReportsIcon',
						// 	'tax_reports-35x35.png',
						// 	$.i18n._( 'Report' ),
						// 	$.i18n._( 'Generate the report to file manually.' )
						// );

						$this.addButton( 'printIcon',
							'print-35x35.png',
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report in PDF format to file manually.' )
						);

						$this.addButton( 'exportExcelIcon',
							'export_to_excel-35x35.png',
							$.i18n._( 'Excel Report' ),
							$.i18n._( 'Generate the report in Excel/CSV format to file manually.' )
						);

						$this.addButton( 'paymentMethodIcon',
							'payment_methods-35x35.png',
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
						);
						break;
					default:
						if ( $this.getWizardObject().selected_remittance_agency_event.payroll_remittance_agency_obj.country == 'US' &&
							(
								( $this.getWizardObject().selected_remittance_agency_event.payroll_remittance_agency_obj.type_id == 20 && $this.getWizardObject().selected_remittance_agency_event.payroll_remittance_agency_obj.parsed_agency_id.id == 20 ) //Type: 20=State, Agency ID: 20=Unemployment
								||
								$.inArray( 'UI', $this.getWizardObject().selected_remittance_agency_event.event_data.tax_codes ) !== -1 //CA, LA, NH, NY, MN combine UI with State tax filing.
							)
						) {
							$this.addButton( 'efileDownload',
								'export_to_efile-35x35.png',
								$.i18n._( 'Download eFile' ),
								$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
							);
							$this.addButton( 'eFileIcon',
								'export_to_efile-35x35.png',
								$.i18n._( 'Upload eFile' ),
								$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
							);
						} else {
							// $this.addButton( 'taxReportsIcon',
							// 	'tax_reports-35x35.png',
							// 	$.i18n._( 'Report' ),
							// 	$.i18n._( 'Generate the report to file manually.' )
							// );

							$this.addButton( 'printIcon',
								'print-35x35.png',
								$.i18n._( 'Report' ),
								$.i18n._( 'Generate the report in PDF format to file manually.' )
							);

							$this.addButton( 'exportExcelIcon',
								'export_to_excel-35x35.png',
								$.i18n._( 'Excel Report' ),
								$.i18n._( 'Generate the report in Excel/CSV format to file manually.' )
							);

							$this.addButton( 'paymentMethodIcon',
								'payment_methods-35x35.png',
								$.i18n._( 'Make Payment' ),
								$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
							);
						}

						break;
				}
			}

			$this.getWizardObject().enableButtons();
		} );
	}

	_onNavigationClick( icon ) {
		//When navigating away, link the wizard.
		var $this = this;
		if ( $this.getWizardObject().selected_remittance_agency_event.status_id == 15 ) { //15=Full Service
			switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
				case 'T4SD':
					switch ( icon ) {
						case 'paymentMethodIcon':
							this.paymentServicesClick( 'payment' );
							break;
					}
					break;
				case 'T4':
				case 'T4A':
					switch ( icon ) {
						case 'eFileIcon':
							this.paymentServicesClick( 'efile_xml' );
							break;
					}
					break;
			}
		} else {
			switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
				case 'T4':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/t4_summary/T4SummaryReportViewController', function() {
								$this.getWizardObject().getReport( 'efile_xml' );
							} );
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;
				case 'T4A':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/t4a_summary/T4ASummaryReportViewController', function() {
								$this.getWizardObject().getReport( 'efile_xml' );
							} );
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;
				case 'ROE':
					switch ( icon ) {
						case 'efileDownload':
							$this.getWizardObject().getReport( 'efile_xml' );
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;

				//US
				case 'FW2':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/formw2/FormW2ReportViewController', function() {
								$this.getWizardObject().getReport( 'efile' );
							} );
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;
				case 'F1099NEC':
					switch ( icon ) {
						case 'Government1099Nec':
							Global.loadScript( 'views/reports/form1099/Form1099NecReportViewController', function() {
								$this.getWizardObject().getReport( 'pdf_form_government' );
							} );
							break;
					}
					break;
				case 'F940':
					switch ( icon ) {
						case 'Government940':
							Global.loadScript( 'views/reports/form940/Form940ReportViewController', function() {
								$this.getWizardObject().getReport( 'pdf_form' );
							} );
							break;
						case  'paymentMethodIcon':
							//show report.
							this.urlClick( 'payment' );
							break;
					}
					break;
				case 'F941':
					switch ( icon ) {
						case 'Government941':
							Global.loadScript( 'views/reports/form941/Form941ReportViewController', function() {
								$this.getWizardObject().getReport( 'pdf_form' );
							} );
							break;
						case  'paymentMethodIcon':
							//show report.
							this.urlClick( 'payment' );
							break;
					}
					break;
				case 'PBJ':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/payroll_export/PayrollExportReportViewController', function() {
								$this.getWizardObject().getReport( 'payroll_export' );
							} );
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;
				case 'EEO1':
				case 'EEO1CA':
				case 'EEO4':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/us_eeo/USEEOReportViewController', function() {
								$this.getWizardObject().getReport( 'efile' );
							} );
							break;
						case 'eFileIcon':
							this.urlClick( 'file' ); //Redirect to 3rd party page.
							break;
					}
					break;
				case 'RETIREMENT':
					switch ( icon ) {
						case 'efileDownload':
							Global.loadScript( 'views/reports/us_pers/USPERSReportViewController', function() {
								$this.getWizardObject().getReport( 'efile' );
							} );
							break;
						case 'eFileIcon':
							this.urlClick( 'file' ); //Redirect to 3rd party page.
							break;
					}
					break;
				case 'NEWHIRE':
					switch ( icon ) {
						case 'taxReportsIcon_new_window':
							this.getWizardObject().showHTMLReport( 'TaxSummary', true );
							break;
						case 'taxReportsIcon':
							this.getWizardObject().showHTMLReport( 'TaxSummary' );
							break;
						case 'efileDownload':
							$this.getWizardObject().getReport( 'csv' ); //Use CSV until we get full eFile support.
							break;
						case 'eFileIcon':
							//show report.
							this.urlClick( 'file' );
							break;
					}
					break;
				//Generic
				default:
					if ( $this.getWizardObject().selected_remittance_agency_event.payroll_remittance_agency_obj.type_id == 20 ) {
						//US State Unemployment
						switch ( icon ) {
							case 'taxReportsIcon_new_window': //Show HTML report in new window
								this.getWizardObject().showHTMLReport( 'USStateUnemployment', true );
								break;
							case 'taxReportsIcon': //Show HTML report
								this.getWizardObject().showHTMLReport( 'USStateUnemployment' );
								break;
							case 'exportExcelIcon': //Show Excel Report
								$this.getWizardObject().getReport( 'csv' ); //Use CSV until we get full eFile support.
								break;
							case 'printIcon': //Show PDF Report
								$this.getWizardObject().getReport( 'pdf' ); //Use CSV until we get full eFile support.
								break;
							case 'efileDownload':
								Global.loadScript( 'views/reports/us_state_unemployment/USStateUnemploymentReportViewController', function() {
									$this.getWizardObject().getReport( 'efile' );
								} );
								break;
							case 'eFileIcon':
								this.urlClick( 'file' ); //Redirect to 3rd party page.
								break;
							case 'paymentMethodIcon':
								this.urlClick( 'payment' ); //Redirect to 3rd party page.
								break;
						}
					} else {
						switch ( icon ) {
							case 'taxReportsIcon_new_window': //Show HTML report in new window
								this.getWizardObject().showHTMLReport( 'TaxSummary', true );
								break;
							case 'taxReportsIcon': //Show HTML report
								this.getWizardObject().showHTMLReport( 'TaxSummary' );
								break;
							case 'exportExcelIcon': //Show Excel Report
								$this.getWizardObject().getReport( 'csv' ); //Use CSV until we get full eFile support.
								break;
							case 'printIcon': //Show PDF Report
								$this.getWizardObject().getReport( 'pdf' ); //Use CSV until we get full eFile support.
								break;
							case 'efileDownload':
								Global.loadScript( 'views/reports/tax_summary/TaxSummaryReportViewController', function() {
									$this.getWizardObject().getReport( 'efile' );
								} );
								break;
							case 'eFileIcon':
								this.urlClick( 'file' ); //Redirect to 3rd party page.
								break;
							case 'paymentMethodIcon':
								this.urlClick( 'payment' ); //Redirect to 3rd party page.
								break;
						}
					}
					break;
			}
		}
	}
}