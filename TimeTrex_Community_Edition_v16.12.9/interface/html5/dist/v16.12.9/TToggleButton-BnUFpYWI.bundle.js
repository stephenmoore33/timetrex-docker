import{j as f}from"./vendor-tTApdY0Y.bundle.js";(function(e){e.fn.TToggleButton=function(i){Global.addCss("global/widgets/toggle_button/ToggleButton.css");var g=e.extend({},e.fn.TToggleButton.defaults,i),o=[],s=this,d={},l;return this.getValue=function(){return l?l.val():null},this.setValue=function(n){l&&l.removeClass("selected"),l=d[n],l&&l.addClass("selected")},this.each(function(){var n=e.meta?e.extend({},g,e(this).data()):g;o=n.data_provider,d={};for(var r=o.length,a=0;a<r;a++){var u=o[a],t=e("<button></button>");a===0?t.addClass("toggle-button first"):a===r-1?t.addClass("toggle-button last"):a===0&&a===r-1?t.addClass("toggle-button first-last"):t.addClass("toggle-button middle"),d[u.value]=t,t.val(u.value),t.text(u.label),t.click(function(){s.setValue(e(this).val()),s.trigger("change",[s.getValue()])}),s.append(t)}}),this},e.fn.TToggleButton.defaults={}})(f);
//# sourceMappingURL=TToggleButton-BnUFpYWI.bundle.js.map
