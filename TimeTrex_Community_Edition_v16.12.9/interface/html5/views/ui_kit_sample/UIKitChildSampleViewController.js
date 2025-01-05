export class UIKitChildSampleViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#ui_kit_child_sample_view_container',
			combo_box_array: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UIKitChildSampleEditView.html';
		this.permission_id = 'user';
		this.viewId = 'UIKitChildSample';
		this.script_name = 'UIKitChildSample';
		this.table_name_key = 'ui_kit_child';
		this.context_menu_name = $.i18n._( 'UIKit Child Sample' );
		this.navigation_label = $.i18n._( 'UIKit Child Sample' );
		this.api = TTAPI.APIUIKitChildSample;
		
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

	initOptions() {

		let options = [
			{ option_name: 'combo_box', api: this.api },
		];

		this.initDropDownOptions( options);
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
			'tab_ui_kit_child': { 'label': $.i18n._( 'UI Kit Child' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUIKitChildSample,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_ui_kit_child_sample',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_widget = this.edit_view_tab.find( '#tab_ui_kit_child' );
		var ui_kit_child_column = tab_widget.find( '.first-column' );
		var label, widgetContainer;

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( ui_kit_child_column );

		//Text input
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'text_input', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Text Input (Display Name)' ), form_item_input, ui_kit_child_column );

		//Checkbox
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'checkbox' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		widgetContainer.append( form_item_input );
		this.addEditFieldToColumn( $.i18n._( 'Checkbox' ), form_item_input, ui_kit_child_column, '', widgetContainer );
		
		//Combo Box
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'combo_box' } );
		form_item_input.setSourceData( $this.combo_box_array );
		this.addEditFieldToColumn( $.i18n._( 'Combo Box' ), form_item_input, ui_kit_child_column, '' );
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

}

UIKitChildSampleViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UIKitChildSample', 'SubUIKitChildSampleView.html', function( result ) {
		var args = {};
		var template = _.template( result );
		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_ui_kit_child_sample_view_controller );
			}
		}
	} );
};