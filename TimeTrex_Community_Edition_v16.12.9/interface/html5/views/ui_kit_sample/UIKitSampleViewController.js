import '@/global/widgets/filebrowser/TImage';
import '@/global/widgets/filebrowser/TImageBrowser';
import '@/global/widgets/filebrowser/TImageAdvBrowser';
import '@/global/widgets/color-picker/TColorPicker';
import '@/global/widgets/formula_builder/FormulaBuilder.js'; // imported unnamed as it self executes on to the jQuery object as a jQuery plugin.

import tinyMCE from 'tinymce/tinymce';
import 'tinymce/icons/default/icons.min.js';
import 'tinymce/themes/silver/theme.min.js';
import 'tinymce/models/dom/model.min.js';
import 'tinymce/skins/ui/oxide/skin.js';
import 'tinymce/plugins/link';
import 'tinymce/skins/content/default/content.js';

export class UIKitSampleViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#ui_kit_sample_view_container',

			report_api: null,
			company_api: null,

			combo_box_array: null,

			sub_ui_kit_child_sample_view_controller: null,

			combo_box_parent_array: [], //Country Array
			combo_box_child_array: [] //Province Array
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UIKitSampleEditView.html';
		this.permission_id = 'user';
		this.viewId = 'UIKitSample';
		this.script_name = 'UIKitSample';
		this.table_name_key = 'ui_kit';
		this.context_menu_name = $.i18n._( 'UIKit Sample' );
		this.navigation_label = $.i18n._( 'UIKit Sample' );
		this.api = TTAPI.APIUIKitSample;
		this.report_api = TTAPI.APITimesheetSummaryReport;
		this.company_api = TTAPI.APICompany;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {

		let options = [
			{ option_name: 'combo_box', api: this.api },
			{ option_name: 'country', field_name: 'combo_box_parent', api: this.company_api },
		];

		this.initDropDownOptions( options, () => {
			this.combo_box_parent_array = this.country_array;
		} );
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		switch ( key ) {
			case 'combo_box_parent':
				var widget = this.edit_view_ui_dic['combo_box_child'];
				widget.setValue( null );
				break;
		}

		this.current_edit_record[key] = target.getValue();

		if ( key === 'combo_box_parent' ) {
			this.onComboBoxParentChanged();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onComboBoxParentChanged() {
		var selectVal = this.edit_view_ui_dic['combo_box_parent'].getValue();
		this.setComboBoxChild( selectVal, true );
	}

	setComboBoxChild( val, refresh, selected_value ) {
		var $this = this;
		var combo_box_child_widget = $this.edit_view_ui_dic['combo_box_child'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.combo_box_child_array = [];
			combo_box_child_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}
					$this.combo_box_child_array = Global.buildRecordArray( res );
					combo_box_child_widget.setSourceData( $this.combo_box_child_array );
					if ( refresh && $this.combo_box_child_array.length > 0 ) {
						//Keep current value if it is selected
						let child_value = selected_value ? selected_value : $this.combo_box_child_array[0].value;
						$this.current_edit_record.combo_box_child = child_value;
						combo_box_child_widget.setValue( child_value );
					}
				}
			} );
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: []
		};

		return context_menu_model;
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_input_basic': {
				'label': $.i18n._( 'Text / Basic' ),
				'html_template': this.getInputTabHtml(),
			},
			'tab_dropdowns': { 'label': $.i18n._( 'Dropdowns' ) },
			'tab_date_selectors': { 'label': $.i18n._( 'Date / Time' ) },
			'tab_image_file': { 'label': $.i18n._( 'Pickers' ) },
			'tab_sub_view': {
				'label': $.i18n._( 'Sub View' ),
				'is_sub_view': true,
				'init_callback': 'initSubUIKitChildView',
				'display_on_mass_edit': false
			},
			'tab_misc': { 'label': $.i18n._( 'Misc' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUIKitSample,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_ui_kit_sample',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_widget = this.edit_view_tab.find( '#tab_input_basic' );
		var text_basic_column = tab_widget.find( '.first-column' );
		var label, widgetContainer;

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( text_basic_column );

		//Text input
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'text_input', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Text Input (Display Name)' ), form_item_input, text_basic_column );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );
		form_item_input.TTagInput( { field: 'tag', object_type_id: 110 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, text_basic_column, '', null, null, true );

		//Textarea
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'textarea', width: '100%', rows: 4 } );
		this.addEditFieldToColumn( $.i18n._( 'Textarea' ), form_item_input, text_basic_column, '', null, null, true );

		//Numeric input
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'numeric_input', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Numeric (20,4)' ), form_item_input, text_basic_column );

		//Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'password_input', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Password' ), form_item_input, text_basic_column, '', null, true );

		//Checkbox
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'checkbox' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		widgetContainer.append( form_item_input );
		this.addEditFieldToColumn( $.i18n._( 'Checkbox' ), form_item_input, text_basic_column, '', widgetContainer );

		var tab_dropdown = this.edit_view_tab.find( '#tab_dropdowns' );
		var tab_dropdown_column_1 = tab_dropdown.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_dropdown_column_1 );

		//Combo Box
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'combo_box' } );
		form_item_input.setSourceData( $this.combo_box_array );
		this.addEditFieldToColumn( $.i18n._( 'Combo Box' ), form_item_input, tab_dropdown_column_1, '' );

		//Combo Box Parent
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'combo_box_parent' } );
		form_item_input.setSourceData( $this.combo_box_parent_array );
		this.addEditFieldToColumn( $.i18n._( 'Combo Box Parent' ), form_item_input, tab_dropdown_column_1, '' );

		//Combo Box Child
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'combo_box_child' } );
		form_item_input.setSourceData( $this.combo_box_child_array );
		this.addEditFieldToColumn( $.i18n._( 'Combo Box Child' ), form_item_input, tab_dropdown_column_1, '' );

		//Awesome Box Multiple
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'awesome_box_multi'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Awesome Box Multiple' ), form_item_input, tab_dropdown_column_1 );

		//Awesome Single
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'awesome_box_single'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Awesome Box Single' ), form_item_input, tab_dropdown_column_1 );

		var tab_date = this.edit_view_tab.find( '#tab_date_selectors' );
		var tab_date_column_1 = tab_date.find( '.first-column' );
		this.edit_view_tabs[2] = [];
		this.edit_view_tabs[2].push( tab_date_column_1 );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'date' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_date_column_1 );

		// Date Range
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TRangePicker( { field: 'date_range', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date Range' ), form_item_input, tab_date_column_1, '', null, true );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'time', validation_field: 'time_stamp' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		this.actual_time_label = $( '<span class=\'widget-right-label\'></span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_date_column_1, '', widgetContainer, true );

		//Time Unit
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'text_input', width: '100%', need_parser_sec: true, mode: 'time_unit' } );
		this.addEditFieldToColumn( $.i18n._( 'Text Input (Display Name)' ), form_item_input, tab_date_column_1 );

		var tab_image_file = this.edit_view_tab.find( '#tab_image_file' );
		var tab_image_file_column_1 = tab_image_file.find( '.first-column' );
		this.edit_view_tabs[3] = [];
		this.edit_view_tabs[3].push( tab_image_file_column_1 );

		// Photo
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );
		this.image_browser = form_item_input.TImageAdvBrowser( {
			field: '',
			default_width: 128,
			default_height: 128,
			enable_delete: true,
			callBack: function( form_data ) {
				new ServiceCaller().uploadFile( form_data, 'object_type=user_photo&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.image_browser.setImage( ServiceCaller.getURLByObjectType( 'user_photo' ) + '&object_id=' + $this.current_edit_record.id );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			},
			deleteImageHandler: function( e ) {
				$this.onDeleteImage();
			}
		} );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Upload Disabled' ) + '</span>' );
		widgetContainer.append( this.image_browser );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Photo' ), this.image_browser, tab_image_file_column_1, '', widgetContainer, false, true );

		// File
		form_item_input = Global.loadWidgetByName( FormItemType.FILE_BROWSER );
		this.file_browser = form_item_input.TImageBrowser( { field: 'file', name: 'file_data', accept_filter: '*' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Upload Disabled' ) + '</span>' );
		widgetContainer.append( this.file_browser );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'File' ), form_item_input, tab_image_file_column_1, '', widgetContainer, true, true );

		//Color
		form_item_input = Global.loadWidgetByName( FormItemType.COLOR_PICKER );
		form_item_input.TColorPicker( { field: 'color' } );
		this.addEditFieldToColumn( $.i18n._( 'Color' ), form_item_input, tab_image_file_column_1 );

		//Misc tab
		var tab_misc = this.edit_view_tab.find( '#tab_misc' );
		var tab_misc_column_1 = tab_misc.find( '.first-column' );
		this.edit_view_tabs[4] = [];
		this.edit_view_tabs[4].push( tab_misc_column_1 );

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Separated Box One' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_misc_column_1, '', null, true, false, 'shifts_scheduled_to_work' );

		//Formula builder
		form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
		form_item_input.FormulaBuilder( {
			field: 'formula_builder', width: '100%', onFormulaBtnClick: function() {
				TTAPI.APIReportCustomColumn.getOptions( 'formula_functions', {
					onResult: function( fun_result ) {
						var fun_res_data = fun_result.getResult();

						$this.report_api.getOptions( 'filter_columns', { onResult: onColumnsResult } );

						function onColumnsResult( col_result ) {
							var col_res_data = col_result.getResult();

							var default_args = {};
							default_args.functions = Global.buildRecordArray( fun_res_data );
							default_args.variables = Global.buildRecordArray( col_res_data );
							default_args.formula = $this.current_edit_record.formula_builder;
							default_args.current_edit_record = Global.clone( $this.current_edit_record );
							default_args.api = $this.api;

							IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
								$this.current_edit_record.formula_builder = val;
								$this.edit_view_ui_dic.formula_builder.setValue( val );
							} );
						}

					}
				} );
			}
		} );

		$this.addEditFieldToColumn( $.i18n._( 'Formula' ), form_item_input, tab_misc_column_1, '', null, true, true );

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Separated Box Two' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_misc_column_1, '', null, true, false, 'shifts_scheduled_to_work' );

	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Text Input' ),
				in_column: 1,
				field: 'text_input',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
		];
	}

	setCurrentEditRecordData() {
		super.setCurrentEditRecordData();
		this.setComboBoxChild( this.current_edit_record.combo_box_parent, true, this.current_edit_record.combo_box_child );
		this.setWYSIWGText();
	}

	setWYSIWGText( el ) {
		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		form_item_label.text( $.i18n._( 'TinyMCE' ) );
		form_item_input_div.addClass( 'edit-view-form-item-tinymce-textarea-div' );
		var tab_input_basic = this.edit_view_tab.find( '#tab_input_basic' );
		// var column = tab_vacancy.find( '.third-column' );
		var tab_tab_input_basic3 = tab_input_basic.find( '.third-column' );
		tab_tab_input_basic3.html( form_item );
		this.edit_view_tabs[0].push( tab_tab_input_basic3 );
		this.editFieldResize( 0 );
		// var widget = widgets;
		form_item_label_div.css( 'height', '340px' );
		form_item_label_div.css( 'width', form_item_label_div.width() + 1 );
		this.showWYSIWGText();

		window.onresize = function() {
			if ( this.edit_view ) {
				$this.resizeMCE();
			}
		};
		return form_item;
	}

	showWYSIWGText() {
		if ( !this.edit_view_tab ) {
			return;
		}
		var form_item_label_div_width = this.edit_view_tab.find( '#tab_input_basic' ).find( '.third-column' ).find( '.edit-view-form-item-label-div' ).width() + 12;
		var el = Global.loadWidgetByName( FormItemType.TINYMCE_TEXT_AREA, true );
		var description = this.current_edit_record['wysiwg_text'];

		var width = $( this.edit_view_tab.find( '.edit-view-tab' )[0] ).width() - form_item_label_div_width;
		var options = {
			description: description,
			width: ( width - 3 ) + 'px',
			height: '335px'
		};

		var readonly = false;
		if ( this.is_viewing ) {
			readonly = true;
		}

		var tpl = _.template( el )( options );
		tinyMCE.remove();
		this.edit_view_tab.find( '#tab_input_basic' ).find( '.third-column' ).find( '.edit-view-form-item-tinymce-textarea-div' ).html( tpl );

		// new tinyMCE.init code here
		tinyMCE.init( {
			height: '335px',
			width: '100%',
			autoresize_min_width: ( width - 3 ),
			selector: '.tinymce-text-area',
			readonly: readonly,
			menubar: false,
			statusbar: false,
			plugins: 'link',
			content_style: 'body { font-size: 14px; margin-top: 0; color: #404042; }',
		} );

		var $this = this;
		var tinymce_textarea = this.edit_view_tab.find( '#tab_input_basic' ).find( '.third-column' ).find( '.edit-view-form-item-tinymce-textarea-div' );
		tinymce_textarea.hide();
		var search_for_tinymce = setInterval( function() {
			var body = tinymce_textarea.find( 'iframe' ).contents().find( 'body' );
			if ( !_.isUndefined( body[0] ) ) {
				clearInterval( search_for_tinymce );
				$this.resizeMCE();
				tinymce_textarea.show();
			}
		}, 50 );
	}

	resizeMCE() {

	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length >= 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	uniformVariable( records ) {
		if ( this.is_mass_editing ) {
			return records;
		} else {
			records.wysiwg_text = tinyMCE.activeEditor.getContent();
		}
		return records;
	}

	removeEditView() {
		this.sub_ui_kit_child_sample_view_controller = null;
		super.removeEditView();
	}

	initSubUIKitChildView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		TTPromise.add( 'UIKitChildSampleView', 'init' );

		if ( this.sub_ui_kit_child_sample_view_controller ) {
			this.sub_ui_kit_child_sample_view_controller.buildContextMenu( true );
			this.sub_ui_kit_child_sample_view_controller.setDefaultMenu();
			$this.sub_ui_kit_child_sample_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_ui_kit_child_sample_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_ui_kit_child_sample_view_controller.initData(); //Init data in this parent view
			TTPromise.resolve( 'UIKitChildSampleView', 'init' );
			return;
		}

		Global.loadScript( 'views/ui_kit_sample/UIKitChildSampleViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}
			var tab_employee = $this.edit_view_tab.find( '#tab_sub_view' );
			var firstColumn = tab_employee.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'UIKitChild' + 'View' );
			UIKitChildSampleViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController, firstColumn ) {

			$this.sub_ui_kit_child_sample_view_controller = subViewController;
			$this.sub_ui_kit_child_sample_view_controller.parent_key = 'parent_id';
			$this.sub_ui_kit_child_sample_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_ui_kit_child_sample_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_ui_kit_child_sample_view_controller.parent_view_controller = $this;
			TTPromise.wait( 'BaseViewController', 'initialize', function() {
				if ( $this.sub_ui_kit_child_sample_view_controller ) {
					$this.sub_ui_kit_child_sample_view_controller.initData(); //Init data in this parent view
				}
			} );
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		//Add view drag and drop
		if ( this.is_add ) {
			Global.initFileDragAndDrop( this.edit_view[0].firstElementChild, ( files ) => {
				this.attachDragAndDropFile( files );
			} );
		}
	}

	attachDragAndDropFile( files ) {
		//Only add the first file as each document is a single file.
		let data_transfer = new DataTransfer()
		data_transfer.items.add( files[0] );

		this.edit_view_ui_dic['file'][0].querySelector('input').files = data_transfer.files;
	}

	getInputTabHtml() {
		return `<div id="tab_input_basic" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_text_content_div">
						<div class="first-column"></div>
						<div class="second-column"></div>
						<div class="fourth-column job-text-summary-description"></div>
						<div class="third-column full-width-column"></div>
					</div>
				</div>`;
	}

}