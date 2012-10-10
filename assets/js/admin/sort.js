$(function(){
  
  // App view
  // ---------------

  App.Sort_view = Backbone.View.extend({

    el: $('#sort-app'),

    events: {
    },

    initialize: function() {    	
      this.$('#sort-table tbody').sortable({
        helper: function(e, ui) {
        	ui.children().each(function() {
            $(this).width($(this).width());
        	});
        	return ui;
      	},
        axis: 'y',
      	update: _.bind(function(event, ui) {
          this.update_order();
        }, this),        
      }).disableSelection();    	    	
    },
    
    update_order: function(){
    	var cnt = 1;
			this.$('#sort-table tbody tr').each(function(){				
				var element = $(this).find('td:first-child').html(cnt);
				cnt++;
			});      	    	
    },
    
  });   
  
  /* Init code
  * ====================== */
  
  // Main app
  App.Sort = new App.Sort_view;	
    
});