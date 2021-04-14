<?php 
/* note: $self is referencing $this */ 
?>
<div class="af-toggle-grouped">
	<p>
		<label for="<?php echo $inputString; ?>[for_admin_email]">Use value as admin email</label>
		<span class="af-input-switch">
			<input id="<?php echo $inputString; ?>[for_admin_email]" name="<?php echo $inputString; ?>[for_admin_email]" class="enabler"  data-target="append_admin_emails" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'for_admin_email' ) == true ) ? 'checked' : '';?>>
			<label for="<?php echo $inputString; ?>[for_admin_email]"></label>
		</span>
	</p>

	<div class="append_admin_emails" style="padding-left:10px;<?php echo ( $self->inputSettings( 'for_admin_email' ) == true ) ? '' : 'display:none;';?>">
	
		<p>
			<label for="<?php echo $inputString; ?>[append_admin_emails]"><small>Append <strong>Value</strong> to existing <strong>Admin Email &raquo; To</strong> value</small></label> 
			<span class="af-input-switch">
				<input id="<?php echo $inputString; ?>[append_admin_emails]" name="<?php echo $inputString; ?>[append_admin_emails]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'append_admin_emails' ) == true ) ? 'checked' : '';?>>
				<label for="<?php echo $inputString; ?>[append_admin_emails]"></label>
			</span>
		</p>
		
		<?php if( in_array($self->fieldsettings->name , array('selectbox','radiobuttons','checkboxgroup')) ): ?>
		
			<p>
				<label for="<?php echo $inputString; ?>[use_choicelabel_as_value]"><small>Use <strong>Label</strong> as <strong>Value</strong> when sending <strong>Admin Email</strong></small></label> 
				<span class="af-input-switch">
					<input id="<?php echo $inputString; ?>[use_choicelabel_as_value]" name="<?php echo $inputString; ?>[use_choicelabel_as_value]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'use_choicelabel_as_value' ) == true ) ? 'checked' : '';?>>
					<label for="<?php echo $inputString; ?>[use_choicelabel_as_value]"></label>
				</span>
			</p>
		
		<?php endif; ?>

	</div>
</div>