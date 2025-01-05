import{j as I}from"./vendor-tTApdY0Y.bundle.js";(function(a){a.fn.TFeedback=function(y){var e=a.extend({source:"",force_source:!1,delay:0,manual_trigger:!1,prompt_for_feedback:!1,review_link:"https://coreapi.timetrex.com/r?id=review&product_edition_id="+Global.getProductEdition()},y),d=Global.loadWidgetByName(FormItemType.FEEDBACK_BOX),k=d.find(".top-bar-title"),g=d.find(".feedback-page"),r=d.find(".feedback-page.default"),b=d.find(".feedback-page.positive"),s=d.find(".feedback-page.negative"),m=TTAPI.APIUser,c={POSITIVE:"postitive",NEUTRAL:"neutral",NEGATIVE:"negative"};function x(){LocalCacheData.getLoginUser()&&LocalCacheData.getLoginUser().prompt_for_feedback==!0&&(e.prompt_for_feedback=!0,LocalCacheData.getLoginUser().prompt_for_feedback=!1),e.prompt_for_feedback&&(e.force_source||(e.source=LocalCacheData.current_open_view_id+"@"+e.source),f("default"),e.delay&&e.delay>0?F(e.delay):v())}function n(){return e.manual_trigger?"click":"popup"}function F(t){t=t||0,Debug.Text("Setting feedback display delay to "+t,"TFeedback.js","TFeedback","initDefaultPage",10),setTimeout(function(){Debug.Text("Triggering delayed feedback display","TFeedback.js","TFeedback","initDefaultPage",10),v()},t)}function v(){a(".feedback-container").length==0?a("body").append(d):Debug.Text("ERROR: Feedback container already exists, halting to prevent duplicate popups.","TFeedback.js","TFeedback","initDefaultPage",1)}function p(){Global.isSet(d)&&d.remove()}function w(){k.html(a.i18n._("Feedback")),r.find(".page-text").text(a.i18n._("Tell us what you think about TimeTrex?")),r.find(".positive-button").html(a.i18n._("It's great!")).bind("click",function(){f("positive"),Debug.Text("Feedback Analytics: Category: feedback, Action: "+n()+", Label: "+n()+":feedback:"+e.source+":"+c.POSITIVE,"TFeedback.js","TFeedback","initDefaultPage",10),Global.sendAnalyticsEvent("feedback",n(),n()+":feedback:"+e.source+":"+c.POSITIVE),u(c.POSITIVE,"")}),r.find(".negative-button").html(a.i18n._("Not so great")).bind("click",function(){f("negative"),Debug.Text("Feedback Analytics: Category: feedback, Action: "+n()+", Label: "+n()+":feedback:"+e.source+":"+c.NEGATIVE,"TFeedback.js","TFeedback","initDefaultPage",10),Global.sendAnalyticsEvent("feedback",n(),n()+":feedback:"+e.source+":"+c.NEGATIVE),u(c.NEGATIVE,"")});var t;if(e.manual_trigger)t=a.i18n._("Close");else var t=a.i18n._("Ask me later");r.find(".cancel-button").html(t).click(function(){p(),Debug.Text("Feedback Analytics: Category: feedback, Action: "+n()+", Label: "+n()+":feedback:"+e.source+":"+c.NEUTRAL,"TFeedback.js","TFeedback","initDefaultPage",10),Global.sendAnalyticsEvent("feedback",n(),n()+":feedback:"+e.source+":"+c.NEUTRAL),u(c.NEUTRAL,"")}),Debug.Text("Feedback Analytics: Category: feedback, Action: "+n()+", Label: "+n()+":feedback:"+e.source,"TFeedback.js","TFeedback","initDefaultPage",10),Global.sendAnalyticsEvent("feedback",n(),n()+":feedback:"+e.source)}function A(){var t=c.POSITIVE;k.html(a.i18n._("Feedback")),b.find(".page-text.block1").text(a.i18n._("It thrills us to hear you think TimeTrex is great!")),b.find(".page-text.block2").text(a.i18n._("Share your experience and WIN a tasty lunch for your team!")),b.find(".page-text.block3").text(a.i18n._("We'll select one winner each month.")),b.find(".openReviewPageButton").html(a.i18n._("Share experience")).bind("click",function(){Debug.Text("Feedback Analytics: Category: feedback, Action: "+n()+"-Link, Label: submit:feedback:"+e.source+":"+t,"TFeedback.js","TFeedback","initPositivePage",10),Global.sendAnalyticsEvent("feedback",n()+"-link","submit:"+n()+":feedback:"+e.source+":"+t),u(t,"",!1),_(1),window.open(e.review_link,"_blank","review_link")}),b.find(".cancel-button").html(a.i18n._("I'm not hungry")).click(function(){_(0),Debug.Text("Feedback: Category: feedback, Action: cancel, Label: cancel:feedback:"+e.source+":"+t,"TFeedback.js","TFeedback","cancelButtonClick",10),Global.sendAnalyticsEvent("feedback","cancel","cancel:feedback:"+e.source+":"+t)})}function E(){var t=c.NEGATIVE,i=P(),o=s.find(".feedback-messagebox"),l=s.find(".feedback-email"),h=s.find(".feedback-phone");k.html(a.i18n._("Feedback")),s.find(".page-text").html(a.i18n._("We're all ears!<br>What improvements do you think we should make?")),s.find(".contact-notice-text").html(a.i18n._("What is the best way to contact you?")),s.find(".email-label-text").html(a.i18n._("Email")),s.find(".phone-label-text").html(a.i18n._("Phone")),l.val(i.user_email),h.val(i.user_phone),s.find(".sendButton").html(a.i18n._("Send")).click(D),s.find(".cancel-button").html(a.i18n._("Back")).click(function(){f("default"),Debug.Text("Feedback: Category: feedback, Action: cancel, Label: cancel:feedback:"+e.source+":"+t,"TFeedback.js","TFeedback","cancelButtonClick",10),Global.sendAnalyticsEvent("feedback","cancel","cancel:feedback:"+e.source+":"+t)});function D(){var T="";o.val().length>0&&(T=o.val()+`
Email: `+l.val()+`
Phone: `+h.val()),Debug.Text("Feedback Analytics: Category: feedback, Action: submit, Label: submit:feedback:"+e.source+":"+t,"TFeedback.js","TFeedback","initNegativePage._sendForm",10),Global.sendAnalyticsEvent("feedback","submit","submit:feedback:"+e.source+":"+t),u(t,T,!0)}}function f(t){switch(t){case"positive":A(),g.hide(),b.show();break;case"negative":E(),g.hide(),s.show();break;default:w(),g.hide(),r.show()}}function P(){var t=TTAPI.APIAuthentication,i=t.getCurrentUser({async:!1});i=i.getResult();var o;i.work_email!=!1&&i.work_email!=""?o=i.work_email:i.home_email!=!1&&(o=i.home_email);var l;return i.work_phone!=!1&&i.work_phone!=""?l=i.work_phone:i.home_phone!=!1&&(l=i.home_phone),{user_email:o,user_phone:l}}function u(t,i,o){e.source&&(i+=`

Feedback source: `+e.source),m.setUserFeedbackRating(t,i,{onResult:function(l){l&&l.isValid()&&o&&p()}})}function _(t,i){m.setUserFeedbackReview(t,{onResult:function(o){o&&o.isValid()&&p()}})}return x(),this},a.fn.TFeedback.html_template=`
	<div class="feedback-overlay">
		<div class="feedback-container">
			<div class="top-bar-title"></div>
			<div class="context-box">
				<div class="feedback-page default">
					<img class="top-image" src="theme/default/css/global/widgets/feedback/images/dogs_listening_with_head_turned_optimized.png">
					<p class="page-text"></p>
					<button class="feedback-button btn btn-success positive-button"></button>
					<button class="feedback-button btn negative-button"></button>
					<button class="feedback-button btn cancel-button"></button>
				</div>
				<div class="feedback-page positive">
					<img class="top-image" src="theme/default/css/global/widgets/feedback/images/dog_with_knife_and_fork_sm_optimized.png">
					<p class="page-text block1"></p>
					<p class="page-text block2"></p>
					<p class="page-text block3"></p>
					<button class="feedback-button btn btn-success positive-button openReviewPageButton"></button>
					<button class="feedback-button btn cancel-button"></button>
				</div>
				<div class="feedback-page negative">
					<img class="top-image" src="theme/default/css/global/widgets/feedback/images/dog_with_big_ears_optimized.png">
					<p class="page-text"></p>
					<textarea class="feedback-messagebox"></textarea>
					<div class="user-contact-details">
						<div class="contact-notice-text"></div>
						<div class="row"><span class="email-label-text" style="width:40px;"></span> <input type="email" class="feedback-email" placeholder="Email" style="width:20em;line-height:15px"></div>
						<div class="row"><span class="phone-label-text" style="width:40px;"></span> <input type="phone" class="feedback-phone" placeholder="Phone" style="width:20em;line-height:15px"></div>
					</div>
					<button class="feedback-button btn btn-success positive-button sendButton"></button>
					<button class="feedback-button btn cancel-button"></button>
					<!--<div class="bottom-bar">-->
					<!--<button class="feedback-button sendButton"></button>-->
					<!--<button class="feedback-button cancel-button"></button>-->
					<!--</div>-->
				</div>
			</div>
		</div>
	</div>
	`})(I);
//# sourceMappingURL=TFeedback-yz2B1x87.bundle.js.map
