export class UserReviewControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_review_control_view_container',



			type_array: null,
			status_array: null,
			term_array: null,
			severity_array: null,

			kpi_group_array: null,
			original_user_review_data: [],

			document_object_type_id: null,

			kpi_group_api: null,

			user_review_api: null,

			kpi_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserReviewControlEditView.html';
		this.permission_id = 'user_review';
		this.viewId = 'UserReviewControl';
		this.script_name = 'UserReviewControlView';
		this.table_name_key = 'user_review_control';
		this.context_menu_name = $.i18n._( 'Reviews' );
		this.navigation_label = $.i18n._( 'Review' );
		this.api = TTAPI.APIUserReviewControl;
		this.kpi_group_api = TTAPI.APIKPIGroup;
		this.user_review_api = TTAPI.APIUserReview;
		this.kpi_api = TTAPI.APIKPI;
		this.document_object_type_id = 220;
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
			include: [
				{
					label: $.i18n._( 'Print' ),
					id: 'pdf_review_print',
					vue_icon: 'tticon tticon-print_black_24dp',
					menu_align: 'right',
					sort_order: 100
				}
			]
		};

		return context_menu_model;
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', api: this.api },
			{ option_name: 'term', api: this.api },
			{ option_name: 'severity', api: this.api },
		];

		this.initDropDownOptions( options );

		this.kpi_group_api.getKPIGroup( false, false, false, {
			onResult: function( res ) {
				res = Global.clone( res.getResult() );

				//Error: Uncaught TypeError: Cannot set property 'name' of undefined in /interface/html5/#!m=Employee&a=edit&id=41499&tab=Reviews line 60
				if ( !res || !res[0] ) {
					$this.kpi_group_array = [];
					return;
				}

				res[0].name = '-- ' + $.i18n._( 'Add KPIs' ) + ' --';

				var all = {};
				all.name = '-- ' + $.i18n._( 'All' ) + ' --';
				all.level = 1;
				all.id = TTUUID.not_exist_id;

				if ( res.hasOwnProperty( '0' ) && res[0].hasOwnProperty( 'children' ) ) {
					res[0].children.unshift( all );
				} else {
					res = [
						{ children: [all], id: 0, level: 0, name: '-- ' + $.i18n._( 'Add KPIs' ) + ' --' }
					];
				}

				res = Global.buildTreeRecord( res );

				$this.kpi_group_array = res;

			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_review': {
				'label': $.i18n._( 'Review' ),
				'html_template': this.getUserReviewTabHtml(),
				'is_multi_column': true
			},
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserReviewControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_kpi_review_control',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_review = this.edit_view_tab.find( '#tab_review' );

		var tab_review_column1 = tab_review.find( '.first-column' );
		var tab_review_column2 = tab_review.find( '.second-column' );
		var tab_review_column4 = tab_review.find( '.forth-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_review_column1 );
		this.edit_view_tabs[0].push( tab_review_column2 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'user_review';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_review_column1, '' );

		// Reviewer
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'reviewer_user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Reviewer' ), form_item_input, tab_review_column1 );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_review_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_review_column1 );

		// Terms
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'term_id' } );
		form_item_input.setSourceData( $this.term_array );
		this.addEditFieldToColumn( $.i18n._( 'Terms' ), form_item_input, tab_review_column1 );

		// Rating
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'rating', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Rating' ), form_item_input, tab_review_column1, '' );

		// Severity
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'severity_id' } );
		form_item_input.setSourceData( $this.severity_array );
		this.addEditFieldToColumn( $.i18n._( 'Severity' ), form_item_input, tab_review_column2, '' );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date' } );

		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_review_column2, '', null );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'end_date' } );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_review_column2, '', null );

		// Due Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'due_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Due Date' ), form_item_input, tab_review_column2, '', null );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 320 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_review_column2, '', null, null, true );

		if ( this.is_add || this.is_edit ) {
			// Add KPIs from Groups
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: false,
				layout_name: 'global_tree_column',
				set_empty: true,
				field: 'group_id'
			} );
			form_item_input.setSourceData( $this.kpi_group_array );

			var tab_review_column3 = tab_review.find( '.third-column' ).css( {
				'float': 'left',
				'margin-top': '10px',
				'margin-bottom': '10px'
			} );
			tab_review_column3.find( '.column-form-item-label' ).css( {
				'float': 'left',
				'margin-right': '10px',
				'margin-top': '5px'
			} ).text( $.i18n._( 'Add KPIs from Groups' ) );
			tab_review_column3.find( '.column-form-item-input' ).css( { 'float': 'left' } ).append( form_item_input );

			this.edit_view_ui_dic[form_item_input.getField()] = form_item_input;

			form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );
		}

		// Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'note', width: '100%', height: 66 } );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_review_column4, 'first_last', null, null, true );
	}

	initInsideEditorUI() {
		//Inside editor
		var tab_review = this.edit_view_tab.find( '#tab_review' );

		var inside_editor_div = tab_review.find( '.inside-editor-div' );

		var args = {
			serial: '#',
			name: $.i18n._( 'Key Performance Indicator' ) + '<br>(' + $.i18n._('Hover for Description') + ')',
			rating: $.i18n._( 'Result' ),
			note: $.i18n._( 'Note' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			onFormItemChange: this.onInsideFormItemChange,
			parent_controller: this,
			api: this.user_review_api,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender()

		} );

		function getRender() {
			return `
			<table class="inside-editor-render">
				<tr class="title" style="font-weight: bold">
					<td style="width: 50px"><%= serial %></td>
					<td style="width: 820px"><%= name %></td>
					<td style="width: 70px"><%= rating %></td>
					<td style="width: 300px;"><%= note %></td>
				</tr>
			</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>`;
		}

		inside_editor_div.append( this.editor );
	}

	/* jshint ignore:start */

	addEditFieldToColumn( label, widgets, column, firstOrLastRecord, widgetContainer, saveFormItemDiv, setResizeEvent, saveFormItemDivKey, hasKeyEvent, customLabelWidget ) {

		var $this = this;
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );
		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );

		if ( customLabelWidget ) {
			form_item_label.parent().append( customLabelWidget );
			form_item_label.remove();
		} else {
			form_item_label.text( $.i18n._( label ) );
		}

		var widget = widgets;

		if ( Global.isArray( widgets ) ) {

			for ( var i = 0; i < widgets.length; i++ ) {
				widget = widgets[i];
				widget.css( 'opacity', 0 );

				this.edit_view_ui_dic[widget.getField()] = widget;

				widget.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );

				if ( hasKeyEvent ) {
					widget.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
						$this.onFormItemKeyUp( target );
					} );

					widget.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
						$this.onFormItemKeyDown( target );
					} );
				}
			}
		} else {

			widget.css( 'opacity', 0 );

			this.edit_view_ui_dic[widget.getField()] = widget;

			widget.bind( 'formItemChange', function( e, target, doNotValidate ) {
				$this.onFormItemChange( target, doNotValidate );
			} );

			if ( hasKeyEvent ) {
				widget.bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				widget.bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
			}

		}

		if ( Global.isSet( widgetContainer ) ) {
			form_item_input_div.append( widgetContainer );

		} else {
			form_item_input_div.append( widget );
		}

		if ( setResizeEvent ) {

			if ( widget.getField() === 'note' ) {

				form_item_input_div.css( 'width', '80%' );
				form_item_label_div.css( 'height', '80' );
				widget.css( { 'width': '100%', 'resize': 'none' } );

			} else {

				form_item.bind( 'resize', function() {

					if ( form_item_label_div.height() !== form_item.height() && form_item.height() !== 0 ) {
						form_item_label_div.css( 'height', form_item.height() );
						form_item.unbind( 'resize' );
					}

				} );

				// This causes extreme (10x) performance degradation on Bootstrap 5.1.0 and later. (Related TText setResizeEvent call)
				// widget.bind( 'setSize', function() {
				// 	form_item_label_div.css( 'height', widget.height() + 5 );
				// } );
			}

		}

		if ( saveFormItemDiv ) {

			if ( Global.isArray( widgets ) ) {
				this.edit_view_form_item_dic[widgets[0].getField()] = form_item;
			} else {
				this.edit_view_form_item_dic[widget.getField()] = form_item;
			}

		}

		column.append( form_item );
		//column.append( "<div class='clear-both-div'></div>" );
	}

	/* jshint ignore:end */

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'user_review';
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Reviewer' ),
				in_column: 1,
				field: 'reviewer_user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				object_type_id: 320,
				form_item_type: FormItemType.TAG_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Terms' ),
				in_column: 2,
				field: 'term_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Severity' ),
				in_column: 2,
				field: 'severity_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Due Date' ),
				in_column: 1,
				field: 'due_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'KPI' ),
				in_column: 2,
				field: 'kpi_id',
				layout_name: 'global_kpi',
				api_class: TTAPI.APIKPI,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	removeEditView() {
		super.removeEditView();
		this.editor = null;
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.initInsideEditorData();
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'group_id':
				var filter = {};
				filter.filter_data = {};
				// why need [c_value, -1], -1 will return all, the filter won't work correct if send -1,remove for testting
				filter.filter_data.group_id = [c_value];
				this.kpi_api['get' + this.kpi_api.key_name]( filter, false, true, {
					onResult: function( res ) {
						$this.setInsideEditorData( res );
					}
				} );
				break;
			default:
				this.current_edit_record[key] = c_value;
				if ( !doNotValidate ) {
					this.validate();
				}
				break;
		}
	}

	onInsideFormItemChange( target, doNotValidate ) {
		target.clearErrorStyle();

		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'rating':
				var minimum_rate = parseInt( target.attr( 'minimum_rate' ) );
				var maximum_rate = parseInt( target.attr( 'maximum_rate' ) );
				if ( c_value !== '' ) {
					c_value = parseInt( c_value );
					if ( c_value >= minimum_rate && c_value <= maximum_rate ) {
						target.clearErrorStyle();
						this.parent_controller.setEditMenu();
					} else {
						target.setErrorStyle( $.i18n._( 'Rating must between' ) + ' ' + minimum_rate + ' ' + $.i18n._( 'and' ) + ' ' + maximum_rate, true );
						this.parent_controller.setErrorMenu();
					}
				}
				break;
			default:
				break;
		}
	}

	initInsideEditorData() {

		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( this.current_edit_record.id ) {

			args.filter_data.user_review_control_id = this.current_edit_record['id'];

			$this.user_review_api['get' + $this.user_review_api.key_name]( args, true, {
				onResult: function( res ) {
					if ( !$this.edit_view ) {
						return;
					}
					$this.setInsideEditorData( res );
				}
			} );
		}
	}

	/* jshint ignore:start */

	setInsideEditorData( res ) {
		var data = res.getResult();
		var len = data.length;

		if ( len > 0 ) {

			if ( !this.editor ) {
				this.initInsideEditorUI();
			}

			var serial = 1;
			for ( var key in data ) {
				var row = data[key];
				var is_existed = false;
				if ( !row.kpi_id ) {
					row.kpi_id = row.id;
					row.id = false;
				}
				// the row.kpi_id if existed in this.editor.editor_data?
				if ( this.editor.editor_data ) {

					for ( var i = 0; i < this.editor.editor_data.length; i++ ) {
						var item = this.editor.editor_data[i];
						if ( row.kpi_id === item.kpi_id ) {
							is_existed = true; // the current row has already displayed.
							break;
						}
					}

					if ( !is_existed ) {
						serial = this.editor.editor_data.length + 1;
						row.serial = serial;
						this.editor.editor_data.push( row );
					}

//					serial++;

				} else {
					row.serial = serial;
					data[key] = row;
					serial++;
				}

				if ( !is_existed ) {
					this.editor.addRow( row );
				}

			}

			if ( !this.editor.editor_data ) {
				this.original_user_review_data = _.map( data, _.clone );
				for ( let i = 0; i < this.original_user_review_data.length; i++ ) {
					//Need to convert these jQuery objects to their values so thwt future data change comparisons do not fail.
					if ( this.original_user_review_data[i].hasOwnProperty( 'rating' ) && this.original_user_review_data[i].rating !== false ) {
						this.original_user_review_data[i].rating = this.original_user_review_data[i].rating.getValue();
					}
					if ( this.original_user_review_data[i].hasOwnProperty( 'note' ) && this.original_user_review_data[i].note !== false ) {
						this.original_user_review_data[i].note = this.original_user_review_data[i].note.getValue();
					}
				}
				this.editor.editor_data = data;
			}

		}

//		$this.editor.setValue( data );
	}

	/* jshint ignore:end */

//	insideEditorSetValue( val ) {
//		var len = val.length;
//		this.removeAllRows();
//
//		if ( len > 0 ) {
//			var serial = 1;
//			for ( var i = 0; i < val.length; i++ ) {
//				if ( Global.isSet( val[i] ) ) {
//					var row = val[i];
//					row.serial = serial;
//					this.addRow( row );
//					serial++;
//				}
//			}
//		}
//
//	},

	insideEditorAddRow( data ) {
		var $this = this;
		if ( !data ) {
//			this.getDefaultData();
		} else {
			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = data; //Save each row's widgets

			//Build row widgets

			// #
			var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'serial', width: 50 } );
			form_item_input.setValue( data.serial ? data.serial : null );
//			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );

			// Key Performance Indicator
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'name', width: 600 } );
			form_item_input.setValue( data.name ? data.name : null );
			form_item_input.attr( 'title', data.description ? data.description : $.i18n._( 'No Description' ) );

			row.children().eq( 1 ).append( form_item_input );

			// Rating
			if ( data.type_id == 10 ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: 'rating', width: 40 } );
				form_item_input.setValue( data.rating ? data.rating : null );
				form_item_input.attr( { 'minimum_rate': data.minimum_rate, 'maximum_rate': data.maximum_rate } );
				form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
					$this.onFormItemChange( target, doNotValidate );
				} );
				widgets[form_item_input.getField()] = form_item_input;
				row.children().eq( 2 ).append( form_item_input );

				this.setWidgetEnableBaseOnParentController( form_item_input );

			} else if ( data.type_id == 20 ) {
				form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
				form_item_input.TCheckbox( { field: 'rating' } );
				form_item_input.setValue( data.rating ? ( data.rating >= 1 ? true : false ) : null ); //Rating is numeric, so make sure we pass true/false to TCheckbox.
				widgets[form_item_input.getField()] = form_item_input;
				row.children().eq( 2 ).append( form_item_input );

				this.setWidgetEnableBaseOnParentController( form_item_input );
			}

			// Note
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
			form_item_input.TTextArea( {
				field: 'note',
				style: { width: '300px', height: '20px', 'min-height': '10px' }
			} );
			form_item_input.setValue( data.note ? data.note : null );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 3 ).css( 'text-align', 'right' ).append( form_item_input );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// end

			if ( this.rows_widgets_array.length === 0 ) {
				$( render ).append( row );
			} else {
				// Get all rows and insert new row according to it's display_order
				let rows = render.find( 'tr' ).get();

				data.display_order = parseInt( data.display_order ) || 0;

				for ( let i = 0; i < rows.length; i++ ) {
					// Loop looks for the correct spot to insert the new row based on the value of display_order.
					// If the last row is undefined or the value of display_order is less than the row to be inserted and the row after this is undefined or has a higher display_order, insert it there.
					if ( ( i === 0 || $( rows[i] ).attr( 'data-order' ) <= data.display_order ) && ( rows[i + 1] === undefined || $( rows[i + 1] ).attr( 'data-order' ) >= data.display_order ) ) {
						$( rows[i] ).after( row );
						break;
					}
				}

				// Now that the rows are in the correct order, renumber them.
				rows = render.find( 'tr' ).get();
				let order_number = 1;
				for ( let i = 0; i < rows.length; i++ ) {
					if ( i !== 0 ) {
						$( rows[i] ).find( "td:first" ).text( order_number );
						order_number++;
					}
				}
			}

			if ( this.parent_controller.is_viewing ) {
				row.find( '.control-icon' ).hide();
			}

			this.rows_widgets_array.push( widgets );

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();

			// Attach display_order to the <tr> for easy reordering after adding new KPI groups.
			row.attr('data-order', data.display_order);
		}
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].current_edit_item.id;
		if ( TTUUID.isUUID( remove_id ) && remove_id != TTUUID.zero_id && remove_id != TTUUID.not_exist_id ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search();
				$this.onSaveDone( result );

				$this.removeEditView();
			} );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	// onSaveAndContinueResult( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	//
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.search( false );
	// 			$this.onEditClick( $this.refresh_id, true );
	//
	// 			$this.onSaveAndContinueDone( result );
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	onEditClick( editId, noRefreshUI ) {
		var $this = this;
		if ( $this.editor ) {
			$this.editor.remove();
			$this.editor = null;
		}

		super.onEditClick( editId, noRefreshUI );
	}

	saveInsideEditorData( callBack ) {
		var $this = this;

		if ( !this.editor ) {
			callBack();
		} else {
			var data = this.editor.getValue( this.refresh_id );
			let changed_data = this.getChangedRecords( data, this.original_user_review_data, [] );

			if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
				this.user_review_api.setUserReview( changed_data, {
					onResult: function( res ) {
						callBack();
					}
				} );
			} else {
				callBack();
			}

		}
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;
		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			if ( row.rating ) {
				row.rating = row.rating.getValue();
			}
			row.note = row.note.getValue();

			row.user_review_control_id = current_edit_item_id;

			this.rows_widgets_array[i] = row;
		}

		return this.rows_widgets_array;
	}

	_continueDoCopyAsNew() {

		var $this = this;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			if ( this.editor ) {
				this.editor.remove();
				this.editor = null;
			}
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();

		} else {
			super._continueDoCopyAsNew();
		}
	}

	onSaveAndNewResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				if ( $this.editor ) {
					$this.editor.remove();
					$this.editor = null;
				}
				$this.onAddClick( true );
			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	onSaveAndCopyResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				if ( $this.editor ) {
					$this.editor.remove();
					$this.editor = null;
				}
				$this.onCopyAsNewClick();
			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	// onSaveAndNextResult( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.onRightArrowClick();
	// 			$this.search( false );
	// 			$this.onSaveAndNextDone( result );
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	onRightArrowClick() {
		if ( this.editor ) {
			this.editor.remove();
			this.editor = null;
		}
		super.onRightArrowClick();
	}

	onLeftArrowClick() {
		if ( this.editor ) {
			this.editor.remove();
			this.editor = null;
		}
		super.onLeftArrowClick();
	}

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'ReviewView', 'init' );
	}

	setDefaultMenuReportRelatedIcons( context_btn, grid_selected_length, pId ) {
		if ( !this.payStubReportIconsValidate() ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'pdf_review_print':
				if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
				} else {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
				}

				break;
		}
	}

	doFormIFrameCall( postData ) {
		Global.APIFileDownload( 'APIKPIReport', 'getKPIReport', postData );
	}

	onCustomContextClick( id ) {
		var $this = this;

		switch ( id ) {
			case 'pdf_review_print':
				var grid_selected_id_array;

				var ids = [];
				if ( $this.edit_view && $this.current_edit_record.id ) {
					ids.push( $this.current_edit_record.id );
				} else {
					grid_selected_id_array = this.getGridSelectIdArray();
					$.each( grid_selected_id_array, function( index, value ) {
						var grid_selected_row = $this.getRecordFromGridById( value );
						ids.push( grid_selected_row.id );
					} );
				}

				this.doFormIFrameCall( { 0: { 'user_review_control_id': ids }, 1: 'pdf_review_print' } );
				break;
		}
	}

	getUserReviewTabHtml() {
		return `<div id="tab_review" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_review_content_div">
						<div class="first-column"></div>
						<div class="second-column"></div>
						<div class="third-column full-width-column">
							<div class="third-column-form-item" style="margin-left: 40%">
								<div class="column-form-item-label"></div>
								<div class="column-form-item-input"></div>
							</div>
						</div>
						<div class="inside-editor-div full-width-column"></div>
						<div class="forth-column full-width-column border-column"></div>
					</div>
				</div>`;
	}
}

UserReviewControlViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserReviewControl', 'SubUserReviewControlView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_user_review_control_view_controller );
				} );
			}
		}
	} );
};