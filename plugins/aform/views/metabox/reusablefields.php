<!--  -->
<ul class="field--list available-<?php echo $args['metabox'];?>">
<?php 
	$reusableFields = _AFORMDB()->getReusableFields();
	if(!empty($reusableFields)):
		foreach( $reusableFields as $field ):
			echo aform\core\view::reusableField( $field );
		endforeach;
	endif;
?>
</ul>
<!--  Dialog used to add reusable field -->
<div style="display:none;">
	<div id="reusableFieldBox" title="Create Reusable Field">
		<p style="display:none;">Duplicating Field...</p>
		<div class="clonename-wrap">
			<label for="clonename">Field Name <small><em>Required</em></small></label><br>
			<input type="text" id="clonename" class="widefat" name="clonename">	
		</div>
	</div>
</div>