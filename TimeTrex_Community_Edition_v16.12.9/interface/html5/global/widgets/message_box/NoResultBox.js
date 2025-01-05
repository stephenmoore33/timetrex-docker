( function( $ ) {

	$.fn.NoResultBox = function( options ) {

		Global.addCss( 'global/widgets/message_box/NoResultBox.css' );
		var opts = $.extend( {}, $.fn.NoResultBox.defaults, options );
		var related_view_controller;
		var message = Global.no_result_message;
		var iconLabel = '';

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.related_view_controller ) {
				related_view_controller = o.related_view_controller;
			}

			if ( o.message ) {
				message = o.message;
			}

			if ( o.iconLabel ) {
				iconLabel = o.iconLabel;
			} else {
				iconLabel = $.i18n._( 'New' );
			}

			var ribbon_button = $( this ).find( '.p-button' );
			var ribbon_button_div = $( this ).find( '.add-div' );
			var label = $( this ).find( '.p-button-label' );
			var icon = $( this ).find( '.icon' );
			var message_div = $( this ).find( '.message' );

			ribbon_button_div.css( 'display', 'block' );

			if ( o.is_new ) {

				icon.addClass( 'tticon tticon-add_black_24dp' );
				label.text( iconLabel );

				ribbon_button.bind( 'click', function() {
					related_view_controller.onAddClick();
				} );

			} else if ( o.is_edit ) {

				icon.addClass( 'tticon tticon-edit_black_24dp' );
				label.text( $.i18n._( 'Edit' ) );

				ribbon_button.bind( 'click', function() {
					related_view_controller.onEditClick();
				} );

			} else {

				ribbon_button_div.css( 'display', 'none' );
			}

			message_div.text( message );

		} );

		return this;

	};

	$.fn.NoResultBox.defaults = {};
	$.fn.NoResultBox.html_template = `
		<div class="no-result-div">
			<span class="message"></span>
			<div class="add-div">
				<button class="tt-button p-button p-component" type="button">
					<span class="icon"></span>
					<span class="label p-button-label"></span>
				</button>
			</div>
		</div>
	`;

} )( jQuery );