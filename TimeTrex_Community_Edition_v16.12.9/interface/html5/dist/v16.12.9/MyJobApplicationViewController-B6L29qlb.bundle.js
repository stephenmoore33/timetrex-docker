import{_ as c,j as i}from"./vendor-tTApdY0Y.bundle.js";import{b as _,T as v}from"./main_ui-z76rxVqJ.entry.bundle.js";class u extends _{constructor(a={}){c.defaults(a,{el:"#my_job_application_view",events:{"click #available_jobs":"availableJobsClick"}}),super(a)}initialize(a){super.initialize(a),this.viewId="MyJobApplication",this.api=v.APIJobApplicantPortal,this.$(".search-result .content").empty(),this.search()}availableJobsClick(){window.location=Global.getBaseURL(null,!0,!0).replace(/#!m=.*?(&|$)/g,"#!m=PortalJobVacancy$1"),LocalCacheData.setAllURLArgs({})}getFilterColumnsFromDisplayColumns(){var a={};return a.id=!0,a.job_vacancy=!0,a.job_vacancy_id=!0,a.interview_date=!0,a.created_date=!0,a}search(a,m,g){var e=this,t={};this.vacancy_list_panel=this.$(".search-result .content"),this.more_btn=this.$(".search-result .more"),this.more_btn.unbind("click").bind("click",function(){e.loadMore()}),t.filter_data={},t.filter_sort={},t.filter_columns=this.getFilterColumnsFromDisplayColumns(),t.filter_items_per_page=0,this.pager_data&&a==="next"?t.filter_page=this.pager_data.next_page:t.filter_page=1,this.api.getJobApplication(t,!1,{onResult:function(o){var s=o.getResult();if(Global.isArray(s)){for(var n=0;n<s.length;n++){var r=s[n],l=Global.loadWidget("views/portal/hr/my_jobapplication/MyJobApplicationRow.html"),b={job_vacancy:i.i18n._(r.job_vacancy),created_date:i.i18n._(r.created_date)};l=i(c.template(l)(b));var d=Global.getBaseURL(null,!0,!0).replace(/#!m=.*?(&|$)/g,"#!m=PortalJobVacancyDetail&id="+r.job_vacancy_id+"$1");i(l).find(".row-vacancy-title > span > a").attr("href",d),e.vacancy_list_panel.append(l)}e.pager_data=o.getPagerData(),e.pager_data.is_last_page?e.more_btn.hide():e.more_btn.show()}else{var p=i('<span class="vacancy-list-no-result">'+i.i18n._("No Results Found")+"</span>");e.vacancy_list_panel.append(p),e.vacancy_list_panel.css("text-align","center"),e.more_btn.hide()}}})}loadMore(){this.search("next")}}u.html_template=`
<div id="my_job_application_view">
	<div class="job-applicant-navbar">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-sm-12 col-xs-12">
					<button id="available_jobs" class="btn btn-block btn-default w-100"><span class="glyphicon glyphicon-menu-left"></span> <%= $.i18n._('Available Jobs') %></button>
				</div>
			</div>
		</div>
	</div>
	<div class="search-result">
		<div class="content container">
		</div>
		<div class="more container">
			<div class="row">
				<button type="button" class="btn btn-primary btn-lg btn-block"><%= $.i18n._('Loading') %>...</button>
			</div>
		</div>
	</div>
</div>
`;export{u as MyJobApplicationViewController};
//# sourceMappingURL=MyJobApplicationViewController-B6L29qlb.bundle.js.map
