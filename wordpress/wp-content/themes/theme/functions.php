<?php
    
    // IMAGE SIZE ( Version 3 ) {

    	function remove_image_sizes( $sizes) {
		    // unset( $sizes['thumbnail']);
		    // unset( $sizes['medium']);
		    unset( $sizes['large']);
		    return $sizes;
		}
		add_filter('intermediate_image_sizes_advanced', 'remove_image_sizes');
        
        /* sizes for icons and previews in wordpress */
        add_image_size( 'thumbnail', '160', '160', /* crop */ false );
        add_image_size( 'medium', '118', '118', /* crop */ false );
        
	// }
	
    // ADAPTIVE IMAGES ( Version 12 (AIFWP 1.1) ) {
    
        add_image_size( 'adaptive-image-base', '2000', '2000', /* crop */ false );
    
    	/* get adaptive image function */
    	function get_adaptive_image( $p = array() ) {

    		$p += array(
        		'name' => 'full', 
        		'id' => false, 
        		'file' => false, 
        		'alt' => false, 
        		'ratio' => false, 
        		'img_class' => '',
        		'img_data' => false,
        		'link_image' => false, /* true or size */
        		'link_page' => false, /* true or id of page */
        		'link_url' => false,
        		'link_class' => false,
        		'link_rel' => false,
        		'link_title' => false,
        		'link_data' => false
        	);
            
            $p['name'] = preg_replace( '/[^a-z0-9\-_]*/', '', $p['name'] );
            if ( $p['ratio'] ) $p['ratio'] = str_replace( ':', '-', preg_replace( '/[^0-9\:\-]*/', '', $p['ratio'] ) );
            
    		$sufix = '?size=' . $p['name'];
    		
    		if ( $p['ratio'] ) {
    		    
    		    $sufix .= '&ratio=' . $p['ratio'];
    		}
    		
    		$img_attr = array();

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
    		if ( $p['link_image'] == 'true') $link_url = $img_src;
        
    		/* link page */
    		if ( $p['link_page'] ) $link_url = get_permalink();
    		if ( $p['link_page'] AND is_int($p['link_page']) ) $link_url = get_permalink( $p['link_page'] );

    		/* link url */
    		if ( $p['link_url'] ) $link_url = $p['link_url'];

    		/* link class */
    		$link_class = '';
    		if ( $p['link_class'] ) $link_class = ' class="' . $p['link_class'] . '"';
        
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
		    
		    /* return link open */
    		if ( $link_url OR $link_class OR $link_rel OR $link_title ) {
    		    if ( $link_url ) $link_url =  ' href="' . $link_url . '"';
    		    $return .= '<a' . $link_url . $link_class . $link_rel . $link_title . $link_data . '>';
    		}
		    
		    /* return image */
    		$img_attr['src'] = $img_src . $sufix;
    		$img_attr['class'] = 'resp ' . $p['img_class'];
		    
    		if ( $p['id'] ) {
    		    if ( $p['alt'] ) $img_attr['alt'] = $p['alt'];
    		    $return .= wp_get_attachment_image( $p['id'], 'adaptive-image-base', false, $img_attr );
		    }
		    
		    if ( $p['file'] ) {
		        $img_alt = '';
		        if ( $p['alt'] ) $img_alt .= ' alt="' . $p['alt'] . '"';
		        $return .= '<img src="' . $img_attr['src'] . '" class="' . $img_attr['class'] . '"' . $img_alt . '/>';
		    }
		    
		    /* return link close */
    		if (  $link_url OR $link_class OR $link_rel OR $link_title ) $return .= '</a>';

            /* remove image dimensions attributes */
		    $return = remove_image_dimensions_attributes( $return );
            
            /* return */
    		return  $return;
    	}

    	function get_adaptive_image_src( $p = array() ) {
            
            $p += array(
                'name' => 'full', 
        		'ratio' => false, 
        		'id' => false
        	);
        	
            $p['name'] = preg_replace( '/[^a-z0-9\-_]*/', '', $p['name'] );
            if ( $p['ratio'] ) $p['ratio'] = str_replace( ':', '-', preg_replace( '/[^0-9\:\-]*/', '', $p['ratio'] ) );
            
    		$sufix = '?size=' . $p['name'];
    		
    		if ( $p['ratio'] ) {
    		    
    		    $sufix .= '&ratio=' . $p['ratio'];
    		}

    		/* image src */
    		$img_param = wp_get_attachment_image_src( $p['id'], 'adaptive-image-base' );
    		$img_src = $img_param[0];

    		return  $img_src.$sufix;
    	}
    
        /* get adaptive image work on multisite */
        function multisite_urls_2_real_urls( $buffer ) {
        
            global $current_blog;
            if ( config_get_curr_blog_id() > 1 ) {
                $buffer = str_replace( $current_blog->path  . 'files', '/wp-content/blogs.dir/' . config_get_curr_blog_id() . '/files', $buffer );
            }
            return $buffer;
        }

        function buffer_start() { ob_start("multisite_urls_2_real_urls"); }
        function buffer_end() { ob_end_flush(); }

        if ( config_get_curr_blog_id() > 1 ) {
            add_action('wp_head', 'buffer_start');
            add_action('wp_footer', 'buffer_end');
            add_action('admin_head', 'buffer_start');
            add_action('admin_footer', 'buffer_end');
        }
    
	// }
    
    // ADAPTIVE-IMAGE SIZES FOR USING IN THE EDITOR ( Version 1 ) {
	    
	    function get_editor_imagesizes() {
            
            // Regenerate Images after changes!
            // examples:
            
            $sizes = array(
                'editor-post' => array(
                    'width' => '600',
                    'height' => '600',
                    'crop' => false,
                    'label' => 'Posts only',
                    'posttypes' => array('post')
                ),
                'editor-page' => array(
                    'width' => '600',
                    'height' => '600',
                    'crop' => false,
                    'label' => 'Pages only',
                    'posttypes' => array('page')
                ),
                'editor-post-page' => array(
                    'width' => '600',
                    'height' => '600',
                    'crop' => false,
                    'label' => 'Pages and Posts',
                    'posttypes' => array('post','page')
                ),
                'editor-all' => array(
                    'width' => '600',
                    'height' => '600',
                    'crop' => false,
                    'label' => 'All',
                    'posttypes' => false
                )
            );

            return $sizes;
	    }
	    
	    /* do not need to change things below */
	    
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
            
            $posttype = get_post_type( $_REQUEST['post'] );
                
            foreach ( get_editor_imagesizes() as $size => $item ) {
                
                $check = true;

                if ( is_admin() && $item['posttypes'] && !in_array( $posttype, $item['posttypes'] ) ) {
                    $check = false;
                }
                
                if ( $check ) $sizes[$size] = $item['label'];
	        }
            
            return $sizes;
        }
        add_filter( 'image_size_names_choose','the_image_size_names', 10, 1 );
	    
	    // add Adaptive-Image parameter size to image-url and zoom to href-url
        function my_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
            
            $src = wp_get_attachment_image_src( $id, 'adaptive-image-base', false );
            
            foreach ( get_editor_imagesizes() as $size => $item ) {
                
                if ( strpos( $html, 'size-' . $size ) !== false ) {
                
                    $html = preg_replace( '/(.*)(src="(.*)\.(jpg|gif|png)")(.*)/', '$1src="' . $src[0] . '?size=' . $size . '"$5', $html );
            	}
	        }
	        
	        $html = remove_image_dimensions_attributes($html);
    	    
            $html = preg_replace( '/(.*)(href="(.*)\.(jpg|gif|png)")(.*)/', '$1href="' . $src[0] . '?size=zoom"$5', $html );
            
            return $html;
        }
        add_filter( 'image_send_to_editor', 'my_image_send_to_editor', 10, 7 );
        
        
	// }