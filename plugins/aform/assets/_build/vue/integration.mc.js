import Vue from 'vue';
import VueResource from 'vue-resource';
import async from 'async';
//// my components
import myForms from './components/integrations/myforms.vue';
import myMailchimp from './components/integrations/mailchimp.vue';

var aformIntegration;

Vue.use(VueResource);

Vue.component('my-forms', myForms );
Vue.component('mailchimp_s', myMailchimp );

/////
window.defaultSettings = {
	settingsComp : '',
	integrationType : '',
	allForms : window.allForms,
	aforms : [],
	type : {
		mailchimp : {
			apikey : '',
			list : false,
			lists : [],
			fields : [],
			selected : false
		}	
	}
};
////
let aformIntegratio = new Vue({
	el : '#action_vue',
	data : defaultSettings,
	computed : {
		currentProperties : function(v){
			if( this.settingsComp === 'mailchimp_s'){ 
				return {
					mailchimp : this.type.mailchimp 
				}; 
			}
		}
	},
	methods : {

	},
	watch : {
		integrationType : function(val,oldVal){
			var self = this;
			if(val == 'mailchimp'){
				self.settingsComp = val + '_s';
				self.type[val].selected = true;
			}else{
				self.settingsComp = '';
			}	
		}	
	}
});

// FROM WP

if('integration_type' in window.intSettings){
		aformIntegratio.integrationType = window.intSettings.integration_type;
		if(window.intSettings.integration_type == 'mailchimp'){
			aformIntegratio.type.mailchimp = Object.assign(  aformIntegratio.type.mailchimp ,  window.intSettings);
		}
}

