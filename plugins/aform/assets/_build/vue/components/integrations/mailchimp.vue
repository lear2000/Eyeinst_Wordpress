<template>
	<div>
		<input type="text" name="_integ[apikey]" placeholder="yourapiekey-xxx" v-model="mailchimp.apikey">
  	<a v-on:click="getLists" class="button">Get Lists</a><br>
  	<select name="_integ[list]" v-model="mailchimp.list" v-on:change="getFields">
  		<option>Select a list...</option>
  		<option v-for="item in mailchimp.lists" :value="item.id">{{item.name}}</option>
  	</select>	
	</div>
</template>
<script>
	export default{
		props : ['mailchimp','type'],
		methods : {
	  	getFields : function(e){
	  		self = this;
	  		if(self.mailchimp.apikey != '' && e.target.value != ''){
		  		var requestData = new FormData();
					requestData.set('action','aformintegration');
					requestData.set('method','mailchimplistdata');
					requestData.set('key',self.mailchimp.apikey);
					requestData.set('listid',e.target.value);
					this.$http.post( ajaxurl , requestData ).then(function(res){
						if(res.status == 200){ var b = res.body;
							if('data' in b && 'mc' in b.data){ var mc = b.data.mc;
								if(typeof mc === 'string' && mc != ''){ mc = JSON.parse(mc);}
									
									self.mailchimp.fields.fields = mc.fields || [];
									self.mailchimp.fields.interests = mc.interests || [];
								
							}
						}
					},function(){
						console.log("error?");
					});	
	  		}
	  	},
	  	getLists : function(){
	  		self = this;
	  		if(self.mailchimp.apikey != ''){
		  		var requestData = new FormData();
					requestData.set('action','aformintegration');
					requestData.set('method','mailchimplist');
					requestData.set('key',self.mailchimp.apikey);
					this.$http.post( ajaxurl , requestData ).then(function(res){
						if(res.status == 200){ var b = res.body;
							if('data' in b && 'mc' in b.data){ var mc = b.data.mc;
								if(typeof mc === 'string' && mc != ''){ mc = JSON.parse(mc); }
								if('lists' in mc && typeof mc === 'object'){ 
									self.mailchimp.lists = mc.lists; 
								}
							}
						}
					},function(){
						console.log("error?");
					});	
	  		}
	  	}
	  }
	}
</script>