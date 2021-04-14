<?php
namespace aform\admin;

class submissions{
	////////////////////////////////////////////////////////////////////
	public $autoloads;//do not delete, this triggers our include method to load this class
	////////////////////////////////////////////////////////////////////

	public $cpt;
	public function __construct(){
		
		$this->cpt = aFormSettings('cptnamesub');

		add_filter( 'parse_query', array($this , 'parseQuery'));		
		add_filter("views_edit-{$this->cpt}", array($this , 'editPostFilter') , 10 , 1);
		add_filter("admin_enqueue_scripts" , array($this , 'scripts'));

		add_action('wp_ajax_aform_subs' , array($this , 'ajax'));
		add_action('admin_footer' , array($this , 'adminfooter') , 100);

	}

	public function adminfooter(){
		global $pagenow;
		if(get_post_type() == $this->cpt && $pagenow == 'edit.php'):
			$cptname = aFormSettings('cptname');
			$allForms = get_posts(array(
				'post_type' 		=> $cptname,
				'posts_per_page' 	=> -1,
			));
			
			?>
				<div class="aform-submissions-interface">
					<div id="aform-submissions_app" class="aform-submissions-interface-inner">
						<a href="#" v-on:click="closeModal" class="export--modal-close">Close</a>
						<form v-on:submit.prevent="onSubmit" id="export--form" :class="exportClassStatus">
							<input type="hidden" name="action" value="aform_subs">
							<input type="hidden" name="calltype" value="getcount" v-model="calltype">
							<input type="hidden" name="total" value="0" v-model="total">
							<input type="hidden" name="resultstotal" value="0" v-model="resultstotal">
							<div class="export--options">
								<div id="submissions_forms" class="form--element">
									<span class="span--label">Select A Form</span>
									<span class="span--select"><select v-on:change="formIdChange" v-model="formId" name="formId">
										<option value="">...</option>
										<?php if(!empty($allForms)): foreach($allForms as $aForm):?>
											<option value="<?php echo $aForm->ID;?>"><?php echo $aForm->post_name;?></option>
										<?php endforeach;endif; ?>
									</select></span>
								</div>
								<div class="submissions_date-select form--element">
									<span class="span--label">Select Date Ranges <i class="fa fa-calendar" aria-hidden="true"></i></span>
									<!-- <span>Select Export Dates</span><br> -->
									<datepicker 
									v-on:opened="datePickerOpened"
									v-on:cleared="cleared"
									v-on:selected="startDateSelected" 
									clear-button 
									v-model="startDate"
									name="startDate" 
									placeholder="Start Date" format="yyyy-MM-dd" :disabled="startDateDisabled"></datepicker>
									<datepicker 
									v-on:opened="datePickerOpened"
									v-on:cleared="cleared"
									v-on:selected="endDateSelected" 
									clear-button 
									v-model="endDate"
									name="endDate" 
									placeholder="End Date" format="yyyy-MM-dd" :disabled="endDateDisabled"></datepicker>
								</div>
								<div class="form--element submissions_run-export-wrap">
									<p class="submissions_run-export"><button class="export--buttons">Start Export</button></p>
								</div>
							</div>
						</form>
						
						<div class="export-steps">
							<div class="status" style="text-align: center;" v-html="exportMessage"></div>
							<p :class="fetch_progressbar" style="text-align: center;"><span v-bind:style="{ width : fetchprogress + '%' }"></span><strong class="fetch-result-count" v-html="exportResults"></strong></p>
	
						</div>
						
						<div :class="CLASS_CSV">
							<download-excel
								class   = "export--buttons"
								:data   = "csv_rows"
								:fields = "csv_headers"
								type    = "csv"
								:name   = "csv_filename">
								Download
							</download-excel>
						</div>
					</div>
				</div>
			<?php
		endif;
	}

	public function ajax(){
		//offset of 20
		global $wpdb;
		
		if(isset($_POST['startDate'])): $_POST['startDate'] = $_POST['startDate'] . ' 00:00:00'; endif;
		if(isset($_POST['endDate'])): $_POST['endDate'] = $_POST['endDate'] . ' 23:59:59'; endif;

		$data = $_POST;
		$startDate = $_POST['startDate'];
		$endDate = $_POST['endDate'];
		$formId = $_POST['formId'];
		
		if(isset($_POST['calltype'])):
			if($_POST['calltype'] == 'getcount'):

				
				$submissionCount = $wpdb->get_var( "
					SELECT COUNT(*) FROM $wpdb->posts WHERE post_parent = {$formId} 
					AND post_status = 'publish' AND post_date between '{$startDate}' and '{$endDate}'" 
				);

				$formPost = get_post($formId);
				
				$formFields = _AFORMDB()->getFields($formId);

				foreach($formFields as $index => $formField):
					if($formField->input_type == 'text'):
						unset($formFields[$index]);
						continue;
					endif;
					$fieldSettings = json_decode($formField->input_settings);
					$formFields[$index]->input_settings = $fieldSettings;
					$formFields[$index]->field_name_id = "field-{$formField->ID}";
					$formFields[$index]->display_name = (!empty($fieldSettings->display_name)) ? strip_tags($fieldSettings->display_name) . " (id:{$formField->ID})" : $formFields[$index]->field_name_id ;
					$formFields[$index]->display_name = preg_replace('/,|\*/i' , ' ' , $formFields[$index]->display_name);
					//$formFields[$index]->display = preg_replace('/,|\*/i' , ' ' , $formFields[$index]->display);
				endforeach;

				$formFields = array_values($formFields);
				
				$data['formFields']   = $formFields;
				$data['formpost']			= $formPost;
				$data['formname']			= sanitize_title( $formPost->post_title );
				$data['total'] 			  = $submissionCount;
				$data['nextcall'] 	  = 'getsubmissions';
				$data['cont'] 	  		= ($submissionCount > 0) ? 1 : 0 ;
			
			endif;
			if($_POST['calltype'] == 'getsubmissions'):
				
				$args = array(
					'post_type' 	=> 'aform_sub',
					'post_parent'	=> $formId,
					'post_status'	=> 'publish',
					'offset'			=> intval($data['resultstotal']),
					'date_query' 	=> array(
						array(
							'after'     => $startDate,
							'before'    => $endDate,
							'inclusive' => true,
						),
					),
					'posts_per_page' => 20,
				);

				$results = new \WP_Query($args);
				$newresults = array();
				if(count($results->posts) > 0){
					foreach($results->posts as $p){
						
						$allFields = _AFORMDB()->getSubmissionFields($p->ID);

						$fields = array(
							array(
								'value'					=>	date('m-d-Y', strtotime($p->post_date)),
								'field_name_id' => 'date-0',
								'display_name'	=> 'Date'

							)
						);
						if(!empty($allFields)):
							foreach($allFields as $postedDataField):
								
								$postedDataField = maybe_unserialize($postedDataField->meta_value);
								$postedDataField = _aFormMakeObject($postedDataField);

								$fieldnameParts = explode('-', $postedDataField->field);
								$fieldId = array_pop($fieldnameParts);
								$postedDataField->field_id = $fieldId;
								$postedDataField->field_name_id = "field-{$fieldId}";
								$postedDataField->display_name = strip_tags($postedDataField->display) . " (id:{$fieldId})";
								$postedDataField->display_name = preg_replace('/,|\*/i' , ' ' , $postedDataField->display_name);
								$postedDataField->display = preg_replace('/,|\*/i' , ' ' , $postedDataField->display);
								$postedDataField->value = preg_replace('/\'|,|\*/i' , ' ' , $postedDataField->value);
								$fields[] = $postedDataField;

							endforeach;
							$postedData->fields = $fields;
						endif;

						$utmTracking = get_post_meta( $p->ID , 'utmtracking', false );
						if( $utmTracking != false ):
								$utmTrackingString = array();
								foreach($utmTracking[0] as $KEY => $VAL):
									$utmTrackingString[] =  "{$KEY}={$VAL}";
								endforeach;
								$utmTrackingString = join("&",$utmTrackingString);
								$postedData->fields[] = _aFormMakeObject(array(
									'value'					=>	$utmTrackingString,
									'field_name_id' => 'utmtracking-0',
									'display_name'	=> 'Campaign Tracking'
								));
						endif;

						$newresults[] = $postedData->fields;
					
					}
				}

				$data['resultstotalold'] 	= intval($data['resultstotal']);
				$data['resultstotal'] 		= ( intval($data['resultstotal']) + $results->post_count);
				$data['results'] 					= $newresults;
				$data['nextcall'] 	  		= $data['calltype'];
				

				
			endif;
			if($_POST['calltype'] == 'done'):
				$data['createcsv'] = true;
			endif;
		endif;
		
		wp_send_json( $data  );

		die();
	}

	public function editPostFilter($views){
		$views['aforms-export'] = '<a href="#export" class="is--export-button aform-action__export">Export Tool</a>';
		return $views;
	}

	public function parseQuery($query){
		global $pagenow;
	 	$postType = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
	 	
	 	if ( is_admin() 
	 		&& $postType == aFormSettings('cptnamesub') 
	 		&& $pagenow == 'edit.php' && isset( $_GET['aform_id'] )  && $_GET['aform_id'] != ''):
	 			
	 				if (! $query->is_main_query()) return;

	 				$query->query_vars['meta_key'] = 'aform_id';
	  			$query->query_vars['meta_value'] = $_GET['aform_id'];
	   	
	   	endif;
	   	return $query;
	}	


	public function scripts(){
		global $pagenow , $post_type;
		$pluginUri = aFormSettings('dir');


		if($post_type == $this->cpt):
			wp_enqueue_style( 'aform-submissions-css', $pluginUri . 'assets/css/submissions.css', array (), '1', 'all' );
			wp_register_style( $handle = 'af-fontawesome', $src = $pluginUri . 'vendor/fontawesome/css/font-awesome.min.css' );
			wp_enqueue_style( $handle = 'af-fontawesome' );
			if($pagenow == 'edit.php')
			wp_register_script( 'aform-submissions-bundle', $pluginUri . 'assets/vue/export.submissions.js', array() , $ver = '1.0.0', true );
			wp_enqueue_script( 'aform-submissions-bundle' );
		endif;
		

	}


}