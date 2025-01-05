class HtmlTemplates {

	constructor() {
	}

	/**
	 * Conditionally insert variable or html. Can be used in two ways.
	 * Either 1) pass html, and if so it will use the html if the field is true.
	 * Or, 2) if the field is a value (and truthy), and html is undefined/not provided, then return the field value.
	 * Instead of 2), you can also reference a variable directly, but risk outputting 'undefined' into the output html.
	 * @param field Either a data value evaluating as truthy, or a Boolean.
	 * @param html Optional html field to use if field is Boolean.
	 * @returns {string|*}
	 */
	outputif( field, html ) { // function to be called outputif, or printif
		if ( field ) {
			return html ? html : field;
		} else {
			return '';
		}
	}

	/***
	 * Conditionally output options passed to the view controller constructor. Wrapper for outputif to handle multiple options.
	 * @param options
	 * @returns {string}
	 */
	outputOptions( options ) {
		let output = [];
		for ( var i = 0; i < options.length; i++ ) {
			let result = this.outputif( options[i].option, options[i].html );
			if ( result ) {
				output.push( result );
			}
		}
		return output.length > 0 ? '{ ' + output.join( ', ' ) + ' }' : '';
	}

	/**
	 * PascalCase to snake_case
	 */
	convertPascalCase2SnakeCase( string ) {
		// all lowercase separated by _
		return string.split( /(?=[A-Z][a-z])/ ).join( '_' ).toLowerCase(); // Fix: [A-Z][a-z] is needed to not split on all caps like ROEView. But this wont handle single caps at the end though.
	}

	/**
	 * PascalCase to kebab-case
	 */
	convertPascalCase2KebabCase( string ) {
		// all lowercase separated by -
		return string.split( /(?=[A-Z][a-z])/ ).join( '-' ).toLowerCase(); // Fix: [A-Z][a-z] is needed to not split on all caps like ROEView. But this wont handle single caps at the end though.
	}

	getTemplateTypeFromFilename( filename ) {
		var type;

		switch ( true ) { // known as 'overloaded switch'
			// The following views will use the new templating logic. Order of these statements is important, first rule to match is used.
			case this.checkViewClassForInlineHtmlbyFilename( filename ) !== false: // If success, it will return a String with the html.
				// If a view class contains a static html_template value, then use this as an override instead of any specific type template matched by filename.
				type = TemplateType.INLINE_HTML;
				break;
			case filename.indexOf( 'Sub' ) === 0: // Checks 'Sub' at start of filename. Must come before ReportView, otherwise it will conflict for SubSavedReportView.html
				type = TemplateType.SUB_VIEW;
				break;
			case filename.indexOf( 'EditView.html' ) !== -1: // Must come before List Views, otherwise it will match for those due to both ending in View.html
				type = TemplateType.EDIT_VIEW;
				break;
			case filename.indexOf( 'ReportView.html' ) !== -1 && filename.includes( 'Saved' ) === false: // Must come before List Views, and after 'Sub' otherwise reports will be loaded as list views. However make sure SavedReport is loaded as a list view.
				type = TemplateType.REPORT_VIEW;
				break;
			case filename.indexOf( 'View.html' ) !== -1: // Must come more or less last, otherwise it will conflict with other files containing View.html, like EditView.html and ReportView.html
				type = TemplateType.LIST_VIEW;
				break;
			default:
				// If no template types are matched, treat as legacy html.
				type = TemplateType.LEGACY_HTML; // This results in the relevant html file being loaded via the network. The new tab parsing logic may still run!
		}

		return type;
	}

	//Certain views do not fall under the rules of 'getTemplateTypeFromFilename()' and require special handling.
	//This can be removed once we switch how views are loaded and can apply these rules directly on the view itself.
	getTemplateOptionsFromViewId( view_id ) {
		let options = {
			view_id: view_id,
			// Remember, sub_view_mode is not included here as not yet available here; view controller has not yet been loaded. Sub View Mode will be determined by template_type using the file name data. Will be applied as an option in HtmlTemplates.getGenericListViewHtml
		};

		//The following tax reports require an additional tab.
		let reports_require_form_setup = ['RemittanceSummaryReport', 'T4SummaryReport', 'T4ASummaryReport', 'Form941Report', 'Form940Report', 'Form1099NecReport', 'FormW2Report', 'AffordableCareReport', 'USStateUnemploymentReport', 'USPERSReport','USEEOReport'];
		if ( reports_require_form_setup.includes( view_id ) ) {
			options.report_form_setup = true;
		}

		let sub_view_require_warning_message = [`UserDateTotal`];
		if ( sub_view_require_warning_message.includes( view_id ) ) {
			options.sub_view_warning_message = true;
		}

		//Issue #3158 - If these controllers are cached then side effects can occur.
		//Such as permission denied alerts after opening login view on Invoice -> Client view.
		let view_do_not_cache_controller = [`LoginUserWizard`, 'LoginUser', 'FindAvailableWizard', 'FindAvailable'];
		if ( view_do_not_cache_controller.includes( view_id ) ) {
			options.do_not_cache_controller = true;
		}

		//Audit log is both a TemplateType.INLINE_HTML and a TemplateType.SUB_VIEW under the current HTML2JS system.
		//Once we switch to loading ViewControllers before HTML this special case can be removed.
		if( view_id === 'Log' ) {
			options.is_sub_view = true;
		}

		//Views extending BaseTreeViewController have slightly different requirments such as not show total number div.
		//Once we switch to loading ViewControllers before the HTML this can be conditional by checking if the controller has tree_mode set to true.
		let base_tree_views = ['JobGroup', 'JobItemGroup', 'PunchTagGroup', 'UserGroup', 'DocumentGroup', 'KPIGroup', 'ClientGroup', 'ProductGroup'];
		if ( base_tree_views.includes( view_id ) ) {
			options.base_tree_view = true;
		}

		return options;
	}

	checkViewClassForInlineHtmlbyFilename( filename ) {
		// Lets see if this view class contains a html_template override, which should precede any type definitions.
		let check_class = window[ filename.replace(/\.html$/,'') + 'Controller' ];
		if( check_class !== undefined && typeof check_class.html_template === 'string' ) {
			return check_class.html_template;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param {TemplateType} type
	 * @param {Object} options
	 * @param {Function} [onResult]
	 */
	getTemplate( type, options = {}, onResult ) {

		var html_template;

		switch ( type ) {
			case TemplateType.LIST_VIEW:
				html_template = this.getGenericListViewHtml( options );
				break;
			case TemplateType.SUB_VIEW:
				options.is_sub_view = true; // Force this to be true, as it's a sub_view after all. (This switch data is based off html filename request)
				html_template = this.getGenericListViewHtml( options );
				break;
			case TemplateType.EDIT_VIEW:
				// code block
				html_template = HtmlTemplatesGlobal.genericEditView( options );
				break;
			case TemplateType.REPORT_VIEW:
				// code block
				html_template = HtmlTemplatesGlobal.genericReportEditView( options );
				break;
			case TemplateType.INLINE_HTML:
				html_template = this.getViewScriptTagHtml( options ) + this.checkViewClassForInlineHtmlbyFilename( options.filename );
				break;
			default:
				Debug.Error( 'HTML2JS: Error occured getting template. No matches for ' + options.view_id, 'HtmlTemplates.js', 'HtmlTemplates.js', 'getTemplate', 1 );
				return -1;
		}

		// If callback onResult exists, call the function, else return html.
		if ( onResult ) {
			onResult( html_template ); // should we put this outside the switch? Depends how similar the other switch statements are.
		} else {
			return html_template;
		}
	}

	/**
	 * This also handles subview script tags, if options.is_sub_view is true.
	 * @param options
	 * @returns {string}
	 */
	getGenericListViewHtml( options = {} ) {
		Debug.Text( 'HTML2JS: Template retrieved for ' + options.view_id, 'HtmlTemplates.js', 'HtmlTemplates.js', 'getGenericListViewHtml', 10 );
		// Prepend the <script> tag to the html template, so it can be executed when inserted into the DOM later on in the onResult.
		return this.getViewScriptTagHtml( options ) + HtmlTemplatesGlobal.genericListView( options );
	}

	getViewScriptTagHtml( options = {} ) {

		/*
		* Note: In the legacy html load, the view controller would get instantiated via the <script> tag at the top of the html file, when this got loaded into the DOM.
		* This would have happened in the onResult() function, one of such is BaseViewController.loadView -> doNext().
		* Rather than insert a check there to trigger the view controller, or prepend the script tag, we can also try to just do that AFTER the onResult.
		* We should not do it before, as there is still cleanup code run by IndexController.removeCurrentView
		*
		* However, for now currently the best option is to pre-append the script tag info, because the onResult function won't always be from BaseViewController.loadView
		* */

		let class_name = options.view_id + 'ViewController';
		let html_view_script_tag = `<!-- JS2HTML ${this.outputif( options.is_sub_view, 'SUB_VIEW ' )}--><script type="text/javascript">
												var ${this.outputif( options.is_sub_view, 'sub_' )}${this.convertPascalCase2SnakeCase( options.view_id )}_view_controller = new ${class_name}(${this.outputOptions( [
			{ option: options.is_sub_view, html: 'sub_view_mode: true' },
			{ option: options.do_not_cache_controller, html: 'can_cache_controller: false' },
		] )});
											</script>`;

		// Prepend the <script> tag to the html template, so it can be executed when inserted into the DOM later on in the onResult.
		// html_template = html_view_script_tag + html_template;
		//
		// debugger; // A good debug point when you get errors around view controller instances not defined. But could also mean parent controller needs manual html insertion into the tab_model, as the subview doesnt have the right columns in the generic template, thus the container (to which the html initializing the controller) is attached, does not exist.
		return html_view_script_tag;
	}

	genericListView( options = {} ) {

		// Prepare the required data for the html template
		let container_id = this.convertPascalCase2SnakeCase( options.view_id ) + '_view_container'; // E.g. branch_view_container. all lowercase separated by _ (Later in code, an id is added at the end of the string, like '_318') - Fix: [A-Z][a-z] is needed to not split on all caps like ROEView. But this wont handle single caps at the end though. // Also in most view.el
		let container_class = this.convertPascalCase2KebabCase( options.view_id ) + '-view'; // E.g. branch-view. all lowercase separated by -
		let view_template = `
			<div class="view js-generic-list-view ${this.outputif( container_class )}${this.outputif( options.is_sub_view, ' sub-view' )}" id="${this.outputif( container_id )}">
				${this.outputif( options.sub_view_warning_message, '<span class="warning-message"></span>' )}
				<div class="clear-both-div"></div>
				${this.genericListGrid( options )}
			</div>
		`;

		return view_template;
	}

	genericListGrid( options = {} ) {
		// let sub_view = options.is_sub_view;
		let grid_template = `
		<div class="grid-div js-generic-grid">
			${this.outputif( !options.is_sub_view && !options.base_tree_view, '<div class="total-number-div"><span class="total-number-span"></span></div>' )}
			<div class="grid-top-border"></div>

			${this.outputif( options.is_sub_view, '<div class="sub-grid-view-div">' )}
				<table id="grid"></table>
			${this.outputif( options.is_sub_view, '</div>' )}

			<div class="bottom-div">
				<div class="grid-bottom-border"></div>
			</div>
		</div>
	`;

		return grid_template;
	}

	genericEditView( options = {} ) {
		// Currently just for User Title Edit.
		// viewId: UserTitle
		// fileName: UserTitleEditView.html
		// Prepare the required data for the html template
		let tab_bar_id = this.convertPascalCase2SnakeCase( options.view_id ) + '_edit_view_tab_bar'; // e.g. user_title_edit_view_tab_bar - all lowercase separated by _ - Fix: [A-Z][a-z] is needed to not split on all caps like ROEView. But this wont handle single caps at the end though.
		let edit_view_class = options.view_id + 'EditView'; // e.g. UserTitleEditView
		let edit_view_template = `
			<div class="js-generic-edit-view edit-view ${this.outputif( edit_view_class )}">
				<div class="edit-view-tab-bar" id="${this.outputif( tab_bar_id )}">
					<div class="navigation-div" style="display: none">
						<span class="navigation-label"></span>
						<img class="left-click arrow"/>
						<div class="navigation-widget-div"></div>
						<img class="right-click arrow"/>
					</div>
					<span class="close-icon">x</span>
					<ul class="edit-view-tab-bar-label"></ul>
				</div>
			</div>
		`;

		return edit_view_template;
	}

	genericTab( options = {} ) {
		let is_multi_column = options.is_multi_column;
		let show_permission_div = options.show_permission_div;
		let is_sub = options.is_sub_view || false;
		let tab_id = options.tab_id;

		let save_continue_sub_view = `
			<div class="save-and-continue-div">
				<span class="message"></span>
				<div class="save-and-continue-button-div">
					<button class="tt-button p-button p-component" type="button">
						<span class="icon"></span>
						<span class="p-button-label"></span>
					</button>
				</div>
			</div>
		`;
		let permission_div = `
				<div class="save-and-continue-div permission-defined-div">
					<span class="message permission-message"></span>
				</div>`;
		let template = `
			<div id="${tab_id}" class="html2js_flag edit-view-tab-outside${this.outputif( is_sub, '-sub-view' )}">
				<div class="edit-view-tab" id="${tab_id}_content_div">
					<div class="first-column${this.outputif( is_sub, '-sub-view' )}${this.outputif( !is_multi_column, ' full-width-column' )}"></div>
					${this.outputif( is_multi_column, '<div class="second-column"></div>' )}

					${this.outputif( is_sub, save_continue_sub_view )}
					${this.outputif( show_permission_div, permission_div )}
				</div>
			</div>
		`;

		return template;
	}

	auditTab( options = {} ) {
		let template = `
			<div id="tab_audit" class="html2js_flag_audit edit-view-tab-outside-sub-view">
				<div class="edit-view-tab" id="tab_audit_content_div">
					<div class="first-column-sub-view"></div>
					<div class="save-and-continue-div">
						<span class="message"></span>
						<div class="save-and-continue-button-div">
							<button class="tt-button p-button p-component" type="button">
								<span class="icon"></span>
								<span class="p-button-label"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		`;

		return template;
	}

	genericReportEditView( options = {} ) {
		// Some examples of the differences. Interestingly, all checked templates so far all had the main div class set to active-shift-report-view.
		// ActiveShiftReportView.html : view_id=ActiveShiftReport : id=active_shift_report_view_tab_bar
		// UserSummaryReportView.html : id=user_summary_report_view_tab_bar
		// PayStubTransactionSummaryReportView.html : id=pay_stub_summary_report_view_tab_bar

		let tab_bar_id = this.convertPascalCase2SnakeCase( options.view_id ) + '_view_tab_bar';
		let edit_view_template = `
			<div class="js-generic-report-edit-view edit-view active-shift-report-view">
				<div class="edit-view-tab-bar" id="${this.outputif( tab_bar_id )}">
					<div class="navigation-div" style="display: none">
						<span class="navigation-label"></span>
						<img class="left-click arrow"/>
						<div class="navigation-widget-div"></div>
						<img class="right-click arrow"/>
					</div>
					<span class="close-icon">x</span>
					<ul class="edit-view-tab-bar-label">
						<li><a ref="tab_report" href="#tab_report"></a></li>
						<li><a ref="tab_setup" href="#tab_setup"></a></li>
						<li><a ref="tab_chart" href="#tab_chart"></a></li>
						${this.outputif( options.report_form_setup || ( options.view_id && options.view_id === 'PayrollExportReport' ), '<li><a ref="tab_form_setup" href="#tab_form_setup"></a></li>' )}
						<li><a ref="tab_custom_columns" href="#tab_custom_columns"></a></li>
						<li><a ref="tab_saved_reports" href="#tab_saved_reports"></a></li>
					</ul>
					<div id="tab_report" class="edit-view-tab-outside">
						<div class="edit-view-tab" id="tab_report_content_div">
							<div class="first-column full-width-column"></div>
						</div>
					</div>
					<div id="tab_setup" class="edit-view-tab-outside">
						<div class="edit-view-tab" id="tab_setup_content_div">
							<div class="first-column full-width-column"></div>
						</div>
					</div>
					<div id="tab_chart" class="edit-view-tab-outside">
						<div class="edit-view-tab" id="tab_chart_content_div">
							<div class="first-column full-width-column"></div>
							<div class="save-and-continue-div permission-defined-div">
								<span class="message permission-message"></span>
							</div>
						</div>
					</div>
					${this.outputif( options.report_form_setup, `
						<div id="tab_form_setup" class="edit-view-tab-outside">
							<div class="edit-view-tab" id="tab_form_setup_content_div">
								<div class="first-column full-width-column"></div>
							</div>
						</div>`
					)}
					${this.outputif( options.view_id && options.view_id === 'PayrollExportReport', `
						<div id="tab_form_setup" class="edit-view-tab-outside">
							<div class="edit-view-tab" id="tab_form_setup_content_div">
								<div class="first-row first-column full-width-column">
								</div>
								<div class="inside-editor-div full-width-column">
									<div class="grid-div">
										<table id="export_grid"></table>
									</div>
								</div>
							</div>
						</div>`
					)}
					<div id="tab_custom_columns" class="edit-view-tab-outside-sub-view">
						<div class="edit-view-tab" id="tab_custom_columns_content_div">
							<div class="first-column-sub-view"></div>
							<div class="save-and-continue-div">
								<span class="message"></span>
								<div class="save-and-continue-button-div">
									<button class="tt-button p-button p-component" type="button">
										<span class="icon"></span>
										<span class="p-button-label"></span>
									</button>
								</div>
							</div>
							<div class="save-and-continue-div permission-defined-div">
								<span class="message permission-message"></span>
							</div>
						</div>
					</div>
					<div id="tab_saved_reports" class="edit-view-tab-outside-sub-view">
						<div class="edit-view-tab" id="tab_saved_reports_content_div">
							<div class="first-column-sub-view"></div>
							<div class="save-and-continue-div">
								<span class="message"></span>
								<div class="save-and-continue-button-div">
									<button class="tt-button p-button p-component" type="button">
										<span class="icon"></span>
										<span class="p-button-label"></span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;

		return edit_view_template;
	}




	// This might still work, but put on hold as we chose to focus on the list, edit and sub views and tabs
	// getEditViewFormItem( options = {} ) {
	// 	let sub_view = options.is_sub_view || false;
	// 	let template = `
	// 	<div class="edit-view-form-item-div">
	// 		<div class="edit-view-form-item-${ sub_view ? 'sub-' : ''}label-div"><span class="edit-view-form-item-label"></span></div>
	// 		<div class="edit-view-form-item-input-div"></div>
	// 	</div>
	// `;
	//
	// 	return template;
	// }
}

/**
 *
 * @type {Readonly<{string, symbol}>}
 */
const TemplateType = Object.freeze( {
	LIST_VIEW: Symbol( 'LIST_VIEW'),
	SUB_VIEW: Symbol( 'SUB_VIEW'),
	EDIT_VIEW: Symbol( 'EDIT_VIEW'),
	REPORT_VIEW: Symbol( 'REPORT_VIEW'),
	INLINE_HTML: Symbol( 'INLINE_HTML'),
	LEGACY_HTML: Symbol( 'LEGACY_HTML'),
} );

const HtmlTemplatesGlobal = new HtmlTemplates();
window.TT_HTML_G = HtmlTemplatesGlobal; // TODO: Temp for dev, remove after all done.
export {
	HtmlTemplatesGlobal,
	// HtmlTemplates as HtmlTemplatesClass, // Not yet used as we are sharing the one global instance across scripts at the moment.
	TemplateType,
};