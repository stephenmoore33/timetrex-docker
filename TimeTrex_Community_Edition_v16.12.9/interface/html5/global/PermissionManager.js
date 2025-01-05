export var PermissionManager = ( function() {

	var validate = function( name, value ) {
		var permission = PermissionManager.getPermissionData();

		//Error: Uncaught TypeError: Cannot read property 'punch' of null in /interface/html5/global/PermissionManager.js?v=8.0.0-20141230-115759 line 6
		if ( !permission || !Global.isSet( permission[name] ) || !Global.isSet( permission[name][value] ) ) {
			return false;
		} else {
			return permission[name][value];
		}
	};

	var getPermissionLevel = function() {
		var permission = PermissionManager.getPermissionData();

		if ( permission && permission['_system'] && permission['_system']['level'] ) {
			return permission['_system']['level'];
		}

		return 0;
	};

	var subJobApplicationValidate = function( viewId ) {
		var permission_section = getPermissionSectionByViewId( viewId );

		if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
			return false;
		} else if ( PermissionManager.validate( permission_section, 'view' ) ||
			PermissionManager.validate( permission_section, 'edit' ) || PermissionManager.validate( permission_section, 'edit_child' ) ) {
//			return true; // hide the tab until the API complete.
			return false;
		}

		return false;

	};

	var HelpMenuValidateAdmin = function() {
		if ( PermissionManager.getPermissionLevel() >= 70 && PermissionManager.validate( 'user', 'edit' ) ) { //70=HR Manager or higher
			return true;
		}

		return false;
	};

	var HelpMenuValidateSupervisor = function() {
		if ( PermissionManager.getPermissionLevel() >= 40 && ( PermissionManager.validate( 'user', 'edit_child' ) || PermissionManager.validate( 'punch', 'edit_child' ) ) ) { //40=Supervisor (Subordinates Only)
			return true;
		}

		return false;
	};

	var checkTopLevelPermission = function( viewId ) {
		var permission_section = getPermissionSectionByViewId( viewId );

		var result = false;

		if ( viewId === 'About' ) {
			return true;
		}

		//TypeError: Cannot read property 'product_edition_id' of null
		//BUG#2066 - fail gracefully
		// Since LocalCacheData.getCurrentCompany() is require data, if the user was logged out or in the process of logging out, it could trigger sendErrorReport()
		//   so first check that 'current_company' exists in the LocalCacheData to avoid calling getCurrentCompany() if it doesn't exist.
		if ( Global.isSet( LocalCacheData ) == false || LocalCacheData.isLocalCacheExists( 'current_company' ) == false || Global.isSet( LocalCacheData.getCurrentCompany() ) == false ) {
			return false;
		}

		switch ( viewId ) {
			case 'GridTest':
			case 'WidgetTest':
			case 'AwesomeboxTest':
			case 'UIKitSample':
			case 'UIKitChildSample':
				result = true;
				break;
			case 'JobInvoice':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'add' ) ||
					PermissionManager.validate( permission_section, 'edit' ) ||
					PermissionManager.validate( permission_section, 'edit_own' ) ) {
					result = true;
				}
				break;
			case 'PaymentGateway':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ||
					PermissionManager.validate( permission_section, 'edit_own' ) ) {
					result = true;
				}
				break;
			case 'InvoiceConfig':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ||
					PermissionManager.validate( permission_section, 'edit_own' ) ) {
					result = true;
				}
				break;
			case 'CustomField':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) &&
					PermissionManager.validate( permission_section, 'edit' ) ) {
					result = true;
				}
				break;
			case 'InOut':
				if ( PermissionManager.validate( permission_section, 'enabled' ) && PermissionManager.validate( permission_section, 'punch_in_out' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Employee':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;
			case 'LegalEntity':
			case 'PayrollRemittanceAgency':
			case 'RemittanceSourceAccount':
			case 'UserTitle':
			case 'UserGroup':
			case 'UserDefault':
			case 'EthnicGroup':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) && PermissionManager.validate( permission_section, 'add' ) ) {
					result = true;
				}
				break;
			case 'RemittanceDestinationAccount': //Uncomment this to enable Employee -> Payment Methods for regular employees. -- Currently Supervisors/Payroll Admins/Admins can see this.
			case 'Punches':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) || PermissionManager.validate( permission_section, 'edit_child' ) ) {
					result = true;
				}
				break;
			case 'GovernmentDocument':
			case 'Exception':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) || PermissionManager.validate( permission_section, 'view_own' ) ) {
					result = true;
				}
				break;
			case 'Company':
			case 'LoginUserContact':
			case 'LoginUserPreference':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit_own' ) ) {
					result = true;
				}
				break;
			case 'Companies':
				if ( ( Global.getProductEdition() >= 15 ) &&
					PermissionManager.validate( permission_section, 'enabled' ) && PermissionManager.validate( permission_section, 'view' ) ) {
					result = true;
				}
				break;
			case 'SavedReport':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else {
					result = true;
				}
				break;
			case 'PermissionControl':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ) {
					result = true;
				}
				break;
			case 'DocumentGroup':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ) {
					result = true;
				}
				break;

			case 'PayPeriodSchedule':
			case 'Branch':
			case 'Department':
			case 'HierarchyControl':
			case 'WageGroup':
			case 'Station':
			case 'Currency':
			case 'PayStubEntryAccount':
			case 'CompanyTaxDeduction':
			case 'PolicyGroup':
			case 'SchedulePolicy':
			case 'RoundIntervalPolicy':
			case 'MealPolicy':
			case 'BreakPolicy':
			case 'OvertimePolicy':
			case 'PremiumPolicy':
			case 'ExceptionPolicyControl':
			case 'AccrualPolicy':
			case 'AbsencePolicy':
			case 'HolidayPolicy':
			case 'RecurringHoliday':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ) {
					result = true;
				}
				break;
			case 'RequestAuthorization':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'request', 'authorize' ) && ( PermissionManager.validate( permission_section, 'view' ) || PermissionManager.validate( permission_section, 'view_child' ) ) ) {
					result = true;
				}
				break;
			case 'TimeSheetAuthorization':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'punch', 'authorize' ) && ( PermissionManager.validate( permission_section, 'view' ) || PermissionManager.validate( permission_section, 'view_child' ) ) ) {
					result = true;
				}
				break;
			case 'ExpenseAuthorization':
				if ( !( Global.getProductEdition() >= 25 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'user_expense', 'authorize' ) && ( PermissionManager.validate( permission_section, 'view' ) || PermissionManager.validate( permission_section, 'view_child' ) ) ) {
					result = true;
				}
				break;
			case 'UserExpense':
				if ( !( Global.getProductEdition() >= 25 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ) {
					result = true;
				}
				break;
			case 'ImportCSV':
				//This is the Company -> Import icon, which should only be displayed if 'company','enabled' is also allowed.
				if ( PermissionManager.validate( 'company', 'enabled' ) ) {
					result = importValidate();
				} else {
					result = false;
				}
				break;
			case 'ImportCSVSchedule':
				result = importValidateFor( 'schedule' );
				break;
			case 'ImportCSVBranch':
				result = importValidateFor( 'branch' );
				break;
			case 'ImportCSVDepartment':
				result = importValidateFor( 'department' );
				break;
			case 'ImportCSVWage':
				result = importValidateFor( 'wage' );
				break;
			case 'ImportCSVEmployeeBankAccount':
				result = importValidateFor( 'user' );
				break;
			case 'ImportCSVEmployee':
				result = importValidateFor( 'user' );
				break;
			case 'ImportCSVEmployeeContact':
				result = importValidateFor( 'user_contact' );
				break;
			case 'ImportCSVEmployeeSkill':
				result = importValidateFor( 'user_skill' );
				break;
			case 'ImportCSVEmployeeMembership':
				result = importValidateFor( 'user_membership' );
				break;
			case 'ImportCSVEmployeeEducation':
				result = importValidateFor( 'user_education' );
				break;
			case 'ImportCSVEmployeeLicense':
				result = importValidateFor( 'user_license' );
				break;
			case 'ImportCSVEmployeeLanguage':
				result = importValidateFor( 'user_language' );
				break;
			case 'ImportCSVPayStubAmendment':
				result = importValidateFor( 'pay_stub_amendment' );
				break;
			case 'ImportCSVJob':
				result = importValidateFor( 'job' );
				break;
			case 'ImportCSVJobItem':
				result = importValidateFor( 'job_item' );
				break;
			case 'PayrollProcessWizard':
				if ( PermissionManager.validate( 'pay_stub', 'add' ) &&
					PermissionManager.validate( 'pay_stub', 'edit' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'QuickStartWizard':
				if ( PermissionManager.validate( 'pay_period_schedule', 'add' ) &&
					PermissionManager.validate( 'user_preference', 'edit' ) &&
					PermissionManager.validate( 'policy_group', 'edit' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'AccrualBalance':
			case 'Accrual':
			case 'Request':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ) {
					result = true;
				}
				break;
			case 'ScheduleShift':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ||
					PermissionManager.validate( permission_section, 'edit_child' ) ) {
					result = true;
				}
				break;
			case 'RecurringScheduleControl':
			case 'RecurringScheduleTemplateControl':
			case 'MessageControl':
				if ( PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Notification':
				result = true; // Notification always returns true as notifications should always be enabled.
				break;
			case 'UserPreference':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'edit' ) ||
					PermissionManager.validate( permission_section, 'edit_child' ) ) {
					result = true;
				}
				break;
			case 'Document':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_private' ) ) {
					result = true;
				}
				break;
			case 'ChangePassword':
				if ( PermissionManager.validate( permission_section, 'edit_own_password' ) ||
					PermissionManager.validate( permission_section, 'edit_own_phone_password' ) ) {
					result = true;
				}
				break;
			case 'ActiveShiftReport':
				if ( PermissionManager.validate( 'report', 'view_active_shift' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'UserSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_user_information' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'AuditTrailReport':
				if ( PermissionManager.validate( 'report', 'view_system_log' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'ScheduleSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_schedule_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'TimesheetSummaryReport':
			case 'TimesheetDetailReport':
				if ( PermissionManager.validate( 'report', 'view_timesheet_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'PunchSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_punch_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'AccrualBalanceSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_accrual_balance_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'ExceptionSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_exception_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'PayStubTransactionSummaryReport':
			case 'PayStubSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_pay_stub_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'PayrollExportReport':
				if ( PermissionManager.validate( 'report', 'view_payroll_export' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'GeneralLedgerSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_general_ledger_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'ExpenseSummaryReport':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'report', 'view_expense' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'JobSummaryReport':
			case 'JobInformationReport':
			case 'JobItemInformationReport':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'job_report', 'view_job_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'JobAnalysisReport':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'job_report', 'view_job_analysis' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'InvoiceTransactionSummaryReport':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'invoice_report', 'view_transaction_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'RemittanceSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_remittance_summary' ) &&
					countryPermissionValidate( 'CA' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'T4SummaryReport':
			case 'T4ASummaryReport':
				if ( PermissionManager.validate( 'report', 'view_t4_summary' ) &&
					countryPermissionValidate( 'CA' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'TaxSummaryReport':
				if ( PermissionManager.validate( 'report', 'view_generic_tax_summary' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Form940Report':
				if ( PermissionManager.validate( 'report', 'view_form940' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Form941Report':
				if ( PermissionManager.validate( 'report', 'view_form941' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Form1099NecReport':
				if ( PermissionManager.validate( 'report', 'view_form1099nec' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'FormW2Report':
				if ( PermissionManager.validate( 'report', 'view_formW2' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'USStateUnemploymentReport':
				if ( PermissionManager.validate( 'report', 'view_us_state_unemployment' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'USPERSReport':
				if ( PermissionManager.validate( 'report', 'view_us_pers' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'USEEOReport':
				if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'report', 'view_us_eeo' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'AffordableCareReport':
				if ( Global.getProductEdition() >= 15 &&
					PermissionManager.validate( 'report', 'view_affordable_care' ) &&
					countryPermissionValidate( 'US' )
				) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'UserQualificationReport':
				if ( PermissionManager.validate( 'hr_report', 'user_qualification' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'KPIReport':
				if ( PermissionManager.validate( 'hr_report', 'user_review' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'UserRecruitmentSummaryReport':
			case 'UserRecruitmentDetailReport':
				if ( !( Global.getProductEdition() >= 25 ) ) {
					result = false;
				} else if ( PermissionManager.validate( 'recruitment_report', 'user_recruitment' ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'Client':
			case 'ClientContact':
			case 'InvoiceDistrict':
			case 'ClientPayment':
			case 'Invoice':
			case 'InvoiceTransaction':
			case 'Product':
			case 'ClientGroup':
			case 'ProductGroup':
			case 'TaxPolicy':
			case 'ShippingPolicy':
			case 'AreaPolicy':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;
			case 'Job':
			case 'JobItem':
			case 'JobGroup':
			case 'JobItemGroup':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;
			case 'PunchTag':
			case 'PunchTagGroup':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;
			case 'GEOFence':
				if ( !( Global.getProductEdition() >= 20 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ) {
					result = true;
				}
				break;
			case 'ExpensePolicy':
			case 'LoginUserExpense':
			case 'JobVacancy':
			case 'JobApplicant':
			case 'JobApplication':

				if ( !( Global.getProductEdition() >= 25 ) ) {
					result = false;
				} else if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;
			case 'ROE':
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) &&
					countryPermissionValidate( 'CA' ) ) {
					result = true;
				}
				break;
			case 'GeneratePayStubs':
				if ( PermissionManager.validate( 'pay_period_schedule', 'enabled' )
					&& ( PermissionManager.validate( 'pay_period_schedule', 'edit' ) || PermissionManager.validate( 'pay_period_schedule', 'edit_own' ) )
					&& ( PermissionManager.validate( 'pay_stub', 'view' ) || PermissionManager.validate( 'pay_stub', 'view_child' ) ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			case 'PayStubTransaction':
				if ( PermissionManager.validate( 'pay_stub', 'enabled' )
					&& ( PermissionManager.validate( 'pay_stub', 'view' ) || PermissionManager.validate( 'pay_stub', 'view_child' ) ) ) {
					result = true;
				} else {
					result = false;
				}
				break;
			default:
				if ( !PermissionManager.validate( permission_section, 'enabled' ) ) {
					result = false;
				} else if ( PermissionManager.validate( permission_section, 'view' ) ||
					PermissionManager.validate( permission_section, 'view_own' ) ||
					PermissionManager.validate( permission_section, 'view_child' ) ) {
					result = true;
				}
				break;

		}

		return result;
	};

	var countryPermissionValidate = function( key ) {

		var country_array = LocalCacheData.getUniqueCountryArray();

		for ( var i = 0; i < country_array.length; i++ ) {
			if ( key === country_array[i] ) {
				return true;
			}
		}

		return false;

	};

	var importValidate = function() {

		var result = false;

		if ( importValidateFor( 'branch' ) ||
			importValidateFor( 'payperiod' ) ||
			importValidateFor( 'schedule' ) ||
			importValidateFor( 'user' ) ||
			importValidateFor( 'department' ) ||
			importValidateFor( 'client' ) ||
			importValidateFor( 'job' ) ||
			importValidateFor( 'jobitem' ) ||
			importValidateFor( 'wage' ) ||
			importValidateFor( 'punch' ) ||
			importValidateFor( 'paystubamendment' ) ||
			importValidateFor( 'accrual' ) ) {

			result = true;
		}

		return result;
	};

	var importValidateFor = function( key ) {
		if ( PermissionManager.validate( key, 'add' ) &&
			( PermissionManager.validate( key, 'edit' ) || PermissionManager.validate( key, 'edit_child' ) ) ) {
			return true;
		}

		return false;
	};

	var getPermissionSectionByViewId = function( viewId ) {

		var permission_section = '';

		switch ( viewId ) {
			case 'AccumulatedTime':
				permission_section = 'user_date_total';
				break;
			case 'PaymentGateway':
				permission_section = 'payment_gateway';
				break;
			case 'InvoiceConfig':
				permission_section = 'invoice_config';
				break;
			case 'AreaPolicy':
				permission_section = 'area_policy';
				break;
			case 'ShippingPolicy':
				permission_section = 'shipping_policy';
				break;
			case 'GovernmentDocument':
				permission_section = 'government_document';
				break;
			case 'TaxPolicy':
				permission_section = 'tax_policy';
				break;
			case 'Product':
				permission_section = 'product';
				break;
			case 'RemittanceSourceAccount':
				permission_section = 'remittance_source_account';
				break;
			case 'RemittanceDestinationAccount':
				permission_section = 'remittance_destination_account';
				break;
			case 'ScheduleShift':
			case 'Schedule':
				permission_section = 'schedule';
				break;
			case 'TimeSheet':
			case 'ManualTimeSheet':
			case 'UserDateTotalParent':
			case 'UserDateTotal':
			case 'InOut':
			case 'Punches':
			case 'TimeSheetAuthorization':
			case 'Exception':
				permission_section = 'punch';
				break;
			case 'AccrualBalance':
			case 'Accrual':
				permission_section = 'accrual';
				break;
			case 'Job':
			case 'JobGroup':
				permission_section = 'job';
				break;
			case 'PunchTag':
			case 'PunchTagGroup':
				permission_section = 'punch_tag';
				break;
			case 'PolicyGroup':
				permission_section = 'policy_group';
				break;
			case 'PayCode':
				permission_section = 'pay_code';
				break;
			case 'PayFormulaPolicy':
				permission_section = 'pay_formula_policy';
				break;
			case 'ContributingPayCodePolicy':
				permission_section = 'contributing_pay_code_policy';
				break;
			case 'ContributingShiftPolicy':
				permission_section = 'contributing_shift_policy';
				break;
			case 'AbsencePolicy':
				permission_section = 'absence_policy';
				break;
			case 'MealPolicy':
				permission_section = 'meal_policy';
				break;
			case 'ExpensePolicy':
				permission_section = 'expense_policy';
				break;
			case 'BreakPolicy':
				permission_section = 'break_policy';
				break;
			case 'HolidayPolicy':
			case 'RecurringHoliday':
				permission_section = 'holiday_policy';
				break;
			case 'PremiumPolicy':
				permission_section = 'premium_policy';
				break;
			case 'RegularTimePolicy':
				permission_section = 'regular_time_policy';
				break;
			case 'OvertimePolicy':
				permission_section = 'over_time_policy';
				break;
			case 'RoundIntervalPolicy':
				permission_section = 'round_policy';
				break;
			case 'Employee':
			case 'LoginUserContact':
			case 'ChangePassword':
				permission_section = 'user';
				break;
			case 'UserDefault':
				permission_section = 'user_default';
				break;
			case 'UserTitle':
				permission_section = 'user_title';
				break;
			case 'UserGroup':
				permission_section = 'user_group';
				break;
			case 'EthnicGroup':
				permission_section = 'ethnic_group';
				break;
			case 'LegalEntity':
				permission_section = 'legal_entity';
				break;
			case 'PayrollRemittanceAgency':
				permission_section = 'payroll_remittance_agency';
				break;
			case 'MessageControl':
				permission_section = 'message';
				break;
			case 'Notification':
				permission_section = 'notification';
				break;
			case 'Wage':
			case 'WageGroup':
				permission_section = 'wage';
				break;
			case 'UserContact':
				permission_section = 'user_contact';
				break;
			case 'LoginUserExpense':
			case 'UserExpense':
				permission_section = 'user_expense';
				break;
			case 'UserSkill':
				permission_section = 'user_skill';
				break;
			case 'JobApplication':
				permission_section = 'job_application';
				break;
			case 'JobApplicant':
			case 'RecruitmentPortalConfig':
				permission_section = 'job_applicant';
				break;
			case 'UserLicense':
				permission_section = 'user_license';
				break;
			case 'UserMembership':
				permission_section = 'user_membership';
				break;
			case 'UserEducation':
				permission_section = 'user_education';
				break;
			case 'UserPreference':
			case 'LoginUserPreference':
				permission_section = 'user_preference';
				break;
			case 'UserLanguage':
				permission_section = 'user_language';
				break;
			case 'Company':
			case 'Companies':
				permission_section = 'company';
				break;
			case 'GEOFence':
				permission_section = 'geo_fence';
				break;
			case 'Qualification':
			case 'QualificationGroup':
				permission_section = 'qualification';
				break;
			case 'PayPeriodSchedule':
			case 'PayPeriods':
				permission_section = 'pay_period_schedule';
				break;
			case 'PayStubAmendment':
			case 'RecurringPayStubAmendment':
				permission_section = 'pay_stub_amendment';
				break;
			case 'PayStubTransactionSummaryReport':
			case 'PayStub':
				permission_section = 'pay_stub';
				break;
			case 'Branch':
				permission_section = 'branch';
				break;
			case 'Department':
				permission_section = 'department';
				break;
			case 'HierarchyControl':
				permission_section = 'hierarchy';
				break;
			case 'Station':
				permission_section = 'station';
				break;
			case 'JobVacancy':
			case 'PortalJobVacancy':
				permission_section = 'job_vacancy';
				break;
			case 'PayStubEntryAccount':
				permission_section = 'pay_stub_account';
				break;
			case 'ROE':
				permission_section = 'roe';
				break;
			case 'CustomField':
				permission_section = 'custom_field';
				break;
			case 'Currency':
				permission_section = 'currency';
				break;
			case 'PermissionControl':
				permission_section = 'permission';
				break;
			case 'CompanyTaxDeduction':
				permission_section = 'company_tax_deduction';
				break;
			case 'UserTaxDeduction':
				permission_section = 'user_tax_deduction';
				break;
			case 'Request':
				permission_section = 'request';
				break;
			case 'RequestAuthorization':
			case 'ExpenseAuthorization':
				permission_section = 'authorization';
				break;
			case 'Document':
			case 'DocumentGroup':
				permission_section = 'document';
				break;
			case 'SchedulePolicy':
				permission_section = 'schedule_policy';
				break;
			case 'AccrualPolicyAccount':
			case 'AccrualPolicy':
				permission_section = 'accrual_policy';
				break;
			case 'Client':
			case 'ClientContact':
			case 'InvoiceDistrict':
			case 'ClientPayment':
				permission_section = 'client';
				break;
			case 'InvoiceTransaction':
				permission_section = 'transaction';
				break;
			case 'JobItemGroup':
			case 'JobItem':
				permission_section = 'job_item';
				break;
			case 'SavedReport':
				permission_section = 'report';
				break;
			case 'RecurringScheduleControl':
				permission_section = 'recurring_schedule';
				break;
			case 'RecurringScheduleTemplateControl':
				permission_section = 'recurring_schedule_template';
				break;
			case 'KPI':
			case 'KPIGroup':
				permission_section = 'kpi';
				break;
			case 'UserReviewControl':
				permission_section = 'user_review';
				break;
			case 'ExceptionPolicyControl':
				permission_section = 'exception_policy';
				break;
			case 'ImportCSV':
				permission_section = 'import_csv';
				break;
			case 'Invoice':
			case 'JobInvoice':
				permission_section = 'invoice';
				break;
		}

		return permission_section;
	};

	var getPermissionData = function() {
		return LocalCacheData.getPermissionData();
	};

	return {
		checkTopLevelPermission: checkTopLevelPermission,
		validate: validate,
		getPermissionLevel: getPermissionLevel,
		getPermissionData: getPermissionData,
		HelpMenuValidateAdmin: HelpMenuValidateAdmin,
		HelpMenuValidateSupervisor: HelpMenuValidateSupervisor,
		importValidate: importValidate,
		subJobApplicationValidate: subJobApplicationValidate
	};

} )();
