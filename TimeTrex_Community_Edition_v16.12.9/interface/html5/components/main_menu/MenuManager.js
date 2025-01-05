// Imports
// Relies on Debug in @/global/Debug.js imported as a global in HTML tags.
// Relies on TopMenuManager imported as a global in main.js
import TTEventBus from '@/services/TTEventBus';

// TODO: Add in the wizards and dialog (In/Out) options etc.
// TODO: Check all items are present, compared to old menu. Check permissions!
// TODO: Fix the group duplicate/missing errors in console. This was just after the big menu update of all items.
// TODO: - Previously the link was parent -> subgroup -> item.
// TODO: - And now, the item needs to link direct to the parent, and that association has to be edited manually, so quite a lot to get through.
// TODO: Fix the menu width when the browser is resized down to mobile. This is related to the width override. Perhaps change the width in the SCSS source files.
// TODO: Do the colour of the topbar correctly using SCSS 'theme' files so that the theme can be properly changed in future.
// TODO: Add Account Menu dividers.
// TODO: Build new context menu with PrimeVue buttons, spend about 20 mins on seeing if we can edit an existing button.
// Otherwise use v-if vue options to show hide the Save and Save Next as diff buttons.
// right click menu mirrors the context menu. it has to be completely flat, rather than the grouping for context.
// context manager that controls both formats. start with the main menu class?
// TODO: later on: the CSS changes together with moving the company name and support chat links
// TODO: back burner: Ordering logic needs to be done for main menu. Do this after context menu.

class MenuManager {
	constructor() {
		this.event_bus = new TTEventBus( {
			component_id: 'menu_manager',
		} );
		this.menu_items = [];
		this.menu_locked = false; // Track state of menu built/unbuilt. Menu must be reset before items can be added. Otherwise there will be a mix of built and unbuilt items.
		window._main_menu = this; // TODO: Temp for local debugging.
	}

	lockMenu() {
		this.menu_locked = true;
	}
	unlockMenu() {
		this.menu_locked = false;
	}
	isMenuLocked() {
		return Boolean( this.menu_locked );
	}
	addItem( item ) {
		if( this.menu_locked ) {
			Debug.Error( 'Menu is built and locked. Reset menu before adding new items.', 'MenuManager.js', 'MenuManager', 'addItem', 2 );
			return false;
		}
		if( typeof item !== 'object' || item === null || item === undefined ) {
		Debug.Error( 'Invalid menu item', 'MenuManager.js', 'MenuManager', 'addItem', 2 );
			return false;
		}
		if( this.menu_items.filter( ( menu_item_check ) => menu_item_check.id === item.id ).length !== 0 ) {
			Debug.Error( 'Duplicate menu item. Item ID ('+ item.id +') already exists in menu.', 'MenuManager.js', 'MenuManager', 'addItem', 2 );
			return false;
		}
		item = new TTMenuItem( item );
		this.menu_items.push( item );
	}

	/**
	 * Get the previously built menu model in PrimeVue format without rebuilding the menu on every request.
	 * @returns {boolean|[]}
	 */
	getMenu() {
		if( !this.menu_locked ) {
			Debug.Error( 'Menu has not been built yet. Run convertMenuItemsToPrimeVueFormat() or rebuildMenu()', 'MenuManager.js', 'MenuManager', 'getMenu', 2 );
			return false;
		}
		return this.menu_items;
	}

	rebuildMenu() {
		this.initDefaultMenuItems();
		this.getMenu();
		return this.menu_items;
	}

	convertMenuItemsToPrimeVueFormat() {
		if( this.menu_locked ) {
			Debug.Error( 'Menu is already built and locked. Reset menu before rebuilding.', 'MenuManager.js', 'MenuManager', 'convertMenuItemsToPrimeVueFormat', 2 );
			return false;
		}

		// Starting build process, lock menu.
		this.lockMenu();

		var menu_output = [];

		this.menu_items.forEach(( item ) => {
			// Make a copy of the item, by creating a TTMenuItem. Otherwise when items are added to group headers, the item[] array will keep growing on each generation run. Using a class instance also helps be more Object Orientated.
			//item = new TTMenuItem( item );

			// Handle tt_link data.
			if ( item.destination && item.tt_link ) {
				item.parseTTLink();
			}

			if ( item.url_type === 'view' && item.destination ) {
				item.url = Global.getBaseURL() + '#!m=' + item.destination;
			} else if ( item.url_type === 'sub_view' && item.destination ) {
				item.url = Global.getBaseURL() + '#!m=Home&sm=' + item.destination;
			} else if ( !item.url && item.destination ) {
				item.url = Global.getBaseURL() + '#!m=Home'; //If view cannot be reached by a URL, go to Home.
			}

			// check if permissions allow this icon for this user.
			if( item.permission_result === false ) {
				return false;
			}

			// Check if item already exists in menu.
			if( menu_output.filter( ( menu_item_check ) => menu_item_check.id === item.id ).length !== 0 ) {
				// Error: Item already exists.
				Debug.Error( 'Menu item ('+ item.id +') already exists. Duplicate warning.', 'MenuManager.js', 'MenuManager', 'convertMenuItemsToPrimeVueFormat', 2 );
				return false;
			}

			if ( item.hide_in_main_menu ) {
				item.visible = false; //We do not want to show menu items that have been moved elsewhere (such as topbar) in the main menu.
			}

			// Grouping. Add item to a parent group, or if no group, add as a root menu element.
			if( item.parent_id ) {

				// Check if needle_value with that group already exists in output. If not error out and say groups must come before items.

				// menu_output.forEach(( array_item ) => {
				// var group_check = menu_output.map(copy).filter( function recursiveFilter( array_item ) {

				var parent_result = this.menu_items.filter( ( parent_item_check ) => parent_item_check.id === item.parent_id )

				if ( parent_result.length === 1 ) {
					// Group exists. Add item to the group.
					parent_result[0].items = parent_result[0].items || []; // By creating a new items array here, rather than earlier (or during addItem), it will always be a fresh array reference on every menu model generation run.
					parent_result[0].items.push( item ); // relies on JS pass-by-reference to add the items.
				} else {
					// Error: Group either does not exist or duplicates.
					Debug.Error( 'Menu group ('+ item.parent_id +') for ('+ item.id +') does not exist or there are duplicates. Check addItem() ordering, as group must be added to menu before items can be added.', 'MenuManager.js', 'MenuManager', 'convertMenuItemsToPrimeVueFormat', 2 );
				}

			} else {
				// Item has no parent_id, treat as root menu element.
				menu_output.push( item );
			}
		});

		// Return only menu item groups that have passed the following checks.
		var validated_items = menu_output.filter( ( validation_item ) => {
			if ( validation_item.items ) {
				//Each report menu item is a potential dropdown with an array of items if permission checks succeed.
				//Checking one level deeper to remove those items if they do not contain items or are a link / separator themselves.
				validation_item.items = validation_item.items.filter( ( item, index, items_array ) => {
					if ( item.parent_id && !item.destination && !item.separator && ( !item.items || item.items.every( child_item => child_item.separator ) ) ) {
						return false;
					}
					//This fixes issues where due to user permissions there might be unwanted separators.
					//For example there might be multiple separators in a row or the first/last item might be a separator.
					if ( item.separator && ( index === 0 || index === items_array.length - 1 || items_array[index - 1].separator ) ) {
						return false;
					}
					return true;
				} );
			}

			var contains_items = Boolean( validation_item.items !== undefined && validation_item.items.length !== 0
				&& validation_item.items.some( item => !item.separator ) ); //Make sure top level contains an actual menu item and not only separators.
			var has_destination = Boolean( validation_item.destination );
			var is_separator = Boolean( validation_item.separator );

			return ( contains_items || has_destination || is_separator );
		} );

		this.menu_items = validated_items;
	}

	initDefaultMenuItems() {
		// reset menu before building.
		this.menu_items = [];
		this.unlockMenu();

		this.addItem( {
			label: $.i18n._( 'Dashboard' ),
			id: 'home',
			order: 1000,
			icon: 'tticon tticon-speed_black_24dp',
			destination: 'Home',
			url_type: 'view'
		} );

		//Attendance Menu
		this.addItem( {
			label: $.i18n._( 'Attendance' ),
			id: 'attendance_menu',
			order: 100000,
			icon: 'tticon tticon-schedule_black_24dp',
		} );

		//Attendance Group Sub Menu
		this.addItem( {
			label: $.i18n._( 'TimeSheet' ),
			id: 'TimeSheet',
			destination: 'TimeSheet',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_attendance',
			permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheet' )
		} );

		this.addItem( {
			label: $.i18n._( 'Punches' ),
			id: 'Punches',
			destination: 'Punches',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_attendance',
			permission_result: PermissionManager.checkTopLevelPermission( 'Punches' )
		} );

		this.addItem( {
			label: $.i18n._( 'Exceptions' ),
			id: 'Exception',
			destination: 'Exception',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_attendance',
			permission_result: PermissionManager.checkTopLevelPermission( 'Exception' )
		} );

		this.addItem( {
			label: $.i18n._( 'Accrual Balances' ),
			id: 'AccrualBalance',
			destination: 'AccrualBalance',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_attendance',
			permission_result: PermissionManager.checkTopLevelPermission( 'AccrualBalance' )
		} );

		this.addItem( {
			label: $.i18n._( 'Accruals' ),
			id: 'Accrual',
			destination: 'Accrual',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_attendance',
			permission_result: PermissionManager.checkTopLevelPermission( 'Accrual' )
		} );

		this.addItem( {
			id: 'separator_attendance_1',
			parent_id: 'attendance_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Jobs' ),
			id: 'Job',
			destination: 'Job',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'Job' )
		} );

		this.addItem( {
			label: $.i18n._( 'Tasks' ),
			id: 'JobItem',
			destination: 'JobItem',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobItem' )
		} );

		this.addItem( {
			label: $.i18n._( 'Punch Tags' ),
			id: 'PunchTag',
			destination: 'PunchTag',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'PunchTag' )
		} );

		//GeoFencing
		this.addItem( {
			label: $.i18n._( 'GEO Fences' ),
			id: 'attendance_GEOFence',
			destination: 'GEOFence',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'GEOFence' )
		} );

		this.addItem( {
			label: $.i18n._( 'Job Groups' ),
			id: 'JobGroup',
			destination: 'JobGroup',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobGroup' )
		} );

		this.addItem( {
			label: $.i18n._( 'Task Groups' ),
			id: 'JobItemGroup',
			destination: 'JobItemGroup',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobItemGroup' )
		} );

		this.addItem( {
			label: $.i18n._( 'Punch Tag Groups' ),
			id: 'PunchTagGroup',
			destination: 'PunchTagGroup',
			url_type: 'view',
			parent_id: 'attendance_menu',
			sub_group_id: 'attendance_job_tracking',
			permission_result: PermissionManager.checkTopLevelPermission( 'PunchTagGroup' )
		} );

		//Schedule Menu
		this.addItem( {
			label: $.i18n._( 'Schedule' ),
			id: 'schedule_menu',
			icon: 'tticon tticon-calendar_today_black_24dp',
		} );

		this.addItem( {
			label: $.i18n._( 'Schedules' ),
			id: 'Schedule',
			destination: 'Schedule',
			url_type: 'view',
			parent_id: 'schedule_menu',
			sub_group_id: 'attendance_schedule',
			permission_result: PermissionManager.checkTopLevelPermission( 'Schedule' )
		} );

		this.addItem( {
			label: $.i18n._( 'Scheduled Shifts' ),
			id: 'ScheduleShift',
			destination: 'ScheduleShift',
			url_type: 'view',
			parent_id: 'schedule_menu',
			sub_group_id: 'attendance_schedule',
			permission_result: PermissionManager.checkTopLevelPermission( 'ScheduleShift' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recurring Schedules' ),
			id: 'RecurringScheduleControl',
			destination: 'RecurringScheduleControl',
			url_type: 'view',
			parent_id: 'schedule_menu',
			sub_group_id: 'attendance_schedule',
			permission_result: PermissionManager.checkTopLevelPermission( 'RecurringScheduleControl' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recurring Templates' ),
			id: 'RecurringScheduleTemplateControl',
			destination: 'RecurringScheduleTemplateControl',
			url_type: 'view',
			parent_id: 'schedule_menu',
			sub_group_id: 'attendance_schedule',
			permission_result: PermissionManager.checkTopLevelPermission( 'RecurringScheduleTemplateControl' )
		} );

		//Employee Menu
		this.addItem( {
			label: $.i18n._( 'Employee' ),
			id: 'employee_menu',
			icon: 'tticon tticon-people_alt_black_24dp',
		} );

		//Employee group Sub Menu
		this.addItem( {
			label: $.i18n._( 'Employees' ),
			id: 'Employee',
			destination: 'Employee',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'Employee' )
		} );

		this.addItem( {
			label: $.i18n._( 'Employee Contacts' ),
			id: 'UserContact',
			destination: 'UserContact',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserContact' )
		} );

		this.addItem( {
			label: $.i18n._( 'Preferences' ),
			id: 'UserPreference',
			destination: 'UserPreference',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserPreference' )
		} );

		this.addItem( {
			label: $.i18n._( 'Wages' ),
			id: 'Wage',
			destination: 'Wage',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'Wage' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Methods' ),
			id: 'RemittanceDestinationAccount',
			destination: 'RemittanceDestinationAccount',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'RemittanceDestinationAccount' )
		} );

		this.addItem( {
			label: $.i18n._( 'Titles' ),
			id: 'UserTitle',
			destination: 'UserTitle',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserTitle' )
		} );

		this.addItem( {
			label: $.i18n._( 'Employee Groups' ),
			id: 'UserGroup',
			destination: 'UserGroup',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserGroup' )
		} );

		this.addItem( {
			label: $.i18n._( 'Ethnic Groups' ),
			id: 'EthnicGroup',
			destination: 'EthnicGroup',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'EthnicGroup' )
		} );

		this.addItem( {
			label: $.i18n._( 'New Hire Defaults' ),
			id: 'UserDefault',
			destination: 'UserDefault',
			url_type: Global.getProductEdition() > 10 ? 'view' : 'sub_view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserDefault' )
		} );

		this.addItem( {
			label: $.i18n._( 'Record of Employment' ),
			id: 'ROE',
			destination: 'ROE',
			url_type: 'view',
			parent_id: 'employee_menu',
			sub_group_id: 'employee_employee',
			permission_result: PermissionManager.checkTopLevelPermission( 'ROE' )
		} );

		//Company Menu
		this.addItem( {
			label: $.i18n._( 'Company' ),
			id: 'company_menu',
			icon: 'tticon tticon-business_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Companies' ),
			id: 'Companies',
			destination: 'Companies',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Companies' )
		} );

		this.addItem( {
			label: $.i18n._( 'Company Information' ),
			id: 'Company',
			destination: 'Company',
			url_type: 'sub_view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Company' )
		} );

		this.addItem( {
			label: ( Global.getProductEdition() >= 15 ) ? $.i18n._( 'Legal Entities' ) : $.i18n._( 'Legal Entity' ),
			id: 'LegalEntity',
			destination: 'LegalEntity',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'LegalEntity' )
		} );

		this.addItem( {
			label: $.i18n._( 'Branches' ),
			id: 'Branch',
			destination: 'Branch',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Branch' )
		} );

		this.addItem( {
			label: $.i18n._( 'Departments' ),
			id: 'Department',
			destination: 'Department',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Department' )
		} );

		this.addItem( {
			label: $.i18n._( 'Hierarchy' ),
			id: 'HierarchyControl',
			destination: 'HierarchyControl',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'HierarchyControl' )

		} );

		this.addItem( {
			label: $.i18n._( 'Secondary Wage Groups' ),
			id: 'WageGroup',
			destination: 'WageGroup',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'WageGroup' )

		} );

		this.addItem( {
			label: $.i18n._( 'Stations' ),
			id: 'Station',
			destination: 'Station',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Station' )
		} );

		this.addItem( {
			label: $.i18n._( 'Permission Groups' ),
			id: 'PermissionControl',
			destination: 'PermissionControl',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'PermissionControl' )
		} );
		this.addItem( {
			label: $.i18n._( 'Currencies' ),
			id: 'Currency',
			destination: 'Currency',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: PermissionManager.checkTopLevelPermission( 'Currency' )

		} );
		this.addItem( {
			label: $.i18n._( 'Custom Fields' ),
			id: 'CustomField',
			destination: 'CustomField',
			url_type: 'view',
			parent_id: 'company_menu',
			sub_group_id: 'company_company',
			permission_result: ( PermissionManager.checkTopLevelPermission( 'CustomField' ) && Global.getFeatureFlag( 'custom_field' ) == true )
		} );

		this.addItem( {
			id: 'separator_company_1',
			parent_id: 'company_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Import' ),
			id: 'ImportCSV',
			destination: 'ImportCSV',
			parent_id: 'company_menu',
			sub_group_id: 'company_other',
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSV' )
		} );

		this.addItem( {
			label: $.i18n._( 'Quick Start' ),
			id: 'QuickStartWizard',
			destination: 'QuickStartWizard',
			parent_id: 'company_menu',
			sub_group_id: 'company_other',
			permission_result: PermissionManager.checkTopLevelPermission( 'QuickStartWizard' )
		} );

		this.addItem( {
			label: $.i18n._( 'Payroll' ),
			id: 'payroll_menu',
			icon: 'tticon tticon-attach_money_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Process Payroll' ),
			id: 'ProcessPayrollWizard',
			destination: 'ProcessPayrollWizard',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayrollProcessWizard' )
		} );

		this.addItem( {
			label: $.i18n._( 'Tax Wizard' ),
			id: 'PayrollRemittanceAgencyEventWizardController',
			destination: 'PayrollRemittanceAgencyEventWizardController',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayrollProcessWizard' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stubs' ),
			id: 'PayStub',
			destination: 'PayStub',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStub' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stub Transactions' ),
			id: 'PayStubTransaction',
			destination: 'PayStubTransaction',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStubTransaction' )
		} );

		this.addItem( {
			label: $.i18n._( 'Government Documents' ),
			id: 'GovernmentDocument',
			destination: 'GovernmentDocument',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'GovernmentDocument' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Periods' ),
			id: 'PayPeriods',
			destination: 'PayPeriods',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayPeriods' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stub Amendments' ),
			id: 'PayStubAmendment',
			destination: 'PayStubAmendment',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStubAmendment' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Period Schedules' ),
			id: 'payroll_PayPeriodSchedule',
			destination: 'PayPeriodSchedule',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayPeriodSchedule' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stub Accounts' ),
			id: 'PayStubEntryAccount',
			destination: 'PayStubEntryAccount',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStubEntryAccount' )
		} );

		this.addItem( {
			label: $.i18n._( 'Taxes & Deductions' ),
			id: 'CompanyTaxDeduction',
			destination: 'CompanyTaxDeduction',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'CompanyTaxDeduction' )
		} );

		this.addItem( {
			label: $.i18n._( 'Remittance Agencies' ),
			id: 'PayrollRemittanceAgency',
			destination: 'PayrollRemittanceAgency',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayrollRemittanceAgency' )
		} );

		this.addItem( {
			label: $.i18n._( 'Remittance Sources' ),
			id: 'RemittanceSourceAccount',
			destination: 'RemittanceSourceAccount',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'RemittanceSourceAccount' )
		} );

		this.addItem( {
			label: $.i18n._( 'Expenses' ),
			id: 'UserExpense',
			destination: 'UserExpense',
			url_type: 'view',
			parent_id: 'payroll_menu',
			sub_group_id: 'payroll_payroll',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserExpense' )
		} );

		this.addItem( {
			label: $.i18n._( 'Policy' ),
			id: 'policy_menu',
			icon: 'tticon tticon-rule_folder_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Policy Groups' ),
			id: 'PolicyGroup',
			destination: 'PolicyGroup',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'PolicyGroup' )
		} );

		this.addItem( {
			id: 'separator_policy_1',
			parent_id: 'policy_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Codes' ),
			id: 'PayCode',
			destination: 'PayCode',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayCode' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Formulas' ),
			id: 'PayFormulaPolicy',
			destination: 'PayFormulaPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayFormulaPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Contributing Pay Codes' ),
			id: 'ContributingPayCodePolicy',
			destination: 'ContributingPayCodePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'ContributingPayCodePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Contributing Shifts' ),
			id: 'ContributingShiftPolicy',
			destination: 'ContributingShiftPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'ContributingShiftPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Accrual Accounts' ),
			id: 'AccrualPolicyAccount',
			destination: 'AccrualPolicyAccount',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'AccrualPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recurring Holidays' ),
			id: 'RecurringHoliday',
			destination: 'RecurringHoliday',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_building_blocks',
			permission_result: PermissionManager.checkTopLevelPermission( 'RecurringHoliday' )
		} );

		this.addItem( {
			id: 'separator_policy_2',
			parent_id: 'policy_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Schedule Policies' ),
			id: 'SchedulePolicy',
			destination: 'SchedulePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'SchedulePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Rounding Policies' ),
			id: 'RoundIntervalPolicy',
			destination: 'RoundIntervalPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'RoundIntervalPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Meal Policies' ),
			id: 'MealPolicy',
			destination: 'MealPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'MealPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Break Policies' ),
			id: 'BreakPolicy',
			destination: 'BreakPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'BreakPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Regular Time  Policies' ),
			id: 'RegularTimePolicy',
			destination: 'RegularTimePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'RegularTimePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Overtime Policies' ),
			id: 'OvertimePolicy',
			destination: 'OvertimePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'OvertimePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Premium Policies' ),
			id: 'PremiumPolicy',
			destination: 'PremiumPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'PremiumPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Exception Policies' ),
			id: 'ExceptionPolicyControl',
			destination: 'ExceptionPolicyControl',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'ExceptionPolicyControl' )
		} );

		this.addItem( {
			label: $.i18n._( 'Accrual Policies' ),
			id: 'AccrualPolicy',
			destination: 'AccrualPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'AccrualPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Absence Policies' ),
			id: 'AbsencePolicy',
			destination: 'AbsencePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'AbsencePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Expense Policies' ),
			id: 'ExpensePolicy',
			destination: 'ExpensePolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'ExpensePolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Holiday Policies' ),
			id: 'HolidayPolicy',
			destination: 'HolidayPolicy',
			url_type: 'view',
			parent_id: 'policy_menu',
			sub_group_id: 'policy_policy',
			permission_result: PermissionManager.checkTopLevelPermission( 'HolidayPolicy' )
		} );

		// Invoice group
		this.addItem( {
			label: $.i18n._( 'Invoice' ),
			id: 'invoice_menu',
			icon: 'tticon tticon-receipt_long_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Clients' ),
			id: 'Client',
			destination: 'Client',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'Client' )
		} );

		this.addItem( {
			label: $.i18n._( 'Client Contacts' ),
			id: 'ClientContact',
			destination: 'ClientContact',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'ClientContact' )
		} );

		this.addItem( {
			label: $.i18n._( 'Invoices' ),
			id: 'Invoice',
			destination: 'Invoice',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'Invoice' )
		} );

		this.addItem( {
			label: $.i18n._( 'Transactions' ),
			id: 'InvoiceTransaction',
			destination: 'InvoiceTransaction',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceTransaction' )
		} );

		this.addItem( {
			label: $.i18n._( 'Payment Methods' ),
			id: 'ClientPayment',
			destination: 'ClientPayment',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'ClientPayment' )
		} );

		this.addItem( {
			label: $.i18n._( 'Products' ),
			id: 'Product',
			destination: 'Product',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'Product' )
		} );

		this.addItem( {
			label: $.i18n._( 'Districts' ),
			id: 'InvoiceDistrict',
			destination: 'InvoiceDistrict',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_invoice',
			permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceDistrict' )
		} );

		this.addItem( {
			id: 'separator_invoice_1',
			parent_id: 'invoice_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Client Groups' ),
			id: 'ClientGroup',
			destination: 'ClientGroup',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_groups',
			permission_result: PermissionManager.checkTopLevelPermission( 'Client' )
		} );

		this.addItem( {
			label: $.i18n._( 'Product Groups' ),
			id: 'ProductGroup',
			destination: 'ProductGroup',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_groups',
			permission_result: PermissionManager.checkTopLevelPermission( 'Product' )
		} );

		this.addItem( {
			id: 'separator_invoice_2',
			parent_id: 'invoice_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Tax Policies' ),
			id: 'TaxPolicy',
			destination: 'TaxPolicy',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_policies',
			permission_result: PermissionManager.checkTopLevelPermission( 'TaxPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Shipping Policies' ),
			id: 'ShippingPolicy',
			destination: 'ShippingPolicy',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_policies',
			permission_result: PermissionManager.checkTopLevelPermission( 'ShippingPolicy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Area Policies' ),
			id: 'AreaPolicy',
			destination: 'AreaPolicy',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_policies',
			permission_result: PermissionManager.checkTopLevelPermission( 'AreaPolicy' )
		} );

		this.addItem( {
			id: 'separator_invoice_3',
			parent_id: 'invoice_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Payment Gateway' ),
			id: 'PaymentGateway',
			destination: 'PaymentGateway',
			url_type: 'view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_settings',
			permission_result: PermissionManager.checkTopLevelPermission( 'PaymentGateway' )
		} );

		this.addItem( {
			label: $.i18n._( 'Settings' ),
			id: 'InvoiceConfig',
			destination: 'InvoiceConfig',
			url_type: 'sub_view',
			parent_id: 'invoice_menu',
			sub_group_id: 'invoice_settings',
			permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceConfig' )
		} );

		//HR Menu
		this.addItem( {
			label: $.i18n._( 'HR' ),
			id: 'hr_menu',
			icon: 'tticon tticon-business_center_black_24dp'
		} );

		//reviews Group Sub Menu
		this.addItem( {
			label: $.i18n._( 'Reviews' ),
			id: 'UserReviewControl',
			destination: 'UserReviewControl',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_reviews',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserReviewControl' )
		} );

		this.addItem( {
			label: $.i18n._( 'KPI' ),
			id: 'KPI',
			destination: 'KPI',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_reviews',
			permission_result: PermissionManager.checkTopLevelPermission( 'KPI' )
		} );

		this.addItem( {
			label: $.i18n._( 'KPI Groups' ),
			id: 'KPIGroup',
			destination: 'KPIGroup',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_reviews',
			permission_result: PermissionManager.checkTopLevelPermission( 'KPIGroup' )
		} );

		this.addItem( {
			id: 'separator_hr_1',
			parent_id: 'hr_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Qualifications' ),
			id: 'Qualification',
			destination: 'Qualification',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'Qualification' )
		} );

		this.addItem( {
			label: $.i18n._( 'Qualification Groups' ),
			id: 'QualificationGroup',
			destination: 'QualificationGroup',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'QualificationGroup' )
		} );

		this.addItem( {
			label: $.i18n._( 'Skills' ),
			id: 'UserSkill',
			destination: 'UserSkill',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserSkill' )
		} );

		this.addItem( {
			label: $.i18n._( 'Education' ),
			id: 'UserEducation',
			destination: 'UserEducation',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserEducation' )
		} );

		this.addItem( {
			label: $.i18n._( 'Memberships' ),
			id: 'UserMembership',
			destination: 'UserMembership',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserMembership' )
		} );

		this.addItem( {
			label: $.i18n._( 'Licenses' ),
			id: 'UserLicense',
			destination: 'UserLicense',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserLicense' )
		} );

		this.addItem( {
			label: $.i18n._( 'Languages' ),
			id: 'UserLanguage',
			destination: 'UserLanguage',
			url_type: 'view',
			parent_id: 'hr_menu',
			sub_group_id: 'hr_qualifications',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserLanguage' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recruitment' ),
			id: 'recruitment_menu',
			icon: 'tticon tticon-switch_account_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Job Vacancies' ),
			id: 'JobVacancy',
			destination: 'JobVacancy',
			url_type: 'view',
			parent_id: 'recruitment_menu',
			sub_group_id: 'hr_recruitment',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobVacancy' )
		} );

		this.addItem( {
			label: $.i18n._( 'Job Applicants' ),
			id: 'JobApplicant',
			destination: 'JobApplicant',
			url_type: 'view',
			parent_id: 'recruitment_menu',
			sub_group_id: 'hr_recruitment',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobApplicant' )
		} );

		this.addItem( {
			label: $.i18n._( 'Job Applications' ),
			id: 'JobApplication',
			destination: 'JobApplication',
			url_type: 'view',
			parent_id: 'recruitment_menu',
			sub_group_id: 'hr_recruitment',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobApplication' )
		} );

		this.addItem( {
			label: $.i18n._( 'Portal Settings' ),
			id: 'RecruitmentPortalConfig',
			destination: 'RecruitmentPortalConfig',
			url_type: 'sub_view',
			parent_id: 'recruitment_menu',
			sub_group_id: 'hr_recruitment',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobApplicant' )
		} );


		// TODO: Above is done - next: Documents

		this.addItem( {
			label: $.i18n._( 'Document' ),
			id: 'document_menu',
			icon: 'tticon tticon-feed_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Documents' ),
			id: 'Document',
			destination: 'Document',
			url_type: 'view',
			parent_id: 'document_menu',
			sub_group_id: 'documentsGroup',
			permission_result: PermissionManager.checkTopLevelPermission( 'Document' )
		} );

		this.addItem( {
			label: $.i18n._( 'Document Groups' ),
			id: 'DocumentGroup',
			destination: 'DocumentGroup',
			url_type: 'view',
			parent_id: 'document_menu',
			sub_group_id: 'documentsGroup',
			permission_result: PermissionManager.checkTopLevelPermission( 'DocumentGroup' )
		} );

		// TODO: Above is done - next: Reports

		//Reports

		this.addItem( {
			label: $.i18n._( 'Report' ),
			id: 'report_menu',
			icon: 'tticon tticon-show_chart_black_24dp'
		} );

		this.addItem( {
			label: $.i18n._( 'Saved Reports' ),
			id: 'report_saved_reports',
			destination: 'SavedReport',
			url_type: 'view',
			tt_link: {
				type: 'view' // the legacy 'saved reports' triggered the closeEditViews, setSelectSubMenu and openSelectView. Same as the wizard code in parseTTLink etc. Which is currently triggered by 'view'
			},
			parent_id: 'report_menu',
			permission_result: PermissionManager.checkTopLevelPermission( 'SavedReport' )
		} );

		this.addItem( {
			id: 'separator_report_saved_reports_1',
			parent_id: 'report_menu',
			separator: true,
			class: 'p-menu-separator'
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'Employee Reports' ),
			id: 'report_employee_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Whos In Summary' ),
			id: 'ActiveShiftReport',
			destination: 'ActiveShiftReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_employee_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'ActiveShiftReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Employee Information' ),
			id: 'UserSummaryReport',
			destination: 'UserSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_employee_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Audit Trail' ),
			id: 'AuditTrailReport',
			destination: 'AuditTrailReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_employee_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'AuditTrailReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'TimeSheet Reports' ),
			id: 'report_timesheet_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Schedule Summary' ),
			id: 'ScheduleSummaryReport',
			destination: 'ScheduleSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'ScheduleSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'TimeSheet Summary' ),
			id: 'TimesheetSummaryReport',
			destination: 'TimesheetSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'TimesheetSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'TimeSheet Detail' ),
			id: 'TimesheetDetailReport',
			destination: 'TimesheetDetailReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'TimesheetDetailReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Punch Summary' ),
			id: 'PunchSummaryReport',
			destination: 'PunchSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'PunchSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Accrual Balance Summary' ),
			id: 'AccrualBalanceSummaryReport',
			destination: 'AccrualBalanceSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'AccrualBalanceSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Exception Summary' ),
			id: 'ExceptionSummaryReport',
			destination: 'ExceptionSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_timesheet_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'ExceptionSummaryReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'Payroll Reports' ),
			id: 'report_payroll_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stub Summary' ),
			id: 'PayStubSummaryReport',
			destination: 'PayStubSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_payroll_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStubSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Pay Stub Transaction Summary' ),
			id: 'PayStubTransactionSummaryReport',
			destination: 'PayStubTransactionSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_payroll_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStubSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Payroll Export' ),
			id: 'PayrollExportReport',
			destination: 'PayrollExportReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_payroll_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'PayrollExportReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'General Ledger Summary' ),
			id: 'GeneralLedgerSummaryReport',
			destination: 'GeneralLedgerSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_payroll_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'GeneralLedgerSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Expense Summary' ),
			id: 'ExpenseSummaryReport',
			destination: 'ExpenseSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_payroll_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'ExpenseSummaryReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'Job Tracking Reports' ),
			id: 'report_job_tracking_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Job Summary' ),
			id: 'JobSummaryReport',
			destination: 'JobSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_job_tracking_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Job Analysis' ),
			id: 'JobAnalysisReport',
			destination: 'JobAnalysisReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_job_tracking_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobAnalysisReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Job Information' ),
			id: 'JobInformationReport',
			destination: 'JobInformationReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_job_tracking_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobInformationReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Task Information' ),
			id: 'JobItemInformationReport',
			destination: 'JobItemInformationReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_job_tracking_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'JobItemInformationReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'Invoice Reports' ),
			id: 'report_invoice_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Transaction Summary' ),
			id: 'InvoiceTransactionSummaryReport',
			destination: 'InvoiceTransactionSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_invoice_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'InvoiceTransactionSummaryReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'Tax Reports' ),
			id: 'report_tax_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Tax Summary (Generic)' ),
			id: 'TaxSummaryReport',
			destination: 'TaxSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'TaxSummaryReport' )
		} );

		this.addItem( {
			id: 'separator_report_tax_reports_1',
			parent_id: 'report_tax_reports',
			separator: true,
			class: 'p-menu-separator'
		} );

		this.addItem( {
			label: $.i18n._( 'Remittance Summary' ),
			id: 'RemittanceSummaryReport',
			destination: 'RemittanceSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'RemittanceSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'T4 Summary' ),
			id: 'T4SummaryReport',
			destination: 'T4SummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'T4SummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'T4A Summary' ),
			id: 'T4ASummaryReport',
			destination: 'T4ASummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'T4ASummaryReport' )
		} );

		this.addItem( {
			id: 'separator_report_tax_reports_2',
			parent_id: 'report_tax_reports',
			separator: true,
			class: 'p-menu-separator',
			permission_result: ( PermissionManager.checkTopLevelPermission( 'RemittanceSummaryReport' ) && PermissionManager.checkTopLevelPermission( 'Form941Report' ) ) //Only show the 2nd separator if Canada and US tax forms are displayed too. Otherwise the separator is doubled up.
		} );

		this.addItem( {
			label: $.i18n._( 'US State Unemployment' ),
			id: 'USStateUnemploymentReport',
			destination: 'USStateUnemploymentReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'USStateUnemploymentReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Form 941' ),
			id: 'Form941Report',
			destination: 'Form941Report',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'Form941Report' )
		} );

		this.addItem( {
			label: $.i18n._( 'Form 940' ),
			id: 'Form940Report',
			destination: 'Form940Report',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'Form940Report' )
		} );

		this.addItem( {
			label: $.i18n._( 'Form 1099-NEC' ),
			id: 'Form1099NecReport',
			destination: 'Form1099NecReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'Form1099NecReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Form W2/W3' ),
			id: 'FormW2Report',
			destination: 'FormW2Report',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'FormW2Report' )
		} );

		this.addItem( {
			label: $.i18n._( 'Affordable Care' ),
			id: 'AffordableCareReport',
			destination: 'AffordableCareReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'AffordableCareReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'US EEO' ),
			id: 'USEEOReport',
			destination: 'USEEOReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'USEEOReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'US PERS' ),
			id: 'USPERSReport',
			destination: 'USPERSReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_tax_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'USPERSReport' )
		} );

		/**************************************************/

		this.addItem( {
			label: $.i18n._( 'HR Reports' ),
			id: 'report_hr_reports',
			parent_id: 'report_menu',
		} );

		this.addItem( {
			label: $.i18n._( 'Qualification Summary' ),
			id: 'UserQualificationReport',
			destination: 'UserQualificationReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_hr_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserQualificationReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Review Summary' ),
			id: 'KPIReport',
			destination: 'KPIReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_hr_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'KPIReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recruitment Summary' ),
			id: 'UserRecruitmentSummaryReport',
			destination: 'UserRecruitmentSummaryReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_hr_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserRecruitmentSummaryReport' )
		} );

		this.addItem( {
			label: $.i18n._( 'Recruitment Detail' ),
			id: 'UserRecruitmentDetailReport',
			destination: 'UserRecruitmentDetailReport',
			url_type: 'sub_view',
			tt_link: {
				type: 'report'
			},
			parent_id: 'report_hr_reports',
			permission_result: PermissionManager.checkTopLevelPermission( 'UserRecruitmentDetailReport' )
		} );

		/**************************************************/

		// End of reports

		/**************************************************/

		//My Account group
		this.addItem( {
			label: $.i18n._( 'My Account' ),
			id: 'my_account_menu',
			icon: 'tticon tticon-person_black_24dp',
			hide_in_main_menu: true,
		} );

		// this.addItem( {
		// 	label: $.i18n._( 'Notifications' ),
		// 	id: 'notification',
		// 	destination: 'Notification',
		// 	parent_id: 'my_account_menu',
		// 	sub_group_id: 'myAccountGroup',
		// 	hide_in_main_menu: true,
		// 	permission_result: true, // Notification always returns true as notifications should always be enabled.
		// } );

		this.addItem( {
			label: $.i18n._( 'Messages' ),
			id: 'message',
			destination: 'MessageControl',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'myAccountGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'MessageControl' )
		} );

		this.addItem( {
			label: $.i18n._( 'Requests' ),
			id: 'request',
			destination: 'Request',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'myAccountGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'Request' )
		} );

		this.addItem( {
			label: $.i18n._( 'Expenses' ),
			id: 'LoginUserExpense',
			destination: 'LoginUserExpense',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'myAccountGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserExpense' )
		} );

		/**************************************************/

		this.addItem( {
			id: 'separator_my_account_1',
			parent_id: 'my_account_menu',
			separator: true,
		} );


		this.addItem( {
			label: $.i18n._( 'Request Authorizations' ),
			id: 'request_authorization',
			destination: 'RequestAuthorization',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'authorization',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'RequestAuthorization' )
		} );

		this.addItem( {
			label: $.i18n._( 'TimeSheet Authorizations' ),
			id: 'timesheet_authorization',
			destination: 'TimeSheetAuthorization',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'authorization',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheetAuthorization' )
		} );

		this.addItem( {
			label: $.i18n._( 'Expense Authorizations' ),
			id: 'expense_authorization',
			destination: 'ExpenseAuthorization',
			url_type: 'view',
			parent_id: 'my_account_menu',
			sub_group_id: 'authorization',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'ExpenseAuthorization' )
		} );

		/**************************************************/

		this.addItem( {
			id: 'separator_my_account_2',
			parent_id: 'my_account_menu',
			separator: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Preferences' ),
			id: 'LoginUserPreference',
			destination: 'LoginUserPreference',
			url_type: 'sub_view',
			parent_id: 'my_account_menu',
			sub_group_id: 'myAccountGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserPreference' )
		} );

		this.addItem( {
			label: $.i18n._( 'Contact Information' ),
			id: 'LoginUserContact',
			destination: 'LoginUserContact',
			url_type: 'sub_view',
			parent_id: 'my_account_menu',
			sub_group_id: 'myAccountGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'LoginUserContact' )
		} );

		this.addItem( {
			label: $.i18n._( 'Passwords / Security' ),
			id: 'ChangePassword',
			destination: 'ChangePassword',
			url_type: 'sub_view',
			parent_id: 'my_account_menu',
			sub_group_id: 'securityGroup',
			hide_in_main_menu: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'ChangePassword' )
		} );

		/**************************************************/

		this.addItem( {
			id: 'separator_my_account_4',
			parent_id: 'my_account_menu',
			separator: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Sign Out' ),
			id: 'Logout',
			icon: 'tticon tticon-logout_black_24dp',
			destination: 'Logout',
			parent_id: 'my_account_menu',
			sub_group_id: 'logoutGroup',
			hide_in_main_menu: true,
			permission_result: true,
		} );

		//Help group
		this.addItem( {
			label: $.i18n._( 'Help' ),
			id: 'help_menu',
			icon: 'tticon tticon-help_center_black_24dp',
			permission_result: true,
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'AI Assistant' ),
			id: 'AIAssistant',
			destination: 'AIAssistant',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: Global.getFeatureFlag( 'assistant' ) && ( PermissionManager.getPermissionLevel() >= 40 ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Administrator Guide' ),
			id: 'AdminGuide',
			destination: 'AdminGuide',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: PermissionManager.HelpMenuValidateAdmin(),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Supervisor Guide' ),
			id: 'SupervisorGuide',
			destination: 'SupervisorGuide',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: PermissionManager.HelpMenuValidateSupervisor(),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Employee Guide' ),
			id: 'EmployeeGuide',
			destination: 'EmployeeGuide',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: true,
			hide_in_main_menu: true,
		} );

		//Only display one "FAQ" entry for the highest permission level they have. This keeps the menu a little smaller and less confusing.
		this.addItem( {
			label: $.i18n._( 'FAQs' ),
			id: 'AdminFAQS',
			destination: 'AdminFAQS',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: PermissionManager.HelpMenuValidateAdmin(),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'FAQs' ), //Supervisor FAQ
			id: 'SupervisorFAQS',
			destination: 'SupervisorFAQS',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( PermissionManager.HelpMenuValidateSupervisor() && !PermissionManager.HelpMenuValidateAdmin() ), //Supervisor FAQ
			hide_in_main_menu: true,
		} );
		this.addItem( {
			label: $.i18n._( 'FAQs' ), //Employee FAQ
			id: 'EmployeeFAQS',
			destination: 'EmployeeFAQS',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( !PermissionManager.HelpMenuValidateSupervisor() && !PermissionManager.HelpMenuValidateAdmin() ), //Employee FAQ
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Chat w/Support' ),
			id: 'LiveChat',
			destination: 'LiveChat',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( ( PermissionManager.HelpMenuValidateAdmin() || PermissionManager.HelpMenuValidateSupervisor() ) && Global.getFeatureFlag( 'support_chat' ) == true && APIGlobal.pre_login_data.demo_mode === false && Global.getProductEdition() >= 15 ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: ( Global.getProductEdition() >= 15 ) ? $.i18n._( 'Email Support' ) : $.i18n._( 'Community Forums' ),
			id: 'EmailHelp',
			destination: 'EmailHelp',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( ( PermissionManager.HelpMenuValidateAdmin() || PermissionManager.HelpMenuValidateSupervisor() ) && ( Global.getProductEdition() == 10 || APIGlobal.pre_login_data.support_email.trim() != '' ) ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Screen Share Download' ),
			id: 'ScreenSharing',
			destination: 'ScreenSharing',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( ( PermissionManager.HelpMenuValidateAdmin() || PermissionManager.HelpMenuValidateSupervisor() ) && Global.getFeatureFlag( 'support_chat' ) == true && APIGlobal.pre_login_data.demo_mode === false && Global.getProductEdition() >= 15 ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'Testing Sandbox' ),
			id: 'Sandbox',
			destination: 'Sandbox',
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( PermissionManager.HelpMenuValidateAdmin() && Global.getProductEdition() >= 15 && APIGlobal.pre_login_data['sandbox_url'] && APIGlobal.pre_login_data['sandbox_url'] != false && APIGlobal.pre_login_data['sandbox_url'].length > 0 && !APIGlobal.pre_login_data['sandbox'] ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'What\'s New' ),
			id: 'WhatsNew',
			destination: 'WhatsNew',
			tt_link: {
				type: 'view_no_close'
			},
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: ( PermissionManager.HelpMenuValidateAdmin() || PermissionManager.HelpMenuValidateSupervisor() ),
			hide_in_main_menu: true,
		} );

		this.addItem( {
			label: $.i18n._( 'About' ),
			id: 'About',
			destination: 'About',
			parent_id: 'help_menu',
			sub_group_id: 'help_group',
			permission_result: PermissionManager.HelpMenuValidateAdmin(),
			hide_in_main_menu: true,
		} );

		//UI Tests
		this.addItem( {
			label: $.i18n._( 'UI Kit Sample' ),
			id: 'ui_kit',
			icon: 'tticon tticon-verified_black_24dp',
			permission_result: APIGlobal.pre_login_data && APIGlobal.pre_login_data.production == false && Global.UNIT_TEST_MODE == true
		} );

		this.addItem( {
			label: $.i18n._( 'Sample List' ),
			id: 'sample_list',
			destination: 'UIKitSample',
			parent_id: 'ui_kit',
			sub_group_id: 'ui_kit',
			permission_result: true, //Will not show unless parent is shown.
		} );

		// Once all menu items added to this.menu_items in raw format, then convert to PrimeVue format.
		this.convertMenuItemsToPrimeVueFormat();

		// Once menu data is generated, update the profile menu with the items for the 'My Account' sub menu.
		this.event_bus.emit( 'tt_topbar','profile_menu_data', {
			// Extract the my account menu data and pass it to profile menu.
			profile_menu_data: this.menu_items
				.find( ( item ) => item.id === 'my_account_menu' ).items
		} );

		//Not all employees can view help menu, so make sure items exist before attempting to send to tttopbar.
		let help_menu = this.menu_items.find( ( item ) => item.id === 'help_menu' );
		if ( help_menu ) {
			this.event_bus.emit( 'tt_topbar', 'help_menu_data', {
				// Extract the help menu data and pass it to the topbar help icon.
				help_menu_data: help_menu.items
			} );
		}
	}

	//TODO: Jeremy -> Temporarily put the following code here that was originally in ribbonViewController and other files.
	//Do we want to export a MenuManager instance? (Currently exporting)
	//Have these functions be static?
	//Have them in another file?
	//The functions in question are - openSelectView(), goToView(), isCurrentView(), doLogout(), setCompanyLogo().

	//Replace this switch statement with something else?
	openSelectView( name ) {
		var $this = this;
		Global.setUINotready();
		switch ( name ) {
			case 'ImportCSV':
				IndexViewController.openWizard( 'ImportCSVWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'QuickStartWizard':
				if ( PermissionManager.checkTopLevelPermission( 'QuickStartWizard' ) ) {
					IndexViewController.openWizard( 'QuickStartWizard' );
				}
				break;
			case 'UserDefault':
				//Community editions can only have 1 new hire default. For those editions do not show the list view.
				if ( Global.getProductEdition() > 10 ) {
					defaultCase( name );
					break;
				}
			//Fall through to below cases.
			case 'InOut':
			case 'Company':
			case 'LoginUserContact':
			case 'LoginUserPreference':
			case 'ChangePassword':
			case 'InvoiceConfig':
			case 'RecruitmentPortalConfig':
			case 'About':
				if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId == name ) { //#2557 - A - Ensure that opening edit only views on top of same edit only view just resets the edit menu
					LocalCacheData.current_open_edit_only_controller.setEditMenu();
				} else if ( LocalCacheData.current_open_edit_only_controller ) { //#2557 - B - Ensure that opening edit only views on top of different edit only  view sets the parent to the existing edit only view
					IndexViewController.openEditView( LocalCacheData.current_open_edit_only_controller, name );
				} else {
					IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, name ); //#2557 - C - Ensure that opening edit views as normal works as before
				}
				break;
			case 'Logout':
				this.doLogout();
				break;
			case 'AIAssistant':
				$this.event_bus.emit( 'tt_topbar', 'open_assistant' );
				break;
			case 'AdminGuide':
				var url = 'https://www.timetrex.com/h?id=admin_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'SupervisorGuide':
				var url = 'https://www.timetrex.com/h?id=supervisor_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'EmployeeGuide':
				var url = 'https://www.timetrex.com/h?id=employee_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'AdminFAQS':
				url = 'https://www.timetrex.com/h?id=admin_faq&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'SupervisorFAQS':
				url = 'https://www.timetrex.com/h?id=supervisor_faq&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'EmployeeFAQS':
				url = 'https://www.timetrex.com/h?id=employee_faq&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'WhatsNew':
				url = 'https://www.timetrex.com/h?id=changelog&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'EmailHelp':
				if ( Global.getProductEdition() >= 15 ) {
					location.href = 'mailto:' + APIGlobal.pre_login_data.support_email + '?subject=Company: ' + LocalCacheData.getCurrentCompany().name + '&body=Company: ' + LocalCacheData.getCurrentCompany().name + '  ' + 'Registration Key: ' + LocalCacheData.getLoginData().registration_key;
				} else {
					url = 'https://www.timetrex.com/r?id=29';
					window.open( url, '_blank' );
				}
				break;
			case 'LiveChat':
				if ( APIGlobal.pre_login_data.demo_mode === false && Global.getProductEdition() >= 15 )  {
					var current_user = LocalCacheData.getLoginUser();
					IndexViewController.testInternetConnection();
					if ( PermissionManager.getPermissionLevel() > 40 ) { //40=Supervisor (Subordinates Only)
						var check_connection_timer = setInterval( function() {
							if ( !is_testing_internet_connection ) {
								clearInterval( check_connection_timer );
								if ( internet_connection_available && current_user ) {
									import(/* webpackChunkName: "live-chat" */'@/global/widgets/live-chat/live-chat' )
									.then(function( module ) {
										window.LHCChatOptions = module.LHCChatOptions;
										window.openSupportChat = module.openSupportChat;
										window.openSupportChat();
									} ).catch( Global.importErrorHandler );
								}
							} else {
								TAlertManager.showAlert( $.i18n._( 'No internet connection found. Cannot use live chat support.' ), $.i18n._( 'Live Chat Support' ) );
							}
						}, 500 );
					}
				}
				break;
			case 'ScreenSharing':
				url = 'https://www.timetrex.com/r.php?id=quicksupport&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'Sandbox':
				if ( APIGlobal.pre_login_data['sandbox_url'] && APIGlobal.pre_login_data['sandbox_url'].length > 0 ) {
					var user = LocalCacheData.getLoginUser();
					Global.NewSession( user.user_name, 'SANDBOX', true );
				}
				break;
			case 'ProcessPayrollWizard':
				IndexViewController.openWizard( 'ProcessPayrollWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'PayrollRemittanceAgencyEventWizardController':
				IndexViewController.openWizardController( 'PayrollRemittanceAgencyEventWizardController', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'ProcessTransactionsWizard':
				IndexViewController.openWizardController( 'ProcessTransactionsWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'LegalEntity':
				if ( Global.getProductEdition() >= 15 ) {
					defaultCase( name );
				} else {
					IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, name, false );
				}
				break;
			default:
				defaultCase( name );
				break;
		}

		//To mitigate duplicate code the default case has been made into it's own function.
		//Some cases have multiple pathways. Such as a view that uses a list view or an edit only view.
		function defaultCase( name ) {
			//#2557 - When opening a view from the submenus, ensure that similarily named edit only views are cancelled (with confirm) first.
			if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId == name ) {
				LocalCacheData.current_open_edit_only_controller.onCancelClick();
				TTPromise.wait( 'base', 'onCancelClick', function() {
					$this.goToView( name );
				}.bind( $this ) );
			} else {
				$this.goToView( name );
			}
		}
	}

	goToView( subMenuId, force_refresh ) {
		if ( this.isCurrentView( subMenuId ) && force_refresh ) {
			IndexViewController.instance.router.reloadView( subMenuId );
		} else {
			deleteCookie( 'OverrideUserPreference' ); //TimeSheet view has ability to override user preferences for using the employees timezone, make sure we clear this everytime we go to another view.

			//#2157 - needed for selenium screenshot test to prevent hanging on
			//various combinations of ribbon and topmenu clicks that are not a change to the hash
			//  Check that we aren't trying to redirect back to the login screen, causing an infinite loop on logout in some cases.
			if ( subMenuId != 'Login' && location.hash == ( '#!m=' + subMenuId ) ) {
				TTPromise.wait();
				//TODO: Replace this jQuery selector.
				$( '#refreshBtn:visible' ).click();
			}
			Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + subMenuId );
		}
	}

	isCurrentView( subMenuId ) {
		var sub_menu_id_url = Global.getBaseURL() + '#!m=' + subMenuId;
		Debug.Text( 'URL: Current: ' + window.location.href + ' Switching To: ' + sub_menu_id_url, 'MenuManager.js', 'MenuManager', 'isCurrentView', 10 );

		//window.location.href.indexOf( sub_menu_id_url ) == 0 doesn't work here, as .../html5/#!m=PayStub matches when on .../html5/#!m=PayStubTransaction view.
		//So instead use a RegEx to match the end of the string, or a & in case there are more URL arguments.
		var regex_pattern = sub_menu_id_url.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' ) + '(&.*)?$'; //replace() escapes the URL chars.

		if ( window.location.href.match( regex_pattern ) !== null ) {
			return true;
		}

		return false;
	}

	doLogout() {
		this.event_bus.emit( 'app_menu', 'set_active_index', {
			index: null
		} );

		if ( LocalCacheData.getLogoutSettings().slo_url ) {
			TTSAML.logoutUser( LocalCacheData.getLoginUser().user_name, Global.getBaseURL( '../../', false ) + 'api/saml/api.php?action=slo' );
			return;
		} else {
			//Don't wait for result of logout in case of slow or disconnected internet. Just clear local cookies and move on.
			var current_user_api = TTAPI.APIAuthentication;
			if ( typeof current_user_api.Logout !== 'undefined' ) { //Fix JS exception: Uncaught TypeError: current_user_api.Logout is not a function -- Which can occur when offline and clicking Logout.
				current_user_api.Logout( {
					onResult() {
					}
				} );
			}
		}

		Global.setAnalyticDimensions();

		//A bare "if" wrapped around lh_inst doesn't work here for some reason.
		if ( typeof ( lh_inst ) != 'undefined' ) {
			//stop the update loop for live chat with support
			clearTimeout( lh_inst.timeoutStatuscheck );
		}

		Global.Logout();
		this.goToView( 'Login' );

		TAlertManager.showBrowserTopBanner();
	}

}

export default new MenuManager()
export {
	MenuManager
}

/**
 * A menu item in TimeTrex format. Will contain PrimeVue and TT attributes.
 * @property {string} label - Item label
 * @property {string} id - Unique menu item reference to use in automated testing.
 * @property {string} [parent_id] - ID of the parent group for the item.
 * @property {string} [sub_group_id] - legacy id of the ribbon menu groupings. Could be used to decide when a line needs to be placed between nav items.
 * @property {string} [destination] - Destination for router when link is clicked.
 * @property {boolean} [permission_result] - Decides if a user has access to this icon. Normally controlled by PermissionManager.checkTopLevelPermission()
 * @property {TTMenuLink} [tt_link] - Object containing link info.
 * @property {TTMenuItem[]} [items] - Array of further sub-menu items.
 */
class TTMenuItem {
	constructor( options ) {
		if( options && options.destination ) {
			// Set default link handling logic if a destination is provided.
			options.tt_link = options.tt_link || {};
			_.defaults( options.tt_link, {
				// destination is required, no default.
				type: 'view', // view, wizard, report
				target: 'main' // main, left, right, footer, popup
			} );
		}

		Object.keys( options ).forEach( ( key ) => {
			this[key] = options[key];
		} );

		return this;
	}

	parseTTLink() {
		if( !this.destination || !this.tt_link.type || !this.tt_link.target ) {
			Debug.Error( 'Unable to parse TTLink without all parameters.', 'MenuManager.js', 'MenuManager', 'convertMenuItemsToPrimeVueFormat', 2 );
			return false;
		}

		switch ( this.tt_link.type ) {
			case 'view':
				Object.assign( this, this.parseViewLink( this.destination, true ) );
				break;
			case 'view_no_close':
				//Certain links such as "What's New", "Email Support" etc should not close edit views as they do not open new views.
				//Kept "view" in type name to be consistent as these types of links have always gone through "openSelectView()"
				//But we can rename to something else in future if we break up the "openSelectView()" function.
				Object.assign( this, this.parseViewLink( this.destination, false ) );
				break;
			case 'report':
				Object.assign( this, this.parseReportLink( this.destination ) );
				break;
			default:

		}
		return true;
	}

/* For the different views, reference the following:
 * RibbonViewController.onSubMenuNavClick
 * RibbonViewController.setSelectSubMenu // might not need this. Is this related to just highlighting the currently clicked menu? Might be a simpler way to do it in vue.
 * RibbonViewController.openSelectView // theres lots of intricate logic here. Re-visit and check what we need here.
 *
*/

	// Add all the various routing type parsers here.
	parseViewLink( destination, close_view ) {
		return {
			command: ( event_info ) => {
				event_info?.originalEvent?.preventDefault();

				if ( close_view ) {
					//TODO: After we decide on where openSelectView() should exist. Stop using temp _main_menu variable.
					Global.closeEditViews( function() {
						_main_menu.openSelectView( destination );
					} );
				} else {
					//Certain links such as "What's new", "Email Support" etc should not close edit views as they do not open new views.
					_main_menu.openSelectView( destination );
				}
			}
		};
	}

	parseReportLink( destination ) {
		return {
			command: ( event_info ) => {
				event_info?.originalEvent?.preventDefault();

				Global.closeEditViews( function() {
					if ( destination === 'AffordableCareReport' && !( Global.getProductEdition() >= 15 ) ) {
						TAlertManager.showAlert( Global.getUpgradeMessage() );
					} else {
						var parent_view = LocalCacheData.current_open_edit_only_controller ? LocalCacheData.current_open_edit_only_controller : LocalCacheData.current_open_primary_controller;
						IndexViewController.openReport( parent_view, destination );
					}
				} );
			}
		};
	}

	parseWizardLink( destination ) {
		return {
			command: ( event_info ) => {
				event_info?.originalEvent?.preventDefault();

				Global.closeEditViews( function() {
					//TODO: After we decide on where openSelectView() should exist. Stop using temp _main_menu variable.
					_main_menu.openSelectView( destination );
				} );
			}
		};
	}

}

/*
Custom types for JSDoc
 */

/**
 * A TT link object containing link info as well as TT specific attributes.
 * @typedef {Object} TTMenuLink
 * @property {string} type - view, wizard, report, popup.
 * @property {string} destination - The old-style TT hash reference for destination.
 * @property {string} target - Which Vue view container should this link open in. main, right, footer.
 */

// Add the above to an actual class definition of the same. See namespace with defaults on https://jsdoc.app/tags-property.html

/*
Sample Data

{
  label: TTi18n::getText(‘Employee’),
  icon: null|myicon.svg,
  tt_id: 'employee', // Maybe make this ID match the destination by default? but its lowercase.
  tt_link: null|{ type: ‘wizard|report|view’, destination: ‘employee’, target: null|main|left|right|footer }
  items: [ {},{},... ]
}

*/
