<?php

	// IMAGE SIZE ( Version 3 ) {

		function remove_image_sizes( $sizes) {

			// unset( $sizes['thumbnail']);
			// unset( $sizes['medium']);
			unset( $sizes['large']);
			return $sizes;
		}

		add_filter( 'intermediate_image_sizes_advanced', 'remove_image_sizes' );

		/* sizes for icons and previews in wordpress */
		add_image_size( 'thumbnail', '160', '160', /* crop */ false );
		add_image_size( 'medium', '118', '118', /* crop */ false );

	// }

	// ADAPTIVE IMAGES ( Version 22 (AIFWP 1.1) ) {

		add_image_size( 'adaptive-image-base', '2000', '2000', /* crop */ false );

		// get image function {

			function get_adaptive_image( $p = array() ) {

				$p += array(
					'name' => 'full', 
					'id' => false, 
					'file' => false, 
					'alt' => false, 
					'img_class' => '',
					'img_class_resp' => 'resp',
					'img_data' => false,
					'link_image' => false, /* true or size */
					'link_page' => false, /* true or id of page */
					'link_url' => false,
					'link_class' => false,
					'root_class' => false,
					'link_rel' => false,
					'link_title' => false,
					'link_data' => false,
					'wrap' => false,
					'wrap_class' => false,
					'style' => false
				);

				$sufix = '?size=' . $p['name'];
				$img_attr = array();
				$img_src = false;

				/* image src */
				if ( $p['id'] ) {

					$img_param = wp_get_attachment_image_src( $p['id'], $p['name'] );
					$img_src = $img_param[0];
				}
				if ( $p['file'] ) {

					$img_src = get_bloginfo('template_url') . '/' . $p['file'];
				}

				$link_url = false;
				$link_rel = false;
				$link_title = false;
				$link_data = false;

				/* root class */

				if ( $p['root_class'] ) {

					if ( $p['link_image'] OR $p['link_page'] OR $p['link_url'] ) {

						 $p['link_class'] = trim( $p['link_class'] . ' ' . $p['root_class'] );
					}

					elseif ( $p['wrap'] ) {

						 $p['wrap_class'] = trim( $p['wrap_class'] . ' ' . $p['root_class'] );
					}

					else {

						 $p['img_class'] = trim( $p['img_class'] . ' ' . $p['root_class'] );
					}
				}

				/* image data */

				if ( $p['img_data'] ) {

					foreach ( $p['img_data'] as $key => $value ) {

						$img_attr['data-' . $key] = $value;
					}
				}

				/* link data */

				if ( $p['link_data'] ) {

					foreach ( $p['link_data'] as $key => $value ) {

						$link_data .= ' data-' .$key . '="' . $value . '"';
					}
				}

				/* link image */

				if ( $p['link_image'] AND $p['link_image'] != 'true' AND $p['link_image'] != 'false' ) {

					$link_url = get_adaptive_image_src( array( 'name' => $p['link_image'], 'id' => $p['id'] ) );
				}

				if ( $p['link_image'] == 'true') {

					$link_url = $img_src;
				}

				/* link page */

				if ( $p['link_page'] ) {

					$link_url = get_permalink();
				}

				if ( $p['link_page'] AND is_int($p['link_page']) ) {

					$link_url = get_permalink( $p['link_page'] );
				}

				/* link url */

				if ( $p['link_url'] ) {

					$link_url = $p['link_url'];
				}

				/* link class */

				$link_class = '';

				if ( $p['link_class'] ) {

					$link_class = ' class="' . $p['link_class'] . '"';
				}

				/* link rel */

				if ( $p['link_rel'] ) $link_rel = ' rel="' . $p['link_rel'] . '"';

				/* link title */

				if ( $p['link_title'] ) {

					$title = $p['link_title'];

					if ( $p['link_title'] === 'titel' ) {

						$data = get_post( $p['id'] );
						$title = $data->post_titel;
					}

					if ( $p['link_title'] === 'beschriftung' ) {

						$data = get_post( $p['id'] );
						$title = $data->post_excerpt;
					}

					if ( $p['link_title'] === 'alt' ) {

						$data = get_post_meta( $p['id'], '_wp_attachment_image_alt' );
						$title = $data[0];
					}

					$link_title = ' title="' . $title . '"';
				}

				/* return begin */
				$return = '';

				/* basic requirement check */
				if ( $img_src ) {

					/* wrap open */

					if ( $p['wrap'] ) {

						if ( $p['wrap_class'] ) $wrap_class = ' class="' .$p['wrap_class'] . '"';
						$return .= '<' . $p['wrap'] . $wrap_class . '>';
					}

					/* return link open */

					if ( $link_url OR $link_class OR $link_rel OR $link_title ) {

						if ( $link_url ) {

							$link_url =	 ' href="' . $link_url . '"';
						}

						$return .= '<a' . $link_url . $link_class . $link_rel . $link_title . $link_data . '>';
					}

					/* return image */

					$img_attr['src'] = $img_src . $sufix;
					$img_attr['class'] = trim( $p['img_class_resp'] . ' ' . $p['img_class'] . ' size-' . $p['name'] );

					if ( $p['id'] ) {

						if ( $p['alt'] ) $img_attr['alt'] = $p['alt'];
						if ( $p['style'] ) $img_attr['style'] = $p['style'];
						$return .= wp_get_attachment_image( $p['id'], 'adaptive-image-base', false, $img_attr );
					}

					if ( $p['file'] ) {

						$img_alt = '';
						if ( $p['alt'] ) $img_alt .= ' alt="' . $p['alt'] . '"';
						if ( $p['style'] ) $img_style .= ' style="' . $p['style'] . '"';
						$return .= '<img src="' . $img_attr['src'] . '" class="' . $img_attr['class'] . '"' . $img_alt . $img_style . '/>';
					}

					/* return link close */

					if ( $link_url OR $link_class OR $link_rel OR $link_title ) {

						$return .= '</a>';
					}

					/* wrap close */

					if ( $p['wrap'] ) {

						$return .= '</' . $p['wrap'] . '>';
					}

					/* remove image dimensions attributes */
					$return = remove_image_dimensions_attributes( $return );
				}

				/* return */
				return	$return;
			}

		// }

		// get src {

			function get_adaptive_image_src( $p = array(
				'name' => 'full', 
				'id' => false,
			) ) {

				$sufix = '?size=' . $p['name'];

				/* image src */
				$img_param = wp_get_attachment_image_src( $p['id'], 'adaptive-image-base' );
				$img_src = $img_param[0];

				return	$img_src.$sufix;
			}

		// }

		// multisite {

			function multisite_urls_2_real_urls( $buffer ) {

				global $current_blog;

				if ( config_get_curr_blog_id() > 1 ) {

					$buffer = str_replace( $current_blog->path	. 'files', '/backend/wp-content/blogs.dir/' . config_get_curr_blog_id() . '/files', $buffer );
				}

				return $buffer;
			}

			function buffer_start() { 

				ob_start("multisite_urls_2_real_urls");
			}

			function buffer_end() {

				ob_end_flush();
			}

			if ( config_get_curr_blog_id() > 1 ) {

				add_action( 'wp_head', 'buffer_start' );
				add_action( 'wp_footer', 'buffer_end' );
				add_action( 'admin_head', 'buffer_start' );
				add_action( 'admin_footer', 'buffer_end' );
			}

		// }

		// styles {

			function adaptive_images_styles() {

				include( 'config/adaptive-images-config.php' );

				$styles = array();

				foreach ( $setup as $size => $item1 ) {

					if (  isset( $item1['use-max-width'] ) ) {

						foreach ( $item1['resolutions'] as $resolution => $item2 ) {

							if ( isset( $config['resolutions'][ $resolution ] ) ) {

								$resolution = $config['resolutions'][ $resolution ];
							}

							if ( isset( $item2['w'] ) ) {

								if ( !isset( $styles[ $resolution ] ) ) {

									$styles[ $resolution ] = '';
								}

								$styles[ $resolution ] .= 'img.size-' . $size . '{max-width: ' . $item2['w'] . 'px;}';
							}
						}
					}
				}

				if ( count( $styles > 0 ) ) {

					echo '<style rel="stylesheet" type="text/css">';

						foreach ( $styles as $key => $item ) {

						   echo '@media screen and (min-width: ' . $key . 'px) {' . $item . '}'; 
						}

					echo '</style>';
				}

			}
			add_action( 'wp_head', 'adaptive_images_styles' );

		// }

	// }

	// IMAGE SIZES FOR EDITOR ( Version 4 ) {
		/*
		function get_editor_imagesizes() {

			// Die Breite und Höhe sollte maximal der für AI benötigten Größe sein
			// Regenerate Images after changes!

			$sizes = array(
				'test' => array(
					'width' => '300',
					'height' => '300',
					'crop' => false,
					'label' => 'Test',
					'posttypes' => array('post','page')
				)
			);

			return $sizes;
		}

		function set_editor_imagesizes() {

			foreach ( get_editor_imagesizes() as $size => $item ) {

				add_image_size( $size, $item['width'], $item['height'], $item['crop'] );
			}
		}
		set_editor_imagesizes();

		// define image-sizes at the media-popup 
		function the_image_size_names( $sizes ) {

			// removing media-sizes from select-input for inserting into the editor
			unset( $sizes['thumbnail'] );
			unset( $sizes['medium'] );
			unset( $sizes['large'] );
			unset( $sizes['full'] );

			// adding relevant media-sizes to the select-input for inserting into the editor

			if ( isset( $_REQUEST['post'] ) ) {

				$posttype = get_post_type( $_REQUEST['post'] );

				foreach ( get_editor_imagesizes() as $size => $item ) {

					$check = true;

					if ( is_admin() && $item['posttypes'] && !in_array( $posttype, $item['posttypes'] ) ) {

						$check = false;
					}

					if ( $check ) {

						$sizes[$size] = $item['label'];
					}
				}
			}

			return $sizes;
		}
		add_filter( 'image_size_names_choose','the_image_size_names', 10, 1 );

		// add Adaptive-Image parameter size to image-url and zoom to href-url
		function my_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {

			$src = wp_get_attachment_image_src( $id, 'adaptive-image-base', false );

			foreach ( get_editor_imagesizes() as $size => $item ) {

				if ( strpos( $html, 'size-' . $size ) !== false ) {

					$html = preg_replace( '/(.*)(src="(.*)\.(jpg|jpeg|gif|png)")(.*)/', '$1src="' . $src[0] . '?size=' . $size . '"$5', $html );
				}
			}

			$html = preg_replace( '/(.*)(href="(.*)\.(jpg|jpeg|gif|png)")(.*)/', '$1href="' . $src[0] . '?size=zoom"$5', $html );

			return $html;
		}
		add_filter( 'image_send_to_editor', 'my_image_send_to_editor', 10, 8 );
		*/

		function tool_filter_the_content( $content ) {

			$content = remove_image_dimensions_attributes( $content );

			return $content;
		}

		add_filter( 'the_content', 'tool_filter_the_content', 20 );

		function fixed_img_caption_shortcode( $attr, $content = null ) {

			/* Source: WordPress > .media.php > The Caption shortcode. */

			if ( ! isset( $attr['caption'] ) ) {

				if ( preg_match( '#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches ) ) {

					$content = $matches[1];
					$attr['caption'] = trim( $matches[2] );
				}
			}

			// $id = str_replace( ' attachment_', '', $attr['id'] );

			$size = preg_replace( '/(.*)(size=(.*))" width(.*)/', '$3', $content );

			$content = str_replace( 'size-' . $size . ' ', '', $content );

			return '<div class="size-' . $size . ' wp-caption ' . esc_attr( $attr['align'] ) . '">' . do_shortcode( $content ) . '<p class="wp-caption-text">' . $attr['caption'] . '</p></div>';
		}

		add_shortcode( 'wp_caption', 'fixed_img_caption_shortcode' );
		add_shortcode( 'caption', 'fixed_img_caption_shortcode' );

	// }
?>