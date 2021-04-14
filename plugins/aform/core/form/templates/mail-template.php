<?php $form = $postedFormDataClean['form']; $fields = $postedFormDataClean['fields']; ?>
<?php
	/*
		Available Variables:
		- $formPostObject
		- $formSettings 
		- $formObject
	*/
?>
<!-- Built Using Cerebus -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8"> <!-- utf-8 works for most cases -->
	<meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
	<title>New Form Submission: <br><?php echo ($formPostObject->post_title);?></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

	<!-- Web Font / @font-face : BEGIN -->
	<!-- NOTE: If web fonts are not required, lines 9 - 26 can be safely removed. -->
	<!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
	<!--[if mso]>
		<style>
			* {
				font-family: sans-serif !important;
			}
		</style>
	<![endif]-->
	
	<!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
	<!--[if !mso]><!-->
		<!-- insert web font reference, eg: <link href='https://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'> -->
	<!--<![endif]-->
	<!-- Web Font / @font-face : END -->
	<!-- CSS Reset -->
</head>
<body style="margin:0; padding:10px; font-family: Verdana, Arial; font-size: 13px; color:#555;">
	<?/* */?>
	<h1 style="line-height:normal;">New Form Submission: <br><?php echo ($formPostObject->post_title);?></h1>
	
	<?/* */?>
	<?php $formWebsite = __aFormRemoveHttp(home_url()); $formWebsite = preg_replace('/\//' , '&#47;' , $formWebsite);?>
	<p><strong>Form Website:</strong> <?php echo ($formWebsite);?></p>
	<p><strong>Form Submission Path:</strong> <?php echo ($form['http_referrer']);?></p>
	
	<?/* */?>
	<?php  if(isset($formSettings->include_client_ip)): ?>
		<p><strong>Client IP:</strong> <?php echo ($_SERVER['REMOTE_ADDR']);?></p>
	<?php endif;?>
	
	<?/* */?>
	<?php  
		$appendFileLinks = array();
	?>
	<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%">
		<?php foreach( $fields as $field ): ?>
			<?php 
			if( isset($field['skipadminmail']) ):
			if($field['skipadminmail'] == true):
					continue;
				endif;	
			endif;
			?>
		<tr>
			<td>
				<?php if( $field['fieldtype'] == 'text' ): ?>
					
					<div style="padding:5px 10px;margin:5px 0px;border-bottom:1px solid #e3e3e3;"><?php echo (htmlspecialchars_decode($field['value']));?></div>

				<?php else: ?>
					<p style="border-bottom:1px solid #e3e3e3;">
						<span style="width:250px;display:inline-block;margin:0;padding:5px 10px;"><strong style="font-size: 14px;"><?php echo ( $field['display'] != '' ) ? $field['display'] : $field['field']; ?></strong></span>
						<?php if( $field['fieldtype'] != 'text' ): ?>
							
							<?php if( !in_array($field['fieldtype'] , array('file','text')) ): ?>							

								<span style="display:inline-block;margin:0;padding:5px 10px;"><?php echo ($field['value']);?></span>
							
							<?php else: ?>
								
								<?php  
									$files = explode(',' , str_ireplace(' ', '', $field['value']));
									
									
									$linkCount = 0;
			
									if(isset($fieldDataObject->input->settings->no_attachement)):
										foreach( $files as $file ):
											
											$filelink  = $file;
											$filelink  = explode('/', $filelink );
											$filename  = array_pop( $filelink );
											
											$appendFileLinks[] = "<a href=\"{$file}\">{$filename}</a>";

											$linkCount++;
										
										endforeach;	
										?>
										<span style="display:inline-block;margin:0;padding:5px 10px;"><?php echo ($linkCount . ( ( $linkCount > 1 ) ? " Files" : " File" ) . " Linked at the bottom");?></span>
										<?php
									else:
										$filenames = array();
										$attachCount = 0;
										foreach( $files as $file ):
									
											$filename = explode('/', $file );
											$filenames[] = array_pop( $filename );


											$attachCount++;

										endforeach;
										?>
										<span style="display:inline-block;margin:0;padding:5px 10px;"><?php echo ( $attachCount . ( ( $attachCount > 1 ) ? " Files" : " File" ) . " Attached" );?></span>
										<?php
									endif;
									
								?>
								
							<?php endif; ?>
						<?php endif; ?>
					</p>			
				<?php endif; ?>
			</td>
		</tr>
		<tr><td><p style="margin:0;padding:10px;"></p></td></tr>
		<?php endforeach; ?>
		<?php  
			if(!empty($appendFileLinks)):
				foreach($appendFileLinks as $fileLink):
				?>
					<tr>
						<td>
							<p style="margin:10px 0px;"><?php echo $fileLink; ?></p>
						</td>
					</tr>
				<?php
				endforeach;
			endif;
		?>
	</table>
	<?php  
		if(isset($formSettings->include_campaign_trackingdata)):
			if(isset($postedFormDataClean['form']['utmtracking']) && !empty($postedFormDataClean['form']['utmtracking'])):
				?>
				<div style="display:block;width:100%;margin-top: 50px;">
				<p style="margin-bottom:5px;"><strong>Campaign Tracking</strong></p>
				<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%">
					<?php foreach ($postedFormDataClean['form']['utmtracking'] as $key => $value): ?>
						<?php  $key = str_replace('utm_', '', $key); $key = ucfirst($key); ?>
						<tr><td><span style="width:150px;display:inline-block;margin:0;padding:5px 10px;"><strong style="font-size: 14px;"><?php echo $key;?></strong></span><span style="display:inline-block;margin:0;padding:5px 10px;"><?php echo $value;?></span></td></tr>
					<?php endforeach ?>
				</table>
				</div>
				<?php
			endif;
		endif;
	?>
	<?php  if(isset($formSettings->include_useragentinfo)): ?>
		<p style="color:#666666;font-size: 8px;"><?php echo $_SERVER['HTTP_USER_AGENT'];?></p>
	<?php endif;?>	
</body>
</html>