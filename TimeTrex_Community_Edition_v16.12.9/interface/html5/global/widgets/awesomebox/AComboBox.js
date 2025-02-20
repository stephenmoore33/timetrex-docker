( function( $ ) {

	$.fn.AComboBox = function( options ) {
		var opts = $.extend( {}, $.fn.AComboBox.defaults, options );

		var label_span = null;

		var a_dropdown = null;

		var layout_name = '';

		var pager_data = null; //use to reset pager data when open dropdown

		var api_class = null;

		var api = null; //Related TTAPI.API instance, use to get data

		var default_columns = null;	 //Default coluns when no layout saved

		var allow_multiple_selection = false;

		var do_not_get_real_data = false; //Get full data when only pass id to select Value.

		var get_real_data_on_multi = false; //Usually only get real data when select has a single value. But punch tags require manual_id of all records.

		var set_any = false;

		var set_empty = false;

		var set_special_empty = false;

		var set_open = false;

		var set_default = false;

		var set_all = false;

		var select_item = null;

		var select_items = null;

		var field = '';

		var validation_field;

		var user_generic_api = null;

		var display_columns = null; //Display columns in ADropDown in jGrid model format

		var possible_display_columns = null; //Only these columns can be shown when init dispaly columns.

		var list_view_default_columns = null; //Only these columns can be shown when init dispaly columns.

		var display_columns_in_columnEditor = null; //Display columns in edit columns

		var all_columns = null; // All columns when edit columns

		var column_editor = null;

		var source_data = null; // This will never change when search in search input. Set it back to dropdown every time when open

		var id = '';

		var get_real_data_when_open = false;

		var set_select_items_when_set_data_provider = false;

		var $this = null;

		var a_dropdown_div = null;

		var is_mouse_over = false;

		var row_per_page = 0;

		var show_all = false;

		var args = null;

		Global.addCss( 'global/widgets/awesomebox/AComboBox.css' );

		var show_search_inputs = true;

		var tree_mode = false;

		var on_tree_grid_row_select = false;

		var key = 'id';

		var error_string = '';

		var error_tip_box;

		$this = this;

		var mass_edit_mode = false;

		var check_box = null;

		var enabled = true;

		var allow_drag_to_order = false;

		var navigation_mode = false;

		var args_from_saved_layout = null;

		var default_args = null;
		//Use this in Navigation Mode, Keep search filter when open. Don't clean it in onClose if navigation_mode
		// Now we di this in both navigation and normal (2014/6/7)
		var cached_search_inputs_filter = null;

		var cached_select_grid_search_inputs_filter = null;

		var cached_sort_filter = null; //Same as above

		var cached_selected_grid_sort_filter = null; //Same as above

		var script_name = '';

		var navigation_mode_source_data_before_open = null;

		var set_default_args_manually = false; //If set default args outside

		var addition_source_function = null;

		var custom_key_name = null;

		var setRealValueCallBack = null; //Set real data call back function

		var custom_first_label = null;

		var added_items = null;

		var column_option_key = 'columns';

		// set this when close, don't allow awesomebox open until 0.3 sec, this prevent awesomebox close in mousedown, and open in click.
		var dontOpenTimer = null;

		var dontOpen = false;

		var total_header_width;

		//if init source data right after initcolumns complete
		var init_data_immediately = false;

		var unselect_grid_search_result;

		// don't do column filter base on display columns, use all instead
		//Use in report edit view. load saved report navigation
		var always_search_full_columns = false;

		//Save what letter user current use to do the search
		var quick_search_dic = {};

		//Save multi key typed when quick search
		var quick_search_typed_keys = '';

		var select_grid_search_result;

		//use to juedge if need to clear quick_search_typed_keys
		var quick_search_timer;

		var current_open_page = 1;

		//deal with result when doing dropdown search, TimesheetAuthorizationViewControl need this.
		var extendDataProcessWhenSearch;

		var always_include_columns = false;
//
//		//Used for modify search result when doing Paging or Searching, For example, used in AccrualBalanceViewController to set correct ids
//		this.customSearchResultHandler = null;

		//Used for modify search filter when open awesomebox or do search/sorting and paging. First used in Timehsheet absency_policy awesomebox
		this.customSearchFilter = null;

		this.getCurrentOpenPage = function() {
			return current_open_page;
		};

		this.getPagerData = function() {
			return pager_data;
		};

		this.getHeaderWidth = function() {
			return total_header_width;
		};

		this.setKey = function( val ) {
			key = val;
		};

		this.getDisplayColumns = function() {
			return display_columns;
		};

		this.getLayout = function() {

			return ALayoutCache.layout_dic[layout_name];
		};

		this.getAPI = function() {
			return api;
		};

		this.getScriptName = function() {

			script_name = Global.getScriptNameByAPI( api_class );

			return script_name;
		};

		this.setCachedSortFilter = function( val ) {
			cached_sort_filter = val;
		};

		this.setCachedSelectedGridSortFilter = function( val ) {
			cached_selected_grid_sort_filter = val;
		};

		this.setCachedSearchInputsFilter = function( val ) {
			cached_search_inputs_filter = val;
		};

		this.setCachedSelectGridSearchInputsFilter = function( val ) {
			cached_select_grid_search_inputs_filter = val;
		};

		this.setAllowMultipleSelection = function( val ) {
			allow_multiple_selection = val;

			if ( val == true ) {
				if ( select_item ) {
					select_items = [select_item];
				}
			} else {
				if ( select_items && select_items.length > 0 ) {
					select_item = select_items[0];
				}
			}

		};

		this.getEnabled = function() {
			return enabled;
		};

		this.setEnabled = function( val ) {
			enabled = val;
			if ( val === false || val === '' ) {
				$this.addClass( 'a-combobox-readonly' );
				if ( check_box ) {
					check_box.hide();
				}
			} else {
				$this.removeClass( 'a-combobox-readonly' );

				if ( check_box ) {
					check_box.show();
				}
			}

		};

		this.setCheckBox = function( val ) {
			if ( check_box ) {
				check_box.children().eq( 0 )[0].checked = val;
			}
		};

		this.isChecked = function() {
			if ( check_box ) {
				if ( check_box.children().eq( 0 )[0].checked === true ) {
					return true;
				}
			}

			return false;
		};

		this.setMassEditMode = function( val ) {
			mass_edit_mode = val;

			if ( mass_edit_mode ) {
				check_box = $( ' <div class="mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox"></input>' +
					'<label for="checkbox-input-1" class="input-helper input-helper--checkbox"></label></div>' );
				check_box.insertBefore( $( this ) );

				check_box.change( function() {
					$this.trigger( 'formItemChange', [$this] );
				} );

			} else {
				if ( check_box ) {
					check_box.remove();
					check_box = null;
				}
			}

		};

		this.getItemByIndex = function( index ) {
			var target_source_data;

			if ( navigation_mode && !source_data ) {
				target_source_data = navigation_mode_source_data_before_open;
			} else {
				target_source_data = source_data;

				//Only for single mode
				if ( unselect_grid_search_result && unselect_grid_search_result.length > 0 ) {
					target_source_data = unselect_grid_search_result;
				}
			}

			if ( !target_source_data ) {
				return null;
			}

			var result = null;
			result = target_source_data[index];
			return result;

		};

		this.getSelectIndex = function() {

			var target_source_data;

			if ( navigation_mode && !source_data ) {
				target_source_data = navigation_mode_source_data_before_open;
			} else {
				target_source_data = source_data;

				//Only for single mode
				if ( unselect_grid_search_result && unselect_grid_search_result.length > 0 ) {
					target_source_data = unselect_grid_search_result;
				}
			}

			if ( !select_item || !target_source_data ) { // can't get correct index if source_data is null
				return 0;
			}

			var len = target_source_data.length;
			for ( var i = 0; i < len; i++ ) {
				var item = target_source_data[i];
				if ( select_item[key] === item[key] ) {
					return i;
				}
			}

			return 0;
		};

		this.getNextSelectItemId = function() {
			var retval = null;
			// This function is commonly used by edit views to get the id of the next record from the navigation box, such as during Delete&Next
			var next_select_item = this.getSelectIndex() + 1;
			var source_data = this.getSourceData();
			if ( source_data && next_select_item < source_data.length ) {
				retval = this.getItemByIndex( next_select_item );
			} else {
				retval = null;
			}

			return retval;
		};

		this.setErrorStyle = function( errStr, show, isWarning ) {
			if ( isWarning ) {
				$( this ).addClass( 'a-warning-tip' );
			} else {
				$( this ).addClass( 'a-error-tip' );
			}
			error_string = errStr;

			if ( show ) {
				this.showErrorTip();
			}
		};

		this.showErrorTip = function( sec ) {

			if ( !Global.isSet( sec ) ) {
				sec = 2;
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox();
			}
			if ( $( this ).hasClass( 'a-warning-tip' ) ) {
				error_tip_box.show( this, error_string, sec, true );
			} else {
				error_tip_box.show( this, error_string, sec );
			}
		};

		this.hideErrorTip = function() {

			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}

		};

		this.clearErrorStyle = function() {
			$( this ).removeClass( 'a-error-tip' );
			$( this ).removeClass( 'a-warning-tip' );
			error_string = '';
			this.hideErrorTip();
		};

		this.setField = function( val ) {
			field = val;
		};

		this.getField = function() {
			return field;
		};

		this.getValidationField = function() {
			return validation_field;
		};

		this.highlightSelectItem = function() {
			if ( !allow_multiple_selection ) {
				let selected_id;

				if ( select_item && select_item[key] ) {
					selected_id = select_item[key];
				} else if ( select_item ) {
					selected_id = select_item;
				}

				if ( selected_id && a_dropdown ) {
					a_dropdown?.getUnSelectGrid()?.grid?.jqGrid( 'setSelection', selected_id, false );

					let selected_item_element = document.getElementById( selected_id );
					if ( selected_item_element ) {
						selected_item_element.scrollIntoView();
					}
				}
			}
		};

		this.getValue = function( return_full_value ) {
			var return_value = null;
			if ( allow_multiple_selection ) {
				if ( return_full_value ) {
					return_value = select_items;
				} else {
					return_value = [];
					if ( Global.isArray( select_items ) ) {
						$.each( select_items, function( index, content ) {
							// In 1891, one value in select items is null
							if ( content ) {
								if ( Global.isString( content ) || $.type( content ) === 'number' ) {
									return_value.push( content );
								} else {
									return_value.push( content[key] );
								}
							}
						} );
						if ( select_items.length === 0 ) {
							if ( set_any ) {
								return_value.push( TTUUID.not_exist_id ); // In fact, if the columns can be multiple selected. and no have any options selected, it's should be return an array.
							}
						}
					} else {
						if ( set_any ) {
							return_value.push( TTUUID.not_exist_id ); // In fact, if the columns can be multiple selected. and no have any options selected, it's should be return an array.
						}
					}
					// Return false when no selected value
					if ( return_value.length === 0 ) {
						if ( set_any ) {
							return_value = TTUUID.not_exist_id;
						} else if ( set_empty ) {
							return_value = TTUUID.zero_id;
						} else if ( set_special_empty ) {
							return_value = TTUUID.not_exist_id;
						} else if ( set_open ) {
							return_value = TTUUID.zero_id;
						} else if ( set_default ) {
							return_value = TTUUID.zero_id;
						}
					}
				}
			} else {
				if ( return_full_value ) {
					return_value = select_item;
				} else {
					if ( select_item ) {

						if ( Global.isSet( select_item[key] ) ) {
							return_value = select_item[key];
						} else {
							return_value = select_item;
						}

					} else {
						if ( set_any ) {
							return TTUUID.not_exist_id;
						} else if ( set_empty ) {
							return TTUUID.zero_id;
						} else if ( set_special_empty ) {
							return TTUUID.not_exist_id;
						} else if ( set_open ) {
							return TTUUID.zero_id;
						} else if ( set_default ) {
							return TTUUID.zero_id;
						}
					}

				}
			}

			return return_value;
		};

		this.setSourceData = function( val ) {
			source_data = val;

			if ( !allow_multiple_selection ) {
				if ( val && val.length > 0 ) {
					if ( set_empty || set_any || set_default || set_open || set_all || set_special_empty ) {
						$this.createFirstItem();
					} else {
						if ( !select_item ) {
							//Related to 1825, directly use item not item id, since use item id willl
							// trigger getReal data which case problems and not necessary.
							this.setValue( val[0] );
						}
					}
				}
			} else {
				if ( val && val.length > 0 ) {
					if ( set_all ) {
						$this.createFirstItem();
					}
				}
			}

			if ( set_select_items_when_set_data_provider && source_data ) {

				if ( allow_multiple_selection ) {
					this.setValue( select_items );
				} else {
					this.setValue( select_item );
				}
			}
		};

		this.getSourceData = function() {

			if ( navigation_mode && navigation_mode_source_data_before_open ) {
				return navigation_mode_source_data_before_open;
			}

			//if done search, return the result o
			if ( unselect_grid_search_result ) {
				return unselect_grid_search_result;
			}

			return source_data;
		};

		//Always return source data only
		this.getStaticSourceData = function() {
			return source_data;
		};

		this.getRowPerPage = function() {
			return row_per_page;
		};

		this.setAllColumns = function( value ) {
			all_columns = value;
		};

		this.setDisplayColumnsForEditor = function( value ) {
			display_columns_in_columnEditor = value;
		};

		this.getAllColumns = function() {

			if ( possible_display_columns ) {
				return possible_display_columns;
			}
			return all_columns;
		};

		this.getDisplayColumnsForEditor = function() {
			return display_columns_in_columnEditor;
		};

		this.setEmptyValueAndShowLoading = function() {
			var current_width = $this.find( '.a-combobox-label' ).width();
			this.setValue( null );
			label_span.text( Global.loading_label );
			$this.find( '.a-combobox-label' ).width( current_width + 'px' );
		};

		this.setValue = function( val ) {
			// Related to 1891, one case is value is [null] in vacatuon_psea_ids in ROE edit view
			if ( _.isArray( val ) && val.length === 1 && val[0] === null ) {
				val = null;
			}
			if ( allow_multiple_selection ) {
				if ( !val || $.type( val ) === 'array' ) {
					this.setSelectItems( val );
				} else if ( $.type( val ) === 'string' || $.type( val ) === 'number' ) {
					this.setSelectItems( [val] );
				}
			} else {
				if ( $.type( val ) === 'array' && val.length > 0 ) {
					val = val[0];
				}
				//If no default value set first item as default select item
				//#1187 - this fix might cause problems but is needed to allow proper selection of default value when zero_uuid is selected.
				if ( !val || val == TTUUID.zero_id ) {
					// if ( !val ) {
					if ( !set_empty && !set_any && !set_default && !set_open && !set_all && !set_special_empty && source_data && source_data.length > 0 ) {
						this.setValue( source_data[0] );
					} else {
						this.setSelectItem( null );
					}
				} else {

					this.setSelectItem( val );
				}
			}

		};

		this.getAllowMultipleSelection = function() {
			return allow_multiple_selection;
		};

		//Get full data from api, if get a id
		this.getRealData = function( val ) {

			if ( Global.isSet( api_class ) ) {

				//Try api awesomebox first
				if ( ( !TTUUID.isUUID( val ) && parseInt( val ) <= 0 ) || val == TTUUID.zero_id || val == TTUUID.not_exist_id ) {

					if ( allow_multiple_selection ) {
						$this.setValue( [this.getLocalSelectItem( val )] );
					} else {
						$this.setValue( this.getLocalSelectItem( val ) );
					}

					return;
				}

				var filter = {};
				//id is a public member of this class which contains the field id, so we wrapped 'id' in quotes here to show it's not the public member from above.
				filter.filter_data = { 'id': val };

				//#2289 - If you have an employee who can view subordinate punches but not view subordinate employee, you will see permission denied without sending the permission section.
				if ( default_args && Global.isSet( default_args.permission_section ) ) {
					filter.permission_section = default_args.permission_section;
				}

				if ( this.customSearchFilter ) {
					filter = this.customSearchFilter( filter );
				}

				label_span.text( Global.loading_label );

				api['get' + custom_key_name]( filter, {
					onResult: function( result ) {
						var result_data = result.getResult();

						if ( result_data && result_data.length > 0 ) {

							var value;
							if ( allow_multiple_selection ) {
								value = result_data;
								$this.setValue( result_data );
							} else {
								value = result_data[0];
								$this.setValue( result_data[0] );
							}

						}

						if ( setRealValueCallBack ) {
							setRealValueCallBack( value );
						}
					}

				} );
			} else {
				if ( source_data && source_data.length > 0 ) {
					$.each( source_data, function( index, content ) {

						//Make the id match when val is string or number. use == instead ===.
						if ( content[key] == val ) {

							$this.setValue( content );
							return false; //This is a boolean false, not an integer or UUID.
						}
					} );

				} else {
					set_select_items_when_set_data_provider = true;
				}
			}

		};

		this.getColumnFilter = function() {

			if ( always_search_full_columns ) {
				return {};
			}

			var column_filter = {};
			column_filter.is_owner = true;
			column_filter.id = true;
			column_filter.is_child = true;
			column_filter.user_id = true;
			column_filter.first_name = true; //always contains this if it exists
			column_filter.last_name = true; //always contains this if it exists
			column_filter.object_type_id = true;
			column_filter.manual_id = true;
			column_filter.default_item_id = true;
			column_filter.accrual_policy_id = true;
			column_filter.pay_code_id = true;
			column_filter.start_date = true;
			column_filter.end_date = true;
			column_filter.pay_period_id = true;

			if ( api && api.className === 'APIUser' ) {
				column_filter.pay_period_schedule_id = true;
				column_filter.policy_group_id = true;
				column_filter.hire_date = true;
				column_filter.termination_date = true;
			}

			$.each( display_columns, function( key, item ) {
				column_filter[item.name] = true;
			} );

			if ( always_include_columns && always_include_columns.length > 0 ) {
				$.each( always_include_columns, function( key, item ) {
					column_filter[item] = true;
				} );
			}

			return column_filter;
		};

		this.cleanDropDownValues = function() {
			if ( a_dropdown ) {
				a_dropdown.setSelectGridData( [] );
			}
		};

		this.setSelectItem = function( val ) {
			//When setting select_item, clear out the select_items (plural) variable so it doesn't contain old data if the dropdown box happens to get switched back to it.
			select_items = null;

			select_item = val;

			if ( val === false || val === '' ) {
				this.setLabel( val );
				if ( setRealValueCallBack ) {
					setRealValueCallBack( false );
				}
				return;
			}

			if ( $.type( val ) === 'string' || $.type( val ) === 'number' ) {
				this.getRealData( val );
			} else {
				this.setLabel();
			}
		};

		this.setSelectItems = function( val ) {
			//When setting select_items (plural), clear out the select_item variable so it doesn't contain old data if the dropdown box happens to get switched back to it.
			select_item = null;

			if ( !val || val.length < 1 ) {
				this.setEmptyLabel();
				select_items = null;
				if ( setRealValueCallBack ) {
					setRealValueCallBack( false );
				}

				this.cleanDropDownValues();

				return;
			} else if ( val == TTUUID.not_exist_id && set_any ) {
				select_items = val;
				this.setEmptyLabel();
				if ( setRealValueCallBack ) {
					setRealValueCallBack( false );
				}
				this.cleanDropDownValues();
				return;
			}

			select_items = val;

			var len = val.length;

			if ( len > 1 ) {
				var item = val[0];
				if ( $.type( item ) === 'string' || $.type( item ) === 'number' ) {
					if ( !do_not_get_real_data ) {
						get_real_data_when_open = true;
					}
					if ( get_real_data_on_multi && setRealValueCallBack ) {
						//Required to get values for Multi-select field callbacks. Currently, used to retrieve punch tag manual ids.
						//If this is not done, manual ids will only be retrieved in the scenario there is only one item selected in which case this.getRealData() is called.
						//However, adding this.getRealData() here causes other issues.
						setRealValueCallBack( val, true );
					}
				}
				this.setLabel();
			} else {
				for ( var i = 0; i < len; i++ ) {
					item = val[i];
					if ( $.type( item ) === 'string' ||
						$.type( item ) === 'number' ) {
						if ( !do_not_get_real_data ) {
							this.getRealData( item );
						} else {
							if ( !source_data ) {
								set_select_items_when_set_data_provider = true;
								return;
							}
							//set select items	which only contains value to select items have label and value
							this.setSelectItems( this.getRealSelectItemsFromSourceData() );
						}
					} else {
						for ( var key in item ) {
							//speical handle for sort field
							if ( item[key] === 'asc' || item[key] === 'desc' ) {
								if ( !source_data ) {
									set_select_items_when_set_data_provider = true;
									return;
								} else {
									break;
								}
							}
						}

						this.setLabel();
					}
				}
			}

		};

		this.getSelectItems = function() {
			return a_dropdown.getSelectItems();
		}

		this.getRealSelectItemsFromSourceData = function() {
			var len = source_data.length;
			var select_items_len = select_items ? select_items.length : 0; // Fix for JS Exception if select_items is null.
			var res = [];

			for ( var i = 0; i < select_items_len; i++ ) {
				var select_value = select_items[i];
				for ( var j = 0; j < len; j++ ) {
					var source_item = source_data[j];

					//Could be string or number, use ==
					if ( select_value == source_item[key] ) {
						res.push( source_item );
						break;
					}

				}
			}

			return res;
		};

		this.setLabel = function() {
			var label_arr = Array();

			if ( allow_multiple_selection ) {
				if ( !select_items ) {
					this.setEmptyLabel();
					return;
				}

				var len = select_items.length;
				if ( len === 1 ) {

					var display_column_len = display_columns.length;
					for ( y = 0; y < display_column_len; y++ ) {

						if ( display_columns[y].hidden === true ) { //Hidden field for jGgrid, usually is id
							continue;
						}

						if ( layout_name === 'global_sort_columns' && select_items[0][display_columns[y].name] === undefined ) {
							var item = select_items[0];

							for ( var key in item ) {
								var c_value = key + ' | ' + item[key];
							}

							//When sort field has source data, use proper label shown on the dropdown.
							if ( source_data ) {
								for ( var i = 0; i < source_data.length; i++ ) {
									var column = source_data[i];

									if ( column.value === key ) {
										var c_value = column.label + ' | ' + item[key];
									}
								}
							}

							if ( c_value !== 0 && c_value == '' ) {
								c_value = '--';
							}
							label_arr.push( c_value );

							break;
						} else {
							var c_value = select_items[0][display_columns[y].name];
						}

						if ( c_value !== 0 && c_value == '' ) {
							c_value = '--';
						}

						if ( c_value === 0 || c_value ) {
							label_arr.push( c_value );
						}
					}

					if ( label_arr.length > 0 ) {
						label_span.text( label_arr.join( ' | ' ) );
					} else {
						this.setEmptyLabel();
					}
				} else {
					label_span.text( len + ' ' + $.i18n._( 'items selected' ) );
				}
			} else {
				// Error: Uncaught TypeError: Cannot read property 'length' of null in interface/html5/global/widgets/awesomebox/AComboBox.js?v=9.0.6-20151231-140748 line 902
				if ( !select_item || !display_columns ) {
					this.setEmptyLabel();
					return;
				}

				display_column_len = display_columns.length;
				for ( var y = 0; y < display_column_len; y++ ) {
					if ( display_columns[y].hidden === true ) { //Hidden field for jGgrid, usually is id
						continue;
					}

					c_value = select_item[display_columns[y].name];

					if ( c_value !== 0 && c_value == '' ) {
						c_value = '--';
					}

					if ( c_value === 0 || c_value ) {
						label_arr.push( c_value );
					}
				}

				if ( label_arr.length > 0 ) {
					label_span.text( label_arr.join( ' | ' ) );
				} else {
					this.setEmptyLabel();
				}
			}

			$this.find( '.a-combobox-label' ).width( 'auto' );
		};

		this.setEmptyLabel = function() {
			if ( set_any ) {
				label_span.text( Global.any_item );
			} else if ( set_empty || set_special_empty ) {
				if ( layout_name === 'global_tree_column' ) {
					label_span.text( Global.root_item );
				} else {
					label_span.text( Global.empty_item );
				}
			} else if ( set_default ) {
				label_span.text( Global.default_item );
			} else if ( set_open ) {
				label_span.text( Global.open_item );
			} else {
				if ( source_data && source_data.length > 0 ) {
					var first_item = source_data[0];

					if ( first_item.hasOwnProperty( 'label' ) ) {
						label_span.text( first_item.label );
					}
				}
			}
			if ( custom_first_label ) {
				label_span.text( custom_first_label );
			}

		};

		this.onColumnSettingSaveFromLayout = function( layout ) {
			var filter = {};
			filter.script = 'global_awesomebox_columns';
			filter.name = layout_name;
			filter.is_default = 'false';

			filter.data = layout.data;
			filter.data.type = 10;
			filter.data.layout_id = layout.id;

			if ( ALayoutCache.layout_dic[layout_name] ) {
				filter.id = ALayoutCache.layout_dic[layout_name].id;
			}

			user_generic_api.setUserGenericData( filter, {
				onResult: function( res ) {
					ALayoutCache.layout_dic[layout_name] = null;
					$this.initColumns();
					source_data = null; //Reload source data if column changed

				}
			} );
		};

		this.onColumnSettingSave = function( seletedColumns, rowsPerPageNumber, layout_id, filter_data ) {

			var filter = {};
			filter.script = 'global_awesomebox_columns';
			filter.name = layout_name;
			filter.is_default = 'false';
			filter.type = 1; //ALayoutType.customize

			if ( ALayoutCache.layout_dic[layout_name] ) {
				filter.id = ALayoutCache.layout_dic[layout_name].id;
			}

			var select_columns_in_JSON = [];
			var len = seletedColumns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_in_JSON = {
					label: seletedColumns[i].label,
					value: seletedColumns[i].value,
					orderValue: seletedColumns[i].orderValue
				};
				select_columns_in_JSON.push( column_in_JSON );
			}

			filter.data = {
				display_columns: select_columns_in_JSON,
				row_per_page: rowsPerPageNumber,
				layout_id: layout_id,
				filter_data: filter_data
			};

			//Error: TypeError: user_generic_api is null in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=9.0.0-20150822-090205 line 987
			if ( !user_generic_api ) {
				user_generic_api = TTAPI.APIUserGenericData;
			}
			user_generic_api.setUserGenericData( filter, {
				onResult: function( res ) {
					ALayoutCache.layout_dic[layout_name] = null;

					$this.initColumns();

					source_data = null; //Reload source data if column changed

					//Reload real data if columns have changed, so they display data for any new columns
					// We can't just pass in the selected_items verbatim here, we need to parse off the 'id' fields and pass that in as an array instead.
					if ( $.type( select_items ) === 'array' ) {
						select_items = select_items.map( function( obj ) {
							if ( obj.hasOwnProperty( 'id' ) ) {
								return obj['id'];
							}
						} );
					}

					//Don't try to load data from the API if there is no filter criteria, as everything is likely be returned and therefore will be selected.
					// This would manifest itself by expanding a dropdown without anything selected in it, then changing the columns.
					if ( select_items ) {
						$this.getRealData( select_items );
					}
				}
			} );

		};

		//Set columns, display columns will be used when open AwesomeBox. If no layout saved. Display columns are the default ones set in this.each
		this.initColumns = function() {

			user_generic_api = TTAPI.APIUserGenericData;

			//Error: TypeError: 'undefined' is not a function (evaluating 'user_generic_api.getUserGenericData') in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=8.0.0-20141117-095711 line 1044
			if ( !user_generic_api || !user_generic_api.getUserGenericData || typeof ( user_generic_api.getUserGenericData ) !== 'function' ) {
				return;
			}

			if ( Global.isSet( ALayoutCache.layout_dic[layout_name] ) ) {
				if ( ALayoutCache.layout_dic[layout_name].pending ) {
					TTPromise.add( 'AComboBox', layout_name );
					TTPromise.wait( 'AComboBox', layout_name, function() {
						initColumnsFromCache( layout_name );
					} );
				} else {
					initColumnsFromCache( layout_name );
				}
			} else {
				//Set cache to pending to stop multiple calls to the API for the same layout_name.
				ALayoutCache.layout_dic[layout_name] = { pending: true };
				var filter = {};
				filter.filter_data = { script: 'global_awesomebox_columns', name: layout_name };
				user_generic_api.getUserGenericData( filter, {
					onResult: function( res ) {

						var resData = res.getResult();
						//Set this here so Column setting dialog can get correct display columns before open Awesomebox
						if ( resData && resData.length > 0 ) {

							var data = resData[0].data;

							//if saved layout is saved view layout. get it
							if ( data.type === 10 ) {

								var columns_result_data = api.getOptions( column_option_key, { async: false } ).getResult();
								all_columns = Global.buildColumnArray( columns_result_data );

								var saved_layout_result = user_generic_api.getUserGenericData( {
									filter_data: {
										id: data.layout_id,
										deleted: false
									}
								}, { async: false } ).getResult();

								if ( saved_layout_result && saved_layout_result.length > 0 ) {
									var saved_layout = saved_layout_result.slice()[0];
									var new_data = saved_layout.data;
									new_data.display_columns = $this.buildDisplayColumns( new_data.display_columns );
									new_data.filter_data = Global.convertLayoutFilterToAPIFilter( saved_layout );
									resData[0].data.display_columns = new_data.display_columns;
									resData[0].data.filter_data = new_data.filter_data;

									data = resData[0].data;
								} else {
									data.filter_data = []; // if saved layou is deleted.
								}

							}

							ALayoutCache.layout_dic[layout_name] = resData[0];

							ALayoutCache.layout_dic[$this.getScriptName()] = layout_name; // bind current view name to layout name;

							if ( Global.isSet( data.type ) && data.type === 10 ) {

								display_columns = data.display_columns;
								if ( display_columns.length > 0 ) {
									if ( possible_display_columns ) {
										display_columns = filterBaseOnPossibleColumns( display_columns );
									}

									display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
										total_header_width = width;
									} );
								} else if ( list_view_default_columns ) {
									display_columns = list_view_default_columns;
									display_columns = filterBaseOnPossibleColumns( display_columns );
									display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
										total_header_width = width;
									} );
								}

								args_from_saved_layout = {};
								args_from_saved_layout.filter_data = data.filter_data;
								args_from_saved_layout.filter_sort = data.filter_sort;

							} else {

								display_columns = data.display_columns;

								//Happen when save no columns in column setting for navigation mode
								if ( display_columns.length > 0 ) {
									if ( possible_display_columns ) {
										display_columns = filterBaseOnPossibleColumns( display_columns );
									}

									display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
										total_header_width = width;
									} );
								} else if ( list_view_default_columns ) {
									display_columns = list_view_default_columns;
									display_columns = filterBaseOnPossibleColumns( display_columns );
									display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
										total_header_width = width;
									} );
								}

								row_per_page = data.row_per_page;
								args_from_saved_layout = null;
//							if ( !navigation_mode && !set_default_args_manually ) {
//								default_args = null;
//							}
							}
						} else {

							//If set possible columns, use it
							if ( list_view_default_columns ) {
								display_columns = list_view_default_columns;
								display_columns = filterBaseOnPossibleColumns( display_columns );
								display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
									total_header_width = width;
								} );
							}
							ALayoutCache.layout_dic[layout_name] = false;
							row_per_page = 0;

						}

						if ( init_data_immediately ) {
							$this.initSourceData();
						}
						TTPromise.resolve( 'AComboBox', layout_name );
					}
				} );

			}

		};

		var initColumnsFromCache = function( layout_name ) {
			//Set this here so Column setting dialog can get correct display columns before open Awesomebox
			if ( Global.isSet( ALayoutCache.layout_dic[layout_name].data ) ) {

				var data = ALayoutCache.layout_dic[layout_name].data;

				if ( Global.isSet( data.type ) && data.type === 10 ) {
					display_columns = data.display_columns;

					if ( display_columns.length > 0 ) {
						if ( possible_display_columns ) {
							display_columns = filterBaseOnPossibleColumns( display_columns );
						}

						display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
							total_header_width = width;
						} );
					} else if ( list_view_default_columns ) {
						display_columns = list_view_default_columns;
						display_columns = filterBaseOnPossibleColumns( display_columns );
						display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
							total_header_width = width;
						} );
					}

					args_from_saved_layout = {};
					args_from_saved_layout.filter_data = data.filter_data;
					args_from_saved_layout.filter_sort = data.filter_sort;

				} else {
					display_columns = data.display_columns;

					if ( display_columns.length > 0 ) {
						if ( possible_display_columns ) {
							display_columns = filterBaseOnPossibleColumns( display_columns );
						}

						display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
							total_header_width = width;
						} );
					} else if ( possible_display_columns ) {
						display_columns = possible_display_columns;
					}
					row_per_page = data.row_per_page;
					if ( !navigation_mode && !set_default_args_manually ) {
						default_args = null;
					}
				}

			} else {
				if ( list_view_default_columns ) {
					display_columns = list_view_default_columns;
					display_columns = filterBaseOnPossibleColumns( display_columns );
					display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name, function( width ) {
						total_header_width = width;
					} );
				}
				row_per_page = 0;
			}
			if ( init_data_immediately ) {
				$this.initSourceData();
			}
		}

		var filterBaseOnPossibleColumns = function( display_columns ) {

			//Error: Unable to get property 'length' of undefined or null reference in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=8.0.0-20141230-125406 line 1169
			if ( !possible_display_columns ) {
				return display_columns;
			}

			var len = possible_display_columns.length;

			var result = [];
			for ( var j = 0; j < display_columns.length; j++ ) {
				var dis_column = display_columns[j];
				var found = false;
				for ( var i = 0; i < len; i++ ) {
					var column = possible_display_columns[i];
					if ( column.value === dis_column.value ) {
						found = true;
						break;
					}

				}

				if ( found ) {
					result.push( dis_column );
				}

			}

			if ( result.length === 0 ) {
				result = possible_display_columns;
			}

			return result;

		};

		this.getId = function() {
			return id;
		};

		//update source data to new saved item, happens in timesheet view, edit employee
		this.updateSelectItem = function( new_item ) {
			select_item = new_item;
			if ( source_data ) {

				for ( var i = 0; i < source_data.length; i++ ) {
					var content = source_data[i];
					if ( content && new_item && content.id === new_item.id ) {
						source_data[i] = new_item;

						return;
					}
				}

			}

		};

		this.getADropDown = function() {
			return a_dropdown_div;
		};

		this.onClose = function( e, target ) {
			if ( a_dropdown == undefined ) {
				Debug.Text( 'ERROR: Unable to close AComboBox check to make sure it wasn\'t instantiated twice.', 'ACombobox.js', 'ACombobox', 'onClose', 10 );
			}

			//In the case where a user double clicks a drop-down box to expand it, that can cause it to open the close again immediately, and show a "-- NONE --" even though something was previously selected.
			//  Therefore if the 'init' promise is still pending because the data is still loading, simply don't close it yet.
			var pending_promise = TTPromise.isPendingPromises( 'AComboBox', 'init' );
			if ( pending_promise == false ) {
				if ( allow_multiple_selection ) {
					//Re load source_data if select items
					var select_items = a_dropdown.getSelectItems();
					$this.setValue( select_items );
				} else {
					var select_item = a_dropdown.getSelectItem();
					if ( select_item ) { // #2593 - null is not an object (evaluating 'select_item._id_')
						if ( !tree_mode ) {
							$this.setValue( select_item );
						} else if ( select_item._id_ ) {
							$this.setValue( select_item._id_ );
						}
					}
				}
			} else {
				Debug.Text( 'WARNING: AComboBox::Init promise still pending, cant call setValue()...', 'ACombobox.js', 'ACombobox', 'onClose', 10 );
			}

			a_dropdown_div.remove();
			is_mouse_over = false; //When close from esc, this maybe true
			LocalCacheData.openAwesomeBox = null;
			if ( a_dropdown.isChanged() || layout_name === 'global_sort_columns' ) {
				if ( check_box ) {
					$this.setCheckBox( true );
				}
				$this.trigger( 'formItemChange', [$this] );
			}
			$this.trigger( 'onClose', [$this] );
			dontOpen = true;
			dontOpenTimer = setTimeout( function() {
				dontOpen = false;
			}, 200 );
			$this.find( '.focus-input' ).focus();
		};

		//set next or last item when key down, call from main.js
		this.selectNextItem = function( e ) {

			var select_index = this.getSelectIndex();
			var next_index;
			var target_grid;
			if ( e.keyCode === 40 ) { //Down
				e.preventDefault();
				if ( !allow_multiple_selection ) {
					if ( select_index === 0 ) {
						next_index = 1;
					} else {
						next_index = select_index + 1;
					}
					var next_select_item = this.getItemByIndex( next_index );

					if ( !next_select_item ) {
						next_index = next_index - 1;
						next_select_item = this.getItemByIndex( next_index );
					}
					select_item = next_select_item;
					a_dropdown.setSelectItem( next_select_item );
					a_dropdown.setIsChanged( true );
				}
			} else if ( e.keyCode === 38 ) { //Up
				e.preventDefault();
				if ( !allow_multiple_selection ) {
					if ( select_index === 0 ) {
						next_index = 0;
					} else {
						next_index = select_index - 1;
					}

					next_select_item = this.getItemByIndex( next_index );
					select_item = next_select_item;
					a_dropdown.setSelectItem( next_select_item );
					a_dropdown.setIsChanged( true );
				}
			} else if ( e.keyCode === 39 ) { //right
				if ( allow_multiple_selection && !$( e.target ).hasClass( 'search-input' ) ) {
					e.preventDefault();
					a_dropdown.onUnSelectGridDoubleClick();
					a_dropdown.setIsChanged( true );
				}
			} else if ( e.keyCode === 37 ) { //left
				if ( allow_multiple_selection && !$( e.target ).hasClass( 'search-input' ) ) {
					e.preventDefault();
					a_dropdown.onSelectGridDoubleClick();
					a_dropdown.setIsChanged( true );
				}
			} else {
				if ( quick_search_timer ) {
					clearTimeout( quick_search_timer );
				}
				var focus_target = $( ':focus' );
				if ( focus_target.length > 0 && $( focus_target[0] ).hasClass( 'search-input' ) ) {
					return;
				}
				quick_search_timer = setTimeout( function() {
					quick_search_typed_keys = '';
				}, 750 );
				e.preventDefault();

				quick_search_typed_keys = quick_search_typed_keys + String.fromCharCode( e.which ).toLowerCase();
				Debug.Text( 'Quick search typed keys: ' + quick_search_typed_keys, 'AComboBox.js', 'AComboBox', 'selectNextItem', 10 );
				var trimmed_quick_search_typed_keys = quick_search_typed_keys.trim();

				if ( allow_multiple_selection || tree_mode ) {
					if ( trimmed_quick_search_typed_keys ) {
						target_grid = a_dropdown.getFocusInSeletGrid() ? a_dropdown.getSelectGrid() : a_dropdown.getUnSelectGrid();

						var search_index = quick_search_dic[trimmed_quick_search_typed_keys] ? quick_search_dic[trimmed_quick_search_typed_keys] : 0;
						var tds = $( target_grid.grid.find( 'tr' ).find( 'td:eq(1)' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( trimmed_quick_search_typed_keys ) == 0;
						} ) );
						var td;
						if ( search_index > 0 && search_index < tds.length ) {

						} else {
							search_index = 0;
						}

						td = $( tds[search_index] );
						a_dropdown.unSelectAll( target_grid.grid, true );
						//next_index = td.parent().index() - 1;

						var next_select_item = false;
						var grid_data = target_grid.getData();
						for ( var z = 0; z < grid_data.length; z++ ) {
							if ( grid_data[z].id == td.parents( 'tr' ).attr( 'id' ) ) {
								next_select_item = grid_data[z];
								break;
							}
						}

						select_item = next_select_item;

						a_dropdown.setSelectItem( next_select_item, target_grid );
						a_dropdown.setIsChanged( true );
						quick_search_dic = {};
						quick_search_dic[trimmed_quick_search_typed_keys] = search_index + 1;
					}
				} else {
					if ( trimmed_quick_search_typed_keys ) {
						search_index = quick_search_dic[trimmed_quick_search_typed_keys] ? quick_search_dic[trimmed_quick_search_typed_keys] : 0;
						tds = $( a_dropdown.getUnSelectGrid().grid.find( 'tr' ).find( 'td:first' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( trimmed_quick_search_typed_keys ) == 0;
						} ) );
						if ( search_index > 0 && search_index < tds.length ) {

						} else {
							search_index = 0;
						}
						td = $( tds[search_index] );
						next_index = td.parent().index() - 1;
						next_select_item = this.getItemByIndex( next_index );
						select_item = next_select_item;
						a_dropdown.setSelectItem( next_select_item );
						quick_search_dic = {};
						quick_search_dic[trimmed_quick_search_typed_keys] = search_index + 1;
					}
				}

			}

		};

		this.gridScrollTop = function() {

			if ( !a_dropdown ) {
				return;
			}

			a_dropdown.gridScrollTop();
		};

		this.gridScrollDown = function() {

			if ( !a_dropdown ) {
				return;
			}

			a_dropdown.gridScrollDown();
		};

		this.selectAll = function() {
			if ( !a_dropdown || tree_mode || !allow_multiple_selection ) {
				return;
			}

			a_dropdown.selectAll();

		};

		this.getIsMouseOver = function() {
			return is_mouse_over;
		};

		this.onShowAll = function( isShowAll ) {
			show_all = isShowAll;

			var args = this.buildUnSelectGridFilter();
			args = this.addExcludeIDsToFilter( args, a_dropdown.getSelectItems() );

			api['get' + custom_key_name]( args, {
				onResult: function( result ) {

					var result_data = result.getResult();

					if ( !result_data ) {
						result_data = [];
					}

					pager_data = result.getPagerData();
					source_data = result_data;

					if ( Global.isSet( api ) ) {
						source_data = Global.formatGridData( source_data, api.key_name );
					}

					a_dropdown.setUnselectedGridData( source_data );
					//a_dropdown.setSelectGridData( a_dropdown.getSelectItems() );
					a_dropdown.setPagerData( pager_data );
				}
			} );
		};

		this.buildUnSelectGridFilter = function( exclude_ids = null ) {
			var args = {};
			args.filter_data = {};
			args.filter_columns = $this.getColumnFilter();
			args.filter_items_per_page = row_per_page;

			if ( a_dropdown ) {
				//use clone so the view search condition not be set when set default args
				args.filter_data = Global.clone( a_dropdown.getUnSelectGridMap() );
				args.filter_sort = Global.clone( a_dropdown.getUnSelectGridSortMap() );
			}

			if ( args_from_saved_layout ) {
				if ( Global.isSet( args_from_saved_layout.filter_data ) ) {
					for ( var key in args_from_saved_layout.filter_data ) {
						if ( !args.filter_data.hasOwnProperty( key ) ) {
							args.filter_data[key] = args_from_saved_layout.filter_data[key];
						}

					}
				}

				if ( args_from_saved_layout && Global.isSet( args_from_saved_layout.permission_section ) ) {
					args.permission_section = args_from_saved_layout.permission_section;
				}

				//Do not override sort condition
				if ( Global.isSet( args_from_saved_layout.filter_sort ) && !args.filter_sort ) {

					args.filter_sort = args_from_saved_layout.filter_sort;
				}
			}

			//default_args is set when navigation mode usually. To keep search filter same as list view grid
			//. In BaseController, setEditViewData function
			if ( default_args ) {

				if ( Global.isSet( default_args.filter_data ) ) {
					for ( var key in default_args.filter_data ) {
						if ( !args.filter_data.hasOwnProperty( key ) ) {
							args.filter_data[key] = default_args.filter_data[key];
						}

					}
				}

				if ( default_args && Global.isSet( default_args.permission_section ) ) {
					args.permission_section = default_args.permission_section;
				}

				//Do not override sort condition
				if ( Global.isSet( default_args.filter_sort ) && !args.filter_sort ) {

					args.filter_sort = default_args.filter_sort;
				}

			}

			//If it has additional search condition from outside viewcontroller
			if ( this.customSearchFilter ) {
				args = this.customSearchFilter( args );
			}

			if ( show_all ) {
				args.second_parameter = true;
			}

			// When using ACombo, at this point a_dropdown.getSelectItems() might be empty, but $this.getValue()/select_items has the selected IDs instead. They are in a different format though.
			//   This is also required in cases where an employee has 50 items, 48 are already selected, and pagination is set to 5 items per row.
			//     Then on the unselected side click to sort by "Last Name" a couple times. These exclude_ids must be passed to the API so no matter what all the unselected rows are always returned, just in the proper sort order.
			if ( exclude_ids ) {
				args = this.addExcludeIDsToFilter( args, exclude_ids );
			}

			return args;
		};

		this.addExcludeIDsToFilter = function( args, exclude_ids ) {
			if ( a_dropdown && args && exclude_ids ) { //#2353 - js exception when a_dropdown is not defined yet
				var selected_ids = exclude_ids;
				if ( selected_ids && Global.isArray( selected_ids ) && selected_ids.length > 0 && selected_ids[0] != TTUUID.zero_id ) {
					args.filter_data.exclude_id = [];
					for ( var x in selected_ids ) {
						// Issue #2899 - Selected items can either be an array of IDs or an object. Need to account for either situation.
						if ( selected_ids[x] && selected_ids[x].id ) {
							args.filter_data.exclude_id.push( selected_ids[x].id );
						} else {
							args.filter_data.exclude_id.push( selected_ids[x] );
						}
					}
				}
			}

			return args;
		}

		this.buildSelectGridFilter = function() {
			var args = {};
			args.filter_columns = $this.getColumnFilter();
			args.filter_items_per_page = row_per_page;
			args.filter_data = a_dropdown.getSelectGridMap();
			args.filter_sort = a_dropdown.getSelectGridSortMap();
			args.second_parameter = true; //Always set true because we want alwasy set all data out in select grid

			return args;
		};

		this.searchIsEmpty = function( a_dropdown ) {
			var inputs = a_dropdown.find( 'input.search-input' );
			for ( var i = 0; i < inputs.length; i++ ) {
				if ( $( inputs[i] ).val() != $.i18n._( 'click to search' ) ) {
					return false;
				}
			}
			return true;
		};

		this.onADropDownSearch = function( targetName, page_action, default_select_item, callBack, trigger_form_item_change = true ) {
			var this_val = this.getValue();
			if ( targetName == 'select_grid' && this_val.length == 0 || ( targetName == 'select_grid' && this_val.length == 1 && this_val[0] == TTUUID.not_exist_id ) ) {
				//#2353 - prevent bug where sorted empty select grid selects everything
				return;
			}

			var args = {};
			args.filter_columns = $this.getColumnFilter();
			args.filter_items_per_page = row_per_page;
			if ( targetName === 'unselect_grid' ) {
				//When using pagination and first opening the dropdown box, we need to exclude the selected items from the API call
				// so the first page will always be populated with a full page of records.
				args = $this.buildUnSelectGridFilter( ( ( a_dropdown ) ? a_dropdown.getSelectItems() : null ) ); //Handle JS exception: Error: Cannot read property 'getSelectItems' of null

				//Error: Unable to get property 'current_page' of undefined or null reference in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=7.4.6-20141027-070003 line 1489
				if ( a_dropdown && a_dropdown.getPagerData() ) {
					if ( LocalCacheData.paging_type === 0 ) {
						if ( page_action === 'next' ) {
							args.filter_page = a_dropdown.getPagerData()?.next_page;
						} else {
							args.filter_page = 1;
						}
					} else {
						switch ( page_action ) {
							case 'next':
								args.filter_page = a_dropdown.getPagerData()?.next_page;
								break;
							case 'last':
								args.filter_page = a_dropdown.getPagerData()?.previous_page;
								break;
							case 'start':
								args.filter_page = 1;
								break;
							case 'end':
								args.filter_page = a_dropdown.getPagerData()?.last_page_number;
								break;
							default:
								args.filter_page = a_dropdown.getPagerData()?.current_page;
								break;
						}
					}
				}

				if ( parseInt( page_action ) >= 1 ) {
					args.filter_page = page_action;
				}

				api['get' + custom_key_name]( args, {
					onResult: function( result ) {

						var focused_element = $( ':focus' );

						var result_data = result.getResult();

						if ( !Global.isArray( result_data ) ) {
							result_data = [];
						}

						//set this outside, to add more data to source_data
						if ( addition_source_function ) {
							result_data = addition_source_function( $this, result_data );
						}

						if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
							var current_data = a_dropdown.getUnSelectGridData();
							result_data = current_data.concat( result_data );

						}

						if ( navigation_mode ) {
							source_data = result_data;
							pager_data = result.getPagerData();
						} else {
							unselect_grid_search_result = result_data;
							current_open_page = result.getPagerData().current_page;
						}

						if ( $.type( result_data ) != 'array' ) {
							result_data = [];
						}

						if ( !allow_multiple_selection ) {
							$this.createFirstItem( result_data );
						}

						result_data = Global.formatGridData( result_data, api.key_name );

						if ( extendDataProcessWhenSearch ) {
							result_data = extendDataProcessWhenSearch( result_data );
						}
						a_dropdown && a_dropdown.setUnselectedGridData( result_data );

						if ( allow_multiple_selection ) {
							a_dropdown && a_dropdown.setSelectGridData( a_dropdown.getSelectItems(), true );
						} else {
							a_dropdown && a_dropdown.setSelectItem( a_dropdown.getSelectItem() );
							$this.highlightSelectItem();
							if ( default_select_item == 'first' ) {
								$this.setValue( result_data[0] );
								if ( trigger_form_item_change == true ) {
									$this.trigger( 'formItemChange', [$this] );
								}
								if ( callBack ) {
									callBack( result_data[0] );
								}
							} else if ( default_select_item == 'last' ) {
								$this.setValue( result_data[result_data.length - 1] );
								if ( trigger_form_item_change == true ) {
									$this.trigger( 'formItemChange', [$this] );
								}
								if ( callBack ) {
									callBack( result_data[result_data.length - 1] );
								}
							} else if ( callBack ) {
								callBack();
							}
						}

						a_dropdown && a_dropdown.setPagerData( result.getPagerData() );

						if ( result_data.length < 1 && a_dropdown.getSelectItems().length < 1 ) {
							a_dropdown && a_dropdown.showNoResultCover( 'unselect_grid' );
						} else {
							a_dropdown && a_dropdown.removeNoResultCover( 'unselect_grid' );
						}

						a_dropdown && a_dropdown.getUnSelectGrid().grid.show();

						if ( focused_element.length ) {
							focused_element.focus();
						}

					}
				} );

			} else {
				args = this.buildSelectGridFilter();
				if ( args.filter_data.id && args.filter_data.id !== false ) { //prevent returning all available rows when nothing is selected
					api['get' + custom_key_name]( args, {
						onResult: function( result ) {
							var result_data = result.getResult();
							var focused_element = $( ':focus' );

							if ( $.type( result_data ) != 'array' ) {
								result_data = [];
							}

							result_data = Global.formatGridData( result_data, api.key_name );

							a_dropdown.setSelectGridSearchResult( result_data ); //set as search result

							if ( result_data.length < 1 ) {
								a_dropdown.getSelectGrid().grid.clearGridData();
								a_dropdown.showNoResultCover( 'select_grid' );
							} else {
								a_dropdown.removeNoResultCover( 'select_grid' );
							}

							a_dropdown.setSelectGridDragAble();

							a_dropdown.getSelectGrid().grid.show();

							if ( focused_element.length > 0 ) {
								focused_element.focus();
							}

						}
					} );
				}
			}

		};

		this.buildDisplayColumns = function( api_display_columns ) {
			var len = all_columns.length;
			var len1 = api_display_columns.length;
			var display_columns = [];

			for ( var j = 0; j < len1; j++ ) {
				for ( var i = 0; i < len; i++ ) {
					if ( api_display_columns[j] === all_columns[i].value ) {
						display_columns.push( all_columns[i] );
					}
				}
			}
			return display_columns;
		};

		this.buildDisplayColumnsForEditor = function() {
			var len = all_columns.length;
			var len1 = display_columns.length;
			var result_display_columns = [];
			for ( var j = 0; j < len1; j++ ) {
				for ( var i = 0; i < len; i++ ) {
					if ( !display_columns[j] ) {
						continue;
					}
					if ( Global.isSet( display_columns[j].name ) ) { //jQgrid column format
						var name = display_columns[j].name;
					} else if ( Global.isSet( display_columns[j].value ) ) {  //ViewColumn format,	label and value
						name = display_columns[j].value;
					}
					if ( name === all_columns[i].value ) {
						result_display_columns.push( all_columns[i] );
					}
				}
			}

			return result_display_columns;

		};

		this.getLocalSelectItem = function( val ) {

			if ( added_items ) {
				for ( var i = 0; i < added_items.length; i++ ) {
					if ( val == added_items[i].value ) { //sometime the value is not number
						var item = {};

						item[key] = val;

						$.each( display_columns, function( index, content ) {

							item[content.name] = added_items[i].label;

							return false;
						} );

						return item;
					}
				}

				return this.getFirstItem();
			} else {
				return this.getFirstItem();
			}
		};

		this.getFirstItem = function() {
			var first_item = {};

			first_item[key] = TTUUID.zero_id;

			if ( set_any || set_all ) {
				first_item[key] = TTUUID.not_exist_id;
			} else {
				first_item[key] = TTUUID.zero_id;
			}

			$.each( display_columns, function( index, content ) {

				if ( key !== 'id' ) {
					//first_item.id = 999; //records id start from 10000
					first_item.id = TTUUID.not_exist_id;
				}
				if ( set_all ) {
					first_item[content.name] = Global.all_item;
				} else if ( set_any ) {
					first_item[content.name] = Global.any_item;
				} else if ( set_empty || set_special_empty ) {

					first_item[content.name] = Global.empty_item;

				} else if ( set_open ) {
					first_item[content.name] = Global.open_item;
				} else if ( set_default ) {
					first_item[content.name] = Global.default_item;
				}

				if ( custom_first_label ) {
					first_item[content.name] = custom_first_label;
				}

				return false;
			} );

			return first_item;
		};

		this.createFirstItem = function( target_data ) {

			var no_first_item = false;
			if ( !target_data ) {
				target_data = source_data;
			}

			if ( target_data.hasOwnProperty( 0 ) ) {
				if ( set_any || set_all ) {
					if ( target_data[0][key] === TTUUID.not_exist_id ) {
						return;
					}

				} else {
					if ( target_data[0][key] === TTUUID.zero_id ) {
						return;
					}
				}

			}

			var first_item = {};

			first_item[key] = TTUUID.zero_id;

			if ( set_any || set_all || set_special_empty ) {
				first_item[key] = TTUUID.not_exist_id;
			} else {
				first_item[key] = TTUUID.zero_id;
			}

			$.each( display_columns, function( index, content ) {

				if ( key !== 'id' ) {
					//first_item.id = 999; //records id start from 10000
					first_item.id = TTUUID.not_exist_id; //records id start from 10000
				}
				if ( set_all ) {
					first_item[content.name] = Global.all_item;
				} else if ( set_any ) {
					first_item[content.name] = Global.any_item;
				} else if ( set_empty || set_special_empty ) {
					first_item[content.name] = Global.empty_item;
				} else if ( set_open ) {
					first_item[content.name] = Global.open_item;
				} else if ( set_default ) {
					first_item[content.name] = Global.default_item;
				} else {
					no_first_item = true;
				}

				if ( custom_first_label ) {
					first_item[content.name] = custom_first_label;
				}

				return false;
			} );

			if ( !no_first_item ) {
				//Issue #3338 - Custom field meta data multi_select_items was getting the "-- None --" option silently added and modifying
				//our original option array. This is because the data passed by setSourceData() is a reference to the original data.
				//This does not cause problems anywhere else than saving a custom field as custom fields is the only area
				//where we send the option array data back to the server. Because of that we now clone the custom field multi_select_items
				//before passing it to setSourceData() so we don't modify the original data. However, we do not do this for
				//all other selects, as other areas of the code may rely on this pass by reference behavior.
				target_data.unshift( first_item );
			}

		};

		this.checkIfLayoutChanged = function( newDisplayColumns ) {

			if ( !display_columns ) {
				return true;
			}

			if ( !newDisplayColumns ) {
				return true;
			}

			if ( display_columns.length !== newDisplayColumns.length ) {
				return true;
			}

			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var display_column = display_columns[i];
				var found = false;
				for ( var j = 0; j < len; j++ ) {
					var new_display_column = newDisplayColumns[j];
					if ( new_display_column.name === display_column.name ) {
						found = true;
						break;
					}
				}

				if ( !found ) {
					return true;
				}
			}

			return false;

		};

		this.setDefaultArgs = function( val ) {
			$this.default_args = val;
			set_default_args_manually = true;
			default_args = val;

		};

		this.setDisPlayColumns = function( val ) {

			display_columns = Global.convertColumnsTojGridFormat( val, layout_name );
		};

		//Only these columns can be shown no matter
		this.setPossibleDisplayColumns = function( val, default_columns ) {
			possible_display_columns = val;

			if ( default_columns ) {
				list_view_default_columns = default_columns;
			} else {
				list_view_default_columns = val;
			}

			if ( layout_name !== 'global_option_column' && //Simple options
				layout_name !== 'global_tree_column' && //Tree Mode
				layout_name !== 'global_sort_columns' &&
				layout_name !== 'global_timesheet' &&
				layout_name !== 'global_absence' ) {
				this.initColumns();
			}

		};

		this.setRowPerPage = function( val ) {
			row_per_page = val;
		};

		this.setPagerData = function( val ) {
			pager_data = val;
		};

		this.shouldInitColumns = function() {
			if ( layout_name === 'global_option_column' || //Simple options
				layout_name === 'global_tree_column' || //Tree Mode
				layout_name === 'global_sort_columns' ||
				layout_name === 'global_timesheet' ||
				layout_name === 'global_absence' ||
				layout_name === 'global_simple_name' ||
				layout_name === 'global_simple_name' + '_navigation' ) {

				return false;
			}

			return true;
		};

		this.createItem = function( val, label ) {
			var item = {};

			item[key] = val;
			$.each( display_columns, function( index, content ) {

				if ( key !== 'id' ) {
					item.id = 20000; //records id start from 10000
				}
				item[content.name] = label;
				return false;

			} );

			return item;

		};

		this.initSourceData = function() {
			var args = $this.buildUnSelectGridFilter();
			api['get' + custom_key_name]( args, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( !Global.isArray( result_data ) ) {
						result_data = [];
					}
					source_data = result_data;
					pager_data = result.getPagerData();

					$this.trigger( 'initSourceComplete', [$this] );

				}
			} );

			//only do this once
			init_data_immediately = false;
		};

		this.addHideIdColumn = function( display_columns ) {
			var id_column = { name: 'id', index: 'id', label: '', width: 0, hidden: true };

			display_columns.push( id_column );

			return display_columns;
		};

		var buildSortBySelectColumns = function( array ) {
			var sort_by_array = array;
			var sort_by_select_columns = [];
			var sort_by_unselect_columns = source_data;

			if ( sort_by_array ) {
				$.each( sort_by_array, function( index, content ) {
					for ( var key in content ) {
						if ( key === 'label' || key === 'value' || key === 'fullValue' ) {
							sort_by_select_columns = sort_by_array;
							return false;
						}
						$.each( sort_by_unselect_columns, function( index1, content1 ) {
							if ( content1.value === key ) {
								content1.sort = content[key];
								sort_by_select_columns.push( content1 );
								return false;
							}
						} );
					}
				} );
			}

			return sort_by_select_columns;

		};

		//For multiple items like .xxx could contains a few widgets.
		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			label_span = $( this ).find( '.a-combobox-label' );
			var focus_input = $( this ).find( '.focus-input' );

			if ( o.always_include_columns ) {
				always_include_columns = o.always_include_columns;
			}

			if ( o.extendDataProcessWhenSearch ) {
				extendDataProcessWhenSearch = o.extendDataProcessWhenSearch;
			}

			if ( o.always_search_full_columns ) {
				always_search_full_columns = o.always_search_full_columns;
			}

			//source_data id that add from outsite, used to set value before open awesomebox
			if ( o.added_items ) {
				added_items = o.added_items;
			}

			if ( o.init_data_immediately ) {
				init_data_immediately = o.init_data_immediately;
			}

			//first item label , like -- No Meal -- in Policy -> Schedule Policies
			if ( o.custom_first_label ) {
				custom_first_label = o.custom_first_label;
			}

			if ( o.script_name ) {
				script_name = o.script_name;
			}

			if ( o.get_real_data_on_multi ) {
				get_real_data_on_multi = o.get_real_data_on_multi;
			}

			if ( o.setRealValueCallBack ) {
				setRealValueCallBack = o.setRealValueCallBack;
			}

			if ( o.navigation_mode ) {
				navigation_mode = o.navigation_mode;
				$( this ).children().eq( 1 ).css( 'max-width', 132 );
				$( this ).children().eq( 1 ).css( 'min-width', 132 );
			}

			if ( o.search_panel_model ) {
				$( this ).children().eq( 1 ).css( 'max-width', 132 );
				$( this ).children().eq( 1 ).css( 'min-width', 132 );
			}

			if ( o.width ) {
				$( this ).children().eq( 1 ).css( 'max-width', o.width );
				if ( o.is_static_width ) {
					$( this ).children().eq( 1 ).css( 'min-width', o.width );
				}
			} else {
				$( this ).children().eq( 1 ).css( 'min-width', 132 );
			}

			if ( o.api_class ) {
				api_class = o.api_class;
				api = o.api_class;
			}

			if ( o.custom_key_name ) {
				custom_key_name = o.custom_key_name;
			} else {

				if ( api ) {
					custom_key_name = api.key_name;
				}
			}

			if ( o.allow_multiple_selection ) {
				allow_multiple_selection = o.allow_multiple_selection;
			}

			if ( o.set_any ) {
				set_any = o.set_any;
			}

			if ( o.set_empty ) {
				set_empty = o.set_empty;
			}

			if ( o.set_special_empty ) {
				set_special_empty = o.set_special_empty;
			}

			if ( o.set_open ) {
				set_open = o.set_open;
			}

			if ( o.set_default ) {
				set_default = o.set_default;
			}

			if ( o.set_all ) {
				set_all = o.set_all;
			}

			if ( o.addition_source_function ) {
				addition_source_function = o.addition_source_function;
			}

			if ( o.column_option_key ) {
				column_option_key = o.column_option_key;
			}

			field = o.field;

			if ( o.validation_field ) {
				validation_field = o.validation_field;
			}

			if ( o.layout_name ) {
				if ( navigation_mode ) {
					layout_name = o.layout_name + '_navigation';
				} else {
					layout_name = o.layout_name;
				}

			}

			if ( o.hasOwnProperty( 'show_search_inputs' ) ) {
				show_search_inputs = o.show_search_inputs;
			}

			if ( o.tree_mode ) {
				tree_mode = o.tree_mode;
			}

			if ( o.on_tree_grid_row_select ) {
				on_tree_grid_row_select = o.on_tree_grid_row_select;
			}

			//Always set this true;
			allow_drag_to_order = true;

			if ( o.key ) {
				key = o.key;
			}

			if ( o.args ) {
				$this.args = o.args;
			}

			if ( o.customSearchFilter ) {
				$this.customSearchFilter = o.customSearchFilter;
			}

			//Set default args use when init source;
			if ( o.default_args ) {
				$this.setDefaultArgs( o.default_args );
			}

			var $$this = this;
			focus_input.unbind( 'keydown' ).bind( 'keydown', function( e ) {
				//Stop click event to top, prevent the body click event
				if ( e.keyCode === 13 || e.keyCode === 32 ) {
					e.stopPropagation();
					e.preventDefault();

					TTPromise.add( 'AComboBox', 'init' );
					openADropDown();
				}
			} );

			focus_input.bind( 'focusin', function() {
				$( $$this ).addClass( 'focus' );
			} );

			focus_input.bind( 'focusout', function() {
				$( $$this ).removeClass( 'focus' );
			} );

			$( this ).click( function( e ) {
				e.stopPropagation();
				if ( !enabled ) {
					if ( LocalCacheData.current_open_sub_controller &&
						LocalCacheData.current_open_sub_controller.edit_view &&
						LocalCacheData.current_open_sub_controller.is_viewing ) {
						error_string = LocalCacheData.current_open_sub_controller.getViewModeErrorMessage();
						$this.showErrorTip( 10 );
					} else if ( LocalCacheData.current_open_primary_controller &&
						LocalCacheData.current_open_primary_controller.edit_view &&
						LocalCacheData.current_open_primary_controller.is_viewing ) {
						error_string = LocalCacheData.current_open_primary_controller.getViewModeErrorMessage();
						$this.showErrorTip( 10 );
					}

				} else {
					if ( dontOpen === true ) {
						return;
					}
					TTPromise.add( 'AComboBox', 'init' );
					openADropDown();
				}
			} );

			$( this ).mouseover( function() {
				if ( enabled ) {
					if ( error_string && error_string.length > 0 ) {
						$this.showErrorTip( 1000 );
					}
				}
			} );

			$( this ).mouseout( function() {
				$this.hideErrorTip();
			} );

			if ( !o.width ) {
				$( this ).css( 'min-width', '169px' );
			} else {
				$( this ).css( 'min-width', ( o.width + 33 ) + 'px' );
			}

			if ( !$this.shouldInitColumns() ) { //Sort Selector in search panel
				do_not_get_real_data = true; // For Simple OPTIONS mode

			}

			display_columns = ALayoutCache.getDefaultColumn( layout_name ); //Get Default columns base on different layout name
			display_columns = Global.convertColumnsTojGridFormat( display_columns, layout_name ); //Convert to jQgrid format

			if ( $this.shouldInitColumns() && !navigation_mode ) { //Sort Selector in search panel

				//init columnd when set possible columns for navigation mode
				$this.initColumns();
			}

			if ( o.id ) {
				$( this ).attr( 'id', o.id + '_AComboBox' );
				id = o.id;
			} else {
				$( this ).attr( 'id', field + '_AComboBox' );
				id = o.field;
			}

			$this.setEmptyLabel();

			//Open ADropDown
			function setADropDownSelectValues( select_items ) {
				if ( !( set_any && select_items == TTUUID.not_exist_id ) ) {
					a_dropdown.setSelectGridData( select_items );
				}
			}

			function openADropDown() {
				if ( !enabled ) {
					return;
				}

				if ( LocalCacheData.openAwesomeBox ) {
					if ( LocalCacheData.openAwesomeBox.getId() === id ) {
						LocalCacheData.openAwesomeBox.onClose();
						return;
					} else {
						LocalCacheData.openAwesomeBox.onClose();
					}
				}

				LocalCacheData.openAwesomeBox = $this;

				//Create and open ADropDown
				a_dropdown = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

				var display_show_all = false;
				if ( $this.shouldInitColumns() && !navigation_mode ) {
					display_show_all = true;
				}

				if ( navigation_mode && default_args && default_args.filter_sort && !cached_sort_filter ) {
					cached_sort_filter = default_args.filter_sort;
				}

				//Create ADropDown
				a_dropdown = a_dropdown.ADropDown( {
					display_show_all: display_show_all,
					allow_drag_to_order: allow_drag_to_order,
					allow_multiple_selection: allow_multiple_selection,
					show_all: show_all,
					key: key,
					id: id,
					comboBox: $this,
					show_search_inputs: show_search_inputs,
					search_input_filter: cached_search_inputs_filter,
					select_grid_search_input_filter: cached_select_grid_search_inputs_filter,
					default_sort_filter: cached_sort_filter,
					default_select_grid_sort_filter: cached_selected_grid_sort_filter,
					tree_mode: tree_mode,
					column_option_key: column_option_key,
					api: api,
					display_column_settings: $this.shouldInitColumns(),

					on_tree_grid_row_select: on_tree_grid_row_select
				} );

				a_dropdown_div = $( '<div id=\'' + id + 'a_dropdown_div\' class=\'a-dropdown-div\'></div>' );

				a_dropdown_div.append( a_dropdown );

				a_dropdown_div.mouseenter( function() {
					is_mouse_over = true;
				} );

				a_dropdown_div.mouseleave( function() {
					is_mouse_over = false;
				} );

				$( 'body' ).append( a_dropdown_div );

				TTPromise.wait( 'AComboBox', 'init', function() {
					a_dropdown.setGridColumnsWidths();

					//Make sure we don't run this API call if the only selected value is a zero/not exists UUID,
					// otherwise it causes companies with thousands of employees to return *all* employees.
					if ( allow_multiple_selection == true && ( $this.getValue().length > 1 || ( $this.getValue().length == 1 && $this.getValue()[0] && ( $this.getValue()[0] != TTUUID.zero_id && $this.getValue()[0] != TTUUID.not_exist_id ) ) ) && api ) {
						var args = {};
						args.filter_data = { id: $this.getValue() };
						args.filter_columns = $this.getColumnFilter();
						args.filter_items_per_page = 10000;

						api['get' + custom_key_name]( args, {
							onResult: function( result ) {
								doNext( result );
							}
						} );
					} else {
						doNext();
					}

					function doNext( result ) {
						if ( result ) {
							a_dropdown.setRealSelectItems( result.getResult(), $this.getValue() );
						}
						a_dropdown_div.css( 'opacity', '1' );

						//#2353 - remove the right side border from jqgrid.
						var tables = $this.find( '.ui-jqgrid' ).find( 'table' );
						if ( tables.length > 0 ) {
							for ( var i = 0; i < tables.length; i++ ) {
								//resize awesomebox grid tables to remove right-size gap.
								var width = $( tables[0] ).parents( '.ui-jqgrid' ).find( '.ui-jqgrid-bdiv' ).width() - 11;
								$( tables[0] ).parents( '.ui-jqgrid' ).find( '.ui-jqgrid-view' ).width( width );
								$( tables[i] ).width( width );
							}
						}

						$this.highlightSelectItem();
						a_dropdown.setUnSelectGridDragAble();
						a_dropdown.setSelectGridDragAble();
					}

				} );

				a_dropdown.bind( 'close', $this.onClose );

				var layout = ALayoutCache.layout_dic[layout_name];

				//Always use columns from global cache if columns is not default
				if ( layout_name && layout && Global.isSet( layout.data ) ) {

					var current_display_columns = layout.data.display_columns;

					//Happen when save no columns in column setting for navigation mode
					if ( current_display_columns.length > 0 ) {
						//Only check possible columns if any
						if ( possible_display_columns ) {
							current_display_columns = filterBaseOnPossibleColumns( current_display_columns );
						}
					} else {

						if ( list_view_default_columns ) {
							current_display_columns = list_view_default_columns;
							current_display_columns = filterBaseOnPossibleColumns( current_display_columns );
						}
					}

					current_display_columns = Global.convertColumnsTojGridFormat( current_display_columns, layout_name );

					var current_row_per_page = layout.data.row_per_page;

					//If current columns or row_per_page not same as saved layout. Reload data base on current setting
					if ( $this.checkIfLayoutChanged( current_display_columns ) || ( row_per_page !== current_row_per_page && !navigation_mode ) ) {

						display_columns = current_display_columns;
						source_data = null;
					}

					if ( !navigation_mode ) {
						row_per_page = current_row_per_page;

						if ( !set_default_args_manually ) {
							if ( Global.isSet( layout.data.type ) && layout.data.type === 10 ) {
								var default_args = {};
								default_args.filter_data = layout.data.filter_data;
								default_args.filter_sort = layout.data.filter_sort;
							} else {
								default_args = null;
							}
						}

					}

				}

				//Set columns first
				a_dropdown.setColumns( display_columns );

				TTPromise.wait( 'AComboBox', 'init', () => {
					//Set DropDown position
					//use default with since .width()  not return correct width when first open
					a_dropdown_div.css( 'left', $( $this ).offset().left + 'px' );

					let viewport_width = $( window ).width();
					let dropdown_rect = a_dropdown_div[0].getBoundingClientRect();
					let dropdown_right = dropdown_rect.right;
					let dropdown_left = dropdown_rect.left;

					if ( Math.abs( dropdown_right - viewport_width ) <= 2 && a_dropdown_div[0].querySelector( '.select-grid-div' ) ) {
						//The dropdown does not show correct right position when off screen as child elements extend beyond the parent. This could be due to floats or other positioning problems.
						//In this scenario we need to calculate the right position based on the child elements.
						dropdown_right = ( a_dropdown_div[0].querySelector( '.select-grid-div' ).getBoundingClientRect().right + 10 );
					}

					if ( dropdown_right > viewport_width ) {
						// If the dropdown overflows on the right side, adjust to align with the viewport's right edge.
						let overflow_right = dropdown_right - viewport_width;
						let current_left = $( a_dropdown_div ).offset().left;
						a_dropdown_div.css( 'left', ( current_left - overflow_right ) + 'px' );
					} else if ( dropdown_left < 0 ) {
						// If the dropdown overflows on the left side, adjust to align with the viewport's left edge.
						a_dropdown_div.css( 'left', '0px' );
					}

					// makes sure it shown on the screen, will calculte position after source setting
					if ( ( $( $this ).offset().top + 25 + 275 ) < Global.bodyHeight() ) {
						a_dropdown_div.css( 'top', ( $( $this ).offset().top + 25 ) );
					} else {
						a_dropdown_div.css( 'top', ( $( $this ).offset().top - 275 ) );
					}

					// Calculate the bottom position of the dropdown
					let dropdown_bottom = a_dropdown_div[0].getBoundingClientRect().bottom;
					let dropdown_height = a_dropdown_div[0].getBoundingClientRect().height;
					let viewport_height = $( window ).height();

					// Check if the dropdown goes off the bottom of the viewport, place it above the clicked element.
					if ( dropdown_bottom > viewport_height ) {
						let field_top = ( $this[0].getBoundingClientRect().top - 5 ); // 5 pixel room, so it does not directly touch the field
						a_dropdown_div.css( 'top', field_top - dropdown_height );
					}

					// If after all adjustments the dropdown still goes out of the viewport, then we modify the height/width to fit instead of the position.
					let overflow_right = Math.max( 0, a_dropdown_div[0].getBoundingClientRect().right - $( window ).width() );
					let overflow_bottom = Math.max( 0, a_dropdown_div[0].getBoundingClientRect().bottom - $( window ).height() );
					let overflow_left = Math.max( 0, -a_dropdown_div[0].getBoundingClientRect().left );
					let overflow_top = Math.max( 0, -a_dropdown_div[0].getBoundingClientRect().top );

					// Adjust dropdown dimensions based on overflow
					if ( overflow_right > 0 || overflow_left > 0 ) {
						let new_width = a_dropdown_div.outerWidth() - overflow_right - overflow_left;
						a_dropdown_div.css( 'width', new_width + 'px' );
					}

					if ( overflow_bottom > 0 || overflow_top > 0 ) {
						let current_height = a_dropdown_div.outerHeight();
						let new_height = current_height - overflow_bottom - overflow_top;
						a_dropdown_div.css( 'height', new_height + 'px' );

						// If there was overflow at the top, adjust the position as well.
						if ( overflow_top > 0 ) {
							let current_top = a_dropdown_div.offset().top;
							let new_top = current_top + overflow_top; // Move down by the overflow amount
							a_dropdown_div.css( 'top', new_top + 'px' );
						}
					}
				} );

				// This will never change when search in search input. Set it back to dropdown every time when open
				if ( !source_data ) {
					//When using pagination and first opening the dropdown box, we need to exclude the selected items from the API call
					// so the first page will always be populated with a full page of records.
					// Lower down we make a separate API call to get the records for the selected items, and source_data.concat() them there.
					var args = $this.buildUnSelectGridFilter( $this.getValue() );

					//Error: TypeError: api is null in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=8.0.0-20141117-112033 line 2364
					if ( !api ) {
						if ( addition_source_function ) { //Required to add data to search UI for custom fields
							source_data = addition_source_function( $this, source_data );
							a_dropdown.setUnselectedGridData( source_data );
						}
						TTPromise.resolve( 'AComboBox', 'init' );
						return;
					}

					api['get' + custom_key_name]( args, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !Global.isArray( result_data ) ) {
								result_data = [];
							}

							source_data = result_data;

							//set this outside, to add more data to source_data
							if ( addition_source_function ) {
								source_data = addition_source_function( $this, source_data );
							}

							navigation_mode_source_data_before_open = null;

							if ( Global.isSet( api ) ) {
								source_data = Global.formatGridData( source_data, api.key_name );
							}

							if ( allow_multiple_selection ) {
								if ( set_all ) {
									$this.createFirstItem();
								}

								// For select items which only contains ids, like all awesomeboxes in edit view
								// Similar code is also below in the } else { clause of this IF statement.
								if ( get_real_data_when_open ) {
									get_real_data_when_open = false;

									a_dropdown.setUnselectedGridData( source_data );

									if ( !select_items || ( select_items && select_items.length == 1 && ( select_items[0] == TTUUID.zero_id || select_items[0] == TTUUID.not_exist_id ) ) ) {
										TTPromise.resolve( 'AComboBox', 'init' );
									} else {
										var args = {};
										args.filter_data = { id: select_items };
										args.filter_columns = $this.getColumnFilter();
										args.filter_items_per_page = 10000;

										//Error: TypeError: null is not an object (evaluating 'select_items.length') in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=8.0.0-20141230-113526 line 2441
										//if select items contains data like 0, for example Employee in Recurring Schedule edit view
										var local_data = false;
										if ( select_items && select_items.length > 0 && ( select_items[0] == TTUUID.zero_id || select_items[0] == TTUUID.not_exist_id ) ) {
											local_data = $this.getLocalSelectItem( select_items[0] );
										}

										api['get' + custom_key_name]( args, {
											onResult: function( result ) {
												select_items = result.getResult();

												//Since the initial API call above excluded the selected items, we need to concat() them back onto the source_data array
												source_data = source_data.concat( select_items ); //Do above the unshift( local_data ) below.

												if ( local_data ) {
													select_items.unshift( local_data );
												}

												//a_dropdown.setSelectGridData( select_items ); //set Selected Data after set sourceData
												setADropDownSelectValues( select_items );
												TTPromise.resolve( 'AComboBox', 'init' );
											}
										} );
									}
								} else {
									a_dropdown.setUnselectedGridData( source_data );
									//Issue #3220 - When an AwesomeBox has only 1 item selected on initial load, that item will disappear on reopen
									//if it is unselected by the user as it is not in the source_data for the AwesomeBox.
									//Since the initial API call above excluded the selected items, we need to concat() them back onto the source_data array
									if ( Array.isArray( select_items ) && select_items.length > 0 && select_items[0].id ) {
										source_data = source_data.concat( select_items );
									}
									setADropDownSelectValues( select_items );
									TTPromise.resolve( 'AComboBox', 'init' );
								}
							} else {
								if ( set_empty || set_any || set_default || set_open || set_special_empty ) {
									$this.createFirstItem();
								}

								a_dropdown.setUnselectedGridData( source_data );
								a_dropdown.setSelectItem( select_item );
								TTPromise.resolve( 'AComboBox', 'init' );
							}

							pager_data = result.getPagerData();
							a_dropdown.setPagerData( pager_data );

							if ( !Global.isEmpty( cached_search_inputs_filter ) || !Global.isEmpty( cached_sort_filter ) ) {
								a_dropdown.getUnSelectGrid().grid.hide();
								$this.onADropDownSearch( 'unselect_grid' );
							}

							if ( !Global.isEmpty( cached_select_grid_search_inputs_filter ) || !Global.isEmpty( cached_selected_grid_sort_filter ) ) {
								a_dropdown.getSelectGrid().grid.hide();
								$this.onADropDownSearch( 'select_grid' );
							}

						}
					} );
				} else { //Use cache if already loaded data before

					if ( Global.isSet( api ) ) {
						source_data = Global.formatGridData( source_data, api.key_name );
					}

					if ( allow_multiple_selection ) {
						if ( layout_name === 'global_sort_columns' ) {
							select_items = buildSortBySelectColumns( select_items );
						}

						// Similar code is also below in the above TRUE IF statement clause.
						if ( get_real_data_when_open ) {
							get_real_data_when_open = false;

							a_dropdown.setUnselectedGridData( source_data );

							if ( !select_items || ( select_items && select_items.length == 1 && ( select_items[0] == TTUUID.zero_id || select_items[0] == TTUUID.not_exist_id ) ) ) {
								TTPromise.resolve( 'AComboBox', 'init' );
							} else {
								var args = {};
								args.filter_data = { id: select_items };
								args.filter_columns = $this.getColumnFilter();
								args.filter_items_per_page = 10000;

								//Error: TypeError: null is not an object (evaluating 'select_items.length') in /interface/html5/global/widgets/awesomebox/AComboBox.js?v=8.0.0-20141230-113526 line 2441
								//if select items contains data like 0, for example Employee in Recurring Schedule edit view
								var local_data = false;
								if ( select_items && select_items.length > 0 && ( select_items[0] == TTUUID.zero_id || select_items[0] == TTUUID.not_exist_id ) ) {
									local_data = $this.getLocalSelectItem( select_items[0] );
								}

								api['get' + custom_key_name]( args, {
									onResult: function( result ) {
										select_items = result.getResult();

										if ( local_data ) {
											select_items.unshift( local_data );
										}

										a_dropdown.setUnselectedGridData( source_data );
										//a_dropdown.setSelectGridData( select_items ); //set Selected Data after set sourceData
										setADropDownSelectValues( select_items );
										TTPromise.resolve( 'AComboBox', 'init' );

									}
								} );
							}
						} else {
							a_dropdown.setUnselectedGridData( source_data );
							//a_dropdown.setSelectGridData( select_items );
							setADropDownSelectValues( select_items );
							TTPromise.resolve( 'AComboBox', 'init' );
						}

					} else {
						a_dropdown.setUnselectedGridData( source_data );
						a_dropdown.setSelectItem( select_item );
						TTPromise.resolve( 'AComboBox', 'init' );
					}

					a_dropdown.setPagerData( pager_data );

					if ( !Global.isEmpty( cached_search_inputs_filter ) || !Global.isEmpty( cached_sort_filter ) ) {
						a_dropdown.getUnSelectGrid().grid.hide();
						$this.onADropDownSearch( 'unselect_grid' );
						current_open_page = 1;
					} else {
						if ( current_open_page > 1 && current_open_page <= pager_data.last_page_number ) {
							a_dropdown.getUnSelectGrid().grid.hide();
							$this.onADropDownSearch( 'unselect_grid', current_open_page );
						}
					}

					if ( !Global.isEmpty( cached_select_grid_search_inputs_filter ) || !Global.isEmpty( cached_selected_grid_sort_filter ) ) {
						//a_dropdown.getSelectGrid().grid.hide(); //#2721 - This helps fixes a bug where if you expand a awesomebox (ie: Edit Policy Group, Absence Policys), on right-hand side click clear search. Click Move All on right-hand side. Collapse, expand, click move All left-hand side. Both sides are now blank because the right grid had a display: none attached to it.
						$this.onADropDownSearch( 'select_grid' );
					}

				}

			}

		} );

		return this;

	};

	$.fn.AComboBox.defaults = {};
	$.fn.AComboBox.html_template = `
	<div class="a-combobox">
		<div class="a-combobox-left-bg"></div>
		<span class="a-combobox-label openADropDown a-combobox-mid-bg"></span>
		<input class="focus-input">
		<div class="clickable-space openADropDown"></div>
	</div>
	`;

} )( jQuery );