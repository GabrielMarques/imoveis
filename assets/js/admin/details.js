$(function(){
  
  // App view
  // ---------------

  App.Details_view = Backbone.View.extend({

    el: $('#details-app'),

    events: {
  		'click .action-modal': 'action_modal',
			'click .confirm-btn': 'confirm_modal',  		  		
    },

    initialize: function() {    	
    	// pretty print
    	//prettyPrint();
    	
      // fancybox
      this.$('.enlarge').fancybox(App.fancy_options);    	
    },  
    
    // show modal
    action_modal: function(e) {
    	e.preventDefault();
    	var btn = this.$(e.target);  	
    	var target = btn.attr('data-target');

    	this.$(target).find('.modal-form').attr('action',  btn.attr('href'));
    	this.show_modal(target);
    },    
   
    // show modal
    show_modal: function(target) {
      this.$(target).modal({'show': true, 'backdrop': true});
    },    
 
    // close modal
    confirm_modal: function(e) {
    	// do not disable if data-action starts with 'export'
    	this.$(e.target).button('loading');
    	this.$('.modal').on('hide', function () {
    	  $(this).stopPropagation();
    	});    		    		
    },       
    
  });   
  
  /* Init code
  * ====================== */
  
  // Main app
  App.Details = new App.Details_view;	
    
});