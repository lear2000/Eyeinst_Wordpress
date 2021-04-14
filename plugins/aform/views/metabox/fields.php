<?php  
/**
 * $post
 * $args
 */
$fieldsClass = new aform\core\field;//get available fields
$fields = $fieldsClass->listFields();

?>
<ul class="field--list available-<?php echo $args['metabox'];?>">
	<?php
	foreach ($fields as $field):
	
		$class = "aform\\fields\\{$field->name}";
		$fieldClass = new $class;
	
	?>
		<li class="add--field rm-droppable-item" data-field-type="<?php echo $field->name; ?>"><?php $fieldClass->metaboxRender($field->label); ?></li>
	<?php
	endforeach;
	?>
</ul>
