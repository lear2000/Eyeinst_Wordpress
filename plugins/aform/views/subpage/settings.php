<?php
/**
* This is further step into securing the form. Here we set up more road blocks for spam bots and spam humans. 
*/
?>
<div class="wrap settings-page">
	<h1><i class="dashicons dashicons-admin-tools" style="vertical-align:baseline;"></i> <?php echo get_admin_page_title();?> </h1>
	<form method="POST" action="options.php">
		<?php  
			settings_fields( 'aform-settings' );
			do_settings_sections( 'aform-settings' );
			$optionSetings = get_option('_aform_settings',array());
			$optionGroupName = '_aform_settings';
		?>
		<?php submit_button(); ?>
		<div class="settings-option">
			<section>
				<h2>Restrictions</h2>
				<p>The following settings are applied to all aForms.</p>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Blocked IPs</span>
						<input type="text" value="<?php echo afGetAOV( $optionSetings , 'ip-block' , '');?>" class="form-control" id="" name="<?php echo $optionGroupName;?>[ip-block]" placeholder="multiple IPs are comma separated">
					</div>
					<code><small><strong>Example</strong>: 127.0.0.1, 193.5.10.27</small></code>
				</div>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Blocked domains</span>
						<input type="text" value="<?php echo afGetAOV( $optionSetings , 'domain-block' , '');?>" class="form-control" id="" name="<?php echo $optionGroupName;?>[domain-block]" placeholder="multiple domains are comma separated">
					</div>
					<code><small><strong>Example</strong>: @domain.com, @aol.com</small></code>
				</div>
			</section>
			<section>
				<h2>reCAPTCHA <small>V3</small></h2>
				<p>The following settings are applied to all aForms.</p>
				<div class="form-group">
					<div class="input-group">
						<label class="input-group-addon" for="recaptcha-enabled">Enable</label>
						<?php $recaptchaEnabled = afGetAOV( $optionSetings , 'recaptcha-enabled' , '');?>
						<input type="checkbox" value="1" <?php checked( $recaptchaEnabled , '1' , true); ?> class="form--control" id="recaptcha-enabled" name="<?php echo $optionGroupName;?>[recaptcha-enabled]">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Client Side Key</span>
						<input type="text" value="<?php echo afGetAOV( $optionSetings , 'recaptcha-client' , '');?>" class="form-control" id="" name="<?php echo $optionGroupName;?>[recaptcha-client]" placeholder="XXXXXXXXXXXXXXXX">
					</div>
					<code><small>Use this site key in the HTML code your site serves to users</small></code>
				</div>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Server Side Key</span>
						<input type="text" value="<?php echo afGetAOV( $optionSetings , 'recaptcha-server' , '');?>" class="form-control" id="" name="<?php echo $optionGroupName;?>[recaptcha-server]" placeholder="XXXXXXXXXXXXXXXX">
					</div>
					<code><small>Use this secret key for communication between your site and reCAPTCHA</small></code>
				</div>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Recaptcha Score</span>
						<input type="text" value="<?php echo afGetAOV( $optionSetings , 'recaptcha-score' , '');?>" class="form-control" id="" name="<?php echo $optionGroupName;?>[recaptcha-score]" placeholder="0.5">
					</div>
					<code><small>Default score is 0.5</small></code>
				</div>
			</section>
		</div>
	</form>
</div>
