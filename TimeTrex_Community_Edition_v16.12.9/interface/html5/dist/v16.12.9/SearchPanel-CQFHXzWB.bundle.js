import{j as k}from"./vendor-tTApdY0Y.bundle.js";(function(a){a.fn.SearchPanel=function(R){var A=a.extend({},a.fn.SearchPanel.defaults,R),o=!0,i=null,e=null,w=this,f,d=0,x=null,_=0,y=null,m=!0,S=!1;this.isAdvTabVisible=function(){return!S},this.isCollapsed=function(){return o},this.hideAdvSearchPanel=function(){a(f).find("li[aria-controls=adv_search]").remove(),S=!0},this.setSearchFlag=function(t){var l=this.find("a[href='#basic_search']"),n=this.find("a[href='#adv_search']");l.removeClass("active-label"),n.removeClass("active-label");var h=!1;for(var s in t)s==="country"&&t[s].value==TTUUID.not_exist_id||a.inArray(s,["private","template","is_attachment"])!==-1&&t[s].value==!1||s==="hierarchy_level"&&(t[s].value==1||t[s].value.value==1)||(h=!0);h?(a(this).find(".search-flag").remove(),d===0||S?(l.addClass("active-label"),l.html(a.i18n._("BASIC SEARCH")+"<img title='"+a.i18n._("Search is currently active")+"' src='"+Global.getRealImagePath("css/global/widgets/ribbon/icons/alert-16x16.png")+"' class='search-flag'> </img>")):(n.addClass("active-label"),n.html(a.i18n._("ADVANCED SEARCH")+"<img title='"+a.i18n._("Search is currently active")+"' src='"+Global.getRealImagePath("css/global/widgets/ribbon/icons/alert-16x16.png")+"' class='search-flag'> </img>"))):a(this).find(".search-flag").remove()},this.setSelectTabIndex=function(t,l){d!==t&&(Global.isSet(l)?m=l:m=!0,a(f).tabs("option","active",t))},this.getLastSelectTabIndex=function(){return _},this.getLastSelectTabId=function(){return y?a(y.tab).attr("ref"):"basic_search"},this.getSelectTabIndex=function(){return d},this.getLayoutsArray=function(){return i};function B(){e.grid&&(e.setGridSize(),e.setGridColumnsWidth(),e.column_selector&&e.column_selector.setGridColumnsWidths())}return this.setLayoutsArray=function(t){i=t;var l=a(this).find("#layout_selector"),n=a(this).find(".layout-selector-div");if(a(l).empty(),i&&i.length>0){for(var h=i.length,s=0;s<h;s++){var v=i[s];a(l).append(a('<option value="'+v.id+'"></option>').text(v.name))}a(a(l).find("option")).filter(function(){return a(this).attr("value")==e.select_layout.id}).prop("selected",!0).attr("selected",!0),a(n).css("display","block")}else a(n).css("display","none")},this.setReleatedViewController=function(t){e=t},this.each(function(){var t=a.meta?a.extend({},A,a(this).data()):A,l=a(this).find("a[href='#basic_search']"),n=a(this).find("a[href='#adv_search']"),h=a(this).find("a[href='#saved_layout']"),s=a(this).find(".current-view-label");l.html(a.i18n._("BASIC SEARCH")),n.html(a.i18n._("ADVANCED SEARCH")),h.html(a.i18n._("SAVED SEARCH & LAYOUT")),s.html(a.i18n._("Current View")),f=a(this).find(".search-panel-tab-bar");var v=a(this).find("#collapseBtn"),T=a(this).find(".search-panel-tab-outside"),I=a(this).find(".search-panel-tab"),G=a(this).find("#layout_selector"),b=a(this).find(".search-btn-div"),D=a(this).find("#refreshBtn");D.bind("click",function(){D.addClass("button-rotate"),e.search(),Global.triggerAnalyticsNavigationOther("click:refresh_data","click",e.viewId)}),a(b).css("display","none");var P=a(b).find("#searchBtn"),V=a(b).find("#clearSearchBtn");P.find("span:nth-child(2)").text(a.i18n._("Search")),V.find("span:nth-child(2)").text(a.i18n._("Clear Search")),P.click(function(){setTimeout(function(){e.onSearch()},100)}),V.click(function(){e.onClearSearch()}),e=t.viewController,a(G).on("change",function(){e.layout_changed=!0;for(var p=a(G).find("option:selected").attr("value"),u=i.length,c=0;c<u;c++){var r=i[c];if(r.id==p){e.select_layout=r,e.setSelectLayout(),e.search();break}}Global.triggerAnalyticsNavigationOther("searchpanel:layout_change","click",e.viewId)}),a(f).tabs({activate:E}),a(f).find("li").click(function(p){o&&C()});function E(p,u){if(_=d,y=x,d=u.newTab.index(),x=u,_!==d){var c=e.basic_search_field_ui_dic;w.isAdvTabVisible()&&_===1&&(c=e.adv_search_field_ui_dic);var r={};a.each(c,function(g){c[g].getValue(!0)&&(r[g]={field:g,id:"",value:c[g].getValue(!0)})}),(e.filter_data===null||e.filter_data===void 0)&&(e.filter_data={}),r=Object.assign(e.filter_data,r),e.filter_data=a.extend(!0,{},r),e.setSearchPanelFilter()}m?w.trigger("searchTabSelect",[p,u.newPanel]):m=!0,o&&C(),B(),Global.triggerAnalyticsTabs(p,u)}a(v).click(C);function C(){o?(o=!1,a(v).removeClass("pi-chevron-down").addClass("pi-chevron-up"),a(T).removeClass("search-panel-tab-outside-collapse"),a(I).removeClass("search-panel-tab-collapse"),a(b).css("display","block")):(o=!0,a(v).removeClass("pi-chevron-up").addClass("pi-chevron-down"),a(T).addClass("search-panel-tab-outside-collapse"),a(I).addClass("search-panel-tab-collapse"),a(b).css("display","none")),B()}}),this},a.fn.SearchPanel.defaults={},a.fn.SearchPanel.html={form_item:'<div class="form-item-div"><span class="form-item-label"></span><div class="form-item-input-div"></div></div>',search_panel:`
			<div class="search-panel" xmlns="http://www.w3.org/1999/html">
				<div class="search-panel-tab-bar" id="search_panel_tab_bar">
					<ul class="search-panel-tab-bar-label">
						<li><a ref="basic_search" href="#basic_search"></a></li>
						<li><a ref="adv_search" href="#adv_search"></a></li>
						<li><a ref="saved_layout" href="#saved_layout"></a></li>
					</ul>
					<div id="basic_search" class="search-panel-tab-outside search-panel-tab-outside-collapse">
						<div class="search-panel-tab search-panel-tab-collapse" id="basic_search_content_div">
							<div class="first-column"></div>
							<div class="second-column"></div>
							<div class="third-column"></div>
						</div>
					</div>
					<div id="adv_search" class="search-panel-tab-outside search-panel-tab-outside-collapse">
						<div class="search-panel-tab search-panel-tab-collapse" id="adv_search_content_div">
							<div class="first-column"></div>
							<div class="second-column"></div>
							<div class="third-column"></div>
						</div>
					</div>
					<div id="saved_layout" class="search-panel-tab-outside search-panel-tab-outside-collapse">
						<div class="search-panel-tab search-panel-tab-collapse" id="saved_layout_content_div">
						</div>
					</div>
				</div>
				<button id="collapseBtn" class="collapse-btn pi pi-chevron-down"></button>
				<span id="refreshBtn" class="tticon tticon-refresh_black_24dp refresh-btn"></span>
				<div class="layout-selector-div">
					<span class="current-view-label"></span>
					<select id="layout_selector" class="t-select layout-selector">
					</select>
				</div>
				<div class="search-btn-div">
					<button id="searchBtn" class="tt-button p-button p-component" type="button">
						<span class="tticon tticon-search_black_24dp"></span>
						<span class="p-button-label"></span>
					</button>
					<button id="clearSearchBtn" class="tt-button p-button p-component" type="button">
						<span class="tticon tticon-cancel_black_24dp"></span>
						<span class="p-button-label"></span>
					</button>
				</div>
			</div>
		`}})(k);
//# sourceMappingURL=SearchPanel-CQFHXzWB.bundle.js.map
