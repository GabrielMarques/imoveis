$(function(){
  
  // App view
  // ---------------

  App.Form_view = Backbone.View.extend({

    el: $('#insert-update-app'),

    events: {
  		'click .has-many-add': 'add_has_many',
  		'click .has-many-remove': 'remove_has_many',
  		'keyup .count-chars': 'count_chars',
    },

    initialize: function() {
    },  
    
  	// add has many element
    add_has_many: function(e){
    	e.preventDefault();
    	var controls = this.$(e.target).parents('.controls');
    	
    	if (controls.find('.has-many').length < 25){
  	  	var new_has_many = controls.find('.has-many:first').clone();
  	  	
  	  	var remove_btn = $('<button type="button" class="btn btn-danger btn-small has-many-remove"><i class="icon-minus icon-white"></i></button>');
  	  	//remove_btn.on('click', has_many_remove);
  	  	new_has_many.find('.has-many-add').replaceWith(remove_btn);
  			new_has_many.find(':input').each(function(key, value){
  				$(this).val('');	  	
  	  	});
  	  	controls.find('.has-many:last').after(new_has_many);
    	}    	
    }, 
    
    // remove has many element
    remove_has_many: function(e){
  		e.preventDefault();    	
  		this.$(e.target).parents('.has-many').remove();
    },

    // remove has many element
    count_chars: function(e){   
    	var input_area = this.$(e.target);
    	var label = input_area.parent().find('.label');    	
    	var chars = input_area.val().length;    	
    	var count = chars + ' / ' + input_area.attr('maxlength');
    	
    	if (chars > 0){      	
    		label.show().html(count);    		
    	}else{
    		label.hide();     		
    	}
    },    
    
  });   
  
  /* Init code
  * ====================== */
  
  // Main app
  App.Form = new App.Form_view;	
    
});