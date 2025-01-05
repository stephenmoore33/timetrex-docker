import{j as I}from"./vendor-tTApdY0Y.bundle.js";(function(e){e.fn.TImageCutArea=function(m){Global.addCss("global/widgets/filebrowser/TImageBrowser.css");var g=e.extend({},e.fn.TImageCutArea.defaults,m),s=this,v;this.clearErrorStyle=function(){},this.getField=function(){return v},this.getValue=function(){};var h=function(t){var a=s.children().eq(1).children().eq(1);if(!t){a.attr("src","");return}a.attr("src",t)};this.setImage=function(t){var a=s.children().eq(0).children().eq(1);if(!t){a.attr("src","");return}a.attr("src",t),h(t),setTimeout(function(){e(a).imgAreaSelect({handles:!0,x1:0,y1:0,x2:e(a).width(),y2:e(a).height(),onSelectEnd:function(p,i){var n=a[0].naturalWidth/a.width(),w=i.x1*n,x=i.y1*n;i.x2*n,i.y2*n-1;var l=i.width*n,u=i.height*n,d=e("<canvas></canvas>");d=d[0],d.width=l,d.height=u;var A=d.getContext("2d");A.drawImage(a[0],w,x,l-1,u-1,0,0,l,u),h(""),h(d.toDataURL())}})},100)},this.getAfterImageSrc=function(){var t=s.children().eq(1).children().eq(1);return t.attr("src")},this.clearSelect=function(){var t=s.children().eq(0).children().eq(1);e(t).imgAreaSelect({remove:!0})},this.setValue=function(t){};for(var c=0;c<this.length;c++){var f=this[c],r=e.meta?e.extend({},g,e(f).data()):g;v=r.field,r.default_width>0&&r.default_width,r.default_height>0&&r.default_height,Global.isSet(r.name)&&r.name;var o=e(f).children().eq(0).children().eq(1);o.on("load",function(){});var q=e(f).children().eq(1).children().eq(1);q.on("load",function(){})}return this},e.fn.TImageCutArea.defaults={},e.fn.TImageCutArea.html_template=`
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
	`})(I);
//# sourceMappingURL=TImageCutArea-DleUw4Kb.bundle.js.map
