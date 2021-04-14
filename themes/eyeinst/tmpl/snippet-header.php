<header>
	<a href="#" class="logo"><img src="<?php bloginfo('template_directory');?>/img/logo.svg" alt="The Eye Institute of Utah - Logo" width="173.087" height="48.026"/></a>
	<div class="nav-wraps">
		<div class="burger">
			<span class="bar1"></span>
			<span class="bar2"></span>
		</div>
		<nav class="nav-top">
			<?php
			wp_nav_menu( array(
			    'menu'           => 'Top Level Menu',
			    'container' => ''
			) );
			 ?>
		</nav>
		<nav class="nav-bottom">
			<?php
			wp_nav_menu( array(
			    'menu'           => 'Main Menu',
			    'container' => '',
			     'walker' => new submenuWrap()
			) );
			 ?>
			<a class="h-tel" href="<?php echo telly_link(get_field( 'header_phone', 'option' )); ?>"><?php the_field( 'header_phone', 'option' ); ?></a>
		</nav>
	</div>
</header>