
<?php  
$allFields = _AFORMDB()->getSubmissionFields($post->ID);
?>
<style>
	#formsubmissionfields{
		border-collapse:collapse;
		width: 100%;
	}
	
	#formsubmissionfields thead td{
		vertical-align: top;
		border:1px solid #0085ba;
		padding:5px 10px;
		font-size: 11px;
	}
	#formsubmissionfields tbody td{
		vertical-align: top;
		border:1px solid #cccccc;
		padding:10px 10px;
	}
	.is-fielddisplayname{
		font-weight: bold;
	}
	#formsubmissionfields thead{
		background: #0085ba;
		color:#ffffff;
	}
	#formsubmissionfields .has-fieldinfo:nth-child(odd){
		background: #eeeeee;
	}
</style>



<table id="formsubmissionfields">
<thead>
		<td style="width:35%;border-right:1px solid #ffffff;"><strong>NAME</strong></td>
		<td style="width:60%;"><strong>VALUE</strong></td>
</thead>
<tbody>
<?php
if(!empty($allFields)):
	foreach($allFields as $index => $allField):
		$fieldData = maybe_unserialize($allField->meta_value);
		$fieldData = _aFormMakeObject($fieldData);
		echo "<tr class=\"has-fieldinfo\">";
			echo "<td class=\"is-fielddisplayname\">{$fieldData->display}</td>";
			echo "<td class=\"is-fieldvalue\">{$fieldData->value}</td>";
		echo "</tr>";
	endforeach;
endif;
?>
</tbody>
</table>
<br>
<hr>
<br><!-- -->

<table id="formsubmissionfields">
<thead>
		<td style="width:35%;"><strong>FORM</strong></td>
		<td></td>
</thead>
<tbody>
	<tr class="has-fieldinfo">
		<td><strong>Name</strong></td>
		<td><?php echo get_the_title($post->post_parent); ?></td>
	</tr>
	<tr class="has-fieldinfo">
		<td><strong>ID</strong></td>
		<td><?php echo $post->post_parent; ?></td>
	</tr>
</tbody>
</table>
<br>
<hr>
<br><!-- -->
<?php $httpreferrer = get_post_meta($post->ID , 'http_referrer' , true); ?>
<table id="formsubmissionfields">
<thead>
		<td><strong>HTTP_REFERRER</strong></td>
</thead>
<tbody>
	<tr class="has-fieldinfo">
		<td><?php echo home_url($httpreferrer); ?></td>
	</tr>
</tbody>
</table>
<br>
<hr>
<br><!-- -->
<?php $clientIp = get_post_meta($post->ID , 'client_ip' , true); ?>
<?php if($clientIp): ?>
	<table id="formsubmissionfields">
	<thead>
			<td><strong>Client IP</strong></td>
	</thead>
	<tbody>
		<tr class="has-fieldinfo">
			<td><?php echo $clientIp; ?></td>
		</tr>
	</tbody>
	</table>
<?php endif; ?>


