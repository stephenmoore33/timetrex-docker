( function( $ ) {

	$.fn.TImageCutArea = function( options ) {

		Global.addCss( 'global/widgets/filebrowser/TImageBrowser.css' );
		var opts = $.extend( {}, $.fn.TImageCutArea.defaults, options );

		var $this = this;
		var field;
		var name = 'file_data';

		var default_width = 400;
		var default_height = 300;

		var default_after_width = 200;
		var default_after_height = 150;

		this.clearErrorStyle = function() {

		};

		this.getField = function() {
			return field;
		};

		this.getValue = function() {

		};

		var setAfterImage = function( val ) {
			var image = $this.children().eq( 1 ).children().eq( 1 );

			if ( !val ) {
				image.attr( 'src', '' );
				return;
			}
			var d = new Date();
			image.attr( 'src', val );

		};

		this.setImage = function( val ) {
			var image = $this.children().eq( 0 ).children().eq( 1 );

			if ( !val ) {
				image.attr( 'src', '' );
				return;
			}
			var d = new Date();
			image.attr( 'src', val );

			setAfterImage( val );

			setTimeout( function() {
				$( image ).imgAreaSelect( {
					handles: true,
					x1: 0,
					y1: 0,
					x2: $( image ).width(),
					y2: $( image ).height(),
					onSelectEnd: function( img, selection ) {

						var rate = image[0].naturalWidth / image.width();
						var sx = selection.x1 * rate;
						var sy = selection.y1 * rate;
						var tx = selection.x2 * rate;
						var ty = selection.y2 * rate - 1;
						var width = selection.width * rate;
						var height = selection.height * rate;

						var canvas = $( '<canvas></canvas>' );
						canvas = canvas[0];
						canvas.width = width;
						canvas.height = height;
						var ctx = canvas.getContext( '2d' );

						ctx.drawImage( image[0], sx, sy, width - 1, height - 1, 0, 0, width, height );
						setAfterImage( '' );
						setAfterImage( canvas.toDataURL() );

					}
				} );
			}, 100 );

		};

		this.getAfterImageSrc = function() {
			var image = $this.children().eq( 1 ).children().eq( 1 );

			return image.attr( 'src' );
		};

		this.clearSelect = function() {
			var image = $this.children().eq( 0 ).children().eq( 1 );

			$( image ).imgAreaSelect( { remove: true } );

		};

		var onImageLoad = function( image ) {

//			if ( $( image ).height() > default_height ) {
//				$( image ).css( 'height', default_height );
//
//			}
//
//			if ( $( image ).width() > default_width ) {
//				$( image ).css( 'width', default_width );
//
//				$( image ).css( 'height', 'auto' );
//			}

//			$( image ).show();

		};

		var onAfterImageLoad = function( image ) {

//			if ( $( image ).height() > default_after_height ) {
//				$( image ).css( 'height', default_after_height );
//
//			}
//
//			if ( $( image ).width() > default_after_width ) {
//				$( image ).css( 'width', default_after_width );
//
//				$( image ).css( 'height', 'auto' );
//			}

//			$( image ).show();

		};

		this.setValue = function( val ) {

			if ( !val ) {
				val = '';
			}

		};

		for ( var i = 0; i < this.length; i++ ) {
			var $item = this[i];

			var o = $.meta ? $.extend( {}, opts, $( $item ).data() ) : opts;

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

			var image = $( $item ).children().eq( 0 ).children().eq( 1 );
			image.on( 'load', function() {
				onImageLoad( this );

			} );

			var after_image = $( $item ).children().eq( 1 ).children().eq( 1 );
			after_image.on( 'load', function() {
				onAfterImageLoad( this );

			} );

		}

		return this;

	};

	$.fn.TImageCutArea.defaults = {};
	$.fn.TImageCutArea.html_template = `
	<div class="t-image-cut">
		<div class="before-div">
			<span>Before:</span>
			<img class="before-img">
		</div>
		<div class="after-div">
			<span>After:</span>
			<img class="after-img">
		</div>
	</div>
	`;

} )( jQuery );