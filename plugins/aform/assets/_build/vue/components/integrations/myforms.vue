<template>
	<div>
		<div>
			
			{{integrationType}}

			<p><a @click="addForm()">addForm</a></p>
			
		</div>
		<div v-for="(aform , index) in aforms">
			<input type="text" readonly placeholder="aForm Name" v-model="aform.selected">
			<div>
				<select v-model="aform.selected" @change="formChange(index)">
					<option disabled>Select Form</option>
					<option v-for="f in aform.forms" :value="f.ID">{{f.post_name}}</option>
				</select>
			</div>
			<div>
				<p><label>Fields</label> <a @click="addField(aform)">addField</a></p>
				<div v-for="( field , fieldindex ) in aform.fields">
					<table>
						<tr>
							<td><input v-model="field.selected" type="text" placeholder="..."></td><td><input type="text" placeholder="..."></td>
						</tr>
						<tr>
							<td>
								<select v-model="field.selected" @change="fieldChange( field )">
									<option disabled>Select Form Field</option>
									<option v-for="ff in aform.formfields" :value="ff.ID">{{ff.input_name}}</option>
								</select>
							</td>
							<td>
								<select>
									<option disabled>Select mailchip field</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>		
	</div>
</template>
<script>
	export default {
		props : ['aforms','integrationType','type','allForms'],
		data : function(){
			return{
				newForm : {
					name : '',
					forms : window.allForms,
					fields : [],
					selected : '',
					formfields : []
				}
			}
		},
		computed : {
			currentProperties : function(){

			}
		},
		methods: {
			addForm : function(){
				var newForm = Object.assign({}, this.newForm);
				this.aforms.push(newForm)
			},
			formChange : function(a){
				var t = this;
				var selectedForm = t.aforms[a];
				var payLoad = {
					'action' : 'aformintegration',
					'method' : 'getFormFields',
					'id' 		 : selectedForm.selected
				};
				t.$http.post(ajaxurl , payLoad , { emulateJSON : true } ).then(function(res){
						if(res.status == 200){ var b = res.body;
							selectedForm.formfields = b.data.fields;
							console.log(selectedForm);
						}
					},function(){
					console.log("error?");
				});	
			},
			addField : function(aform){
				aform.fields.push({});
			},
			fieldChange : function(fieldindex, formindex){
				console.log(fieldindex, formindex);
			},
		}
	}
</script>