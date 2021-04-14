var aformSubmissions = function(settings , el){
	
	this.settings = {
		exportAvailable : false
	};
	this.dataArr = [];
	this.el = {
		exportButton 	: document.querySelector(".aform-action__export"),
		exportBtnWrap : document.querySelector(".aforms-export"),
		formIdSelect 	: document.querySelector("[name='aform_id']"),
		dateFilter 		: document.querySelector("#filter-by-date"),
	};

}
//only available when a form is selected
aformSubmissions.prototype.availability = function(a){
	if(a.selectedIndex == 0){
		this.settings['exportAvailable'] = false;
	}else{
		this.settings['exportAvailable'] = true;
	}
}

aformSubmissions.prototype.init = function(){
	var _t = this;
	//click
	_t.el['exportButton'].addEventListener('click',function(e){
		document.querySelector('body').classList.add('export--modal');
	});
	//change
	// _t.el['formIdSelect'].addEventListener('change',function(e){
	// 	_t.availability(this);
	// 	if(_t.settings['exportAvailable'] == true){
	// 		_t.el['exportBtnWrap'].classList.add("is--available");
		
	// 	}else{
	// 		_t.el['exportBtnWrap'].classList.remove("is--available");
	// 	}
	// });

	//document.querySelector('#submissions_date-select').appendChild(_t.el['dateFilter'].cloneNode(true));

}

var _subs = new aformSubmissions();
_subs.init();

Vue.use(DatePicker)

var _subsVue = new Vue({
	el : '#aform-submissions_app',
	data: {
		startDate 		:'',
		showStartDate :false,
		endDate 			: '',
		showEndDate 	:false,
  },
	methods : {
		formatDate: function (date) {
		  return moment(date).format('MM-DD-YYYY')
		},
		closeModal : function(){
			document.querySelector('body').classList.remove('export--modal');
		},
		formChange : function(ev){
			if(ev.target.selectedIndex == 0){
				document.querySelector(".submissions_run-export").classList.remove('run--ready');
			}else{
				document.querySelector(".submissions_run-export").classList.add('run--ready');
			}
		},
		runExport : function(ev){
			//console.log("something");
		},
		onSubmit : function(ev){
			
			var form = document.querySelector('#export--form');
			var formData = new FormData(form);
			formData.append('calltype','getcount');			
			this.startExport(formData);

		},
		startExport : function(data){
			this.$http.post(ajaxurl, data).then(function(response){
				
				console.log("all good");
			
			});
		}
	}
});








