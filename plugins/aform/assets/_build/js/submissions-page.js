(function($) {
	function _mergeObjects(obj1,obj2){
	    var obj3 = {};
	    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
	    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
	    return obj3;
	}
	function _ucfirst(string) {
    	return string.charAt(0).toUpperCase() + string.slice(1);
	}

	var submissionsPagination = new Paginathor();
		submissionsPagination.updateSettings({ elClass : 'submission-page' , target : '#' }); /* we set constants/defaults */
	
	var submissionData = {};
	
	var $deleteDialogBox = $("#deleteDialog");
	$deleteDialogBox.dialog({
		'autoOpen'		: false,
		'closeOnEscape'	: true,
		'dialogClass'	: 'wp-dialog',
		'minHeight'		: 80,
		'modal'			: true
	});
	
	var adminAjax = {
		
		results: $("#formSubmissionResults"),
		pages : $(".form-result-pagination"),
		rebuildPages : true,
		currentQuery : {},
		reuseQuery : false, //true for resorting, false for new queries
		setup: function(){
			$.ajaxSetup({
				url: ajaxurl,
				type: 'post'
			});		
		}, /*setup*/
		classToggle: function() {
			this.results.toggleClass( 'ajax-active' );
		}, /*classToggle*/
		deleteSubmission : function(){
			var d = $('.delete-submission');
			d.on('click', function(e){
				e.preventDefault();
				var t = $(this);
				var _ID = t.data('deleteSubmission');
				var parentEl = t.parents('tr');
				$deleteDialogBox.dialog({
					'buttons'	: {
						'Don\'t Delete' : function() {
							$(this).dialog( 'close' );
						},
						'Delete' : function() {
							$.ajax({
								data: {
									action : 'deleteSubmission',
									id     : _ID
								}
							}).done( function(res) {
								res = JSON.parse( res );
								//console.log(res);
								parentEl.fadeOut('500', function(){
									$(this).remove();
								});

								$deleteDialogBox.dialog( 'close' );
							});
						}
					}
				});
				$deleteDialogBox.dialog( 'open' );	
			});
		},
		getSubmissions: function(fieldID, from, to, limit , page ) {
			
			var reuseQuery = adminAjax.reuseQuery;
			defaultObj = {
				action: 'formSubmissions',
			};
			if(reuseQuery == false){
				var limitInput = $("#formCount option:selected");/*on first load*/
				var limit = limit ? limit : limitInput.val() ;/*on first load*/
				var page = page ? page : 1;/*on first load*/
				mergedData = {
					fieldID: fieldID,
					from: from,
					to: to,
					limit: limit,
					page : page,
				};
			}
			else{ mergedData = adminAjax.currentQuery; }
			
			mergedData = _mergeObjects(defaultObj , mergedData); /* Merge our objects */

			adminAjax.currentQuery = mergedData;
			
			$.ajax({
				beforeSend: this.classToggle(),
				complete: this.classToggle(),
				data: mergedData
			}).done( function(data) {

				submissionData = {};/* FLUSH OUR submission data */
				
				if( data.success == false ){ return; }// do nothing when success fails
				
				var resultsList = adminAjax.results.find( 'tbody' );
				var pagesParent = adminAjax.pages.find("ul");
				if(adminAjax.rebuildPages){
					/*prevent duplicates*/
					pagesParent.html('');/*clear pagination when new filter options*/
				}
				resultsList.html('')				
				results = JSON.parse( data.data );
				//console.log( results.total );
				if(results.results == ''){
					return;
				}
				else{
					if(adminAjax.rebuildPages){
						_fieldID = fieldID ? fieldID : '';
						_from = from ? from : '';
						_to = to ? to : '';
						
						// clone instead of reference
						var sendQuery = jQuery.extend(true , {}, adminAjax.currentQuery);
						delete sendQuery.page;
						sendQuery.limit = false;
						sendQuery.action = 'downloads_fs';
						sendQuery.datafor = 'filtered';
						downloadQuery = $.param(sendQuery);
						//console.log(downloadQuery);
						//downloadurl = ajaxurl+'?action=downloads_fs&form_id='+_fieldID+'&from='+_from+'&to='+_to+'&limit='+false+'&datafor=filtered';
						downloadurl = ajaxurl + '?' + downloadQuery;
						//console.log(downloadurl2);
						$('a#downloadFs').attr('href',downloadurl).removeAttr('style');
						
						if( !adminAjax.reuseQuery ) {
							var defaultSort = $("#defaultSort");
							if( !defaultSort.hasClass( 'selected' ) ) {
								$('.form-sort-control').find('.selected').toggleClass('selected');
								defaultSort.addClass('selected');
							}							
						}
					}
				}
				/*PAGINATION */
				if(results.pages > 0){

					var currentQ = adminAjax.currentQuery;
					var resLength = results.results.length;
					var resultsInfo = { page : currentQ.page , results : resLength , total : parseInt( results.total ) };
					/** 
					* New Pagination : V3 ** 
					* Uses JS (non-ajax)
					**/
					
					pagesParent.html('');/* CLEAR */

					var paginathorSettings = { page : currentQ.page , total :results.total , limit : currentQ.limit };
					var paginathorHtml = submissionsPagination.build( paginathorSettings );
					
					setTimeout(function() {
						pagesParent.html( paginathorHtml ); /* Apply new pagination */
					}, 300);
					var checkPagesParent = setInterval(function(){ 
						if( pagesParent[0].childElementCount > 0 ){
							 /* Apply click event  */
							pagesParent.find('.submission-page').on('click',function (e){
								e.preventDefault();
								var el = $(this), getPage = el.data('submissionPage');
								adminAjax.reuseQuery = true;
								adminAjax.currentQuery.page = getPage;
								adminAjax.getSubmissions();
							});
							console.info(resultsInfo); /* */
							clearInterval(checkPagesParent); /* clear interval */
						}
						else{
							//console.log('...');
						}
					}, 150);
				}
				/*PAGINATION END*/
				
				c = 1;
				$.each( results.results , function() {
					/** 
					* - Instead of saving to a data attribute we push to submissionData 
					* - reference to data is set in data-formdata="submission-{id of submission}"
					* - Maybe we can introduce backbone to render this (?)
					**/
					
					this.form_data = JSON.parse(this.form_data);

					var opts = '';
					var tr = '<tr><td>' + this.post_title + '</td>';
						tr += '<td>' + this.date_time + '</td>';
						tr += '<td>' + this.email + '</td>';
						
						if('submissionmeta' in this){

							opts = $('<div><span class="meta-info"></span></div>');
							submissionMeta = this.submissionmeta;

							this.form_data.meta = submissionMeta;
							
							$.each(submissionMeta , function(){
								var m = this;
								if(m.meta_type == 'tracking' && m.meta_key == 'campaign'){
									opts.find('span').append('<i class="sf-sm-info info-campaign-tracking">CT</i>');
								}
							});
							
						}
						tr += '<td><a class="data-link" data-formdata="submission-'+this.id+'">View Form Data'+( (opts != '') ? opts.html() : '' )+'</a></td>';
						
						tr += '<td><a><span data-delete-submission="'+this.id+'" class="delete-submission dashicons dashicons-trash"></span></a></td></tr>';

					resultsList.append( tr );
					
					submissionData['submission-'+this.id] = this.form_data;					
				
					if(resLength > 0 && c == resLength ){
						adminAjax.deleteSubmission();
					}
					c = (c + 1);	
				});
				adminAjax.reuseQuery = false;/* when done with ajax , set to default */
			});			
		} /*getSubmissions*/
		
	};

	adminAjax.setup();	
	adminAjax.getSubmissions();
	
	
	var filterBtn = $("#filterSubmit");
	var filterForm = $("#submissionsFilter");
	var filterReset = $("#filterReset");
	
	var formInput = $("#formName");
	var fromInput = $("#fromDate");
	var toInput = $("#toDate");	
	var limitInput = $("#formCount");

	$("#submissionsFilter :input").change(function() {        
        $('a#downloadFs').hide();
    });

	filterBtn.on( 'click', function(e) {
		e.preventDefault();

		var selectedForms,
		forms = formInput.val();
		if( forms != null ){

			forms.forEach( function(a) {
				if( selectedForms == undefined ) {
					selectedForms = a;				
				} else {
					selectedForms += ', ' + a;
				}
			});
		}
			adminAjax.rebuildPages = true;
			adminAjax.getSubmissions( selectedForms, fromInput.val(), toInput.val(), limitInput.val() );
		
	});
	
	filterReset.on( 'click', function(e) {
		e.preventDefault();
		
		formInput.find('option:selected').prop('selected', false);
		fromInput.add(toInput).val(''); //grab both inputs at once
		limitInput.val(10);
		
		adminAjax.getSubmissions();
	});

	var orderbyEl = $('.orderby');
	orderbyEl.on('click',function (e){
		e.preventDefault();
		var el = $(this);
		var filtered = adminAjax.currentQuery;
		var orderby = el.data('orderby');
		var orderbyObj = {};
		var selectedClass = 'selected';

		orderbyEl.removeClass(selectedClass);
		el.addClass(selectedClass);

		filtered.page = 1;
		orderbyObj.orderby = orderby;

		filtered = _mergeObjects( filtered , orderbyObj );/* Merge our objects */
		adminAjax.reuseQuery = true;
		adminAjax.rebuildPages = true;
		adminAjax.currentQuery = filtered;/* update query */
		adminAjax.getSubmissions();

	});	
	
	$("#formSubmissionResults").on( 'click', '.data-link', function(e) {
		
		var data = $(this).data( 'formdata' );		
			
			data = submissionData[data];

		var sample = viewData( data );
		
		$.fancybox(
			['<div>' + sample + '</div>'],
			{
				'arrows': false,
				'autoSize': false,
				'height' : '65%',
				'margin' : [20,20,20,180],
				'width' : '80%'
				
			}
		);
	});
	
	var viewData = function( data ) {

		var form = data.form,
			fields = data.fields;			
		
		//console.log(data);

		var result = "<div><h2><center>Form &raquo; " + form.sfname + "</center></h2>";
			

			if('submission-page' in form){
				result += "<p><strong style=\"display:inline-block;width:150px;\">Submitted From Page:</strong>" + form["submission-page"] + "</p>";
			}
			if('http_referrer' in form){
				result += "<p><strong style=\"display:inline-block;width:150px;\">Submitted From URL:</strong>"+ form['http_referrer']+"</p>";
			}

			if('client_ip' in form){
				result += "<p><strong style=\"display:inline-block;width:150px;\">Submitted From IP:</strong>"+ form['client_ip']+"</p>";
			}

			result += "<h3>Form Data</h3>";
				
			$.each( fields, function(index, value ) {
				result += '<p><strong>' + value.display + "</strong>: " + value.value + "</p>";
			});

			if('meta' in data ){
				$.each(data.meta , function(){
					
					var m = this;
					meta_value = '';
					if(m.meta_type == 'tracking' && m.meta_key == 'campaign'){
						meta_value = JSON.parse(m.meta_value);
						result += "<h3><strong>Campaign Tracking</strong></h3>";
						$.each(meta_value , function( a , b){
							result += '<span><strong style="display:inline-block;width:125px;">' + _ucfirst(a) + ':</strong> ' + b + '</span><br>';
						});
					}

				});
			}
		result += "</div>";		
		return result;
	}
	
	$(".date-picker").datepicker();

	
})(jQuery);