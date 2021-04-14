<?php 
/* note: $self is referencing $this */ 
?>
<p>
	<label for="<?php echo $inputString; ?>[reverse_display]">Show input before label</label> 
	<span class="af-input-switch">
		<input id="<?php echo $inputString; ?>[reverse_display]" name="<?php echo $inputString; ?>[reverse_display]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'reverse_display' ) == true ) ? 'checked' : '';?>  >
		<label for="<?php echo $inputString; ?>[reverse_display]"></label>
	</span>
</p>