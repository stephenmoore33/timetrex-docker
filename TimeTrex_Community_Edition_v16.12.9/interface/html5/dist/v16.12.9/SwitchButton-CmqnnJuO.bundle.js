import{j as u}from"./vendor-tTApdY0Y.bundle.js";(function(s){s.fn.SwitchButton=function(r){Global.addCss("global/widgets/switch_button/SwitchButton.css");var l=s.extend({},s.fn.SwitchButton.defaults,r),n=this,t=null,i=!0;return this.getEnabled=function(){return i},this.setEnable=function(e){i=e,e?this.removeClass("disable-element"):this.removeClass("disable-element").addClass("disable-element")},this.getValue=function(e){return e?t&&t.hasClass("selected")?1:0:!!(t&&t.hasClass("selected"))},this.setValue=function(e){t&&(t.removeClass("selected"),e&&t.addClass("selected"))},this.setIcon=function(e){t.addClass(e)},this.each(function(){var e=s.meta?s.extend({},l,s(this).data()):l;t=s("<div></div>"),n.append(t),e.tooltip&&t.attr("title",e.tooltip),n.setIcon(e.icon),t.click(function(o){if(!i){o.stopImmediatePropagation(),o.stopPropagation();return}n.setValue(!n.getValue())})}),this},s.fn.SwitchButton.defaults={}})(u);var a=function(){};a.daily_total="daily";a.weekly_total="weekly";a.all_employee="all-employee";a.strict_range="strict-range";a.wages="strict-range";export{a as SwitchButtonIcon};
//# sourceMappingURL=SwitchButton-CmqnnJuO.bundle.js.map
