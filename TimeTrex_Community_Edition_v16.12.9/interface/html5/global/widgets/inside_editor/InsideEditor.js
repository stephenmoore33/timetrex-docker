( function( $ ) {

	$.fn.InsideEditor = function( options ) {

		Global.addCss( 'global/widgets/inside_editor/InsideEditor.css' );
		var opts = $.extend( {}, $.fn.InsideEditor.defaults, options );

		var $this = this;
		var field;
		var row_render;
		var render;

		this.rows_widgets_array = null;
		this.delete_ids = null;
		this.addRow = null;
		this.setValue = null;
		this.getValue = null; // set outside
		this.removeRow = null;
		this.updateAllRows = null;
		this.render_inline_html = false;
		this.editor_data = null;
		this.onFormItemChange = null;

		this.parent_controller = null;
//		this.parent_id = null;

		this.api = null;

		this.setValue = function( val ) {
			if ( val && val.length > 0 ) {
				this.setValue( val );
			} else {
				this.addRow();
			}
		};

		this.getRender = function() {
			return render;
		};

		this.getRowRender = function() {
			return row_render.clone();
		};

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		this.setTitle = function( val ) {
			var title = $this.children().eq( 0 );

			title.text( val );

		};

		this.removeLastRowLine = function() {
			var table = this.find( '.inside-editor-render' );
			var trs = table.find( 'tr' );
			table.find( 'td' ).removeClass( 'no-line' );

			var last_tr = trs.eq( trs.length - 1 );

			last_tr.find( 'td' ).addClass( 'no-line' );
		};

		this.removeAllRows = function( include_header ) {
			var table = this.find( '.inside-editor-render' );
			var trs = table.find( 'tr' );
			if ( include_header ) {

				table.find( 'tr' ).each( function() {
					$( this ).remove();
				} );

			} else {
				table.find( 'tr.inside-editor-row' ).remove();
			}

			this.rows_widgets_array = [];
		};

		this.setWidgetEnableBaseOnParentController = function( form_item_input ) {
			if ( this.parent_controller.is_viewing ) {
				form_item_input.setEnabled( false );
			} else {
				form_item_input.setEnabled( true );
			}
		};

		this.addIconsEvent = function( row ) {
			var plus_icon = row.find( '.plus-icon' );
			var minus_icon = row.find( '.minus-icon' );

			plus_icon.click( function() {
				$this.addRow( null, $( this ).parents( 'tr' ).index(), row );
			} );

			minus_icon.click( function() {
				$this.removeRow( row );

				if ( render.find( 'tr' ).length === 1 ) {
					$this.addRow();
				}

			} );

		};

		this.getDefaultData = function( index, callback ) {

			if ( Global.isSet( this.api ) ) {
				this.api['get' + this.api.key_name + 'DefaultData']( {
					onResult: function( result ) {
						var result_data = result.getResult();
						result_data.id = false;

						if ( !result_data ) {
							result_data = [];
						}

						$this.addRow( result_data, index );

						if ( callback && typeof callback === 'function' ) {
							callback();
						}

					}
				} );
			}
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;
			if ( o.title ) {
				$this.setTitle( o.title );
			}
			if ( o.onFormItemChange ) {
				$this.onFormItemChange = o.onFormItemChange;
			}
			$this.rows_widgets_array = [];
			$this.delete_ids = [];
			$this.addRow = o.addRow;
			$this.removeRow = o.removeRow;
			$this.getValue = o.getValue;
			$this.setValue = o.setValue;
			$this.parent_controller = o.parent_controller;
			$this.api = o.api;
			$this.updateAllRows = o.updateAllRows;
			$this.render_inline_html = o.render_inline_html;
			render = $this.render_inline_html ? o.render : Global.loadWidget( o.render );
			row_render = $this.render_inline_html ? $( o.row_render ) : $( Global.loadWidget( o.row_render ) );
			var args = o.render_args;
			var template = _.template( render );
			var render_div = $this.children().eq( 1 );
			render_div.append( template( args ) );
			render = $( render_div.find( '.inside-editor-render' ) );

		} );

		// Sets data as modified when a widget triggers onFormItemChange.
		// Some inside editor widgets bind their own formItemChange and to prevent unintended interactions we only set data changed.
		$this.bind( 'formItemChange', function( e, target ) {
			// Fix issue #2885: Error: Uncaught TypeError: o.parent_controller.setIsChanged is not a function.
			// This was happening in TimeSheet -> New Punch and opening the inside editor for dates and changing a date.
			if ( $this.parent_controller && typeof $this.parent_controller.setIsChanged === 'function' ) {
				$this.parent_controller.setIsChanged( target );
			}
		} );

		return this;

	};

	$.fn.InsideEditor.defaults = {};
	$.fn.InsideEditor.html_template = `
	<div class="inside-editor">
		<span class="title"></span>
		<div class="render-div"></div>
	</div>
	`;

} )( jQuery );