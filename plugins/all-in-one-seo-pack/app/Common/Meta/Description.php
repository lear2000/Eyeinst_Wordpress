<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the (Open Graph) description.
 *
 * @since 4.0.0
 */
class Description {
	/**
	 * Returns the homepage description.
	 *
	 * @since 4.0.0
	 *
	 * @return string The homepage description.
	 */
	public function getHomePageDescription() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$description = $this->getPostDescription( (int) get_option( 'page_on_front' ) );
			return $description ? $description : aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) );
		}

		$description = $this->prepareDescription( aioseo()->options->searchAppearance->global->metaDescription );
		return $description ? $description : aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) );
	}

	/**
	 * Returns the description for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post $post    The post object (optional).
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The page description.
	 */
	public function getDescription( $post = null, $default = false ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			return $this->getHomePageDescription();
		}

		if ( $post || is_singular() || aioseo()->helpers->isStaticPage() ) {
			$description = $this->getPostDescription( $post, $default );
			if ( $description ) {
				return $description;
			}

			if ( is_attachment() ) {
				$post    = empty( $post ) ? aioseo()->helpers->getPost() : $post;
				$caption = wp_get_attachment_caption( $post->ID );
				return $caption ? $this->prepareDescription( $caption ) : $this->prepareDescription( $post->post_content );
			}
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = $post ? $post : get_queried_object();
			return $this->getTermDescription( $term, $default );
		}

		if ( is_author() ) {
			$description = $this->prepareDescription( aioseo()->options->searchAppearance->archives->author->metaDescription );
			if ( $description ) {
				return $description;
			}

			$author = get_queried_object();
			return $author ? $this->prepareDescription( get_the_author_meta( 'description', $author->ID ) ) : '';
		}

		if ( is_date() ) {
			return $this->prepareDescription( aioseo()->options->searchAppearance->archives->date->metaDescription );
		}

		if ( is_search() ) {
			return $this->prepareDescription( aioseo()->options->searchAppearance->archives->search->metaDescription );
		}

		if ( is_archive() ) {
			$postType = get_queried_object();
			$options  = aioseo()->options->noConflict();
			if ( $options->searchAppearance->dynamic->archives->has( $postType->name ) ) {
				return $this->prepareDescription( aioseo()->options->searchAppearance->dynamic->archives->{ $postType->name }->metaDescription );
			}
		}
	}

	/**
	 * Returns the description for a given post.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|int $post    The post object or ID.
	 * @param  boolean     $default Whether we want the default value, not the post one.
	 * @return string               The post description.
	 */
	public function getPostDescription( $post, $default = false ) {
		$post     = $post && is_object( $post ) ? $post : aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		$description = '';
		if ( ! empty( $metaData->description ) && ! $default ) {
			$description = $this->prepareDescription( $metaData->description, $post->ID, false, false );
		}

		if (
			! $description &&
			in_array( 'autogenerateDescriptions', aioseo()->internalOptions->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->searchAppearance->advanced->autogenerateDescriptions
		) {
			return $description;
		}

		if ( ! $description ) {
			$description = $this->prepareDescription( $this->getPostTypeDescription( $post->post_type ), $post->ID, $default, false );
		}

		$generateDescriptions = apply_filters( 'aioseo_generate_descriptions_from_content', true, [ $post ] );
		if ( ! $description && ! post_password_required( $post ) ) {
			$description = $post->post_excerpt;
			if (
				$generateDescriptions &&
				in_array( 'useContentForAutogeneratedDescriptions', aioseo()->internalOptions->deprecatedOptions, true ) &&
				aioseo()->options->deprecated->searchAppearance->advanced->useContentForAutogeneratedDescriptions
			) {
				$description = aioseo()->helpers->getContent( $post );
			}

			$description = $this->prepareDescription( $description, $post->ID, $default, false );
			if ( ! $description && $generateDescriptions && $post->post_content ) {
				$description = $this->prepareDescription( aioseo()->helpers->getContent( $post ), $post->ID, $default, false );
			}
		}

		if ( ! is_paged() ) {
			if ( in_array( 'descriptionFormat', aioseo()->internalOptions->deprecatedOptions, true ) ) {
				$descriptionFormat = aioseo()->options->deprecated->searchAppearance->global->descriptionFormat;
				if ( $descriptionFormat ) {
					$description = preg_replace( '/#description/', $description, $descriptionFormat );
				}
			}
		}

		return $description ? $this->prepareDescription( $description, $post->ID, $default ) : $this->prepareDescription( term_description( '' ), $post->ID, $default );
	}

	/**
	 * Retrieve the default description for the post type.
	 *
	 * @since 4.0.6
	 *
	 * @param  string $postType The post type.
	 * @return string           The description.
	 */
	public function getPostTypeDescription( $postType ) {
		$options = aioseo()->options->noConflict();
		if ( $options->searchAppearance->dynamic->postTypes->has( $postType, false ) ) {
			return $options->{$postType}->metaDescription;
		}

		return '';
	}

	/**
	 * Returns the term description.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Term $term    The term object.
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The term description.
	 */
	public function getTermDescription( $term, $default = false ) {
		$description = '';
		if (
			! $description &&
			in_array( 'autogenerateDescriptions', aioseo()->internalOptions->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->searchAppearance->advanced->autogenerateDescriptions
		) {
			return $description;
		}

		$options = aioseo()->options->noConflict();
		if ( ! $description && $options->searchAppearance->dynamic->taxonomies->has( $term->taxonomy ) ) {
			$description = $this->prepareDescription( aioseo()->options->searchAppearance->dynamic->taxonomies->{$term->taxonomy}->metaDescription, false, $default );
		}
		return $description ? $description : $this->prepareDescription( term_description( $term->term_id ), false, $default );
	}

	/**
	 * Prepares and sanitizes the description.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $description  The description.
	 * @param  int     $id           The ID of the page or post.
	 * @param  boolean $default      Whether we want the default value, not the post one.
	 * @param  boolean $addPaged     Whether the paged format should be added to the description.
	 * @return string  $description  The sanitized description.
	 */
	public function prepareDescription( $description, $id = false, $default = false, $addPaged = true ) {
		if ( ! empty( $description ) && ! is_admin() && $addPaged && 1 < aioseo()->helpers->getPageNumber() ) {
			$description .= '&nbsp;' . trim( aioseo()->options->searchAppearance->advanced->pagedFormat );
		}

		$description = $default ? $description : aioseo()->tags->replaceTags( $description, $id );
		$description = apply_filters( 'aioseo_description', $description );

		if ( apply_filters( 'aioseo_description_do_shortcodes', true ) ) {
			$description = aioseo()->helpers->doShortcodes( $description );
		}

		$description = aioseo()->helpers->decodeHtmlEntities( $description );
		$description = wp_strip_all_tags( strip_shortcodes( $description ) );
		// Trim both internal and external whitespace.
		$description = preg_replace( '/[\s]+/u', ' ', trim( $description ) );
		return aioseo()->helpers->internationalize( $description );
	}
}