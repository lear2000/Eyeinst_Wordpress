<?php  
/**
 * $post
 * $args
 */
global $post;
$settings = _AFORMDB()->getSettings( $post->ID )?:null;
$formSettings = ($settings) ? json_decode($settings->settings, true) : null;

if(!empty($formSettings)):
	foreach( $formSettings as $f => $v ):
		$formSettings[$f] = stripslashes_deep( $v );
	endforeach;
endif;
?>
<div class="form-settings">
	
	<?php do_action('aform/admin/form-settings/top', $formSettings ); ?>

	<section>
	
		<h3>Admin Email Settings</h3>
		<p><small>This is the email that is sent to the admin each time a form is completed</small></p>

		<div class="admin-form-headers">

			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">To</span>
					<?php $emailAtCreate = get_bloginfo('admin_email'); ?>
					<input type="text" class="form-control" id="admin_email" name="form-settings[admin_email]" value="<?php echo afGetAOV( $formSettings , 'admin_email' , $emailAtCreate ); ?>">
				</div>
				<label><small>Multiple emails must be comma-separated</small></label>
			</div>

			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">Bcc</span>
					<input type="text" class="form-control" id="admin_bcc" name="form-settings[admin_bcc]" value="<?php echo afGetAOV( $formSettings , 'admin_bcc' ); ?>">
				</div>
				<label><small>Multiple emails must be comma-separated</small></label>
			</div>

			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">From</span>
					<?php $FromAtCreate = get_bloginfo('name') .' <'.get_bloginfo('admin_email').'>';?>
					<input type="text" class="form-control" id="admin_email_from" name="form-settings[admin_email_from]" value="<?php echo afGetAOV( $formSettings , 'admin_email_from' , $FromAtCreate); ?>">
				</div>
				<label><small>i.e. <em>forms@yourdomain.com</em> or <em>Company Name &lt;forms@yourdomain.com&gt;</em></small></label>
			</div>
		</div>	
		<br>
		
		<?php $adminEmailType = afGetAOV( $formSettings, 'admin_email_type', '' ); ?>
		
		<div class="form-group">
			<div class="input-group">
				<span><strong>Type of admin email to send:</strong>&nbsp;&nbsp;</span>
				<label class="check-label radio-inline" for="admin_email_html"><input type="radio" id="admin_email_html" name="form-settings[admin_email_type]" value="html" <?php echo ( $adminEmailType == 'html' || $adminEmailType == '' ) ? 'checked' : ''; ?>> HTML</label> 
				<label class="check-label radio-inline" for="admin_email_plain"><input type="radio" id="admin_email_plain" name="form-settings[admin_email_type]" value="plaintext" <?php echo ( $adminEmailType == 'plaintext' ) ? 'checked' : ''; ?>> Plain Text</label>
			</div>
		</div>
		
		


		<div>
			<?php $customSubjectEnabled = afGetAOV( $formSettings, 'use_custom_admin_subject', false ); ?>
			<label class="check-label" for="use_custom_subject"><strong>Use a custom subject line for this email</strong></label>
			<span class="af-input-switch">
				<input type="checkbox" class="enabler" data-target="custom-admin-subject" id="use_custom_subject" name="form-settings[use_custom_admin_subject]" value="true" <?php echo ( $customSubjectEnabled ) ? 'checked' : ''; ?>>
				<label for="use_custom_subject"></label>
			</span>
		
			<div class="custom-admin-subject" <?php echo ( $customSubjectEnabled == false ) ? 'style="display: none;"' : ''; ?>>				
				<div class="form-group no-marginbottom">
					<div class="input-group">
						<span class="input-group-addon">Custom Subject</span>
						<input type="text" class="form-control" id="custom_admin_subect" name="form-settings[custom_admin_subject]" value="<?php echo afGetAOV( $formSettings , 'custom_admin_subject' ); ?>">
					</div>
				</div>
			</div>
		</div>
		
		<br>
		<div>
			<?php $campaignTrackingdata = afGetAOV( $formSettings, 'include_campaign_trackingdata', false ); ?>
	
			<label class="check-label" for="include_campaign_trackingdata"><strong>Include Campaign Tracking Data</strong></label>
			<span class="af-input-switch">
				<input type="checkbox" id="include_campaign_trackingdata" name="form-settings[include_campaign_trackingdata]" value="true" <?php echo ( $campaignTrackingdata ) ? 'checked' : ''; ?>>
				<label for="include_campaign_trackingdata"></label>
			</span>
		</div>
		<br>
		<div>
			<?php $clientIp = afGetAOV( $formSettings, 'include_client_ip', false ); ?>
	
			<label class="check-label" for="include_client_ip"><strong>Include Client IP</strong></label>
			<span class="af-input-switch">
				<input type="checkbox" id="include_client_ip" name="form-settings[include_client_ip]" value="true" <?php echo ( $clientIp ) ? 'checked' : ''; ?>>
				<label for="include_client_ip"></label>
			</span>
		</div>
		<br>
		<div>
			<?php 
				$appendBrowserInfo = afGetAOV( $formSettings, 'include_useragentinfo', false ); 
			?>
			<label class="check-label" for="include_useragentinfo"><strong>Include Browser Data <br><small>Applied to Admin Email Footer</small></strong></label>
			<span class="af-input-switch">
				<input type="checkbox" id="include_useragentinfo" name="form-settings[include_useragentinfo]" value="true" <?php echo ( $appendBrowserInfo ) ? 'checked' : ''; ?>>
				<label for="include_useragentinfo"></label>
			</span>
		</div>
	
	</section>
	
	<hr><hr><hr>
	<br>

	<section>

		<h3>Success Settings</h3>
		<p><em>Display a success message or redirect to a success URL.</em></p>
		<label for="conf_page"><strong>Success URL</strong></label>
		<small>- Redirects after success. 
			<br>- When <em>Success Message</em> is enabled, redirect is voided.
			<br>- When <em>Success Message</em> is enabled, the URL is sent to GA for tracking.</small>
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon"><?php echo (home_url());?></span>
				<input class="form-control confirm-url" id="conf_page" name="form-settings[conf_page]" value="<?php echo afGetAOV( $formSettings , 'conf_page' ); ?>">
			</div>
		</div>
		<div>
			<?php $campaignTrackingdata = afGetAOV( $formSettings, 'redirect_with_post', false ); ?>
	
			<label class="check-label" for="redirect_with_post"><strong>Redirect with post data</strong></label>
			<span class="af-input-switch">
				<input type="checkbox" id="redirect_with_post" name="form-settings[redirect_with_post]" value="true" <?php echo ( $campaignTrackingdata ) ? 'checked' : ''; ?>>
				<label for="redirect_with_post"></label>
			</span>
			<br>
			<small>
				- When enabled, post data is sent with redirect.<br>
				- When <em>Success Message</em> is enabled this option voided.<br>
				- JavaScript dependent.
			</small>
		</div>	

		<?php $ajaxEnabled = afGetAOV( $formSettings , 'ajax_redirect' , $default = false );?>
		<br>
		<div>
			<label for="ajax_redirect" class="check-label"><strong>Enable Success Message</strong></label>
			
			<span class="af-input-switch">
				<input type="checkbox" class="enabler" data-target="ajax-confirmation" id="ajax_redirect" name="form-settings[ajax_redirect]" value="true" <?php echo ( $ajaxEnabled ) ? 'checked' : ''; ?>>
				<label for="ajax_redirect"></label>
				<br><span>
					<small>
						- Message will replace form when successful.<br>
						- JavaScript dependent.
					</small>
				</span>
			</span>
			<div class="ajax-confirmation" <?php echo ( $ajaxEnabled == false ) ? 'style="display: none;"' : ''; ?>>	
			<label for="ajax_confirmation"><strong>Message:</strong></label>
				<?php 
					wp_editor( 
						afGetAOV($formSettings, 'ajax_confirmation' ) , 
						"ajax_confirmation_editor", 
						$settings = array( 
							'textarea_name' => "form-settings[ajax_confirmation]",
							'textarea_rows' => 8
						)
					); 
				?>
			</div>	
		</div>
	</section>
	<hr><hr><hr>
	<br>
	<section>
		
		<h3>Email Confirmation Message</h3>
		<p><em>This email goes to the person who completed the form.</em></p>
		<?php $confEmail  = afGetAOV( $formSettings , 'conf_email_enabled' , $default = false ); ?>	
		<div>
			<label for="conf_email_enabled" class="check-label"><strong>Enable Confirmation:</strong></label>
			<span class="af-input-switch">
			
				<input type="checkbox" class="enabler" data-target="email-fields" id="conf_email_enabled" name="form-settings[conf_email_enabled]" value="true" <?php echo ( $confEmail ) ? 'checked' : ''; ?>>
				<label for="conf_email_enabled"></label>
			</span>
			<br><br>
			<div class="email-fields" <?php echo ( $confEmail == false ) ? 'style="display: none;"' : ''; ?>>

				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">Confirmation From</span>
						<?php $FromAtCreate = get_bloginfo('name') .' <'.get_bloginfo('admin_email').'>';?>
						<input type="text" class="form-control" id="conf_email_address" name="form-settings[conf_email_address]" value="<?php echo afGetAOV( $formSettings , 'conf_email_address' , $FromAtCreate ); ?>">
					</div>
					<label><small>i.e. <em>forms@yourdomain.com</em> or <em>Company Name &lt;forms@yourdomain.com&gt;</em></small></label>
				</div>
							
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><label for="conf_email_subject">Confirmation Subject</label></span>
						<input type="text" placeholder="Form has been received" class="form-control" id="conf_email_subject" name="form-settings[conf_email_subject]" value="<?php echo afGetAOV( $formSettings , 'conf_email_subject' ); ?>">
					</div>
				</div>

				<?php ##comment since > 1.3.3 ?>
				<div class="form-group">
					<?php $applyConfirmationBcc  = afGetAOV( $formSettings , 'enable_bcc_confirmation' , $default = false ); ?>
					<label for="enable_bcc_confirmation" class="check-label"><strong>Enable BCC Confirmation:</strong></label>
					<span class="af-input-switch">
						<input type="checkbox" class="enabler" data-target="enables-confirmation-bcc" id="enable_bcc_confirmation" name="form-settings[enable_bcc_confirmation]" value="true" <?php echo ( $applyConfirmationBcc ) ? 'checked' : ''; ?>>
						<label for="enable_bcc_confirmation"></label>
					</span>	
					<br>		
					<div class="enables-confirmation-bcc" <?php echo ( $applyConfirmationBcc == false ) ? 'style="display: none;"' : ''; ?>>
						<div class="form-group no-marginbottom">
							<div class="input-group">
								<span class="input-group-addon"><label for="confirmation_email_bcc">Confirmation Bcc</label></span>
								<input type="text" class="form-control" id="confirmation_email_bcc" name="form-settings[confirmation_email_bcc]" value="<?php echo afGetAOV( $formSettings , 'confirmation_email_bcc' ); ?>">
							</div>
							<label><small>Multiple emails must be comma-separated</small></label>
						</div>
					</div>
				</div>
					
				<?php ##/comment-end?>
				
				<div class="form-group">
				<label for="conf_email_text"><strong>Confirmation Email Message:</strong></label>
					<?php 
					wp_editor( 
						afGetAOV( $formSettings , 'conf_email_text' ), 
						"email_confirmation_editor", 
						$settings = array( 
							'textarea_name' => 'form-settings[conf_email_text]',
							'textarea_rows' => 8,
							'editor_class' => 'form-control'
						)
					); 
					?>
					<?php $confmsgApplyfilter = afGetAOV( $formSettings , 'conf_email_text_applyfilter' , $default = false ); ?>
					<p>
						<label for="conf_email_text_applyfilter" class="check-label"><strong>Apply <small>the_content</small> filter:</strong></label>
						<span class="af-input-switch"><input 
						type="checkbox" 
						id="conf_email_text_applyfilter" 
						name="form-settings[conf_email_text_applyfilter]" 
						value="true" <?php echo ( $confmsgApplyfilter ) ? 'checked' : ''; ?>><label for="conf_email_text_applyfilter"></label></span>
					</p>
				</div>
			</div>
		</div>
	</section>
	<hr><hr><hr>
	<br>
	<section class="html-form-settings">
		
		<h3>HTML Form Settings</h3>
		<br>
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">Form Class</span>
				<input type="text" class="form-control" id="form_class" name="form-settings[form_class]" value="<?php echo afGetAOV( $formSettings , 'form_class' ); ?>">
			</div>
			<label><small>Adds a custom class to this form. Multiple classes are separated by a space.</small></label>
		</div>
		<br>
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">Submit Button Text</span>
				<input type="text" class="form-control" id="form_submit_text" placeholder="Submit" name="form-settings[form_submit_text]" value="<?php echo afGetAOV( $formSettings , 'form_submit_text' ); ?>">
			</div>
			<label><small>Overrides the submit button text.</small></label>
		</div>
		<br>
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">Submit Button Class</span>
				<input type="text" class="form-control" id="form_submit_class" name="form-settings[form_submit_class]" value="<?php echo afGetAOV( $formSettings , 'form_submit_class' ); ?>">
			</div>
			<label><small>Adds a custom class to the submit button. Multiple classes are separated by a space.</small></label>
		</div>

	</section>

	<?php do_action('aform/admin/form-settings/bottom', $formSettings ); ?>


</div><!--.form-settings-->
