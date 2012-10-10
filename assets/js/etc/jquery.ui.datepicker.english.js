jQuery(function($){
	$.datepicker.regional['english'] = {
		closeText: 'Close',
		prevText: '&#x3c;Previous',
		nextText: 'Next&#x3e;',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June',
		'July','August','September','October','November','December'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','May','Jun',
		'Jul','Aug','Sep','Oct','Nov','Dec'],
		dayNames: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
		dayNamesShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
		dayNamesMin: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
		weekHeader: 'Sm',
		dateFormat: 'yy-mm-dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['english']);
});

$(document).ready(function() {
	
	// Datepicker
	$.datepicker.setDefaults($.datepicker.regional['']);
	$('.datepicker').datepicker($.datepicker.regional['english']);
	$('#ui-datepicker-div').wrap('<div class="ui-theme" />');

});