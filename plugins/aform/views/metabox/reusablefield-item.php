<?php
/* uses $field */
$class = "aform\\fields\\{$field->input_type}";
$fieldClass = new $class;
?>
<li class="add--field rm-droppable-item" data-reusable-id="<?php echo $field->ID;?>" data-field-type="<?php echo $fieldClass->fieldsettings->name; ?>">
	<span><strong><?php echo $field->field_label;?></strong></span><br>
	<span><small>Field Type: <?php echo $fieldClass->fieldsettings->label;?></small></span><br>
	<span><small>ID: <?php echo $field->ID;?></small>
	<a href="#remove" class="delete-reusable-field" data-remove-reusable="<?php echo $field->ID;?>">Delete</a>
</li>
