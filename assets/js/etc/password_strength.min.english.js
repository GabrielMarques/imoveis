/**
 * Password Strength - jQuery plugin to check password strength
 * http://labs.rnzmedia.co.za/
 * @requires jQuery Library: http://jquery.com/
 * 
 * Copyright (c) 2011 Riaz Sabjee
 * 
 * Dual licensed under the MIT and GPL licenses:
 * http://www.gnu.org/licenses/gpl.html
 * 
 */

(function (b) {
	b.fn.extend({
		password: function (e) {
    	e = b.extend({
        score: ""
    	}, e);
    	return this.each(function () {
    		var g = e;
    		b(this).on('keyup', function () {
    			var c = b(this).val(),
    			f = g.score,
    			e = "Very weak,Weak,Medium,Strong,Very strong".split(","),
    			a = 0;
    			c.length > 6 && a++;
    			c.match(/[a-z]/) && c.match(/[A-Z]/) && a++;
    			c.match(/\d+/) && a++;
    			c.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) && a++;
    			//c.length > 12 && a++;
    			if (a == 0) var d = "label label-important";
					a == 1 && (d = "label label-warning");
					a == 2 && (d = "label");
					a == 3 && (d = "label label-info");
					a == 4 && (d = "label label-success");
					//a == 5 && (d = "#bde813");
					c.length > 0 ? (b(f).attr("class", d), b(f).html(e[a])) : (b(f).attr("class", ""), b(f).html(""))
    		})
    	})
		}
	})
})(jQuery);

$(document).ready(function() {	
  // password strength
  $('input[name="password"]').password({
    score: '#password-score',
  });
});