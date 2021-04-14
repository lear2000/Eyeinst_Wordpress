<?php $form = $postedFormDataClean['form']; $fields = $postedFormDataClean['fields']; ?>

New Form Submission: <?php echo $formPostObject->post_title . "\r\n"; ?>
<?php/*?>
Submitted from page: <?php echo $form['submission-page']; echo "\r\n"; ?>
<?php */?>
<?php if(isset($form['http_referrer'])): ?>
	Submitted From URL: <?php echo $form['http_referrer'] . "\r\n"; ?>
<?php endif; ?>
<?php  if(isset($formSettings->include_client_ip)): ?>
	Client IP: <?php echo $_SERVER['REMOTE_ADDR'] . "\r\n"; ?>
<?php endif; ?>
<?php echo "\r\n"; ?>	
<?php foreach( $fields as $field ): ?>
		<?php 
		if( isset($field['skipadminmail']) ):
			if($field['skipadminmail'] == true):
				continue;
			endif;	
		endif; 
		?>
		<?php if( $field['fieldtype'] == 'text' ): ?>
			<?php $text = htmlspecialchars_decode($field['value']); ?>
			<?php echo strip_tags( $text ) . "\r\n"; ?>
		<?php else: ?>
			<?php if( !in_array($field['fieldtype'] , array('file','text')) ): ?>	
				<?php echo ( ( $field['display'] != '' ) ? $field['display'] : $field['field'] ) .' : '. $field['value'] . "\r\n"; ?>
			<?php else: ?>
				<?php  
				$files = explode(',' , str_ireplace(' ', '', $field['value']));
				$count = 0;
				$filenames = array();
				foreach( $files as $file ):
					$filename = explode('/', $file );
					$filenames[] = array_pop( $filename );
					$count++;
				endforeach;
				?>
				<?php echo $count . ( ( $count > 1 ) ? " Files" : " File" ) . " Attached" . "\r\n"; ?>
			<?php endif; ?>
		<?php endif; ?>
<?php endforeach; ?>
<?php /* tracking data */ ?>
<?php if(isset($formSettings->include_campaign_trackingdata)): session_start(); if(isset($_SESSION['seaformsUtmTracking'])): ?>
	<?php echo "\r\n\r\n"; ?>	
	Campaign Tracking	
	<?php echo "\r\n"; ?>
	<?php foreach ($_SESSION['seaformsUtmTracking']as $key => $value): ?>
		<?php  $key = str_replace('utm_', '', $key); $key = ucfirst($key); ?>
		<?php echo $key .' : '. $value . "\r\n\r\n"; ?>
	<?php endforeach; ?>
<?php endif; endif; ?>			