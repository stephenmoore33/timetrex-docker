import{_ as r,j as t}from"./vendor-tTApdY0Y.bundle.js";class s extends BaseWizardController{constructor(e={}){r.defaults(e,{el:"#report-view-wizard"}),super(e)}init(e){this.title=t.i18n._("Report View"),this.steps=1,this.current_step=1,this.render()}render(){super.render(),this.initCurrentStep()}buildCurrentStepUI(){switch(this.stepsWidgetDic[this.current_step]={},this.current_step){case 1:this.content_div.children().eq(0)[0].contentWindow.document.open(),this.default_data.post_data?(this.content_div.children().eq(0)[0].contentWindow.post_data=this.default_data.post_data,this.content_div.children().eq(0)[0].contentWindow.document.writeln(this.default_data.result)):this.content_div.children().eq(0)[0].contentWindow.document.writeln(this.default_data),this.content_div.children().eq(0)[0].contentWindow.document.close();break}}onCloseClick(){t(this.el).remove(),LocalCacheData.current_open_wizard_controllers=LocalCacheData.current_open_wizard_controllers.filter(i=>i.wizard_id!==this.wizard_id);var e="View";LocalCacheData.getAllURLArgs()&&LocalCacheData.getAllURLArgs().sm&&(e=LocalCacheData.getAllURLArgs().sm+"@View"),t().TFeedback({source:e,force_source:!0})}onDoneClick(){this.onCloseClick()}}export{s as ReportViewWizardController};
//# sourceMappingURL=ReportViewWizardController-D5LedxDY.bundle.js.map
