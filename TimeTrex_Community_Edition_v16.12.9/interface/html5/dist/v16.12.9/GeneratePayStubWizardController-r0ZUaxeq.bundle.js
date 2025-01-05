import{_ as h,j as s}from"./vendor-tTApdY0Y.bundle.js";class D extends BaseWizardController{constructor(i={}){h.defaults(i,{el:".wizard-bg"}),super(i)}init(i){this.title=s.i18n._("Generate Pay Stub Wizard"),this.steps=3,this.current_step=1,this.render()}render(){super.render(),this.initCurrentStep()}buildCurrentStepUI(){var i=this;switch(this.content_div.empty(),this.stepsWidgetDic[this.current_step]={},this.current_step){case 1:var t=this.getLabel();t.text(s.i18n._("Generate pay stubs for individual employees when manual modifications or a termination occurs. Use Payroll -> Process Payroll if you wish to generate pay stubs for all employees instead.")),this.content_div.append(t);break;case 2:t=this.getLabel(),t.text(s.i18n._("Select one or more pay periods and choose a payroll run type")),this.content_div.append(t);var e=s(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),a=e.find(".form-item-label"),r=e.find(".form-item-input-div"),o=this.getAComboBox(TTAPI.APIPayPeriod,!0,"global_Pay_period","pay_period_id");o.unbind("formItemChange").bind("formItemChange",function(u,p){i.saveCurrentStep(),i.onPayPeriodChange(!0),i.setPayRun(p.getValue())}),a.text(s.i18n._("Pay Period")),r.append(o),this.content_div.append(e),this.stepsWidgetDic[this.current_step][o.getField()]=o,e=s(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),a=e.find(".form-item-label"),r=e.find(".form-item-input-div");var n=this.getComboBox("type_id",!1);a.text(s.i18n._("Payroll Run Type")),r.append(n),this.content_div.append(e),this.stepsWidgetDic[this.current_step][n.getField()]=n,n.unbind("formItemChange").bind("formItemChange",function(u,p){i.saveCurrentStep(),i.onPayrollTypeChange(!0)}),e=s(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),a=e.find(".form-item-label"),r=e.find(".form-item-input-div");var d=this.getDatePicker("carry_forward_to_date");a.text(s.i18n._("Carry Forward Adjustments to")),r.append(d),this.content_div.append(e),this.stepsWidgetDic[this.current_step][d.getField()]=d,this.stepsWidgetDic[this.current_step][d.getField()+"_row"]=e,e.hide(),e=s(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),a=e.find(".form-item-label"),r=e.find(".form-item-input-div"),d=this.getDatePicker("transaction_date"),a.text(s.i18n._("Transaction Date")),r.append(d),this.content_div.append(e),this.stepsWidgetDic[this.current_step][d.getField()]=d,this.stepsWidgetDic[this.current_step][d.getField()+"_row"]=e,e.hide(),e=s(Global.loadWidget("global/widgets/wizard_form_item/WizardFormItem.html")),a=e.find(".form-item-label"),r=e.find(".form-item-input-div");var l=Global.loadWidgetByName(FormItemType.TEXT_INPUT);l=l.TTextInput({field:"run_id",width:20}),a.text(s.i18n._("Payroll Run")),r.append(l),this.content_div.append(e),this.stepsWidgetDic[this.current_step][l.getField()]=l,this.stepsWidgetDic[this.current_step][l.getField()+"_row"]=e,e.hide();break;case 3:t=this.getLabel(),t.text(s.i18n._("Select one or more employees")),o=this.getAComboBox(TTAPI.APIUser,!0,"global_user","user_id",!0);var _=s("<div class='wizard-acombobox-div'></div>");_.append(o),this.stepsWidgetDic[this.current_step]={},this.stepsWidgetDic[this.current_step][o.getField()]=o,this.content_div.append(t),this.content_div.append(_);break}}setPayRun(i){var t=TTAPI.APIPayStub,e=this.stepsWidgetDic[2];t.getCurrentPayRun(i,{onResult:function(a){var r=a.getResult();e.run_id.setValue(r)}})}buildCurrentStepData(){var i=this,t=this.stepsDataDic[this.current_step],e=this.stepsWidgetDic[this.current_step];if(t&&e)switch(this.current_step){case 2:if(t.pay_period_id){var a=t.pay_period_id;a=Global.array_unique(a),t&&e.pay_period_id.setValue(a),i.setPayRun(a)}this.onPayPeriodChange();break;case 3:if(t.user_id){var r=t.user_id;r=Global.array_unique(r),e.user_id.setValue(r)}break}}onDoneClick(){var i=this;super.onDoneClick(),this.saveCurrentStep(),(!this.stepsDataDic||!this.stepsDataDic[2]||!this.stepsDataDic[3])&&(TAlertManager.showAlert(s.i18n._("Wizard data is not correct on step 2 or step 3, please open wizard and try again")),i.onCloseClick());var t=TTAPI.APIPayStub,e=this.stepsDataDic[2].pay_period_id,a=this.stepsDataDic[3].user_id,r=this.stepsDataDic[2].type_id,o=this.stepsDataDic[2].run_id,n=null,d=!1;r==5?(n=this.stepsDataDic[2].carry_forward_to_date,d=!0):n=this.stepsDataDic[2].transaction_date,t.setIsIdempotent(!0),t.generatePayStubs(e,a,d,o,r,n,{onResult:l});function l(_){if(_&&_.isValid()){var u=_.getAttributeInAPIDetails("user_generic_status_batch_id");u&&TTUUID.isUUID(u)&&u!=TTUUID.zero_id&&u!=TTUUID.not_exist_id&&UserGenericStatusWindowController.open(u,a,function(){if(d){var p={filter_data:{}},c={value:a};p.filter_data.user_id=c,p.filter_data.status_id=50,IndexViewController.goToView("PayStubAmendment",p)}})}i.onCloseClick(),i.call_back&&i.call_back()}i.onCloseClick()}onPayrollTypeChange(i){var t=this.stepsWidgetDic[this.current_step],e=this.stepsDataDic[this.current_step];if(!(!t||!t.run_id_row||!t.carry_forward_to_date_row||!t.transaction_date_row)){t.run_id_row.hide(),t.carry_forward_to_date_row.hide(),t.transaction_date_row.hide();var a=this.getNewestPayPeriod(this.selected_pay_periods);e.type_id==20&&t.run_id_row.show(),e.type_id!=5&&(t.transaction_date_row.show(),i?t.transaction_date.setValue(a?Global.strToDateTime(a.transaction_date).format():null):e.transaction_date?t.transaction_date.setValue(Global.strToDateTime(e.transaction_date).format()):t.transaction_date.setValue(a?Global.strToDateTime(a.transaction_date).format():null)),e.type_id==5&&(t.carry_forward_to_date_row.show(),i?t.carry_forward_to_date.setValue(new Date().format()):t.carry_forward_to_date.setValue(e.carry_forward_to_date||new Date().format()))}}buildPayPeriodStatusIdArray(i){for(var t=[],e=0;e<i.length;e++){var a=i[e];t.push(a.status_id)}return t}getNewestPayPeriod(i){for(var t,e=0;e<i.length;e++){var a=i[e],r=Global.strToDateTime(a.transaction_date).getTime();(!t||r>t)&&(t=a)}return t}onPayPeriodChange(i){var t=this,e=this.stepsWidgetDic[this.current_step],a=this.stepsDataDic[this.current_step],r=TTAPI.APIPayStub,o=TTAPI.APIPayPeriod,n={};n.filter_data={},n.filter_data.id=a.pay_period_id,(!a.pay_period_id||a.pay_period_id.length===0)&&(n.filter_data.id=[0]),o.getPayPeriod(n,{onResult:function(d){t.selected_pay_periods=d.getResult();var l=t.buildPayPeriodStatusIdArray(t.selected_pay_periods);r.getOptions("payroll_run_type",l,{onResult:function(_){var u=_.getResult(),p=Global.buildRecordArray(u);e.type_id.setSourceData(p),i?(a.type_id=p&&p[0].value,e.type_id.setValue(a.type_id)):(a.type_id||(a.type_id=p&&p[0].value),e.type_id.setValue(a.type_id)),t.onPayrollTypeChange(i)}})}})}saveCurrentStep(){this.stepsDataDic[this.current_step]={};var i=this.stepsDataDic[this.current_step],t=this.stepsWidgetDic[this.current_step];switch(this.current_step){case 1:break;case 2:i.pay_period_id=t.pay_period_id.getValue(),i.transaction_date=t.transaction_date.getValue(),i.carry_forward_to_date=t.carry_forward_to_date.getValue(),i.type_id=t.type_id.getValue(),i.run_id=t.run_id.getValue();break;case 3:i.user_id=t.user_id.getValue();break}}setDefaultDataToSteps(){if(!this.default_data)return null;this.stepsDataDic[2]={},this.stepsDataDic[3]={},this.getDefaultData("user_id")&&(this.stepsDataDic[3].user_id=this.getDefaultData("user_id")),this.getDefaultData("pay_period_id")&&(this.stepsDataDic[2].pay_period_id=this.getDefaultData("pay_period_id"))}}export{D as GeneratePayStubWizardController};
//# sourceMappingURL=GeneratePayStubWizardController-r0ZUaxeq.bundle.js.map
