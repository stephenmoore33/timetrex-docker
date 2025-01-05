( function( $ ) {

	$.fn.TImageBrowser = function( options ) {
		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImageBrowser.defaults, options );

		var $this = this;
		var field;
		var id = 'file_browser';
		var name = 'file_data';
		var browser;

		var accept_filter = '';
		var file = null;

		var default_width = 177;
		var default_height = 42;
		var enabled = true;

		this.setEnabled = function( val ) {
			enabled = val;

			var btn = this.find( '.browser-form input' );

			if ( !val ) {
				btn.attr( 'disabled', true );
				btn.removeClass( 'disable-element' ).addClass( 'disable-element' );
			} else {
				btn.removeAttr( 'disabled' );
				btn.removeClass( 'disable-element' );
			}
		};

		this.clearErrorStyle = function() {
		};

		this.getFileName = function() {
			return browser.val();
		};

		this.getField = function() {
			return field;
		};

		this.setEnableDelete = function( val ) {
			var image = $this.find( '.image' );
			if ( !val ) {
				image.removeAttr( 'enable-delete' );
				return;
			} else {
				image.attr( 'enable-delete', 1 );
			}
		};

		this.getValue = function() {
			//If the raw file data came from a File Upload Wizard that uses this widget as well, just return it verbatim.
			if ( file ) {
				return file;
			}

			var form_data;
			if ( browser && browser.val() ) {

				if ( typeof FormData == 'undefined' ) {
					form_data = $this.find( '.browser-form' );
				} else {
					form_data = new FormData( $( $this.find( '.browser-form' ) )[0] );
				}

			} else {

				form_data = null;
			}

			return form_data;
		};

		this.setValue = function( val ) {
			if ( !val ) {
				val = '';
			}
		};

		this.getImageSrc = function() {
			var image = $this.find( '.image' );
			return image.attr( 'src' );
		};

		this.setImage = function( val ) {
			var image = $this.find( '.image' );

			if ( !val ) {
				image.attr( 'src', '' );
				image.hide();
				return;
			}

			var d = new Date();
			image.hide();
			image.attr( 'src', val + '&t=' + d.getTime() );
			image.css( 'height', 'auto' );
			image.css( 'width', 'auto' );

		};

		var onImageLoad = function( image ) {

			var image_height = $( image ).height() > 0 ? $( image ).height() : image.naturalHeight;
			var image_width = $( image ).width() > 0 ? $( image ).width() : image.naturalWidth;

			if ( image_height > default_height ) {
				$( image ).css( 'height', default_height );

			}

			if ( image_width > default_width ) {
				$( image ).css( 'width', default_width );
				$( image ).css( 'height', 'auto' );
			}

			// This causes extreme (10x) performance degradation on Bootstrap 5.1.0 and later. (Related TText setResizeEvent call)
			// $this.trigger( 'setSize' );

			if ( image_height < 5 ) {
				$( image ).hide();
			} else {
				$( image ).show();
			}
		};

		this.each( function() {
			var o = $.meta ? $.extend( {}, opts, $( this ).data() ) : opts;

			field = o.field;

			if ( o.default_width > 0 ) {
				default_width = o.default_width;
			}

			if ( o.default_height > 0 ) {
				default_height = o.default_height;
			}

			if ( Global.isSet( o.name ) ) {
				name = o.name;
			}

			if ( Global.isSet( accept_filter ) ) {
				accept_filter = o.accept_filter;
			}

			if ( Global.isSet( o.file ) ) {
				file = o.file
			}

			browser = $( this ).find( '.browser' );
			var image = $( this ).find( '.image' );
			image.hide();
			image.on( 'load', function() {
				onImageLoad( this );
			} );

			if ( accept_filter ) {
				browser.attr( 'accept', accept_filter );
			} else {
				accept_filter = 'image/*';
				browser.attr( 'accept', 'image/*' );
			}

			browser.attr( 'id', id );
			browser.attr( 'name', name );

			if ( Global.isSet( o.changeHandler ) ) {
				$this.bind( 'imageChange', o.changeHandler );
			}
			if ( Global.isSet( o.deleteImageHandler ) ) {
				this.find( '.file-browser' ).on( 'deleteClick', function() {
					o.deleteImageHandler();
				} );
			}

			browser.bind( 'change', function() {
				image.hide();

				if ( ( this.files && this.files[0] ) || o.file ) {
					let main_div = this.parentNode;

					//If this is a custom file input, we need to get the label and change it's text to the file name to show the user what file they selected
					if ( main_div.classList.contains( 'custom-file-browser' ) ) {
						let label = main_div.querySelector( '.file-browser-label' );
						if ( label ) {
							if ( this.files && this.files[0] ) {
								label.innerText = this.files[0].name;
							} else {
								label.innerText = '';
							}
						}
					}
				}

				if ( typeof FileReader != 'undefined' ) {
					var files = !!this.files ? this.files : [];

					// If no files were selected, or no FileReader support, return
					if ( !files.length || !window.FileReader ) {
						return;
					}

					if ( accept_filter === 'image/*' ) {
						// Create a new instance of the FileReader
						var reader = new FileReader();

						// Read the local file as a DataURL
						reader.readAsDataURL( files[0] );

						// When loaded, set image data as background of div
						reader.onloadend = function() {
							var url = this.result;
							image.attr( 'src', url );
						};
					}
				}

				$this.trigger( 'imageChange', [$this] );
			} );

			if ( Global.isSet( o.file ) ) { //File name should already be specified, so trigger change event so it looks like it was already uploaded.
				browser.trigger( 'change' );
			}

			this.addEventListener( 'click', function( e ) {
				let main_div = this.querySelector( '.custom-file-browser' );

				if ( main_div ) {
					main_div.querySelector( 'input.browser' ).click();
				}
			} );

		} );

		return this;

	};

	$.fn.TImageBrowser.defaults = {};
	$.fn.TImageBrowser.html_template = `
	<div class="file-browser">
		<img class="image">
		<form enctype="multipart/form-data" class="browser-form">
			<input name="file_data" class="browser" type="file"/>
		</form>
	</div>
	`;

} )( jQuery );