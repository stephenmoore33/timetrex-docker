import{j as h}from"./vendor-tTApdY0Y.bundle.js";(function(a){a.fn.CameraBrowser=function(l){Global.addCss("global/widgets/filebrowser/TImageBrowser.css");var c=a.extend({},a.fn.CameraBrowser.defaults,l),o=this,d,i=null,s=null,r=null;return this.stopCamera=function(){r&&(r.stop?r.stop():r.getTracks&&r.getTracks().forEach(t=>t.stop()))},this.showCamera=function(){navigator.getUserMedia=navigator.getUserMedia||navigator.webkitGetUserMedia||navigator.mozGetUserMedia||navigator.msGetUserMedia||navigator.oGetUserMedia,navigator.mediaDevices&&navigator.mediaDevices.getUserMedia?navigator.mediaDevices.getUserMedia({video:!0}).then(function(e){"srcObject"in i?i.srcObject=e:i.src=URL.createObjectURL(e),i.play(),r=e}).catch(function(e){t()}):navigator.getUserMedia?navigator.getUserMedia({video:!0},function(e){"srcObject"in i?i.srcObject=e:i.src=URL.createObjectURL(e),i.play(),r=e},t):navigator.webkitGetUserMedia?navigator.webkitGetUserMedia({video:!0},function(e){i.src=window.webkitURL.createObjectURL(e),i.play(),r=e},t):navigator.mozGetUserMedia?navigator.mozGetUserMedia({video:!0},function(e){i.src=window.URL.createObjectURL(e),i.play(),r=e},t):t();function t(){TAlertManager.showAlert(a.i18n._("Unable to access Camera.<br><br>Please check your camera connections, permissions, and ensure you are using HTTPS. Alternatively, use the File upload method instead."))}},this.setEnable=function(t){var e=this.children().eq(1);t?(e.removeAttr("disabled"),e.removeClass("disable-element")):(e.attr("disabled",!0),e.removeClass("disable-element").addClass("disable-element"))},this.clearErrorStyle=function(){},this.getField=function(){return d},this.getValue=function(){return!1},this.getFileName=function(){return"camera_stream.png"},this.getImageSrc=function(){return s[0].toDataURL()},this.setImage=function(t){var e=o.children().eq(0);if(!t){e.attr("src",""),e.hide();return}var n=new Date;e.hide(),e.attr("src",t+"&t="+n.getTime()),e.css("height","auto"),e.css("width","auto")},this.onImageLoad=function(t){a(t).show()},this.setValue=function(t){},this.each(function(){var t=a.meta?a.extend({},c,a(this).data()):c;d=t.field,i=a(this).children().eq(0).children().eq(0)[0],s=a(this).children().eq(0).children().eq(1);var e=a(this).children().eq(1).children().eq(0),n=a(this).children().eq(1).children().eq(1);e.prop("disabled",!1),n.prop("disabled",!0),e.bind("click",function(){e.prop("disabled",!0),n.prop("disabled",!1),s.parent().addClass("flash"),setTimeout(function(){s.parent().removeClass("flash")},1e3);var u=s[0].getContext("2d");u.drawImage(i,0,0,400,300),s.css("z-index",51),o.trigger("change",[o])}),n.bind("click",function(){e.prop("disabled",!1),n.prop("disabled",!0),Global.glowAnimation.stop(),s.css("z-index",-1),o.trigger("NoImageChange",[o])})}),this},a.fn.CameraBrowser.defaults={},a.fn.CameraBrowser.html_template=`
	<div class="file-browser">
		<div class="video-div">
			<video class="video-display" width="400" height="300">
			</video>
			<canvas class="video-capture" width="400" height="300">
			</canvas>
		</div>
		<div class="buttons">
			<button id="take_picture" class="t-button">Take Picture</button>
			<button id="try_again" class="t-button">Try Again</button>
		</div>
	</div>
	`})(h);
//# sourceMappingURL=CameraBrowser-ZqjxPQAO.bundle.js.map
