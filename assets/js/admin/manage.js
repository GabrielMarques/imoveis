$(function(){
  	
  // Row Model
  // ----------

  App.Row = Backbone.Model.extend({  	
  
    initialize: function(){
  	},
  
  });			
	
  // Rows Collection
  // ---------------

  App.Rows_collection = Backbone.Collection.extend({
  	
    model: App.Row,
    url: server_vars.fetch_url,

    initialize: function(){  	
  	
    },   
    
    parse: function(response) {
      App.total_rows = response.total_rows;
      this.totals = response.totals;
    	return response.rows;
    }    
    
  });   
  
  // Pagination View
  // --------------

 App.Pagination_view = Backbone.View.extend({
  	 
		tagName: 'li', 
		className: 'pagination-num',
		template: _.template('<a href="#"><%= page_num %></a>'),
 
		events: {
			//'click #a-page-item': 'onPageClick',
		},
 
		render: function() {
			this.$el.html(this.template({page_num : this.options.page_id + 1}));
			return this;
		},
	});    
	
  // Row View
  // --------------

  App.Row_view = Backbone.View.extend({

    tagName: 'tr',
    template: _.template('<td><%= value %></td>'),

    events: {
    },

    initialize: function() {
      this.model.on('change', this.render, this);   
    },  

    render: function() {
    	var model_data = this.model.toJSON();    	   
    	
      // append cols
    	var cols = _.isUndefined(model_data.cols) ? model_data : model_data.cols;
      _.each(cols, function(value){
      	this.$el.append(this.template({value: value}));
      }, this);            	
    	
      return this;
    },  
    
  });  
  
  // App view
  // ---------------

  App.Manage_view = Backbone.View.extend({

    el: $('#manage-app'),

    events: {
			'click #show-filters-btn': 'toggle_filters',
  		'click .table-header': 'table_sort',
  		'click .limit-options': 'table_limit',
  		'click #search-btn': 'table_search',
  		'click #search-clear-btn': 'clear_search',
  		'keypress #search-input': 'search_keypress',
  		'click .alert-client .close': 'close_alert',
  		'click #check-all': 'check_all',
  		'click .action-table': 'table_action',
  		'click .action-modal': 'action_modal',
  		'click #pagination a': 'select_page',
  		'click #filters-btn': 'set_filters',
  		'click #filters-reset-btn': 'reset_filters',
			'click .confirm-btn': 'confirm_modal',
			'keypress form.modal-form': 'confirm_modal_on_enter',
    },    
    
    initialize: function() {    	
    	_.bindAll(this, 'ajax_start', 'ajax_complete');
    	
    	App.Rows.on('reset', this.render_all, this);    	
    	
    	this.$el.ajaxStart(this.ajax_start);
    	this.$el.ajaxComplete(this.ajax_complete);
    	
    	App.Rows.reset(server_vars.rows);
      
      this.init_upload_btn();      	            
    },  
    
    // add all
    render_all: function() {
    	this.clear_table();
    	
    	if (App.Rows.length > 0){
    		App.Rows.each(this.render_row);
    		
    		// set info str
      	var start = ((App.table_params.page - 1) * App.table_params.limit) + 1;
      	var end = App.table_params.page * App.table_params.limit;      	
      	var end = _.min([end, App.total_rows]);  
    	}else{  		
    		var colspan = this.$('#manage-table thead th').length;
    		this.$('#table-body').html('<tr><td colspan="' + colspan + '">' + lang.error_no_rows + '</td></tr>');
      	var start = 0;
      	var end = 0;
    	}  	
    	
    	var info_str = start + ' ' + lang.to + ' ' + end + ' ' + lang.of + ' ' + App.total_rows + ' ' + lang.records;
    	this.$('#table-info').html(info_str);    	

    	// pagination      	
    	var on_page = _.min([App.table_params.limit, App.total_rows]);
      $('#pagination').pagination({
        items: App.total_rows,
        itemsOnPage: on_page,
				edges: 1,
				currentPage: App.table_params.page,
				selectOnClick: false,
      });    	
      
      // totals
      if (_.isUndefined(App.Rows.totals) === false){
      	this.render_totals_row(App.Rows.totals);
      }
      
    	/***************************
    	 * Project js
    	 ***************************/
      
      if (_.isUndefined(server_vars.chart) === false && server_vars.chart === true){
      	App.Sales.render_chart();
      }	   
      
      /***************************/      

      
      // fancybox
      this.$('.enlarge').fancybox(App.fancy_options);	
    },      
    
    // add row
    render_row: function(model) { 
      var row_view = new App.Row_view({
      	model: model,
      }); 
      this.$('#table-body').append(row_view.render().el);
    },      
    
    // add totals row
    render_totals_row: function(totals) {
    	this.$('#totals-row').html('');
      _.each(totals, function(value){
      	this.$('#totals-row').append('<td>' + value + '</td>');
      }, this);      	    	
    },     
    
    // fetch server rows
    fetch_rows: function(clear){    	
    	if (clear != false){
      	this.clear_all();    		
    	}
    	
    	var data = {};
    	_.extend(data, App.table_params, App.filters);    	
    	//console.log(data);
    	
	    App.Rows.fetch({
	    	data: data,
	    	error: _.bind(function (collection, errors){
	    		if (errors.status == 401){
		    		this.show_error_alert(lang.error_session);	    		
	    		}else if (errors.status == 400){
	    			this.show_error_alert(lang.error_default);
	    			
	    			var form_errors = JSON.parse(errors.responseText);	 
						_.each(form_errors, function(value, key){
							this.show_form_error(value.field, value.message);			
						}, this);	  	    			
	    				    			
	    		}else{
	    			this.show_error_alert(lang.error_default);
	    		}
	    		
	    	}, this),   	
	    	success: _.bind(function (collection, response){
	    		// nothing
	    	}, this),
	    });   		
    },     

    // sort cols
    table_sort: function(e) {    	   
    	var header = this.$(e.target);
    	
    	App.table_params.page = 1;
    	App.table_params.order_by.field = header.attr('data-field');
    	if (header.hasClass('sort-asc')){
      	this.$('.table-header').removeClass('sort-asc sort-desc');    		
    		App.table_params.order_by.direction = 'desc';
    		header.addClass('sort-desc');
    	}else{
      	this.$('.table-header').removeClass('sort-asc sort-desc');    		
    		App.table_params.order_by.direction = 'asc';
    		header.addClass('sort-asc');
    	}	
    	this.fetch_rows();
    },
    
    // pagination
    select_page: function(e) {    	
    	e.preventDefault();
    	var btn = this.$(e.target);
    	
    	if (btn.parent().hasClass('active') || btn.parent().hasClass('disabled')){
    		return;
    	}
    	
    	App.table_params.page = btn.attr('data-page');
    	this.fetch_rows();    	
    },     
    
    // table limit
    table_limit: function(e) {
    	e.preventDefault();
    	var btn = this.$(e.target);
    	
    	this.$('.limit-options').parent().removeClass('active');
    	btn.parent().addClass('active');
    	
    	App.table_params.page = 1;
    	App.table_params.limit = btn.attr('data-limit');
    	this.fetch_rows();
    },    
    
    // table limit
    table_search: function() {
    	App.table_params.page = 1;
    	App.table_params.search = _.trim(this.$('#search-input').val());
    	this.fetch_rows();
    },     
    
    // clear search
    clear_search: function(e) {
    	e.preventDefault();
    	this.$('#search-input').val('');
    	this.$('#search-clear-btn').hide();
    	App.table_params.page = 1;
    	App.table_params.search = '';
    	this.fetch_rows();
    },    
    
    // create on key press
    search_keypress: function(e) {
      if (e.keyCode == 13) {
        this.table_search();
      }else{
        if (this.$('#search-input').val().length > 0) {
        	this.$('#search-clear-btn').show();
        }else {
        	this.$('#search-clear-btn').hide();
        }
      }
    },     
            
    // set table filters
    set_filters: function() {
    	var form = this.$('#filters-form');
      _.each(App.filters, function(value, key){
      	if (form.find('input[name=' + key + ']').length){
      		App.filters[key] = form.find('input[name=' + key + ']').val();
      	}else if (form.find('textarea[name=' + key + ']').length){
      		App.filters[key] = form.find('textarea[name=' + key + ']').val();
      	}else if (form.find('select[name=' + key + ']').length){
      		App.filters[key] = form.find('select[name=' + key + ']').val();
      	}
      }, this);   
  
      this.fetch_rows();
    },     
    
    // reset table filters
    reset_filters: function() {
    	var form = this.$('#filters-form');
      _.each(App.filters, function(value, key){
      	if (form.find('input[name=' + key + ']').length){
      		App.filters[key] = form.find('input[name=' + key + ']').val('').val();
      	}else if (form.find('textarea[name=' + key + ']').length){
      		App.filters[key] = form.find('textarea[name=' + key + ']').val('').val();
      	}else if (form.find('select[name=' + key + ']').length){
      		App.filters[key] = form.find('select[name=' + key + ']').val(0).val();
      	}
      }, this);   
  
      this.fetch_rows();
    },     
    
    // show/close filters
    toggle_filters: function() {
      if (this.$('#show-filters-btn').hasClass('active')){
        $('#manage-content').removeClass('span10').addClass('span12');  	
        $('#sidebar').removeClass('span2').hide();      	
      }else{
        $('#manage-content').removeClass('span12').addClass('span10');
        $('#sidebar').addClass('span2').fadeIn();      	
      }    
    },
    
    // upload btn
    init_upload_btn: function() {
    	if (this.$('#upload-btn').length == 0){
    		return;
    	}
    	
      // upload
    	this.upload_errors = [];
    	this.upload_success = 0;
    	
    	this.$('#upload-btn').fileupload({
        dataType: 'json',
        url: server_vars.page_url + '/upload',
        seqentialUploads: true, 
        
        start: _.bind(function (e) {
    			console.log('start');
    			this.upload_start();
        }, this),
        
        stop: _.bind(function (e) {
    			console.log('stop');    	
        	this.upload_stop();   
        }, this),     
        
        send: _.bind(function (e, data){
    			console.log('send ' + data.files[0].name);
    			var valid = this.validate_files(data);
        }, this),
        
        done: _.bind(function (e, data) {
    			console.log('done ' + data.files[0].name);   
    			
        	if (data.jqXHR.status == 200){
    				this.upload_success++;
        	}else{
        		this.push_upload_error(data.files[0].name, data.jqXHR.responseText);
        	}        	
        }, this),      
        
        fail: _.bind(function (e, data) {
    			console.log('fail ' + data.files[0].name);
    			if (_.isUndefined(data.jqXHR) == false){
      			this.push_upload_error(data.files[0].name, data.jqXHR.responseText);    				
    			}
        }, this),
        
        progressall: _.bind(function (e, data) {
        	var progress = parseInt(data.loaded / data.total * 100, 10);
        	this.$('#upload-progress .bar').css('width', progress);    
        }, this),
        
      });
    },       
    
    // start upload
    upload_start: function(){
    	this.clear_all();
    	
    	this.upload_errors = [];
    	this.upload_success = 0;    	
    	
    	// progress
    	this.$('#upload-progress .bar').css('width', 0);    
    	this.$('#upload-progress').show();
    	this.$('#upload-btn').fileupload('disable');
    	this.$('#upload-btn').parent().attr('disabled', true);
    },  
    
    // stop upload
   upload_stop: function(){
    	this.$('#upload-progress .bar').css('width', 100);
    	this.$('#upload-progress').fadeOut();	
    	
    	// alert
    	if (this.upload_errors.length == 0){
    		this.show_success_alert(lang.success_upload);
    	}else{
    		var message = lang.error_upload + '<ul>';
    		_.each(this.upload_errors, function(error, key){ 
    			message += '<li><strong>' + error.file_name + '</strong> - ' + error.message + ' ' + '</li>';
    		});
    		message += '</ul>';
    		this.show_error_alert(message);
    	} 
    	
    	// enable upload btn
    	this.$('#upload-btn').fileupload('enable');
    	this.$('#upload-btn').parent().removeAttr('disabled');    	   	
    	
    	// reload rows
    	this.fetch_rows(false);     		    	
    },   
    
    // send upload
    validate_files: function(data){
    	// check extensions
      var ext = data.files[0].name.split('.').pop().toLowerCase();
      if(_.include(server_vars.extensions, ext) == false) {
    		this.push_upload_error(data.files[0].name, lang.error_file_format);
    		return false;  	
      }		
    	
    	// check file size
    	if (data.files[0].size > server_vars.max_size){
    		this.push_upload_error(data.files[0].name, lang.error_file_size);
    		return false;
    	}
    	
    	return true;
    }, 
    
    // add to upload errrors array
    push_upload_error: function(file_name, message){	
    	var file_names = _.pluck(this.upload_errors, 'file_name');    	
    	if (_.include(file_names, file_name) == false){    		
    		this.upload_errors.push({file_name: file_name, message: message});    		
    	}
    },    
    
    // table action btns
    table_action: function(e){
    	e.preventDefault();
    	var btn = this.$(e.target);  	
    	this.clear_all();
    	 
    	// checked elements?
    	if (this.$('#form-manage input:not(#check-all):checked').length > 0){
        // open modal
    		if (btn.attr('data-target')){
        	this.show_modal(btn.attr('data-target'));
        }else{
        	// modal btn
        	if (btn.hasClass('confirm-btn')){      	
  		      btn.closest('form').find(':input').each(function(){
  		      	$('<input type="hidden" name="' + $(this).attr('name') + '" value="' + $(this).val() + '">').appendTo('#form-manage');
  		      });
        	}         	
        	
        	// submit form
        	if (_(btn.attr('data-action')).startsWith('export')){
        		this.$('#form-manage').attr('target', '_blank');    		
        	}else{
        		this.$('#form-manage').removeAttr('target');    		        		
        	}
		      this.$('#form-manage').attr('action', btn.attr('href'));	      
		      this.$('#form-manage').submit();
        }
    	}else{
    		this.show_error_alert(lang.error_checked_items);
    	}      
    },
    
    // show modal
    action_modal: function(e) {
    	e.preventDefault();
    	var btn = this.$(e.target);
    	var target = btn.attr('data-target');
    	this.clear_all();

    	this.$(target).find('.modal-form').attr('action',  btn.attr('href'));
    	this.show_modal(target);
    },        
    
    // check all rows
    check_all: function(e){
    	var check = this.$(e.target);
    	if (check.attr('checked')){
    		this.$('#form-manage input:checkbox').attr('checked', check.attr('checked'));
    	}else{
    		this.$('#form-manage input:checkbox').removeAttr('checked');    		
    	}
    },
   
    // show modal
    show_modal: function(target) {
      this.$(target).modal({'show': true, 'backdrop': true});
    },    
    
    // confirm modal
    confirm_modal: function(e) {
    	// do not disable if data-action starts with 'export'
    	if (_(this.$(e.target).attr('data-action')).startsWith('export')){
    		this.$(e.target).closest('form').attr('target', '_blank');    		
    	}else{
      	this.$(e.target).button('loading');
      	this.$('.modal').on('hide', function () {
      	  $(this).stopPropagation();
      	});    		    		
    	}
    },  
    
    // confirm modal on enter
    confirm_modal_on_enter: function(e) {
      if (e.keyCode == 13) {
      	e.preventDefault();
      	this.$(e.target).closest('form').find('.confirm-btn').click();
      }
    },       
    
    // show alert
    show_error_alert: function(message){
    	this.$('#alert-client-error .message').html('<strong>' + lang.error + '!</strong> ' + message);
    	this.$('#alert-client-error').show();
    },
    
    // show alert
    show_success_alert: function(message){
    	this.$('#alert-client-success .message').html('<strong>' + lang.success + '!</strong> ' + message);
    	this.$('#alert-client-success').show();
    },    
    
    // show form error
    show_form_error: function(field, message){
    	this.$('#control-' + field).addClass('error');
    	this.$('#control-' + field + ' .error-msg').html(message).show();
    	this.$('#control-' + field + ' .help-msg').hide();	
    },
    
    // close alert
    close_alert: function(e){   
			e.preventDefault();
    	this.$(e.target).parent().fadeOut('fast');
    },
    
    // ajax start
    ajax_start: function() {
    	this.$('#spin-container').spin(App.spin_options);
    },

    // ajax complete
    ajax_complete: function() {
    	this.$('#spin-container').spin(false);  	
    },            
    
    // clear table
    clear_table: function() {
    	this.$('#table-body').html('');
    },      
    
    // clear all
    clear_all: function(){
    	this.$('.alert').hide();
    	this.$('.control-group').removeClass('error');
    	this.$('.error-msg').hide();
    	this.$('.help-msg').show();	
    	this.$('.spin-container').spin(false);
    },     
    
  });   
  
  /* Init code
  * ====================== */
  
  // default vars
  App.table_params = server_vars.table_params;
  App.total_rows = server_vars.total_rows;
  
  App.filters = server_vars.filters;
  //console.log(App.filters);

  // Rows collection
  App.Rows = new App.Rows_collection;
  
  // Main app
  App.Manage = new App.Manage_view;
    
});