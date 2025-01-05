import{j as S}from"./vendor-tTApdY0Y.bundle.js";(function(e){e.fn.ColumnEditor=function(A){var y=e.extend({},e.fn.ColumnEditor.defaults,A),l=null,u=null,p=this,m=!1,d=null,C=null,b=null,h,g,w=[{label:e.i18n._("Default"),value:0},{label:5,value:5},{label:10,value:10},{label:15,value:15},{label:20,value:20},{label:25,value:25},{label:50,value:50},{label:100,value:100},{label:250,value:250},{label:500,value:500},{label:1e3,value:1e3}];return this.getIsMouseOver=function(){return m},this.getParentAwesomeBox=function(){return l},Global.addCss("global/widgets/column_editor/ColumnEditor.css"),this.show=function(){if(LocalCacheData.openAwesomeBoxColumnEditor)if(LocalCacheData.openAwesomeBoxColumnEditor.getParentAwesomeBox().getId()===l.getId()){LocalCacheData.openAwesomeBoxColumnEditor.onClose();return}else LocalCacheData.openAwesomeBoxColumnEditor.onClose();var a=l.getLayout(),n=e(this).find(".column-editor-drop-down-div"),o=e(this).find(".rows-per-page-div");u=Global.loadWidgetByName(FormItemType.AWESOME_DROPDOWN),u=u.ADropDown({display_show_all:!1,id:"column_editor",key:"value",display_close_btn:!1,display_column_settings:!1,static_height:150,resize_grids:!0}),n.append(u),e("body").append(e(this).css("visibility","hidden")),u.setColumns([{name:"label",index:"label",label:e.i18n._("Column Name"),width:100,sortable:!1}]),u.setUnselectedGridData(l.getAllColumns()),g=l.getDisplayColumnsForEditor(),u.setSelectGridData(g),u.setResizeGrids(!0),958+e(l).offset().left+50>Global.bodyWidth()?e(this).css("left",Global.bodyWidth()-958-50):e(this).css("left",e(l).offset().left);var t=this;setTimeout(function(){e(t).height()+e(l).offset().top+50>Global.bodyHeight()?e(t).css("top",Global.bodyHeight()-e(t).height()-25):e(t).css("top",e(l).offset().top+25),e(t).css("visibility","visible")},100),e(this).mouseenter(function(){m=!0}),e(this).mouseleave(function(i){i.target==this&&(m=!1)}),LocalCacheData.openAwesomeBoxColumnEditor=p,a&&Global.isSet(a.data.type)&&a.data.type===10?(n.css("display","none"),o.css("display","none")):(n.css("display","block"),o.css("display","block"));var c=l.getScriptName(),s=l.getAPI();s.getOptions("columns",{onResult:function(i){var r=i.getResult();h=Global.buildColumnArray(r)}}),C.getUserGenericData({filter_data:{script:c,deleted:!1}},{onResult:function(i){var r=i.getResult();if(b=r,r&&r.length>0){r.sort(function(x,E){return Global.compare(x,E,"name")}),e(d).empty();var v=[];v.push({label:e.i18n._(Global.customize_item),value:-1});for(var D=r.length,f=0;f<D;f++){var _=r[f];v.push({label:_.name,value:_.id})}if(d.setSourceData(v),a&&Global.isSet(a.data.type)&&a.data.type===10){e(e(d).find("option")).filter(function(){return parseInt(e(this).attr("value"))===a.data.layout_id}).prop("selected",!0).attr("selected",!0);var G=d.getValue();G===-1&&(n.css("display","block"),o.css("display","block"))}}else v=[],v.push({label:e.i18n._(Global.customize_item),value:-1}),d.setSourceData(v),n.css("display","block"),o.css("display","block")}})},this.onClose=function(){e(p).remove(),LocalCacheData.openAwesomeBoxColumnEditor=null,m=!1},this.onSave=function(){p.onClose();var a=d.getValue();if(b)if(a!==-1)for(var n=b.length,o=0;o<n;o++){var t=b[o];if(t.id===a){t.data.filter_data=Global.convertLayoutFilterToAPIFilter(t),t.data.display_columns=p.buildDisplayColumns(t.data.display_columns),l.onColumnSettingSaveFromLayout(t);break}}else{var c=e(p).find("#rows-per-page-selector");c.find("option:selected").each(function(){var s=e(this).attr("value"),i=u.getSelectItems();i.length===0&&(i=g),l.onColumnSettingSave(i,s,"-1")})}},this.buildDisplayColumns=function(a){for(var n=h.length,o=a.length,t=[],c=0;c<o;c++)for(var s=0;s<n;s++)a[c]===h[s].value&&t.push(h[s]);return t},this.each(function(){var a=e.meta?e.extend({},y,e(this).data()):y,n=e(this).find("#close_btn"),o=e(this).find("#save_btn");C=TTAPI.APIUserGenericData,l=a.parent_awesome_box,n.bind("click",p.onClose),o.bind("click",p.onSave);for(var t=e(this).find("#rows-per-page-selector"),c=w.length,s=0;s<c;s++){var i=w[s];e(t).append('<option value="'+i.value+'">'+i.label+"</option>")}e(e(t).find("option")).filter(function(){return e(this).attr("value")===l.getRowPerPage()}).prop("selected",!0).attr("selected",!0),d=e(this).find("#layout-selector"),d=d.TComboBox();var r=e(this).find(".column-editor-drop-down-div"),v=e(this).find(".rows-per-page-div");d.bind("formItemChange",function(D,f){var _=f.getValue();_!==-1?(r.css("display","none"),v.css("display","none")):(r.css("display","block"),v.css("display","block"))})}),this},e.fn.ColumnEditor.defaults={},e.fn.ColumnEditor.html_template=`
		<div class="column-editor">
			<div class="layout-name-div">
				<div class="form-item-div">
					<span class="choose-layout form-item-label"></span>
					<div class="form-item-input-div">
						<select id="layout-selector" class="t-select"/>
					</div>
				</div>
			</div>
			<div class="column-editor-drop-down-div"></div>
			<div style="clear: both"></div>
			<div class="rows-per-page-div">
				<div class="form-item-div">
					<span class="rows-per-page form-item-label"></span>
					<div class="form-item-input-div">
						<select id="rows-per-page-selector" class="t-select"/>
					</div>
				</div>
			</div>
			<div style="clear: both"></div>
			<div class="bottom-action-div">
				<button id="save_btn" class="t-button"></button>
				<button id="close_btn" class="t-button">Close</button>
			</div>
		</div>
	`})(S);
//# sourceMappingURL=ColumnEditor-BI0uji2X.bundle.js.map
