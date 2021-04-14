import Vue from 'vue';
import Datepicker from 'vuejs-datepicker';
import VueResource from 'vue-resource';
import JsonExcel from 'vue-json-excel';
//import moment from 'moment';
import async from 'async';

var aformSubmissions = {
	el : {
		exportButton 	: ".aform-action__export",
		exportBtnWrap : ".aforms-export",
		formIdSelect 	: "[name='aform_id']",
		dateFilter 		: "#filter-by-date",
	},
	qS : function(s){
			return document.querySelector(s);
	},
	init : function(){
		var _t = this;
		//click
		_t.qS(_t.el['exportButton']).addEventListener('click',function(e){
			_t.qS('body').classList.add('export--modal');
		});
	}
};

aformSubmissions.init();

///VUE
Vue.use(VueResource);
Vue.component('downloadExcel', JsonExcel);
var headings = [];
var _subsVue = new Vue({
	el : '#aform-submissions_app',
	components: { 
		Datepicker 
	},
	data : {
		exportClassStatus  : '',
		CLASS_CSV 				 : 'csv-not-ready',
		CLASS_exporsteps 	 : 'export--steps no-show',
		fetch_progressbar  : 'fetch-progressbar',
		fethpercentitem    : 0,
		fetchprogress 		 : 0,
		csv_filename  		 : 'filename.csv',
		exportHeaders 		 : [],
		exportData 				 : [],
		exportMessage 		 : '',
		exportResults 		 : '&nbsp;',
		total     				 : 0,
		resultstotal 			 : 0,
		calltype 					 : 'getcount',
		startDate 				 : null,
		endDate   				 : null,
		formId    				 : '',
		exportStartTimeout : 300,
		startDateDisabled  : {
			to 		: "",
			from 	: "",
		},
		endDateDisabled : {
			to 		: "",
			from 	: ""
		},
		csv_headers : {

		},
		csv_rows : [],
		json_meta: [
			[{
				"key" 	: "charset",
				"value"	: "utf-8"
			}]
		]
	},
	actions : {

	},
	methods : {
		cleared : function(){
			//
		},
		formDate : function(date){
			//return moment(date).format('YYYY-MM-DD');
		},
		datePickerOpened : function(){
			//console.log("picker was opened");
		},
		startDateSelected : function(val){
			
			this.endDateDisabled.to = val;
			//if(val == null){ this.startDate = ''; }
			this.requiredCheck();
		},
		endDateSelected : function(val){

			this.startDateDisabled.from = val;
			//if(val == null){ this.endDate = ''; }
			this.requiredCheck();
		
		},
		formIdChange: function(){
			this.requiredCheck();
		},
		resetSettings : function(t){
			
			t = t||false;	

			this.calltype 					= 'getcount';//reset call type when submit
			this.resultstotal 			= 0;
			this.total 							= 0;
			this.exportHeaders 			= [];
			this.exportData 				= [];	
			this.csv_headers 				= {};
			this.csv_rows						= [];
			this.exportMessage  		= '';
			this.CLASS_CSV 					= 'csv-not-ready';
			this.CLASS_exporsteps 	= 'export--steps no-show';
			this.fetch_progressbar 	= 'fetch-progressbar';
			this.fethpercentitem   	= 0;
			this.fetchprogress 			= 0;
			this.exportResults 			= '&nbsp;';
			if(t == true){
				this.formId = '';
				this.startDate = null;
				this.endDate = null;
			}

		},
		requiredCheck : function(){
			var self = this;
			setTimeout(function() {
				if(self.startDate != null && self.endDate != null && self.formId != ''){
					setTimeout(function() {
						document.querySelector(".submissions_run-export").classList.add('run--ready');
						self.exportStartTimeout = 250;
					}, self.exportStartTimeout );
				}else{
					document.querySelector(".submissions_run-export").classList.remove('run--ready');
					self.exportStartTimeout = 250;
				}	
			}, 250);

			//clear data when any input changes
			self.resetSettings();

		},
		closeModal : function(){
			this.resetSettings(true);
			this.requiredCheck();
			document.querySelector('body').classList.remove('export--modal');
		},
		getFormData : function(){
			var form = document.querySelector('#export--form');
			var formData = new FormData(form);
			return formData;
		},
		onSubmit : function(ev){
			
			var self = this;

			setTimeout(function() { self.startExport(); }, 300);
			
			document.querySelector(".submissions_run-export").classList.remove('run--ready');

			self.exportMessage = 'Export Started';

		},
		startExport : function(data){
			var self = this, fieldLength;
			var data = this.getFormData();
			this.$http.post(ajaxurl, data).then(function(response){
				setTimeout(function() {
					if(response.status == 200){
						response = response.body;
						if( !( typeof response === 'object')){
							self.exportMessage = 'Something went wrong';
							return;
						}
						if(response.calltype == 'getcount'){
							if(response.total > 0 && ( 'cont' in response && response.cont == true ) ){
									
									self.fetch_progressbar = 'fetch-progressbar is--visible';

									self.fethpercentitem 	= 100 / response.total;	
									self.exportMessage 		= response.total + ' Found';
									self.calltype 				= response.nextcall;
									self.total 						= response.total;
									fieldLength 					= response.formFields.length;
										
										//
										self.csv_headers['Date'] = 'date-0'; 
										self.exportHeaders['date-0'] = '';
										self.csv_filename = 'aform-export--'+response.formname + '.csv';
										async.map(response.formFields , function(d , next){
											
										
											self.csv_headers[d.display_name] = d.field_name_id; 
											self.exportHeaders[d.field_name_id] = '';
											//continue
											next(false);
										
										}, function(err , response){
												setTimeout(function() {
													self.exportMessage = 'Fetching Results';
													self.startExport()
												}, 300);
												
										});

							}else{
								self.exportMessage = 'No submissions founds';
							}	
						}
						if(response.calltype == 'getsubmissions'){
							
							self.calltype 			= response.nextcall;
							self.total 					= response.total;
							self.resultstotal 	= response.resultstotal;

							//push
							Array.prototype.push.apply(self.exportData , response.results);
							
							setTimeout(function() {
								if(response.total  == response.resultstotal){
									self.calltype 		= 'done';
									self.fetchprogress = 100;
								}
								
								var resultsnow = self.resultstotal + '/' + self.total;
								
								self.exportMessage = 'Fetching Results';
								self.exportResults = resultsnow;
								
								var resultsLength  	= response.results.length;
								self.fetchprogress 	= (self.fetchprogress + (self.fethpercentitem * resultsLength));

								if(response.results.length <= 0){
									self.calltype 		= 'error';
								}
								setTimeout(function() {

									self.startExport();		
								
								}, 250);	
							}, 250);
						}

						if(response.calltype == 'error'){
							console.log("huh?");
						}
						if(response.calltype == 'done'){
							
							self.exportMessage = '<span>Building Rows</span> <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';
							var currentRow = 0;
							async.map(self.exportData , function(res , step1){
								
								var ROW = Object.assign({},self.exportHeaders);
								
								//starts adding data to row columns
								async.map( res , function(ress,step2){

									if(ress.field_name_id == 'date-0'){
										//var _d = new Date(ress.value);
										//console.log(_d.getFullYear() + '-'+ ( _d.getMonth() + 1 ) +'-'+ _d.getDate());
										//ress.value = self.formDate(ress.value);
									}
									
									//save item to row column
									ROW[ress.field_name_id] = ress.value;
									
									//if column header is not available then create 
									if( !(ress.display_name in self.csv_headers) ){
										self.exportHeaders[ress.field_name_id] = '';
										self.csv_headers[ress.display_name] = ress.field_name_id;
									}

									setTimeout(function() {
										step2(false);
									}, 250);
										
								
								}, function(){//when done

									setTimeout(function() {
										
										self.$set( self.csv_rows , currentRow , ROW );

										currentRow++;
										step1(false)
									}, 250);
									
								});

							} , function(err , results){


								var timeoutMax = ( 10 * self.total);
								if(self.total < 99 ){
									timeoutMax = 2000;
								}
								setTimeout(function() {
									self.exportStartTimeout = 1500;
									self.exportMessage 		= 'Creating CSV <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';
									setTimeout(function() {
										self.exportMessage 	= 'Complete';
										self.CLASS_CSV 			= 'csv-is-ready';	
									}, timeoutMax );	
								}, 500);
								
							});
						}
					}
				}, 500);
			});
		},
	}
});


