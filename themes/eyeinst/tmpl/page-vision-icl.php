<section class="layout-1-2 layout-1-2-type__inner">
	<div class="layout-1-2__image" style="background-image:url(<?php echo get_template_directory_uri( )?>/img/icl-stock-img.jpg);"></div>
	<div class="layout-1-2__text">
		<h1 class="is-heading">Visian ICL®</h1>
		<p>Will need blurb on Visian. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>
	</div>
</section>


<section class="layout-1-2-alternate">
	<div class="layout-1-2-alternate__wrapper layout-container">
		<div class="layout-1-2-alternate__item layout-1-2-alternate__image">
			<img src="<?php echo get_template_directory_uri(); ?>/img/vision-wireframe.png" alt="{replace me}">
		</div>
		<div class="layout-1-2-alternate__item layout-1-2-alternate__text">
			<p>If your doctor said you weren’t a good candidate for LASIK or PRK because you had too thin corneas, dry eyes, or your prescription was out of range, then the Visian ICL may be just perfect for you! In addition, the Visian ICL is also available to people who are looking for other alternatives to LASIK. The Visian ICL is an implantable contact/collamer lens. It’s different from LASIK because it doesn’t involve a laser to treat the cornea. The Visian ICL is actually an implant that works in harmony with your natural eye! The surgeon will make a tiny incision and insert a microthin contact lens in front of the eye’s natural lens inside the eye. You will never have to worry again about foggy glasses or losing a contact. Our ICL patients are thrilled with their results – in the F.D.A. clinical trials, patients reported a 99% satisfaction rate!</p>
		</div>
	</div>
</section>


<section class="layout-1-2 layout-1-2-reverse layout-1-2-type__inner layout-1-2-wide-text">
	<div class="layout-1-2__image layout-1-2-reverse__image" style="background-image:url(<?php echo get_template_directory_uri( )?>/img/AdobeStock_218472006.jpg);"></div>
	<div class="layout-1-2__text layout-1-2-reverse__text">
		<h2 class="is-heading u-text-medium">Visian Toric ICL®</h2>
		<p>Will need blurb on Visian. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>
	</div>
</section>

<?php
	$candidateTypes = [
		[
			'image' => get_template_directory_uri() . '/img/candidate-2.png',
			'copy' => 'Are between the ages <span>of 21 and 45</span>'
		],
		[
			'image' => get_template_directory_uri() . '/img/candidate-1-2.png',
			'copy' => 'Are nearsighted <span>(-3D to -20D in prescription)<span>'
		],
		[
			'image' => get_template_directory_uri() . '/img/candidate-1-3.png',
			'copy' => 'Have astigmatism <span>(-1D to -4D in prescription)</span>'
		],
		[
			'image' => get_template_directory_uri() . '/img/candidate-1-4.png',
			'copy' => 'Have dry eyes, severe <span>myopia, or thin corneas</span>'
		]
	];
?>

<section class="layout-candidates">
	<div class="layout-candidates__wrapper layout-container">

		<div class="layout-candidates__text u-text-center">
			<h2 class="is-heading layout-candidates__text-heading">Am I a Candidate?</h2>
			<div class="layout-candidates__text-text">
				<p>The best way to determine if Visian ICL or Visian Toric ICL can give you the visual freedom you’ve always wanted is to schedule a consultation with an ICL surgeon at The Eye Institute of Utah. You may be a candidate if you:</p>
			</div>
		</div>

		<div class="layout-candidates__types">
			<?php foreach($candidateTypes as $index => $candidate) { ?>
				<div class="layout-candidates__types-type">
					<div class="layout-candidates__types-type-image u-text-center"><img src="<?php echo $candidate['image'];?>" alt="{replace me}"></div>
					<div class="layout-candidates__types-type-copy"><?php echo $candidate['copy'];?></div>
				</div>
			<?php } ?>
		</div>

	</div>
</section>


<section class="layout-1-2-video">
	<div class="layout-1-2-video__wrapper layout-container">
		
		<div class="layout-1-2-video__item layout-1-2-video__text">
			<h3 class="layout-1-2-video__text-heading is-heading">The Visian ICL Experience</h3>
			<p class="layout-1-2-video__text-text">Lori’s experience with her Visian ICL procedure.  Lipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. </p>
		</div>	
		<div class="layout-1-2-video__item layout-1-2-video__video">
			<div class="layout-1-2-video__video-object">
				<img src="<?php echo get_template_directory_uri();?>/img/video-dummy.jpg" alt="{replace me}">
			</div>
		</div>
	</div>
</section>

<section class="layout-1-2-video layout-1-2-video-reverse">
	<div class="layout-1-2-video__wrapper layout-container">
		
		<div class="layout-1-2-video__item layout-1-2-video__text">
			<h3 class="layout-1-2-video__text-heading is-heading">How Does It Work?</h3>
			<p class="layout-1-2-video__text-text">Lori’s experience with her Visian ICL procedure.  Lipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. </p>
		</div>	
		<div class="layout-1-2-video__item layout-1-2-video__video">
			<div class="layout-1-2-video__video-object">
				<img src="<?php echo get_template_directory_uri();?>/img/video-dummy.jpg" alt="{replace me}">
			</div>
		</div>
	</div>
</section>


<section class="layout-text-only">
	<div class="layout-text-only__wrapper layout-container">
		<p>In addition, the benefit of the Visian ICL and Visian Toric ICL include a short recovery time, minimal risks and complications, removable, and UV blocking. If you would like to see how the Visian ICL or Visian Toric ICL can benefit your lifestyle, schedule a complimentary evaluation today at 801-980-0640 and take advantage of this great technology!</p>
	</div>
</section>
