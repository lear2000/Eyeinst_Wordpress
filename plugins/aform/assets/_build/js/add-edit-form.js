Array.max = function( array ){
    return Math.max.apply( Math, array );
};
function whichTransitionEvent(){
  var t,
      el = document.createElement("fakeelement");

  var transitions = {
    "transition"      : "transitionend",
    "OTransition"     : "oTransitionEnd",
    "MozTransition"   : "transitionend",
    "WebkitTransition": "webkitTransitionEnd"
  }

  for (t in transitions){
    if (el.style[t] !== undefined){
      return transitions[t];
    }
  }
}
function whichAnimationEvent(){
  var t,
      el = document.createElement("fakeelement");

  var animations = {
    "animation"      : "animationend",
    "OAnimation"     : "oAnimationEnd",
    "MozAnimation"   : "animationend",
    "WebkitAnimation": "webkitAnimationEnd"
  }

  for (t in animations){
    if (el.style[t] !== undefined){
      return animations[t];
    }
  }
}
var animationEvent = whichAnimationEvent();
var transitionEvent = whichTransitionEvent();
(function($) {
		
		var unsaved = false;

		///FORM CREATE FUNCTIONS 
		var formAjaxGlobals = {};
		var adminAjax = {
			formFields: $("#formFields"),
			setup: function(){
				$.ajaxSetup({
					url   : ajaxurl,
					type  : 'post'
				});			
			}, // setup
			classToggle: function() {
				formFields.toggleClass( 'ajax-active' );
			}, // classToggle
			createDuplicateField: function( fields , fieldLabel , f ){
				$.ajax({
					data: {
						action 		: 'afAjaxSwitch',
						method	   : 'cloneField',				
						fields 	   : fields,
						fieldlabel 	: fieldLabel
					}
				}).done( function( data ) {
					
					f = f||null;/* function */
					if(f != null){
						f.call( this , data );
					}else{
						console.log(data);
					}				
				});
			},
			fieldDrop: function( fieldType, fieldCount, aformPost , replaceItem , reusableID ) {
				
				var currentFieldCount = $(".form--field").length;
					
				reusableID = reusableID||false;

				$.ajax({
					beforeSend: this.classToggle,
					complete: this.classToggle,
					data: {
						action	: 'afAjaxSwitch',
						method   : 'newFormField',				
						field 	: fieldType,
						fieldcount : fieldCount,
						aformPost : aformPost,
						reusableID : reusableID
					}
				}).done( function(data) {
					
					var data = $(data),
						submitBtn = fieldDropZone.find('.submit-field');

					
					data.one(animationEvent,function(event) { data.removeClass('new-fieldstart') });	
					
					data.addClass('new-fieldstart');
					
					if(submitBtn.length > 0){
						if( $(replaceItem).prev().hasClass('submit-field') ){
							$(replaceItem).remove();
							submitBtn.before(data);
						}
						else if( fieldCount > 0 ){
							$(replaceItem).replaceWith( data );				
						}
						else{
							$(replaceItem).remove();
							submitBtn.before(data);
						}	
					}else{
						
						$(replaceItem).replaceWith( data );	
						
					}
									
					var newField = $(".form--field:not(.submit-field)").last(),
					newInputs = newField.find( '.input-values' );

					newInputs.sortable({
						handle: '.handle',
						items: '> div:not(.submit-field)'
					});
					
					checkboxToggler('for_admin_email');
					checkboxToggler('primary_email_recipient');
					checkboxToggler('use_as_confirmation_subject');
					checkboxToggler('conditional_confirmation');
					choicesLabelRequired();

					setTimeout(function() {
						var newFieldCount = $(".form--field");
						if(currentFieldCount < newFieldCount.length ){
							
							var fields = newFieldCount;
							
							fields.each(function(i) {
								$(this).find('.aform-input-order').val( i );
								$(this).attr('data-index',i);
								if( (i+1) == newFieldCount.length){
									
									data.slideDown('500', function() {});
									
									uiSlider.create();
									duplicateField();	
								}
							});
						}
					}, 250);

					unsaved = true;
					
				});
				
			}, // fieldDrop
			
			newInputValue: function( values, index, count , option  , inputType) {
				option = option || null;
				$.ajax({
					beforeSend    : this.classToggle,
					complete      : this.classToggle,
					data          : {
						action    : 'afAjaxSwitch',
						method    : 'createChoice',
						index     : index,
						count     : count,
						option    : option,
						inputtype : inputType
					}
				}).done( function(data) {
					values.append( data );	
				});
			}, // newInputValue
			
			deleteField: function( fieldID, field , f ) {
				f = f||null;/* function */
				$.ajax({
					beforeSend  : this.classToggle,
					complete    : this.classToggle,
					data        : {
						action	: 'afAjaxSwitch',
						method   : 'deleteFormField',
						fieldID	: fieldID
					}
				}).done( function( data ){
					if(f != null){
						f.call( this , data );
						unsaved = true;
					}else{
						field.remove();
						unsaved = true;
					}
				});
			}, // deleteField
			deleteReusableField:function( id , f ){
				f = f||null;
				$.ajax({
					data: {
						action	: 'afAjaxSwitch',
						method	: 'deleteFormField',
						fieldID	: id,
						t        : 'reusablefields'
					}
				}).done(function( data ){
					/* run our callback */
					if(f != null){
						f.call( this , data );
					}
				});
			}

		}//adminAjax


		var uiSlider = {
			maxsize : 200,
			parentWrap : '.filesize-slider-wrap',
			maxFileSizeInputs : 'input.max-filesize-input',
			spanMaxFileSizeInputs : 'span.max-filesize-input',
			filesizeSlider : ".filesize-slider",
			setup : function(maxsize){
			var t = this;
			var maxsize = maxsize ? maxsize : t.maxsize;

				t.maxsize 	= maxsize;
				t.create().liveSlide();
				return t;
			},
			create : function(){
				var t 		= this;
				var maxsize = t.maxsize;

				$(t.filesizeSlider).not('.ui-slider').slider({
					range  : "max",
					value  : 1,
					min    : 1,
					max    : maxsize,
					create : function(event, ui){
						var s 	= $(this);
						var	v = s.parents( t.parentWrap ).find( t.maxFileSizeInputs ).val();						
						s.slider("value",v);
					}
			    });
				
				return t;
			},
			liveSlide : function(){
				var t = this;
				$(t.filesizeSlider).on( "slide", function( event, ui ) {
					$(this).parent().find( t.maxFileSizeInputs ).val(ui.value);
					$(this).parent().find( t.spanMaxFileSizeInputs ).html(ui.value);
				} );
				return t;
			},
		};//uiSlider

		adminAjax.setup();
		
		uiSlider.setup();
		
		var fieldDropZone  = $('#fieldDropzone'),
			formFields     = $("#formFields"),
			submitBtn      = $("#submitField"),
			fieldList      = $(".add--field");
		
		/* variables below are repeatedly used when new fields are dropped, cached for that reason */
		var submitFields   = submitBtn.find('input');
		var subIO          = submitBtn.find('input[name*="input_order"]');

		//drag-n-drop for adding new fields
		function fieldListDraggable($o){
			$o.draggable({
				appendTo: "body",
				helper: "clone",
				cursor: "move",
				connectToSortable : '#fieldDropzone',
				drag : function( event, ui ){

				},
				start: function( event, ui ) {
					fieldDropZone.addClass('droppable-active');
				},
				stop: function( event, ui ) {
					fieldDropZone.removeClass('droppable-active');
				}
			});
		}
		fieldListDraggable(fieldList);

		//sort fields that have been added to the form
		fieldDropZone.sortable({
			containment: 'parent',
			handle: '.handle',
			items: ' > div:not(.submit-field)',
			opacity: .7,
			receive: function( e , ui ) {
				//alert();
				$(ui.helper).css({'opacity' : '0.25','width':'100%'});
				setTimeout(function() {

					var fields 		= fieldDropZone.children('.form--field'),
						reusableId 	= ui.helper.data('reusableId')||false,
						fieldType 	= ui.helper.data('fieldType'),	
						fieldCount;
					

					
					var existingIndexes = [0];
					fields.each( function( index, elem ) {		
						var thisOrder = $(elem).find('input[name*="input_order"]').val();

						existingIndexes.push( thisOrder );
					});

			    	existingIndexes.sort(function(a, b){
				        return b - a;
				    });
				    
				    fieldCount = existingIndexes[0];
				    
					// drop new field via AJAX
					adminAjax.fieldDrop( fieldType , fieldCount, aformPost , replaceItem = ui.helper , reusableId );			
					
					if(submitBtn.length > 0){
						setTimeout(function() {
							var newCount = parseInt(fieldCount) + 1;
							subIO.val( newCount );
						}, 200);	
					}

					
					
				}, 200);

			},
			stop: function( event, ui ) {
				if( $(ui.item).hasClass('form--field')){
					var fields = $(this).find( '.form--field' );
					fields.each(function(i) {
						$(this).find('.aform-input-order').val( i );
						$(this).attr('data-index' , i);
					});
				}
			},
			tolerance: 'pointer'
		}).droppable({

		});
			
		/************************************
		******
		* Field UI Functions
		******
		************************************/
		 

		/******************************************************
		* make input-values sortable, this works on preexisting fields
		****************************************************/
		var inputValues = formFields.find('.input-values');	
		inputValues.sortable({
			handle: '.handle',
			items: 'div'		
		});

		/******************************************************
		* add new input value for select, checkbox groups, radio buttons
		****************************************************/
		formFields.on('click', '.add-input-value', function(e) {
			e.preventDefault();
			var $this       	= $(this),
				values       	= $this.siblings('.input-values'),
				fieldIndex   	= $this.parents('.form--field').find('.aform-field-id').val(),
				valIndex     	= values.find('div').length,				
				fieldParent  	= $this.parents('.form--field'),
				findConditional = fieldParent.find('.apply-conditional-conf'),
				inputType 		= fieldParent.data('inputType'),
				option 			= '';

			if( findConditional.prop('checked') ){
				option = 'conditional_confirmation';
			}
			else{
				//console.log('false');
			}
			var inputValuesWrapper = fieldParent.find('.input-values');
			
			if(inputValuesWrapper.attr('data-label-optional') == true ){
				option = 'label_is_required';
			}

			adminAjax.newInputValue( values , fieldIndex , valIndex , option , inputType );
			
		});

		/******************************************************
		*	CONDITIONAL CONFIRMATION 
		******************************************************/
		// var conditionalConfirmationApplied = false,
		// 	conditionalConfirmationCheck = $('.apply-conditional-conf');

		// 	if(conditionalConfirmationCheck.length > 0){
		// 		conditionalConfirmationCheck.each(function( i , o){
		// 			if( o.checked == true){
		// 				conditionalConfirmationApplied = true;
		// 			}
		// 		});
		// 	}

		formFields.on('click', '.apply-conditional-conf', function(e) {

			var t = $(this),
				parent = t.parents('.form--field');

			// if( conditionalConfirmationApplied == true && t.prop('checked') ){
			// 	t.attr('checked', false);
			// 	alert('Already in Use');
			// 	return;
			// }

			if(t.prop('checked')){
				parent.find('.conditional-confirmation-launch').removeAttr('style');
				// conditionalConfirmationApplied = true;
				// console.log('has run');
			}else{
				parent.find('.conditional-confirmation-launch').hide();
				// conditionalConfirmationApplied = false;
			}
		});
		
		var conditionalConfirmationLaunch = true;
		formFields.on('click', '.conditional-confirmation-launch', function(e) {
			e.preventDefault();
			
			if(conditionalConfirmationLaunch == true){
				
				var t 		= $(this);
					parents = t.parents('.field-tab'),
					parent  = t.parent();
					base    = parents.parent();
					text    = parent.find('textarea').val();
					subject = parent.find('.cond-conf-subject').val();

					parent.addClass('now-editing');
					parents.addClass('now-editing');

					fieldID = base.find('.field-id-ref').data('fieldRefId');
				$.ajax({
						data: {
							action	  : 'afAjaxSwitch',
							method     : 'renderWPField',
							field      : 'wysiwyg',
							id         : fieldID
						}
					}).done( function( data ) {
						parents.find('.wysiwig-wrapper .content-box').append(data);
						setTimeout(function() {
							parents.find('.wysiwig-wrapper').slideDown('400', function() {
								var id = parents.find('.wysiwig-wrapper').find('textarea').attr('id');
								quicktags({
									id: id ,
									buttons: "",
									disabled_buttons: ""
								});
								QTags._buttonsInit();
								parents.find('.wysiwig-wrapper').find('textarea').val(text);
								parents.find('.wysiwig-wrapper').find('.custom-subject-line-temp').val(subject);
							});							
						}, 500);
						
					});
			}
			conditionalConfirmationLaunch = false;
		});
		formFields.on('click', '.close-content', function(e) {
			e.preventDefault();
			var t 		= $(this),
				parent = t.parents('.wysiwig-wrapper');
				parents = t.parents('.field-tab');

				parent.slideUp('400',function(){
					conditionalConfirmationLaunch = true;
					parent.find('.content-box').html('');
					parent.find('.custom-subject-line-temp').val('');
					parents.find('.now-editing').removeClass('now-editing');
					parents.removeClass('now-editing');
				});

		});

		formFields.on('click', '.apply-content', function(e) {
			e.preventDefault();
			var t 		= $(this);
				parent = t.parents('.wysiwig-wrapper');
				parents = t.parents('.field-tab');
				wpeditor = parent.find('textarea').val();
				subject  = parent.find('.custom-subject-line-temp');
				
				parents.find('.now-editing').find('textarea').html( wpeditor );
				parents.find('.now-editing').find('.cond-conf-subject').val( subject.val() );
				
				setTimeout(function() {
					parent.slideUp('400',function(){
						conditionalConfirmationLaunch = true;
						parent.find('.content-box').html('');
						subject.val('');
						parents.find('.now-editing').removeClass('now-editing');
						parents.removeClass('now-editing');
					});
				}, 1000);
				

		});
		
		/******************************************************
		* Toggle Basic/Advanced field setings
		****************************************************/
		formFields.on('click', '.field-create-tabs button', function(e) {
			e.preventDefault();
			$this = $(this);
			
			if( !$this.hasClass('selected') ) {
				var field = $this.parents('.field-create');
				field.find('.field-tab.selected').removeClass('selected');
				$this.siblings('.selected').removeClass('selected');

				
				$this.addClass('selected');
				field.find( '#fieldCreate' + $this.data('tab') ).addClass('selected');			
			}			
		});
		
		formFields.on('click', '.adv-toggle-tab a', function(e) {
			e.preventDefault();
			var toggle = $(this).parents('.toggle-tab');
			toggle.toggleClass('active');
			
			//var target = toggle.next('.field-tab');
			//target.slideToggle();
			
			$(this).parents('.form--field').toggleClass('edit-mode');
			$('body').toggleClass('edit-mode-on');

		});

		formFields.on('click', '.toggle-choices a', function(e) {
			e.preventDefault();
			var toggle = $(this).parents('.toggle-tab');
			toggle.toggleClass('active');
			
			var target = toggle.next('.field-tab');
			target.slideToggle();
			
			//$(this).parents('.form--field').toggleClass('edit-mode');
			//$('body').toggleClass('edit-mode-on');

		});

		formFields.on('click', '.close-field-wrapper a', function(e) {
			e.preventDefault();
			var t = $(this);
			t.parents('.form--field').find('.adv-toggle-tab a').trigger('click');
		});
		

		/******************************************************
		* remove a given form field
		****************************************************/
		var $deleteDialogBox = $("#deleteDialog");
		$deleteDialogBox.dialog({
			'autoOpen'		: false,
			'closeOnEscape'	: true,
			'dialogClass'	: 'wp-dialog',
			'minHeight'		: 80,
			'modal'			: true
		}); 	
		formFields.on('click', '.delete-field', function(e){
			e.preventDefault();
			var $this = $(this);
			var field = $this.parents('.form--field');
			
			var fieldID = field.find('input.aform-field-id').val();
			
			$deleteDialogBox.dialog({
				'buttons'	: {
					'Don\'t Delete' : function() {
						$(this).dialog( 'close' );
					},
					'Delete' : function() {
						adminAjax.deleteField( fieldID, field , function( data ){
							field.slideUp('slow',function(){
								$(this).remove();
									checkboxToggler( 'for_admin_email' );
									checkboxToggler( 'primary_email_recipient' );
									checkboxToggler( 'use_as_confirmation_subject');
									checkboxToggler( 'conditional_confirmation');
									choicesLabelRequired();
							});
						});
						$(this).dialog( 'close' );
					}
				}
			});
			
			$deleteDialogBox.dialog( 'open' );
			
			//$(this).parents('.form--field').remove();
		});	
		
		/******************************************************
		* Delete value options
		****************************************************/  
		formFields.on( 'click', '.delete-value', function(e) {
			console.log( 'loggy');
			var target = $(this).parent('div');
			$deleteDialogBox.dialog({
				'buttons'	: {
					'Don\'t Delete' : function() {
						$(this).dialog( 'close' );
					},
					'Delete' : function() {
						target.remove();
						$(this).dialog( 'close' );
					}
				}
			});
			$deleteDialogBox.dialog( 'open' );
		});
		
		 	 
		/******************************************************
		* bind values that should be paired, separate once changes are intentional
		****************************************************/  
		var keytimeout;	 
		formFields.on( 'keyup', '.value-bind', function(){
			var $this = $(this);
			var parent = $(this).parents(".value-bind-parent");
			
			clearTimeout(keytimeout);
			 
			keytimeout = setTimeout(function() {
				if( $this.data('uglify') == true ) {
					
					valueSet = parent.find('.value-set')
					valueSet.val( $this.val().toLowerCase() );
					
					/* remove special characters */
					valueSetClean  = valueSet.val().replace(/\?|<|>|\.|,|\/|{|}|\[|\]|=|\+|\*|\&|\^|\%|\$|\#|\@|\!|\(|\)|\_|\`|\~/g,'');
					valueSetClean = valueSetClean.replace(/\s/g,'-');
					valueSet.val(valueSetClean);

					
				} 
				else {
					parent.find('.value-set').val( $this.val() );
				}			
			}, 150);
			 
		});	 
		 
		formFields.on( 'keyup', '.value-set', function() {
			if ( $(this).val() != $(this).siblings('.value-bind').val() ) {
				$(this).removeClass( 'value-set' );
				$(this).parents('.field-create-basic').find('.value-bind').removeClass( 'value-bind' );
			}
			/* should always be bound?*/
			// else if($(this).hasClass('is-label-optional-input')){
			// 	$(this).removeClass( 'value-set' );
			// 	$(this).parents('.field-create-basic').find('.value-bind').removeClass( 'value-bind' );
			// }
		});
		
		/******************************************************
		* Enable & disable fields that are mutually exclusive
		****************************************************/ 	
		function checkboxToggler(inputName) { //gets passed the input name of checkboxes where only one per form should be checked
			var boxes = $('input[name*="'+inputName+'"]');
			if( boxes.length == 0 ) {
				return false;
			}
			var checked = boxes.filter(":checked");
			if( checked.length > 0 ) {
				boxes.filter(':not(:checked)').prop( 'disabled', true );
			} else {
				boxes.filter(':not(:checked)').prop( 'disabled', false );
			}
		}
		function choicesLabelRequired(){
			formFields.on( 'change', 'input[name*="use_choicelabel_as_value"]', function() {
				var t = $(this);
				var fieldParent  = t.parents('.form--field');
				if(t[0].checked == true){
					fieldParent.find('.is-label-optional').html('required');
					fieldParent.find('.is-label-optional-input').attr('required' , 'required');
					fieldParent.find('.input-values').attr('data-label-optional' , 1 );
				}else{
					fieldParent.find('.is-label-optional').html('optional');
					fieldParent.find('.is-label-optional-input').removeAttr('required');
					fieldParent.find('.input-values').attr('data-label-optional' , 0 );
				}
			});	
		}
		
		formFields.on( 'change', 'input[name*="for_admin_email"]', function() {
			checkboxToggler( 'for_admin_email' );
		});
		
		formFields.on( 'change', 'input[name*="primary_email_recipient"]', function() {
			checkboxToggler( 'primary_email_recipient' );
		});

		formFields.on( 'change', 'input[name*="use_as_confirmation_subject"]', function() {
			checkboxToggler( 'use_as_confirmation_subject' );
		});

		formFields.on( 'change', 'input[name*="conditional_confirmation"]', function() {
			checkboxToggler( 'conditional_confirmation' );
		});
		
		checkboxToggler( 'for_admin_email' );
		checkboxToggler( 'primary_email_recipient' );
		checkboxToggler( 'use_as_confirmation_subject');
		checkboxToggler( 'conditional_confirmation');

		/* 
			Use value as admin email 
			Use Label as Value when sending Admin Email
			Label is REQUIRED
		*/
		choicesLabelRequired();

			
		
		/******************************************************
		* Toggle visibility of paired fields
		*****************************************************/	
		function toggleTarget( toggle, targetClass ) {
			
			var parent   = $(toggle).parent();
			var parentTop = $(toggle).parents('.af-toggle-grouped');
			
			if(parentTop.length > 0){ 
				var targets  = parentTop.find( '.' + targetClass );
			}else{
				var targets  = parent.siblings( '.' + targetClass );
			}

			targets.slideToggle(400);		
		}	
		
		formFields.on( 'change', '.enabler', function() {		
			toggleTarget( this, $(this).data( 'target' ) );
		});
		
		$("#formsettings").on( 'change', '.enabler', function() {
			toggleTarget( this , $(this).data( 'target' ) );		
		});

		formFields.on('change', '.html5_type_options' , function(){
			var t = $(this),
				o = $('option:selected' , $(this)),
				v = o.attr('value');
			if(v == 'date'){
				t.parents('.field-tab').find('.dateValidationFormat').show();
				t.parents('.field-tab').find('.html5RangeOptions').hide();
			}
			else if(v == 'range'){
				t.parents('.field-tab').find('.dateValidationFormat').hide();
				t.parents('.field-tab').find('.html5RangeOptions').show();
			}
			else{
				t.parents('.field-tab').find('.html5RangeOptions').hide();
				t.parents('.field-tab').find('.dateValidationFormat').hide();
			}
		});
		
		
		/****************************************************** 
		* GENERATE SHORTCODE 
		*****************************************************/
		// $("#shortcodeGenerator").on( 'click', function(e) {
		// 	e.preventDefault();
		// 	var postName = $("#post_name").val();
		// 	$(this).siblings('input')
		// 		.attr( 'type', 'text' )
		// 		.val( '[seaforms name="' + postName + '"]' );
		// });	
		
		/****************************************************** 
		* CLONE FIELD : this may need some work
		*****************************************************/
		var reusableFieldBox = $("#reusableFieldBox");
		reusableFieldBox.dialog({
			'autoOpen'		: false,
			'closeOnEscape'	: false,
			'width'         : 390,
			'minHeight'		: 80,
			'modal'			: true,
		});
		function duplicateField(){
			$('.duplicate-field').unbind('click');
			$('.duplicate-field').on('click',function (e){
				e.preventDefault();

				var t = $(this),
				fieldWrap = t.parents('.form--field'),
				fields = $(':input' , fieldWrap),
				fieldWrapId = fieldWrap.attr('id');
				
				reusableFieldBox.dialog({
					appendTo: '#'+fieldWrapId,
					'buttons'	: {
						'Cancel' : function() {
							$(this).find(':input').removeAttr('style');
							$(this).dialog( 'close' );
						},
						"Create" : function() {

							var clonename = $(this).find(':input');
							if( clonename.val() == ""){
								
								clonename.css({"border":"1px solid red"});
							
							}else{
								myModal = $(this);
								fields = fields.serialize();
								clonename.removeAttr('style');
								
								myModal.find('.clonename-wrap').hide();
								myModal.find('p').show();
								myModal.parent().find('.ui-dialog-buttonpane').hide();
								myModal.parent().find('button.ui-dialog-titlebar-close').hide();

								setTimeout(function() {	
									adminAjax.createDuplicateField( fields , clonename.val() , function ( data ){
										data = JSON.parse(data);
										myModal.find(':input').val('');
										var newRF = $(data.html).addClass('new--reusable').hide();
										fieldListDraggable( newRF );//make Draggable
										$('.available-reusablefields').append( newRF );
										setTimeout(function() {
											myModal.find('.clonename-wrap').removeAttr('style');
											myModal.parent().find('.ui-dialog-buttonpane').removeAttr('style');
											myModal.parent().find('button.ui-dialog-titlebar-close').removeAttr('style');
											myModal.find('p').hide();	
											myModal.dialog('close');
											myModal.dialog('close');
											myModal.dialog({
												appendTo : 'body'
											});
											$('.available-reusablefields').find('.new--reusable').slideDown('slow', function() {
												$(this).removeClass('new--reusable')
											});
										}, 750);
									});
								}, 750);
							}
						}
					}
				});
				reusableFieldBox.dialog( 'open' );
			});	
		}
		duplicateField();
		//$(".add--field")
		$("#reusablefields").on('click','.delete-reusable-field',function (e){
			e.preventDefault();
			var t = $(this),
				id = t.data('removeReusable')||false;
				parent = t.parents('.add--field');
			$deleteDialogBox.dialog({
				'buttons'	: {
					'Don\'t Delete' : function() {
						$(this).dialog( 'close' );
					},
					'Delete' : function() {
						var modal = $(this);
						formAjaxGlobals['deleteReusable'] = {};
						parent.css({
							'opacity' : '0.5'
						});
						adminAjax.deleteReusableField( id , function( data ){
							setTimeout(function() {
								formAjaxGlobals.deleteReusable = data;
							}, 1500);
						});
						/**
						* Waits on ajax to complete then removes 
						*/
						var _checkStatus = setInterval(function(){
							if( formAjaxGlobals.deleteReusable.hasOwnProperty('success') ){
								
								if(formAjaxGlobals.deleteReusable.success == true ){
									parent.slideUp('slow',function (e){ $(this).remove(); });
								}
								else{
									parent.css({ 'opacity' : '1' });
								}
								delete formAjaxGlobals.deleteReusable;
								clearInterval(_checkStatus); 
							}
						},200)
						modal.dialog( 'close' );	
					}
				}
			});
			$deleteDialogBox.dialog( 'open' );
		});

		/* TOGGLE FIELDS/SETTINGS */
		$('ul#formTabs li a').on('click',function(e){
			e.preventDefault();
			var t  		= $(this),
				ul 		= t.parents('#formTabs'),
				elHide 	= ul.data('tabfor'),
				elShow 	= t.attr('href');
			ul.find('li').removeClass('selected');	
			t.parent('li').addClass('selected');
			$(elHide).hide();
			$(elShow).show();
		});

		/*click on load*/
		$('ul#formTabs li').first().find('a').trigger('click');
		
		/********
		*	Updates Form name/slug
		*	-- shortcode name --
		********/
		$('.afsc-input input').on('keydown' , function(e){
			var code = e.keyCode || e.which;
			if(code == 13){
				$('#afsc a.afsc-close').trigger('click');
				return false;
			}else if( code == 9 ){
				$('#afsc a.afsc-close').trigger('click');
			}
		});
		
		$('#afsc a.afsc-edit').on('click' , function (e){
			e.preventDefault();
			var t = $(this);
			t.addClass('hidden');
			t.parents('#afsc').find('.afsc-name').addClass('hidden');
			t.parents('#afsc').find('.afsc-input').removeClass('hidden');
			t.parents('#afsc').find('.afsc-close').removeClass('hidden');
			$('#shortcodeUpdateDialog').dialog({
				modal         : true,
				position      : { my: "center top", at: "center bottom" , of: $("#afsc") },
				buttons: {
					Ok: function() {
					  $( this ).dialog( "close" );
					}
				}
			});
		});
		$('#fieldDropzone').on('click','.choicesfromstring',function (e){
			e.preventDefault();
			var t = $(this),
			fieldId 			= t.data('fieldid'),
			inputVal 			= t.parents('.field-tab').find('.holds-choices'),
			inputValLength 		= inputVal.children('div').length,
			inputValLengthArr	= [];

			if(inputValLength > 0){
				inputVal.children('div').each(function(i, el) {
					el = $(el);
					inputValLengthArr.push(el.data('currentindex'));
					if(inputValLength == (i+1)){
						inputValLength = Array.max(inputValLengthArr);
						inputValLength = (inputValLength+2);
					}
				});
			}

			/* CREATES CHOICES FROM STRING */
			$('#createChoicesDialog').dialog({
				title 			: 'Bulk Create From String',
				minWidth 		: 450,
				modal 			: true,
				buttons: {
					Cancel : function(){
						$(this).dialog( 'close' );
					},
					Create: function() {
					  	var d = $(this);
					  	var textarea = $(this).find('textarea'),
					  		choices = textarea.val();

					  if(choices != ""){
					  	dialogbuttons = d.next();
					  	dialogbuttons.hide();
					  	$.ajax({
							data        : {
								action  : 'afAjaxSwitch',
								method  : 'createChoicesFromString',
								fieldId : fieldId,
								choices : choices,
								count   : (inputValLength+2)
							}
						}).done( function(data) {
							data = JSON.parse(data);
							if('html' in data){
								inputVal.append(data.html);
								textarea.val('');
								dialogbuttons.show();
								d.dialog( "destroy" );
							}else{
								dialogbuttons.show();
								d.dialog( 'close' );//dialog box close
								alert('something went wrong :(');
							}
						});	
					  }else{
					  	//alert('something went wrong :(');
					  }
					}
				}
			});
		});
		/* 
			COPY FROM INTO 'Confirmation FROM' 
			but only if 'Confirmation FROM' is empty
		*/
		$('#formsettings').on('blur','input#admin_email_from', function(e){
			var valueCheck = $('#conf_email_address').val(), 
				t = $(this), 
				v = t.val();
			if($.trim(valueCheck) == ''){
				if($.trim(v) != ''){
					$('#conf_email_address').val(v);
				}
			}
		});
		
		/* SHORTCODE */
		$('#afsc a.afsc-close').on('click' , function (e){
			var t = $(this);
			$.post(ajaxurl, {
	            action: 'sample-permalink',
	            post_id: $('#post_ID').val(),
	            new_slug: t.parents('#afsc').find('input').val(),
	            new_title: $('#title').val(),
	            samplepermalinknonce: $('#samplepermalinknonce').val()
	        }, function(data) {
	        	
	        	$('.permalink-ph').html(data);
	        	var slug = $('.permalink-ph').find('#editable-post-name-full').html();

	        	t.parents('#afsc').find('.afsc-name .post_name').html(slug);
	        	t.parents('#afsc').find('.afsc-input input').val(slug);
	        	t.parents('#afsc').find('.afsc-name').removeClass('hidden');
	        	t.parents('#afsc').find('.afsc-input').addClass('hidden');
	        	t.parents('#afsc').find('.afsc-edit').removeClass('hidden');
	        	t.addClass('hidden');

	        });
		});

		//check before save/reload
		var emptyFieldBox = '<div style="display:none;"><div id="emptyFieldsDialog" style="text-align:center;">';
			emptyFieldBox += 'Oops!<br>Not much of a form if there\'s no fields<br>:)';
			emptyFieldBox += '</div></div>';
				
		$('body').append(emptyFieldBox);
		// //WHEN POST IS CRETED/UPDATED
		$('#emptyFieldsDialog').dialog({
			autoOpen			: false,
			closeOnEscape	: false,
			minHeight		: 80,
			modal 			: true,
			position 		: { my: "center", at: "top+150", of: window },

		});
		$('form#post').on('submit',function(){
			var availableFields = $('.field--dropzone').find('.form--field');
			if(availableFields.length == 0){
				//unsaved = true;
				$('#emptyFieldsDialog').dialog( 'open' );
				return false;
			}
			unsaved = false;
		});
		function unloadPage(){ 
		    if(unsaved){ return "The changes you made will be lost if you navigate away from this page."; }
		}
		window.onbeforeunload = unloadPage;

})(jQuery);
