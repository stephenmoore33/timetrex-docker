import{j as h}from"./vendor-tTApdY0Y.bundle.js";(function(t){t.fn.ErrorTipBox=function(a){var s=t.extend({},t.fn.ErrorTipBox.defaults,a),l=this,i,o;return Global.addCss("global/widgets/error_tip/ErrorTipBox.css"),this.cancelRemove=function(){Global.isSet(o)&&clearTimeout(o)},this.show=function(f,e,r,n){i=f,n?t(this).addClass("warningtip-box"):t(this).removeClass("warningtip-box"),t.type(e)==="array"&&(e=e.join("<br>"));var p=t(this).find(".errortip-label");p.html(e),t(this).css("left",i.offset().left+i.width()+5),i.hasClass("a-combobox")?t(this).css("top",i.offset().top):t(this).css("top",i.offset().top),t("body").append(this),r>0&&(o=setTimeout(function(){l.remove()},r*1e3))},this.remove=function(){t(this).remove()},this.each(function(){t.meta&&t.extend({},s,t(this).data())}),this},t.fn.ErrorTipBox.defaults={},t.fn.ErrorTipBox.html_template=`
	<div class="errortip-box">
		<span class="errortip-label"></span>
	</div>
	`})(h);
//# sourceMappingURL=ErrorTipBox-4H87YvLI.bundle.js.map
