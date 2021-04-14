<style>
	#normal-sortables{
		display: none;
	}
	#misc-publishing-actions > *{
		display: none;
	}
</style>
<?php  

	// $fields = _AFORMDB()->getFields(8526);
	// echo '<pre>'; 
	// 	print_r($fields); 
	// echo '</pre>';

	global $post;
	$settings = get_post_meta( $post->ID , '_integ' , true);
	if(count($settings) > 0):
		if(isset($settings['integration_type']) && $settings['integration_type'] == 'mailchimp'):
			if(isset($settings['apikey'])):
				$transientName = $settings['apikey'] . '/lists';
				$transientitem = get_transient( $transientName );
				if($transientitem): 
					$settings['lists'] = $transientitem->lists; 
					if(isset($settings['list'])):
						// echo '<pre>'; 
						// 	print_r($transientitem->lists[0]); 
						// echo '</pre>';
					endif;
				endif;
			endif;
		endif;
	endif;
?>
<script>
	window.intSettings = [];
	<?php if(count($settings)>0): ?> window.intSettings = <?php echo json_encode($settings);?>; <?php endif; ?>
</script>
<?php $aforms = _AFORMDB()->getFormPosts(); ?>
<script>
	window.allForms = [];
	<?php if(count($aforms) > 0): ?> window.allForms = <?php echo json_encode($aforms);?>; <?php endif; ?>
</script>
<div id="action_vue">
	<section class="integrationSelection">
		<select name="_integ[integration_type]" id="action_type" v-model="integrationType">
			<option value="">Select Integration...</option>
			<option value="mailchimp">MailChimp</option>
		</select>
	</section>
	
	<section id="integrationSettings">
		<component :is="settingsComp" v-bind="currentProperties" :type="type"></component>
	</section>
	
	<section id="integrationForms">
		<my-forms :aforms="aforms" :integrationType="integrationType" :type="type" :allForms="allForms"></my-forms>
	</section>

</div>
