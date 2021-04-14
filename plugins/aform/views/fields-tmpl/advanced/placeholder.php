<?php 
/* note: $self is referencing $this */ 
?>
<p><label for="<?php echo $inputString; ?>[input_placeholder]">Placeholder</label> <input class="form-control-inline" id="<?php echo $inputString; ?>[input_placeholder]" name="<?php echo $inputString;?>[input_placeholder]" type="text" value="<?php echo htmlentities($self->inputSettings( 'input_placeholder' )); ?>"></p>