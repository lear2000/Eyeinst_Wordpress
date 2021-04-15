<?php $whatfooter = get_field( 'what_kind_of_footer' ); ?>
<div class="mtns"></div>
<footer>

<?php if ($whatfooter == "single"):?>
	<!-- footer button -->
	<div class="container">
		<h2><span>Schedule a</span>
		Free Consultation</h2>
		<?php $footer_contact_us_button_link = get_field( 'footer_contact_us_button__link', 'option' ); ?>
		<?php if ( $footer_contact_us_button_link ) : ?>
			<a class="btn secondary" href="<?php echo esc_url( $footer_contact_us_button_link['url'] ); ?>" target="<?php echo esc_attr( $footer_contact_us_button_link['target'] ); ?>"><?php echo esc_html( $footer_contact_us_button_link['title'] ); ?></a>
		<?php endif; ?>
	</div>
	<!-- footer button -->
<?php endif; ?>

<?php if ($whatfooter == "quick"):?>

	<!-- footer form small -->
	<div class="container formsmall">
		<h2><span>Wondering if Visian ICL is Right for You?</span>
		Schedule a Consultation Today</h2>
		<?php $footer_quick_contact_form = get_field( 'footer_quick_contact_form', 'option' ); ?>
		<?php if ( $footer_quick_contact_form ) : ?>
			<?php
					$post = $footer_quick_contact_form;
					$formname = $post->post_name;
			 ?>

			<?php echo do_shortcode( '[aform name="'.$formname.'"]' ); ?>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>
		<small>By completing this contact form and providing your email address, you are opting in to receive email communications from The Eye Institute of Utah. You may choose to opt-out of future email communications at any time.</small>
	</div>
	<!-- footer form small -->
	<?php endif; ?>

	<div class="container">
	<div class="f-bar-one">
		<span class="f-logo">
			<a href="<?php bloginfo('url');?>/"><img loading="lazy" src="<?php echo get_template_directory_uri();?>/img/logo-white.svg" alt="The Eye Institute of Utah - Logo White" width="247" height="72" /></a>
		</span>
		<address>
			<a target="_blank" rel="noopener" href="<?php the_field( 'address_link', 'option' ); ?>">755 East 3900 South,<br>Salt Lake City, UT 84107</a>
		</address>
		<div class="f-apts">
			<span>Appointments</span>
			<a href="<?php echo telly_link(get_field( 'header_phone', 'option' )); ?>"><?php the_field('header_phone','option'); ?></a>
		</div>
	</div>
	<div class="f-bar-two">
		<ul>
			<li><a target="_blank" rel="noopener" href="https://www.facebook.com/theeyeinstitute"><img loading="lazy" alt="Facebook Icon" src="<?php echo get_template_directory_uri();?>/img/facebook-white.svg" /></a></li>
			<li><a target="_blank" rel="noopener" href="https://www.instagram.com/eiuvision/"><img loading="lazy" alt="Instagram Icon" src="<?php echo get_template_directory_uri();?>/img/insta-white.svg" /></a></li>
			<li><a target="_blank" rel="noopener" href="https://www.youtube.com/user/EyeInstituteUtah"><img loading="lazy" alt="Youtube Icon" src="<?php echo get_template_directory_uri();?>/img/youtube-white.svg" /></a></li>
		</ul>
	</div>
	<div class="copyright">
		<p>Copyright © <?php echo date('Y'); ?> The Eye Institute of Utah  <span class="bar">|</span>  <a href="<?php the_field( 'nondiscrimination_link', 'option' ); ?>">Nondiscrimination Policy</a>  <span class="bar">|</span>  <a href="<?php the_field( 'privacy_link', 'option' ); ?>">Privacy Policy</a></p>
	</div>
</div>
<div class="sticky-bar">
	<a href="<?php the_field( 'phone_link', 'option' ); ?>"><img  loading="lazy" src="<?php echo get_template_directory_uri();?>/img/sticky-phone.svg" width="23" height="23" /></a>
	<a href="<?php the_field( 'calendar_link', 'option' ); ?>"><img  loading="lazy" src="<?php echo get_template_directory_uri();?>/img/sticky-cal.svg" width="22" height="26" /></a>
	<a href="<?php the_field( 'location_link', 'option' ); ?>"><img  loading="lazy" src="<?php echo get_template_directory_uri();?>/img/sticky-location.svg" width="17" height="26" /></a>
	<a href="#">$2,020 off<br>LASIK PRK or ICL</a>
</div>
<div class="desk-sticky">
	<?php if ( have_rows( 'special_link', 'option' ) ) : ?>
		<?php
					while ( have_rows( 'special_link', 'option' ) ) : the_row();
						//spans will stack the left
			?>

			<a href="<?php the_sub_field( 'link' ); ?>">
				<div><?php the_sub_field( 'text_left' ); ?></div>
				<div><span><?php the_sub_field( 'text_right' ); ?></span></div>
			</a>
		<?php endwhile; ?>
	<?php endif; ?>


</div>
</footer>
