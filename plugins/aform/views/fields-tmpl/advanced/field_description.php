<?php 
/* note: $self is referencing $this */ 
?>
<div class="af-toggle-grouped">
	<p>
		<label for="<?php echo $inputString; ?>[enable_field_desc]">Enable Field Description</label> 
		<span class="af-input-switch">
			<input class="enabler" data-target="field_desc_html" id="<?php echo $inputString; ?>[enable_field_desc]" name="<?php echo $inputString; ?>[enable_field_desc]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'enable_field_desc' ) == true ) ? 'checked' : '' ;?>>
			<label for="<?php echo $inputString; ?>[enable_field_desc]"></label>
		</span>
	</p>
	<div class="field_desc_html" <?php echo ( $self->inputSettings( 'enable_field_desc' ) == false ) ? 'style="display: none;"' : ''; ?>>
		<p>
			<label for="<?php echo $inputString; ?>[field_desc_wpautop]">Automatically Add Paragraphs</label> 
			<span class="af-input-switch">
				<input id="<?php echo $inputString; ?>[field_desc_wpautop]" name="<?php echo $inputString; ?>[field_desc_wpautop]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'field_desc_wpautop' ) == true ) ? 'checked' : '' ;?>>
				<label for="<?php echo $inputString; ?>[field_desc_wpautop]"></label>
			</span>
		</p>
		<p>
		<label for="<?php echo $inputString; ?>[field_desc_html]">Field Description</label>
		<textarea class="widefat form-control" name="<?php echo $inputString; ?>[field_desc_html]"><?php  echo $self->inputSettings( 'field_desc_html'); ?></textarea>
		</p>
	</div>
	
</div>