<?php  
namespace aform\fields;

class file extends \aform\core\field {
	
	var $inputvalues;
	
	function __construct(){
		$this->fieldsettings = new \StdClass;
		$this->fieldsettings->name  = 'file';
		$this->fieldsettings->label = 'File Upload';
	}

	function renderField( $fieldData = null , $index = null){
		global $post,$post_id;		
		ob_start();
		$fieldIdAsIndex = $fieldData->ID;
		$this->formfieldTop( $index , $fieldData );
		$this->formFieldSetup( $index, $this->fieldsettings->label , $fieldData );

		//custom field layout here

		?>
		
		<div class="toggle-tab toggle-choices"><h4><a>Files <span class="dashicons dashicons-arrow-right"></span></a></h4></div>
		<div class="field-tab field-tab-choices">
			<div class="input-values">
				<?php
				$count = 0;
				foreach( $this->input->values as $choiceItem ):				
					$this->renderFieldValue( $fieldIdAsIndex , $count, $choiceItem->value, $choiceItem->display , '' , $choiceItem , $fieldData);
					$count++;
				endforeach; ?>
			</div>
			<button class="add-input-value button"><span><i class="fa fa-plus" aria-hidden="true"></i> Add New</span></button>
		</div>

		
		<?php $this->formfieldAdvancedToggle(); ?>
		
		<div class="field-create-advanced field-tab">
			<?php 
				$this->standardAdvSettings($index , $fieldData, array('placeholder','for_admin_email','send_to_CM')); 
				$inputString = "aform-fields[$fieldIdAsIndex][input_settings]"; 
			?>
			<div class="settings-holder">
				<div>
					<p>
						<label for="<?php echo $inputString; ?>[hide_file_label]">Hide file label</label>
						<span class="af-input-switch">
							<input id="<?php echo $inputString; ?>[hide_file_label]" name="<?php echo $inputString; ?>[hide_file_label]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'hide_file_label' ) == true ) ? 'checked' : '';?>>
							<label for="<?php echo $inputString; ?>[hide_file_label]"></label>
						</span>
					</p>
					<p>
						<label for="<?php echo $inputString; ?>[no_attachement]">Don't attach files to email <br><small>outputs links to file</small></label>
						<span class="af-input-switch">
							<input id="<?php echo $inputString; ?>[no_attachement]" name="<?php echo $inputString; ?>[no_attachement]" type="checkbox" value="true" <?php echo ( $this->inputSettings( 'no_attachement' ) == true ) ? 'checked' : '';?>>
							<label for="<?php echo $inputString; ?>[no_attachement]"></label>
						</span>
					</p>
				</div>
			</div>

			<div>
				<p>
				<label for="<?php echo $inputString; ?>[use_extensions]">File Type(s) Allowed:<br></label> 
				<ul class="file-type-list">
					<?php $filePattern = 'jpg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv'; ?>
					<?php $defaultList = 'jpg|png|gif'; ?>
					<?php 
						$filePattern = explode('|', $filePattern);
						$defaultList = explode('|', $defaultList);
						$extlist = $this->inputSettings( 'use_extensions' )?:$defaultList;
					?>
					<?php foreach($filePattern as $type): ?>
						<li><input <?php  echo (in_array( $type , $extlist )) ? 'checked': '';?> type="checkbox" value="<?php echo $type?>" name="<?php echo $inputString;?>[use_extensions][]"><label for=""><?php echo $type?></label></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="filesize-slider-wrap">
				<p><label for="<?php echo $inputString; ?>[max_filesize]">Max File Size Allowed:</label> <input class="max-filesize-input" id="<?php echo $inputString; ?>[max_filesize]" name="<?php echo $inputString;?>[max_filesize]" type="hidden" value="<?php echo htmlspecialchars($this->inputSettings( 'max_filesize' ))?:'2'; ?>"> <span class="max-filesize-input"><?php echo htmlspecialchars($this->inputSettings( 'max_filesize' ))?:'2'; ?></span> <strong><small>MB</small></strong></p>
				<div class="filesize-slider"></div>
			</div>
		<?php $this->advancedSettingsBottom(); ?>			
		</div> <!-- .field-create-advanced-->

		<?php

		$this->formfieldBottom();

		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	function metaboxRender($label) {
		/*
		?>
		<?php echo $label; ?><br />
		 <input type="file" name="" id="">
		<?php
		*/
		echo $label;
	}


	/*##############################################################*/
	/*#######################     PUBLIC   #########################*/
	/*##############################################################*/
	/* PUBLIC RENDER : called when creating Form on the front-end */
	function publicRender( $form = null ){
		$filedata = ( isset($this->returnValue) ) ? $this->returnValue : null ;		
		$fieldData = $this;
		$ext = $this->inputSettings( 'use_extensions' );
		$hideFileLabel = $this->inputSettings('hide_file_label')
		?>
		<?php  foreach($this->input->values as $count =>  $fileupload):?>
			<div class="file-input-item">
				<?php if(empty($hideFileLabel)): ?>
					<label for="<?php echo  sanitize_title($fileupload->value); ?>"><?php echo $fileupload->display;?></label>
				<?php endif; ?>
				
				<span class="file-input-span"><input <?php $this->fieldRequired();?> data-name="<?php echo AF_FIELDNAME_PREFIX;?>[<?php echo $this->fieldname?>--<?php echo $count;?>]" id="<?php echo sanitize_title($fileupload->value); ?>" value="<?php echo (isset($filedata[$count])) ? $filedata[$count]['name']: ''?>" type="file" data-fielid="<?php echo $this->fieldID; ?>" name="<?php echo afFieldName($this->fieldname);?>[<?php echo $count;?>]" class="af-file"></span>

				
				<?php 
				if(isset($fileupload->fileinput_after)):
					echo $fileupload->fileinput_after;
				endif;
				?>

				<?php 
					do_action('aform/field/afterinput' , $fileupload , $fieldData ); 
				?>
			
			</div>
		<?php endforeach;
		
	}
	function getRules() {
		$rules = array();
		
		if( $this->inputSettings( 'use_extensions' ) ):
			$rules['realextension'] = implode('|', $this->inputSettings( 'use_extensions' ));
		endif; 
		if( $this->inputSettings( 'max_filesize' ) ):
			//1024
			$rules['filesize'] = $this->inputSettings( 'max_filesize' ) * 1024 ;
		endif;
		
		
		return ( !empty( $rules ) ) ?  $rules  : false;
	}



}