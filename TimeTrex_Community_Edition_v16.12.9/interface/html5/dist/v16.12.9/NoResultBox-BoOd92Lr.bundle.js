import{j as u}from"./vendor-tTApdY0Y.bundle.js";(function(t){t.fn.NoResultBox=function(b){Global.addCss("global/widgets/message_box/NoResultBox.css");var i=t.extend({},t.fn.NoResultBox.defaults,b),e,a=Global.no_result_message,n="";return this.each(function(){var s=t.meta?t.extend({},i,t(this).data()):i;s.related_view_controller&&(e=s.related_view_controller),s.message&&(a=s.message),s.iconLabel?n=s.iconLabel:n=t.i18n._("New");var o=t(this).find(".p-button"),l=t(this).find(".add-div"),d=t(this).find(".p-button-label"),c=t(this).find(".icon"),r=t(this).find(".message");l.css("display","block"),s.is_new?(c.addClass("tticon tticon-add_black_24dp"),d.text(n),o.bind("click",function(){e.onAddClick()})):s.is_edit?(c.addClass("tticon tticon-edit_black_24dp"),d.text(t.i18n._("Edit")),o.bind("click",function(){e.onEditClick()})):l.css("display","none"),r.text(a)}),this},t.fn.NoResultBox.defaults={},t.fn.NoResultBox.html_template=`
		<div class="no-result-div">
			<span class="message"></span>
			<div class="add-div">
				<button class="tt-button p-button p-component" type="button">
					<span class="icon"></span>
					<span class="label p-button-label"></span>
				</button>
			</div>
		</div>
	`})(u);
//# sourceMappingURL=NoResultBox-BoOd92Lr.bundle.js.map
