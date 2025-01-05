( function( $ ) {

	$.fn.SaveAndContinueBox = function( options ) {
		var opts = $.extend( {}, $.fn.SaveAndContinueBox.defaults, options );
		var field;
		var related_view_controller;

		this.each( function() {

			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			if ( o.related_view_controller ) {
				related_view_controller = o.related_view_controller;
			}

			var label = $( this ).find( '.p-button-label' );
			var icon = $( this ).find( '.icon' );
			var message = $( this ).find( '.message' );

			var ribbon_button = $( this ).find( '.p-button' );

			message.text( Global.save_and_continue_message );
			icon.addClass('tticon tticon tticon-save_black_24dp');
			label.html( $.i18n._( 'Save & Continue' ) );

			var context_menu_array = ContextMenuManager.getMenuModelByMenuId( related_view_controller.determineContextMenuMountAttributes().id );
			var len = context_menu_array.length;

			for ( var i = 0; i < len; i++ ) {

				let context_btn = context_menu_array[i];
				let id = context_btn.id;

				if ( id === 'save_and_continue' ) {
					if ( !context_btn.visible || context_btn.disabled ) {
						ribbon_button.addClass( 'disable-image' );
					}
				}

			}

			ribbon_button.off( 'click' ).on( 'click', function() {

				if ( ribbon_button.hasClass( 'disable-image' ) ) {
					return;
				}
				related_view_controller.onSaveAndContinue();
			} );

		} );

		return this;

	};

	$.fn.SaveAndContinueBox.defaults = {};

} )( jQuery );