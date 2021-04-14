<?php 
/* note: $self is referencing $this */ 
?>
<p><label for="<?php echo $inputString; ?>[css_class]">Custom CSS class</label> <input class="form-control-inline" id="<?php echo $inputString; ?>[css_class]" name="<?php echo $inputString;?>[css_class]" type="text" value="<?php echo $self->inputSettings( 'css_class' ); ?>"></p>