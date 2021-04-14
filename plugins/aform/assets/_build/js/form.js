(function($) {
	/**
	 * IE polyfill: CutomEvent
	*/
	!function () { if ("function" == typeof window.CustomEvent) return !1; window.CustomEvent = function (n, t) { t = t || { bubbles: !1, cancelable: !1, detail: null }; var e = document.createEvent("CustomEvent"); return e.initCustomEvent(n, t.bubbles, t.cancelable, t.detail), e } }();
	/**
	 * Feature detection
	*/
	var afFeatureCheck = {}; afFeatureCheck.fileapi = $("<input type='file'/>").get(0).files !== undefined; afFeatureCheck.formdata = window.FormData !== undefined,
	userAgent = navigator.userAgent || navigator.vendor || window.opera;
	if(afFeatureCheck.fileapi == true && window.FileReader && window.FileList && window.Blob){ afFeatureCheck.allpass = true;}

	//used for cookies/tracking campaigns
	var cookieMonsterWasHere = document.cookie;
		cookieMonsterWasHere = cookieMonsterWasHere.replace(/(?:(?:^|.*;\s*)__formUtmTracking\s*\=\s*([^;]*).*$)|^.*$/, "$1");
	if(cookieMonsterWasHere != ''){
		console.info('UTM Found : ' + cookieMonsterWasHere );
	}

	/*
	DATE VALIDATION
	*/
	var __dateValidator = (function () {

		var __DIM = function(month,year){
			return new Date(year, month, 0).getDate();
		};
		var __VALIDATE = function(DATE , FORMAT){
			var date = DATE.split("/");
			if(date.length < 3){
				return 0;
			}
			switch(FORMAT){
			case 'dd/mm/yyyy':
			    	var d = parseInt(date[0], 10),
			    		m = parseInt(date[1], 10),
			    		y = parseInt(date[2], 10);

				break;
			case 'mm/dd/yyyy':
					var m = parseInt(date[0], 10),
			    		d = parseInt(date[1], 10),
			    		y = parseInt(date[2], 10);
			break;
			case 'yyyy/mm/dd':
					var y = parseInt(date[0], 10),
			    		m = parseInt(date[1], 10),
			    		d = parseInt(date[2], 10);
			break;
			}
			 if (m > 12 || m < 1 ) { // check month
			  return 0;
			}
			var daysinMonth = __DIM(m,y);
			if(d > daysinMonth){//check if day is available in month
				return 0;
			}
			var yLength = y.toString().length;
			if( yLength != 4 ){
				return 0;
			}
			var datePass = new Date(y, m - 1, d);
				datePass = (datePass != 'Invalid Date') ? 1 : 0;
				return datePass;
		};
		return function(_d , _f){
			return __VALIDATE(_d , _f);
		}
	})();
	function __sfInArray(needle, haystack) {
	    var length = haystack.length;
	    for(var i = 0; i < length; i++) {
	        if(haystack[i] == needle) return true;
	    }
	    return false;
	}
	/**************************
		VALIDATOR CUSTOM METHODS
	*************************/
	$.validator.addMethod('filesize' , function( value, element, param){
			if( value != ''){
				var filesizekb = element.getAttribute('data-filesize');
			}
			else{
				return this.optional(element);
			}
			if(filesizekb <= param){
				$(element).parent().removeClass('file-not-valid');
			}else{
				$(element).parent().removeClass('file-is-valid').addClass('file-not-valid');
			}
		return this.optional(element) || ( filesizekb <= param );
	});
	/*  */
	$.validator.addMethod('realextension' , function( value, element, param){
		if( value != ''){
			var realmime = element.getAttribute('data-realmime');
			if(realmime == null){
				return true;
			}
		}
		else{
			return this.optional(element);
		}
		var extensions = param.split('|');
		if(__sfInArray( realmime , extensions )){
			$(element).parent().removeClass('file-not-valid');
		}else{
			$(element).parent().removeClass('file-is-valid').addClass('file-not-valid');
		}
		return this.optional(element) || ( __sfInArray( realmime , extensions ) );
	});
	$.validator.addMethod('datevalidation' , function( value , element, param){
		if(value == ''){
			return this.optional(element);
		}
		else{
			return this.optional(element) || __dateValidator( value , param );
		}
	});

	//afFeatureCheck.allpass = false;//used to test old browsers

	if(afFeatureCheck.allpass == false){ console.log('legacy'); }

	/*********************************************************************
		Form Handlers: send our final data to the server;process our data.
	*********************************************************************/
	var _aformHandler = function() {
		this.whaleCall = function(_f , fd , formData ){
			/*
				This is a fallback for browsers that don't support the FormData/File WebApi
				uses: https://cmlenz.github.io/jquery-iframe-transport/
			*/
			/*
				fd : fileData
			*/
			var _self = this;
			formData = JSON.stringify( formData );

			$.ajax({
				url: afData.afAjaxUrl,
				data :{
					action 		: 'aformPublic',
					method 		: 'dolphin',
					form   		: formData,
					utmtracking : cookieMonsterWasHere,
					iframe 		: true
				},
				files: fd,
        		iframe: true,
        		dataType: 'json',
			}).done(function( res ){

				_self.ajaxDone(_f , res);

			}).fail(function(res) { alert('Unable to process form'); });
		}
		/*
			used by modern browsers that support the JS formData API
		*/
		this.dolphinCall = function(_f , formData , fd ){
			/*
				_f : formInstance
				fd : formDataInstance
				uses : WebApi : FormData , File
			*/
			var _self = this;
			formData = JSON.stringify( formData );
			fd.append('action' , 'aformPublic');
			fd.append('method' , 'dolphin');
			fd.append('form' , formData );
			fd.append('utmtracking' , cookieMonsterWasHere );
			$.ajax({
				url: afData.afAjaxUrl,
				type: 'post',
				data: fd,
				processData: false,  // tell jQuery not to process the data
				contentType: false   // tell jQuery not to set contentType
			}).done(function( res ){

				_self.ajaxDone(_f , res);

			}).fail(function(res) { alert('Unable to process form (500)'); });
		}

		this.ajaxDone = function(_f , res){

			$(_f.form).removeClass( 'ajax-active' );
				if(typeof res === 'string'){
					var rule = /^\{/i;
					var ruleMatch = res.match(rule);
					/* check if the string is a '{something:we can work with}'; */
					if( ruleMatch instanceof Array ){
						res =JSON.parse( res );
					}
				}
				//window.aform = res;
				if(typeof res === 'object' && 'success' in res ){
					if(res.success == false){

						$(_f.form).find('input[type="submit"]').prop( 'disabled', false );
						$(_f.form).find('input[type="submit"]').val(_f.submitText);
						
						var responseMessage = res.data.message;

						//get failed object reason and print to screen
						if('invalid-fields' in res.data){
							var failedobject = Object.values(res.data['invalid-fields']).join(',');
							responseMessage = responseMessage +':<br> ' + failedobject;
						}
						$(_f.form).find('.ajaxfail--response--message').html('<p><small>'+responseMessage +'</small></p>').show();

					}else{
						_f.postProcess( res.data );
					}
				}else{
					$(_f.form).find('input[type="submit"]').prop( 'disabled', false );
					$(_f.form).find('input[type="submit"]').val(_f.submitText);
					$(_f.form).find('.ajaxfail--response--message').html('<p><small>Response data not found</small></p>').show();
				}

		}
	}

	var formHandler = new _aformHandler();

	/* */

	this.aformPublic = function( form ) {

		aformPublic.prototype.robotic = function(form){

			if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
				$('.stopyenoh input[type="text"]',form).prop('type','password');
				$('.stopyenoh input[type="password"]',form).prop('autocomplete','new-password');
			}

			$(form).on('blur', 'input',function (e){
				var secret1 = $(form).find('input[name="thefaxnumber9tX4bPz"]').val(),secret2 = $(form).find('input[name="contact_by_fax"]').prop( "checked" );
				if(secret1 != ''){ $(form).find('input[name="thefaxnumber9tX4bPz"]').val(''); }
				if(secret2 == true){ $(form).find('input[name="contact_by_fax"]').prop( "checked" , false); }
				if(secret1 != '' && secret2 == true){ $(form).off('blur','input');}
			});

			$(form).on('mousemove',function (e){
				var secret1 = $(form).find('input[name="thefaxnumber9tX4bPz"]').val(),secret2 = $(form).find('input[name="contact_by_fax"]').prop( "checked" );
				if(secret1 != ''){ $(form).find('input[name="thefaxnumber9tX4bPz"]').val(''); }
				if(secret2 == true){ $(form).find('input[name="contact_by_fax"]').prop( "checked" , false); }
				if(secret1 != '' && secret2 == true){ }
			});
		}

		var forminstance 		= this,
			inputs 						= $(form).find('input'),
			formId 						= $(form).data('form-id'),
			formRules 				= aformRules[formId],
			fileData 		  		= null,
			formDataInstance 	= null,
			formAjaxResponse  = '',
			interacting;

		//var aformParallelSubmit = new CustomEvent( 'aformParallelSubmit');
		var aformAfterSubmit = new CustomEvent( 'aformAfterSubmit' );

		if( afFeatureCheck.allpass == true ){
			if( formDataInstance == null){  formDataInstance = new FormData(); }
		}

		forminstance.form = form;
		forminstance.submitText = $(form).find('input[type="submit"]').val();
		this.submitBtn = $(".submit-btn", $(form) );

		forminstance.robotic(form);

		/************************************************************
			set as a variable so i can call it from other functions
		*************************************************************/
		var formValidator = $(form).validate({
			errorElement: 'span',
			errorPlacement: function(error, element) {
				if(element[0].tagName == 'INPUT'){
					if(element[0].type == 'file'){
						$(error).insertBefore(element);
					} else {
						error.appendTo( element.parents('.field-wrap') );
					}
				}
			},
			submitHandler: function( form ) {
				//form.preventDefault();
				var robotic = $('.robotic input', form),
					robotStep1 = $(robotic[0]),
					robotStep2 = $(robotic[1]);

				if( robotStep1[0].value == "" && robotStep2[0].checked == false ){

					$(form).find('.ajaxfail--response--message').html('').hide();
					$(form).addClass( 'ajax-active' ).find('input[type="submit"]').prop( 'disabled', true );
					$(form).find('input[type="submit"]').val('Processing');


					////turns alongside
					// aformParallelSubmit.initCustomEvent('aformParallelSubmit', true , true , {
					// 	instance 	: forminstance,
					// 	formid 		: $(form).attr('data-form-id')
					// });
					// form.dispatchEvent(aformParallelSubmit);

					//check if form has files to send + detect file api features
					if( afFeatureCheck.allpass == true ){

						postedFormData = $(form).serializeArray();
						formHandler.dolphinCall( forminstance , postedFormData , formDataInstance );
					}
					else{
						//older browsers
						fileData = $(form).find(':file');
						fileDataLength = fileData.length;
						haveFiles = 0;
						if( fileDataLength > 0){
							fileData.each(function(index, el) {
								l = (index + 1);
								if(el.value !=''){
									haveFiles++;
								}
								if(fileDataLength == l){

									if(haveFiles <= 0){
										//no file ? no problem, let's setup a dummy input:file
										//so our legacy ajax call won't fail (whaleCall)
										fileData = $("<input type='file'/>");
									}

									formData = $(form).serializeArray();
									formHandler.whaleCall( forminstance , fileData , formData );
								}
							});
						}
					}//end-if
				}else{
					$(form).find('.ajaxfail--response--message').html('<p><small>For security reasons, please fill out form manually without auto complete. <br>Refresh to try again.</small></p>').show();
					$(form).removeClass( 'ajax-active' ).find('input[type="submit"]').prop( 'disabled', false );
					console.info('sorry , no robots allowed');
				}
				return false;
			}
		}); //end .validate setup



		this.fileChunked = function(theFile , fileSize){
			return theFile.slice( 0,  fileSize );
		}
		this.postProcess = function( response ) {

			if(typeof response === 'string'){ response =JSON.parse( response ); }

			//window.aform = response;

			var details = response,
				 blogurl = afData.blogurl,
				 confirmURL = details.url;

			if( 'use_ajax' in details && details.use_ajax ) { //if we're using ajax for our thank you message


				if(cookieMonsterWasHere!=''){
					document.cookie="__formUtmTracking=;expires=0;path=/";
				}


				// grab message or set default
				var $form  = $(form);
				var message = '<div class="aforms_confirmation_message">';
					message += ( details.confirmation_message != undefined && details.confirmation_message != '' ) ? details.confirmation_message : '<h2>Thank You</h2>';
					message += '</div>';

				message = $(message).attr('id','sf-message-'+$(form).attr('data-form-id'));
				//play nicely with the animation below
				$form.attr('style', 'overflow: hidden').removeClass( 'ajax-active' );
				$form.animate({ height: '0px'}, 500, function() {
					$form.after( message ); //add the message after the form
					$form[0].reset();
					$form.remove(); //remove the now submitted form from the DOM
				});

				if( typeof ga != 'undefined' ) {

					//if analytics is present then send a response :)
					if( confirmURL.indexOf(blogurl) > -1 ) { confirmURL = confirmURL.replace(blogurl, ''); }
					if( confirmURL.indexOf('/') != 0 ) { confirmURL = '/' + confirmURL; }

					ga('send', 'pageview', confirmURL);
					gtag('config', ga.getAll()[0].get('trackingId'), {'page_path': confirmURL});

				}

				//old method
				forminstance.afterPostMessage( forminstance , $(form).attr('data-form-id') , response );

				//custom event
				aformAfterSubmit.initCustomEvent('aformAfterSubmit', true , true , {
					instance 	: forminstance,
					formid 		: $(form).attr('data-form-id'),
					response 	: response
				});
				form.dispatchEvent(aformAfterSubmit);

			}/* <======== end of ajax message */
			else { /* no ajax? fine, we're goin home */
				if( confirmURL.indexOf(blogurl) < 0 ){

					if( confirmURL.indexOf('/') != 0 ) {
						confirmURL = blogurl + '/' + confirmURL;
					} else {
						confirmURL = blogurl + confirmURL;
					}
				}
				if(details.redirectwithpost == false){
					window.location.href = confirmURL;
				}
				else{
					//redirect to url with post data
					// - only send submission id & form id
					var redirectForm = '<div style="visibility:hidden;width:0;height:0;overflow:hidden;">';
						redirectForm  += '<form id="af__rd__form" action="'+confirmURL+'" method="POST">';
						redirectForm  += '<input type="hidden" name="aform-redirect-post[submission_id]" value="'+details.submissionid+'">';
						redirectForm  += '<input type="hidden" name="aform-redirect-post[form_id]" value="'+formId+'">';
						redirectForm  += '</form>';
						redirectForm  += '</form>';
						redirectForm += '</div>';

					$('body').append(redirectForm);
					$('#af__rd__form').submit();
				}
			}
		} // end this.postProcess

		/****************************
			THESE ARE THE FORM RULES
		*****************************/
		if( typeof(formRules) != undefined ) {
			formRules = formRules.rules;
			for( var field in formRules ) { //iterate through each field in the formRules object
				if( formRules.hasOwnProperty(field) ) {
					rules = formRules[field];
					var thisField = $("input:not(.sf-file)", ".field-"+field); //grab corresponding field in DOM
					if(thisField.length > 0){
						for( var r in rules ) {
							if( rules.hasOwnProperty(r) ) {
								var i = {};
								i[r] = rules[r];	// ie { email : true }
								if(r == 'datevalidation'){
									i.messages = {
										datevalidation : 'Invalid Date'
									};
								}
								thisField.rules("add", i); //add each rule to the jquery validate rules list
							}
						}
					}
					var thisFile = $("input.sf-file", ".field-"+field);
					if(thisFile.length > 0){
						thisFile.each(function( ii , el ){
							for( var r in rules ) {
								if( rules.hasOwnProperty(r) ) {
									var i = {};
									i[r] = rules[r];	// ie { email : true }
									if(r == 'filesize'){
										i.messages = {
											filesize : 'File Size must be less than ' + i.filesize + 'KB '
										};
									}
									if(r == 'realextension'){
										var ext = i.realextension;
										var reg = /(\|)/g;
										i.messages = {
											realextension : "Please enter a valid file: <i>." + ext.replace( reg , " .") + '</i>'
										};
									}
									$('input[name="'+el.name+'"]').rules("add", i);//add each rule to the jquery validate rules list
								}
							}
						});
					}

				}
			} // end rules function
		}
		/* fileData */
		var inputFile = $(form).find('input:file');
		inputFile.on('change', function (e){

			var f 			= $(this),//jquery
				ff 			= this,//js
				inputName 	= f.attr('data-name');

			if(f.hasClass('error')){ f.prev().remove(); }

			if( afFeatureCheck.allpass == true ){

				if(ff.files.length == 0 ){
					//file empty is cleared
					f.prev().remove();
					f.parent('span').removeClass('file-not-valid file-is-validating file-is-valid');
					$(form).removeClass('ajax-file-validation');
					return;
				}

				$(form).addClass('ajax-file-validation');
				f.parent('span').addClass('file-is-validating');
				f.parent('span').removeClass('file-not-valid file-is-valid');

				var theFile = ff.files[0],
					 fileChunk = forminstance.fileChunked( theFile , 150),
					 elFileReader = new FileReader();

				elFileReader.onloadend = function( event ) {

					if ( event.target.readyState !== FileReader.DONE ) { return; }
						//we send out a small piece of our file to the server so it can check its real filetype
						$.ajax({
							url: afData.afAjaxUrl,
							type: 'POST',
							dataType: 'json',
		            	cache: false,
							data: {
								action   : 'aformPublic',
								method   : 'dolphinValidatesFile',
								filedata : event.target.result,
								filetype : theFile.type,
								filename : theFile.name
							},
						})
						.done(function(res) {
							if('data' in res && 'mime' in res.data){
								f.attr('data-realmime' , res.data.mime );
								f.attr('data-filesize' , f.get(0).files[0].size / 1024 );

								setTimeout(function() {
									$(form).removeClass('ajax-file-validation');
									/* validate after done with ajax */
									if(res.data.mime != null ){ formValidator.element(f); }
									setTimeout(function() {
										if(!f.parent().hasClass('file-not-valid')){

											f.parent().removeClass('file-not-valid').addClass('file-is-valid');
											formDataInstance.append( inputName , f.get(0).files[0] );//only if valid
											console.info('file is valid')
										}
										f.parent().removeClass('file-is-validating');
									}, 500);
								}, 500 );

							}else{
								alert('file validation has failed');
							}
						}).fail(function(res) { alert('file validation has failed'); });

				};
				elFileReader.readAsDataURL( fileChunk );
				//_f.postProcess(data.data);
			}else{

				/*
					CHECK FOR FILESIZE + mime
				*/
				/* WE DO AJAX HERE for browsers that don't support the File/FormData API */
				/* ajax : sends file and checks the size using jquery-iframe-transport */
				/*
					CHECK FOR REAL MIME/extension
					uses : file transport js
				*/
				$(form).addClass('ajax-file-validation');
				f.parent('span').addClass('file-is-validating');
				f.parent('span').removeClass('file-not-valid file-is-valid');

				$.ajax({
					url: afData.afAjaxUrl,
					data :{
						//checkfilesizemime <-- later version do this
						action : 'aformPublic',
						method : 'whaleValidatesFile',
						iframe : true,
					},
					files: ff,
	        		iframe: true,
	        		dataType: 'json',
				}).done(function( res ){


						if('data' in res && 'mime' in res.data && 'filesize' in res.data){
							f.attr('data-realmime' , res.data.mime );
							f.attr('data-filesize' , res.data.filesize / 1024 );
							setTimeout(function() {

								$(form).removeClass('ajax-file-validation');
								/* validate after done with ajax */

								if(res.data.mime != null ){ formValidator.element(f); }

								if(!f.parent('span').hasClass('file-not-valid')){ f.parent('span').removeClass('file-not-valid').addClass('file-is-valid'); }
								f.parent('span').removeClass('file-is-validating');

							}, 250 );
						}else{
							alert('file validation has failed');
						}

				}).fail(function(res) { alert('file validation has failed'); });
			}

		});


		/*******************************************************************
			checkbox custom input limiting, goes along with validation
		******************************************************************/
		$('.max-select-set','#'+form.id).on('change', function() {
		  var curInput = $('[name="'+$(this).attr('name')+'"]');
		  //console.log(curInput);
		  var checked = curInput.filter(':checked'),
		  unchecked = curInput.filter(':not(:checked)');
		  if( checked.length >= curInput.data('max-selectable') ) {
		    unchecked.prop('disabled', true);
		  } else {
		    unchecked.prop('disabled', false);
		  }
		});

		/*******************************************************************
			Range w/ Preview
		******************************************************************/
		var aFormRanges = $('.range-inputtype','#'+form.id);
		if(aFormRanges.length > 0){
			aFormRanges.on('change', function(){
				var t = $(this);
				t.parents('.field-wrap').find('.range-inputtype-currentvalue').find('span').html(t.val());
			});
		}

		/*******************************************************************
			add slashes to date
		*******************************************************************/
		$('.date-inputtype-format','#'+form.id).bind('keyup',function(e){
			var key = e.keyCode || e.charCode;
			if (key == 8 || key == 46) return false;
			var strokes = $(this).val().length,
				dateFormat = $(this).data('useFormat');
			switch(dateFormat){
				case 'dd/mm/yyyy':
				case 'mm/dd/yyyy':
					if(strokes === 2 || strokes === 5){
					    var thisVal = $(this).val();
					    thisVal += '/';
					    $(this).val(thisVal);
					}
				break;
				case 'yyyy/mm/dd':
					if(strokes === 4 || strokes === 7){
					    var thisVal = $(this).val();
					    thisVal += '/';
					    $(this).val(thisVal);
					}
				break;
			}
			if(strokes >= 11){
				var vallimit = $(this).val();
				$(this).val(vallimit.substr(0, 10));
			}
		});

		/* error response fail message */
		$(form).append('<div style="color:red;display:none;" class="ajaxfail--response--message"></div>');
		/* UTM TRACKING added to the form */
		if(cookieMonsterWasHere != ''){
			$(form).attr('data-utm-tracking', cookieMonsterWasHere );
		}


		//custom
		aformPublic.prototype.afterPostMessage = function( aFI , aFID , ajaxresponse ){
			//aFI  = aForm instance
			//aFID = aForm ID
		}

	}

	$(function() { //doc.ready

		var AformsGroup = $("form.aform"); //grab all aForms
		AformsGroup.each( function(i,o) {
			new aformPublic( this ); //spin up new instance for each aForm, in case there's more than one per page
		});



	});

})(jQuery);
