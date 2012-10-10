/* Backbone settings
* ====================== */

window.App = {};

/* Spin settings
* ====================== */

App.spin_options = {
  lines: 9, 
  length: 4,
  width: 2,
  radius: 4,
  corners: 1,
  color: '#555',
  speed: 2,
  trail: 54,
  shadow: false,
  hwaccel: false,
  top: 10,
  left: 0,
  className: 'spin',
  zIndex: 1051,
};

App.spin_options_center = {
  lines: 9, 
  length: 4,
  width: 2,
  radius: 4,
  corners: 1,
  color: '#555',
  speed: 2,
  trail: 54,
  shadow: false,
  hwaccel: false,
  top: 0,
  left: 'auto',
  className: 'spin',
  zIndex: 1051,
};

/* Fancybox settings
* ====================== */

App.fancy_options = {
	padding: 10,
	tpl: {
		closeBtn: '<a title="" class="fancy-close" href="javascript:;">&times;</a>',
		next: '<a title="" class="fancy-nav right" href="javascript:;"><span>&rsaquo;</span></a>',
		prev: '<a title="" class="fancy-nav left" href="javascript:;"><span>&lsaquo;</span></a>',		
	},
  afterLoad: function() {
    this.outer.prepend('<div class="fancy-header"><h5>' + (this.title ? this.title + ' - ' : '') + '<small>' + (this.index + 1) + ' / ' + this.group.length + '</small></h5></div>');
	},	
	helpers: {
		overlay : {
			closeClick: true,
			speedOut: 50,
			showEarly: true,
			css: {
				background: 'rgba(0, 0, 0, 0.8)',		
			},
		},	
	},		
}

/* Doc ready
* ====================== */

$(function() {		  
  // enable tooltips
  $('[rel="tooltip"]').tooltip();
});