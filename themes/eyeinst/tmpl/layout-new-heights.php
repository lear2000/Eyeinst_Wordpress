<?php // temp data ?>

<?php
	$items = [
		[
			'image' => get_template_directory_uri() . '/img/new-height-1.png',
			'heading' => 'LASIK/PRK',
			'copy' => 'Looking to free yourself from glasses and contacts?'
		],
		[
			'image' => get_template_directory_uri() . '/img/new-height-1.png',
			'heading' => 'Vision ICL',
			'copy' => 'A wonderful LASIK/PRK alternative'
		],
		[
			'image' => get_template_directory_uri() . '/img/new-height-1.png',
			'heading' => 'Vision Correction <span>Over 40</span>',
			'copy' => 'A wonderful LASIK/PRK alternative'
		],
		[
			'image' => get_template_directory_uri() . '/img/new-height-1.png',
			'heading' => 'Custom Cataract Surgery',
			'copy' => 'Regain your freedom and take back your lifestyle!'
		]
	];
?>

<?php // TEMPLATE ?>

<section class="layout-new-heights">
	<div class="layout-new-heights__wrapper">
			
		<div class="layout-new-heights__heading">
			<h2 class="is-heading u-text-center"><span>How Can We</span> <span>Take Your Vision</span> To New Heights?</h2>
		</div>

		<div class="layout-new-heights__items u-text-center">
			<?php foreach ($items as $itemIndex => $itemValue) { ?>
				<?php if($itemIndex > 3){ break; }	?>
				<div class="layout-new-heights__item">
					<div class="layout-new-heights__item-image">
						<img src="<?php echo $itemValue['image'];?>" alt="">
					</div>
					<div class="layout-new-heights__item-text">
						<h3 class="layout-new-heights__item-text-heading"><?php echo $itemValue['heading'];?></h3>
						<div class="layout-new-heights__item-text-text">
							<?php echo $itemValue['copy'];?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>

	<hr class="u-mountain-lines">

</section>
