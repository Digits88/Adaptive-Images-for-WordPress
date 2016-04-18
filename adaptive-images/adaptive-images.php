<?php

	/* Adaptive-Images ( is based on and inspired from "Adaptive Images" by Matt Wilcox ) {

		forked from:
			GitHub:		https://github.com/MattWilcox/Adaptive-Images
			Version:	1.5.2
			Homepage:	http://adaptive-images.com
			Twitter:	@responsiveimg
			LEGAL:		Adaptive Images by Matt Wilcox is licensed under a Creative Commons Attribution 3.0 Unported License.

		extended by:
			GitHub:		https://github.com/johannheyne/adaptive-images-for-wordpress
			Version:	1.3
			Changed:	2014.06.20 10:00

	} */

	// CONFIG {

		$wordpressfolder = '/'; // @TODO: if you use wordpress inside a folder change it like '/wordpress/'
		$themefolder = 'twentyfourteen'; // @TODO: rename with your themename like 'mytheme'

		ini_set( 'memory_limit', '128M' );

	// }

	// PREPARE {

		// PATHS {

			// WORDPRESSFOLDER {

			    $wordpressfolder = trim( $wordpressfolder, '/' );

				if ( $wordpressfolder !== '' ) {

					$wordpressfolder = $wordpressfolder . '/';
				}

			// }

			$document_root = str_replace( 'adaptive-images/adaptive-images.php', '', $_SERVER['SCRIPT_FILENAME'] );
			$requested_uri = parse_url( urldecode( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
			$requested_file = basename( $requested_uri );
			$source_file = $document_root . $requested_uri;

			$source_file = preg_replace( '/(.*)\/(.*)\/wp-content(.*)/', '$1/' . $wordpressfolder . 'wp-content$3', $source_file );

			$themepath = $document_root . '/' . $wordpressfolder . 'wp-content/themes/' . trim( $themefolder, '/' ) . '/';
			include( $themepath	 . 'config/adaptive-images-config.php' );

			if ( ! isset( $config ) ) {

				sendErrorImage( 'Configuration data in config/adaptive-images-config.php missing!' );
			}

		// PATHS }

		// IF THERE IS NO SIZE PARAMETER {

			if ( ! isset( $_GET['size'] ) ) {

				sendImage( $source_file, 1 );
				die( );
			}

		// IF THERE IS NO SIZE PARAMETER }

		// SCRIPT VARIABLES {

			$highresmode		= false;

			$resolutions		= $config['resolutions']; // the image break-points to use in the src-parameter 
			$cache_path			= $config['cache_path']; // where to store the generated re-sized images. Specify from your document root! 
			$highresmode		= $config['highres_mode']; // use high resolutions, means double the pixel size of image dimensions, prevent retina option
			$jpg_quality		= $config['jpg_quality']; // the quality of any generated JPGs on a scale of 0 to 100
			$jpg_quality_retina = $config['jpg_quality_retina']; // the quality of any generated JPGs on a scale of 0 to 100 for retina
			$retina				= true;
			$sharpen			= $config['sharpen']['status']; // Shrinking images can blur details, perform a sharpen on re-scaled images?
			$watch_cache		= $config['watch_cache']; // check that the adapted image isn't stale ( ensures updated source images are re-cached )
			$browser_cache		= $config['browser_cache']; // How long the BROWSER cache should last ( seconds, minutes, hours, days. 7days by default )
			$debug_mode			= $config['debug_mode']; // Write new Image dimentions into the stored imageif ( ! $_GET['w'] ) $_GET['w'] = 100;
			$prevent_cache		= $config['prevent_cache']; // always generate and deliver new images
			$setup_ratio_arr	= FALSE;
			$img_setup			= array( 
									'w' => false,
									'h' => false
								 );
			$setup_crop			= false;
			$setup_filter		= false;
			$lowres				= FALSE;

			if ( isset( $setup[ $_GET['size'] ]['ratio'] ) ) $setup_ratio_arr  = explode( ':', $setup[ $_GET['size'] ]['ratio'] );

			if ( isset( $_GET['ratio'] ) ) {

				$temp = explode( '-', $_GET['ratio'] );

				if ( count( $temp ) == 2 ) {

					$temp[0] = ( int ) $temp[0];
					$temp[1] = ( int ) $temp[1];

					if ( $temp[0] > 0 && $temp[1] > 0 ) {

						$setup_ratio_arr = $temp;
					}
				}
			}

			if ( isset( $setup[ $_GET['size'] ]['sharpen']['amount'] ) ) {

				$config['sharpen']['amount'] = $setup[ $_GET['size'] ]['sharpen']['amount'];
			}

			if ( isset( $setup[ $_GET['size'] ]['jpg_quality'] ) ) {

				$jpg_quality = $setup[ $_GET['size'] ]['jpg_quality'];
			}

			if ( isset( $setup[ $_GET['size'] ]['jpg_quality_retina'] ) ) {

				$jpg_quality_retina = $setup[ $_GET['size'] ]['jpg_quality_retina'];
			}

			if ( isset( $setup[ $_GET['size'] ]['retina'] ) ) {

				$retina = $setup[ $_GET['size'] ]['retina'];
			}

			if ( isset( $setup[ $_GET['size'] ]['crop'] ) ) {

				$setup_crop = $setup[ $_GET['size'] ]['crop'];
			}

			if ( isset( $setup[ $_GET['size'] ]['filter'] ) ) {

				$setup_filter = $setup[ $_GET['size'] ]['filter'];
			}

			if ( isset( $setup[ $_GET['size'] ]['lowres'] ) ) {

				$lowres = $setup[ $_GET['size'] ]['lowres'];
			}

			foreach ( $setup[ $_GET['size'] ]['resolutions'] as $key => $item ) {

				$images_param[ $key ]['val'] = $item;
			}

		// SCRIPT VARIABLES }

		// IF THERE IS NO COOKIE {

			if ( ! isset( $_COOKIE['resolution'] ) ) {

				$_COOKIE['resolution'] = '9999,1';
			}

		// IF THERE IS NO COOKIE }

		// IF THERE IS AN UNKNOWN SIZE {

			if ( ! $setup[ $_GET['size'] ] ) {

				sendImage( $source_file, $browser_cache );
				die( );
			}

		// IF THERE IS AN UNKNOWN SIZE }

		// CACHE DIRECTORY CHECK {

			// does the $cache_path directory exist already?
			if ( ! is_dir( "$document_root/$cache_path" ) ) { // no

				if ( ! mkdir( "$document_root/$cache_path", 0755, true ) ) { // so make it

					if ( ! is_dir( "$document_root/$cache_path" ) ) { // check again to protect against race conditions

						// uh-oh, failed to make that directory
						sendErrorImage( "Failed to create cache directory at: $document_root/$cache_path" );
					}
				}
			}

		// CACHE DIRECTORY CHECK }

	// PREPARE }

	// FUNCTIONS {

		/* Mobile detection 
		NOTE: only used in the event a cookie isn't available. */
		function is_mobile( ) {

			$userAgent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
			return strpos( $userAgent, 'mobile' );
		}

		/* helper function: Send headers and returns an image. */
		function sendImage( $filename, $browser_cache ) {

			//global $images_param;
			//print_r( $images_param );

			$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

			if ( in_array( $extension, array( 'png', 'gif', 'jpeg' ) ) ) {

				header( "Content-Type: image/" . $extension );
			}
			else {

				header( "Content-Type: image/jpeg" );
			}

			header( "Cache-Control: private, max-age=".$browser_cache );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time( ) + $browser_cache ) . ' GMT' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $filename ) ) . ' GMT' );
            header( 'Content-Length: ' . filesize( $filename ) );
			readfile( $filename );

			exit( );
		}

		/* helper function: Create and send an image with an error message. */
		function sendErrorImage( $message ) {

			/* get all of the required data from the HTTP request */
			$document_root	= $_SERVER['DOCUMENT_ROOT'];
			$requested_uri	= parse_url( urldecode( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
			$requested_file = basename( $requested_uri );
			$source_file	= $document_root . $requested_uri;

			$im			   = ImageCreateTrueColor( 800, 300 );
			$text_color	   = ImageColorAllocate( $im, 233, 14, 91 );
			$message_color = ImageColorAllocate( $im, 91, 112, 233 );

			ImageString( $im, 5, 5, 5, "Adaptive-Images encountered a problem:", $text_color );
			ImageString( $im, 3, 5, 25, $message, $message_color );

			ImageString( $im, 5, 5, 85, "Potentially useful information:", $text_color );
			ImageString( $im, 3, 5, 105, "DOCUMENT ROOT IS: $document_root", $text_color );
			ImageString( $im, 3, 5, 125, "REQUESTED URI WAS: $requested_uri", $text_color );
			ImageString( $im, 3, 5, 145, "REQUESTED FILE WAS: $requested_file", $text_color );
			ImageString( $im, 3, 5, 165, "SOURCE FILE IS: $source_file", $text_color );

			header( "Cache-Control: no-store" );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time( ) - 1000 ) . ' GMT' );
			header( 'Content-Type: image/jpeg' );
			ImageJpeg( $im );
			ImageDestroy( $im );

			exit( );
		}

		/* refreshes the cached image if it's outdated */
		function refreshCache( $source_file, $cache_file, $img_setup ) {

			// prevents caching by config ( $prevent_cache and $debug mode )
			global $debug_modem, $prevent_cache;

			if ( $prevent_cache ) {

				unlink( $cache_file );
			}

			if ( file_exists( $cache_file ) ) {

				// not modified
				if ( filemtime( $cache_file ) >= filemtime( $source_file ) ) {

					return $cache_file;
				}

				// modified, clear it
				unlink( $cache_file );
			}

			return generateImage( $source_file, $cache_file, $img_setup );
		}

		/* generates the given cache file for the given source file with the given resolution */
		function generateImage( $source_file, $cache_file, $img_setup ) {

			global $sharpen, $jpg_quality, $jpg_quality_retina, $setup_ratio_arr, $setup_crop, $setup_filter, $retina, $lowres;

			// CROPING SETUP {

				$crop_behavior = array();

					if ( $setup_crop ) {

					if ( isset( $setup_crop['behavior'] ) ) {

						$crop_behavior = explode( ' ', $setup_crop['behavior'] );
					}
				}

			// }

			// GET IMAGE EXTENSION {

				$extension = strtolower( pathinfo( $source_file, PATHINFO_EXTENSION ) );

			// GET IMAGE EXTENSION }

			// GET IMAGE SIZE {

				$dimensions	  = GetImageSize( $source_file );
				$img_src['w'] = $dimensions[0];
				$img_src['h'] = $dimensions[1];

			// GET IMAGE SIZE }

			// CHANGE IMAGE {

				unset( $temp );

				/* IN

					$img_src['w'] = 300;
					$img_src['h'] = 300;
					$img_setup['w'] = false;
					$img_setup['h'] = false;
					$setup_ratio_arr[0] = false;
					$setup_ratio_arr[1] = false;

				*/

				// RATIO {

					$temp['ratio-changed'] = false;
					$temp['ratio-src'] = $img_src['w'] / $img_src['h'];
					$temp['ratio-new'] = $temp['ratio-src'];

					// IF RATIO SETUP {

						if ( $setup_ratio_arr ) {

							$temp['ratio-new'] = $setup_ratio_arr[0] / $setup_ratio_arr[1];
							$temp['ratio-changed'] = true;
						}

					// IF RATIO SETUP }

					// IF FIXED IMAGE WIDTH AND HEIGHT {

						if ( $img_setup['w'] && $img_setup['h'] ) {

							if ( !in_array( 'none' , $crop_behavior ) ) {

								$temp['ratio-new'] = $img_setup['w'] / $img_setup['h'];
								$temp['ratio-changed'] = true;
							}
						}

					// IF FIXED IMAGE WIDTH AND HEIGHT }

				// RATIO }

				// NEW SIZE {

					// IF SETUP WIDTH {

						if ( $img_setup['w'] ) {

							$img_new['w'] = $img_setup['w'];
							$img_new['h'] = $img_setup['w'] / $temp['ratio-new'];
						}

					// IF SETUP WIDTH }

					// IF SETUP HEIGHT {

						if ( $img_setup['h'] ) {

							$img_new['w'] = $img_setup['h'] * $temp['ratio-new'];
							$img_new['h'] = $img_setup['h'];
						}

					// IF SETUP HEIGHT }

					// CROPPING NONE {

						if ( in_array( 'none' , $crop_behavior ) && $img_setup['w'] && $img_setup['h'] ) {

							$img_setup_ratio = $img_setup['w'] / $img_setup['h'];
							$img_src_ratio = $img_src['w'] / $img_src['h'];

							if ( $img_setup_ratio > $img_src_ratio ) {

								if ( $img_setup['h'] ) {

									$img_new['w'] = $img_setup['h'] * $temp['ratio-src'];
									$img_new['h'] = $img_setup['h'];
								}
							}
							else {

								if ( $img_setup['w'] ) {

									$img_new['w'] = $img_setup['w'];
									$img_new['h'] = $img_setup['w'] / $temp['ratio-src'];
								}
							}
						}
						
					// }

					// LOWRES RULES {

						if ( $lowres == 'preserve-size' && ( $img_src['w'] < $img_new['w'] OR $img_src['h'] < $img_new['h'] ) ) {

							$img_new['w'] = $img_src['w'];
							$img_new['h'] = $img_src['h'];
						}

					// LOWRES RULES }

				// NEW SIZE }

				// SCALED SIZE {

					$temp['scale'] = 1;

					if ( $temp['ratio-src'] > $temp['ratio-new'] ) {

						$temp['scale'] = $img_src['h'] / $img_new['h'];
						$img_scaled['w'] = $img_src['w'] / $temp['scale'];
						$img_scaled['h'] = $img_src['h'] / $temp['scale'];
					}
					else {

						$temp['scale'] = $img_src['w'] / $img_new['w'];
						$img_scaled['w'] = $img_src['w'] / $temp['scale'];
						$img_scaled['h'] = $img_src['h'] / $temp['scale'];
					}

				// SCALED SIZE }

				// OFFSET {

					$img_offset['x'] = 0;
					$img_offset['y'] = 0;

					if ( $temp['ratio-changed'] ) {

						if ( $temp['ratio-src'] < $temp['ratio-new'] ) {

							$img_offset['y'] = ( $img_new['h'] - ( $img_new['w'] / $temp['ratio-src'] ) ) / 2;
						}
						else {

							$img_offset['x'] = ( $img_new['w'] - ( $img_new['h'] * $temp['ratio-src'] ) ) / 2;
						}
					}

				
					if ( in_array( 'top' , $crop_behavior ) ) {

						$img_offset['y'] = 0;
					}

				// OFFSET }

				/* OUT

					$img_new['w']
					$img_new['h']
					$img_scaled['w']
					$img_scaled['h']
					$img_offset['x']
					$img_offset['y']
				*/

				// DEBUG {

					$print_r['temp']		= $temp;
					$print_r['img_src']		= $img_src;
					$print_r['img_setup']	= $img_setup;
					$print_r['img_new']		= $img_new;
					$print_r['img_scaled']	= $img_scaled;
					$print_r['img_offset']	= $img_offset;

					if ( 1 === 0 ) {

						echo '<pre>';
							print_r( $print_r );
						echo '</pre>';
						die( );
					}

				// DEBUG }

			// CHANGE IMAGE }

			// CREATE NEW IMAGE {

				$dst = ImageCreateTrueColor( $img_new['w'], $img_new['h'] ); // re-sized image

			// CREATE NEW IMAGE }

			// GET SOURCE IMAGE {

				switch ( $extension ) {

					case 'png':
					$src = @ImageCreateFromPng( $source_file ); // original image
					break;

					case 'gif':
					$src = @ImageCreateFromGif ( $source_file ); // original image
					break;

					default:
					$src = @ImageCreateFromJpeg( $source_file ); // original image
					ImageInterlace( $dst, true ); // Enable interlancing ( progressive JPG, smaller size file )
					break;
				}

				// PNG ALPHABLENDING

				if ( $extension == 'png' ) {

					imagealphablending( $dst, false );
					imagesavealpha( $dst,true );
					$transparent = imagecolorallocatealpha( $dst, 255, 255, 255, 127 );
					imagefilledrectangle( $dst, 0, 0, $img_new['w'], $img_new['h'], $transparent );
				}

				// FILTER

				if ( is_array( $setup_filter ) ) {

					foreach ( $setup_filter as $key => $value ) {

						if ( $value === true ) {

							imagefilter( $src, $key );
						}
						elseif ( is_array( $value ) ) {

							if ( count( $value ) === 2 ) {

								imagefilter( $src, $key, $value[0], $value[1] );
							}

							if ( count( $value ) === 3 ) {

								imagefilter( $src, $key, $value[0], $value[1], $value[2] );
							}
						}
						else {

							imagefilter( $src, $key, $value );
						}
					}
				}

			// GET SOURCE IMAGE }

			// RESAMPLE IMAGE {

				/* DEBUG WITH ERROR-LOG

					error_log( "\n" . '----------------------------------------------' . "\n"

						. $_GET['size'] . "\n"

						. "img_src: " . print_r( $img_src, true ) . "\n"
						. "img_new: " . print_r( $img_new, true ) . "\n"
						. "img_scaled: " . print_r( $img_scaled, true ) . "\n"
						. "img_offset: " . print_r( $img_offset, true ) . "\n"

					, 0 );

				*/

				// fix black lines on the edge of the image
				$img_scaled['w'] = round( $img_scaled['w'] );
				$img_scaled['h'] = round( $img_scaled['h'] );

				ImageCopyResampled( $dst, $src, $img_offset['x'], $img_offset['y'], 0, 0, $img_scaled['w'], $img_scaled['h'], $img_src['w'], $img_src['h'] ); // do the resize in memory

				ImageDestroy( $src );

			// RESAMPLE IMAGE }

			// DEBUG MODE {

				global $debug_mode, $the_filesize;

				if ( $debug_mode ) {

					$color = imagecolorallocate( $dst, 255, 255, 255 ); // ugly red 
					$cookie_data = explode( ',', $_COOKIE['resolution'] );
					$debug_ratio = '';
					if ( $setup_ratio_arr ) $debug_ratio = ' ' . $setup_ratio_arr[0] . ':' . $setup_ratio_arr[1];

					imagestring( $dst, 5, 10, 5, ceil( $img_new['w'] ) . ' x ' . ceil( $img_new['h'] ) . ' ' . $the_filesize . $debug_ratio, $color ); 
					// device:' . $cookie_data[0] . '*' . $cookie_data[1] . '=' . ceil( $cookie_data[0] * $cookie_data[1] )
				}

			// DEBUG MODE }

			// SHARPEN {

				if ( $sharpen == TRUE ) {

					global $config;

					$amount = $config['sharpen']['amount']; // max 500
					$radius = '1'; // 50
					$threshold = '0'; // max 255

					if ( strtolower( $extension ) == 'jpg' OR strtolower( $extension ) == 'jpeg' ) {

						if ( $amount !== '0' ) {

							$dst = UnsharpMask( $dst, $amount, $radius, $threshold );
						}
					}
				}

			// SHARPEN }

			// CACHE {

				$cache_dir = dirname( $cache_file );

				// does the directory exist already?
				if ( ! is_dir( $cache_dir ) ) { 

					if ( ! mkdir( $cache_dir, 0755, true ) ) {

						// check again if it really doesn't exist to protect against race conditions
						if ( ! is_dir( $cache_dir ) ) {

							// uh-oh, failed to make that directory
							ImageDestroy( $dst );
							sendErrorImage( "Failed to create cache directory: $cache_dir" );
						}
					}
				}

				if ( ! is_writable( $cache_dir ) ) {

					sendErrorImage( "The cache directory is not writable: $cache_dir" );
				}

				// save the new file in the appropriate path, and send a version to the browser
				switch ( $extension ) {

					case 'png':
						$gotSaved = ImagePng( $dst, $cache_file, 9, PNG_FILTER_NONE );
						break;
					case 'gif':
						$gotSaved = ImageGif ( $dst, $cache_file );
						break;
					default:
						$gotSaved = ImageJpeg( $dst, $cache_file, $jpg_quality );
						break;
				}
				ImageDestroy( $dst );

				if ( ! $gotSaved && ! file_exists( $cache_file ) ) {

					sendErrorImage( "Failed to create image: $cache_file" );
				}

			// CACHE }

			return $cache_file;

		}

		/* sharpen image */
		function UnsharpMask( $img, $amount, $radius, $threshold ) {

			/*
				New:  
				- In version 2.1 ( February 26 2007 ) Tom Bishop has done some important speed enhancements. 
				- From version 2 ( July 17 2006 ) the script uses the imageconvolution function in PHP	
				version >= 5.1, which improves the performance considerably. 

				Unsharp masking is a traditional darkroom technique that has proven very suitable for  
				digital imaging. The principle of unsharp masking is to create a blurred copy of the image 
				and compare it to the underlying original. The difference in colour values 
				between the two images is greatest for the pixels near sharp edges. When this  
				difference is subtracted from the original image, the edges will be 
				accentuated.  

				The Amount parameter simply says how much of the effect you want. 100 is 'normal'. 
				Radius is the radius of the blurring circle of the mask. 'Threshold' is the least 
				difference in colour values that is allowed between the original and the mask. In practice 
				this means that low-contrast areas of the picture are left unrendered whereas edges 
				are treated normally. This is good for pictures of e.g. skin or blue skies. 

				Any suggenstions for improvement of the algorithm, expecially regarding the speed 
				and the roundoff errors in the Gaussian blur process, are welcome. 

			*/

			////////////////////////////////////////////////////////////////////////////////////////////////   
			////   
			////				  Unsharp Mask for PHP - version 2.1.1
			////   
			////	Unsharp mask algorithm by Torstein Hønsi 2003-07.
			////			 thoensi_at_netcom_dot_no.
			////			   Please leave this notice.
			////   
			///////////////////////////////////////////////////////////////////////////////////////////////	  

			// $img is an image that is already created within php using
			// imgcreatetruecolor. No url! $img must be a truecolor image.

			// Attempt to calibrate the parameters to Photoshop:
			if ( $amount > 500 ) {

				$amount = 500;
			}

			$amount = $amount * 0.016;

			if ( $radius > 50 ) {

				$radius = 50;
			}

			$radius = $radius * 2;

			if ( $threshold > 255 ) {

				$threshold = 255;
			}	

			$radius = abs( round( $radius ) );	   // Only integers make sense.

			if ( $radius == 0 ) {

				return $img; imagedestroy( $img ); break;
			}  

			$w = imagesx( $img );
			$h = imagesy( $img );
			$imgCanvas = imagecreatetruecolor( $w, $h );
			$imgBlur = imagecreatetruecolor( $w, $h );

			// Gaussian blur matrix:  
			//							
			//	  1	   2	1		   
			//	  2	   4	2		   
			//	  1	   2	1		   
			//							
			//////////////////////////////////////////////////	

			if ( function_exists( 'imageconvolution' ) ) { // PHP >= 5.1   

				$matrix = array(   
					array( 1, 2, 1 ),	
					array( 2, 4, 2 ),	
					array( 1, 2, 1 )   
				 ); 

				imagecopy ( $imgBlur, $img, 0, 0, 0, 0, $w, $h );	 
				imageconvolution( $imgBlur, $matrix, 16, 0 );	  
			}	
			else {	 

				// Move copies of the image around one pixel at the time and merge them with weight	 
				// according to the matrix. The same matrix is simply repeated for higher radii.  
				for ( $i = 0; $i < $radius; $i++ ) {	

					imagecopy ( $imgBlur, $img, 0, 0, 1, 0, $w - 1, $h ); // left	 
					imagecopymerge ( $imgBlur, $img, 1, 0, 0, 0, $w, $h, 50 ); // right	 
					imagecopymerge ( $imgBlur, $img, 0, 0, 0, 0, $w, $h, 50 ); // center	
					imagecopy ( $imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h );	 

					imagecopymerge ( $imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up	 
					imagecopymerge ( $imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25 ); // down	
				}  
			}  

			if ( $threshold > 0 ) {	 

				// Calculate the difference between the blurred pixels and the original	 
				// and set the pixels  
				for ( $x = 0; $x < $w-1; $x++ )	 { // each row 

					for ( $y = 0; $y < $h; $y++ )	   { // each pixel	

						$rgbOrig = ImageColorAt( $img, $x, $y );	
						$rOrig = ( ( $rgbOrig >> 16 ) & 0xFF );	 
						$gOrig = ( ( $rgbOrig >> 8 ) & 0xFF );	
						$bOrig = ( $rgbOrig & 0xFF );	 

						$rgbBlur = ImageColorAt( $imgBlur, $x, $y );	

						$rBlur = ( ( $rgbBlur >> 16 ) & 0xFF );	 
						$gBlur = ( ( $rgbBlur >> 8 ) & 0xFF );	
						$bBlur = ( $rgbBlur & 0xFF );	 

						// When the masked pixels differ less from the original	 
						// than the threshold specifies, they are set to their original value.	
						$rNew = ( abs( $rOrig - $rBlur ) >= $threshold )   
						? max( 0, min( 255, ( $amount * ( $rOrig - $rBlur ) ) + $rOrig ) )	 
						: $rOrig;  
						$gNew = ( abs( $gOrig - $gBlur ) >= $threshold )   
						? max( 0, min( 255, ( $amount * ( $gOrig - $gBlur ) ) + $gOrig ) )	 
						: $gOrig;  
						$bNew = ( abs( $bOrig - $bBlur ) >= $threshold )   
						? max( 0, min( 255, ( $amount * ( $bOrig - $bBlur ) ) + $bOrig ) )	 
						: $bOrig;  

						if ( ( $rOrig != $rNew ) || ( $gOrig != $gNew ) || ( $bOrig != $bNew ) ) {	

							$pixCol = ImageColorAllocate( $img, $rNew, $gNew, $bNew );	
							ImageSetPixel( $img, $x, $y, $pixCol );	 
						}  
					}  
				}  
			}  
			else {	

				for ( $x = 0; $x < $w; $x++ ) { // each row	 

					for ( $y = 0; $y < $h; $y++ ) { // each pixel

						$rgbOrig = ImageColorAt( $img, $x, $y );	
						$rOrig = ( ( $rgbOrig >> 16 ) & 0xFF );	 
						$gOrig = ( ( $rgbOrig >> 8 ) & 0xFF );	
						$bOrig = ( $rgbOrig & 0xFF );	 

						$rgbBlur = ImageColorAt( $imgBlur, $x, $y );	

						$rBlur = ( ( $rgbBlur >> 16 ) & 0xFF );	 
						$gBlur = ( ( $rgbBlur >> 8 ) & 0xFF );	
						$bBlur = ( $rgbBlur & 0xFF );	 

						$rNew = ( $amount * ( $rOrig - $rBlur ) ) + $rOrig;	 

						if ( $rNew > 255 ) {

							$rNew = 255;
						}	
						elseif ( $rNew < 0 ) {

							$rNew = 0;
						}

						$gNew = ( $amount * ( $gOrig - $gBlur ) ) + $gOrig;	 

						if ( $gNew > 255 ) {

							$gNew = 255;
						}	
						elseif ( $gNew < 0 ) {

							$gNew = 0;
						}	

						$bNew = ( $amount * ( $bOrig - $bBlur ) ) + $bOrig;	 

						if ( $bNew>255 ) {

							$bNew = 255;
						}	
						elseif ( $bNew<0 ) {

							$bNew=0;
						}

						$rgbNew = ( $rNew << 16 ) + ( $gNew <<8 ) + $bNew;	
						ImageSetPixel( $img, $x, $y, $rgbNew );	 
					}  
				}  
			}  
			imagedestroy( $imgCanvas );	 
			imagedestroy( $imgBlur );	 

			return $img;
		}
		
	// FUNCTIONS }

	// PROCEDURE {

		// check if the file exists at all
		if ( ! file_exists( $source_file ) ) {

			header( "Status: 404 Not Found" );
			exit( );
		}

		/* check that PHP has the GD library available to use for image re-sizing */
		if ( ! extension_loaded( 'gd' ) ) { // it's not loaded

			if ( ! function_exists( 'dl' ) || ! dl( 'gd.so' ) ) { // and we can't load it either

				// no GD available, so deliver the image straight up
				trigger_error( 'You must enable the GD extension to make use of Adaptive-Images', E_USER_WARNING );
				sendImage( $source_file, $browser_cache );
			}
		}

		/* Check to see if a valid cookie exists */

		if ( isset( $_COOKIE['resolution'] ) ) {

			$cookie_value = $_COOKIE['resolution'];

			// does the cookie look valid? [whole number, comma, potential floating number]
			if ( ! preg_match( "/^[0-9]+[,]*[0-9\.]+$/", "$cookie_value" ) ) { // no it doesn't look valid

				setcookie( "resolution", "$cookie_value", time( )-100 ); // delete the mangled cookie
			}
			else {

				// the cookie is valid, do stuff with it
				$cookie_data   = explode( ",", $_COOKIE['resolution'] );
				$client_width  = ( int ) $cookie_data[0]; // the base resolution ( CSS pixels )
				$total_width   = $client_width;
				$pixel_density = 1; // set a default, used for non-retina style JS snippet

				if ( @$cookie_data[1] ) { // the device's pixel density factor ( physical pixels per CSS pixel )
					$pixel_density = $cookie_data[1];
				}

				if ( ! $retina ) $pixel_density = 1;
				if ( $highresmode ) $pixel_density = 2;

				if ( $pixel_density >= 2 ) $jpg_quality = $jpg_quality_retina;

				$current_breackpoint = 'undefinded';

				$img_setup['w'] = false;
				$img_setup['h'] = false;

				foreach ( $images_param as $key => $item ) {

					if ( is_numeric( $key ) ) {

						$width = $key;
					}
					else {

						$width = $resolutions[ $key];
					}

					if ( $width <= $total_width ) {

						$current_breackpoint = $width;

						if ( ! $img_setup['w'] || ( $item['val']['w'] * $pixel_density ) > $img_setup['w'] ) {

							if ( isset( $item['val']['w'] ) ) {

								$img_setup['w'] = ceil( $item['val']['w'] * $pixel_density );
							}

							if ( isset( $item['val']['h'] ) ) {

								$img_setup['h'] = ceil( $item['val']['h'] * $pixel_density );
							}
						}
					}
				}
			}
		}

		/* if the requested URL starts with a slash, remove the slash */
		if ( substr( $requested_uri, 0,1 ) == '/' ) {

			$requested_uri = substr( $requested_uri, 1 );
		}

		// IMAGE CACHE PATCH {

			// THE SLUG {

				$cache_slug = '';

				$cache_slug .= '_d' . $pixel_density;

				$cache_slug .= '_w' . str_pad( $img_setup['w'], 4, '0', STR_PAD_LEFT );
				$cache_slug .= '_h' . str_pad( $img_setup['h'], 4, '0', STR_PAD_LEFT );

				if ( $setup_ratio_arr ) {

					$cache_slug .= '_r' . $setup_ratio_arr[0] . '-' . $setup_ratio_arr[1];
				}

			// THE SLUG }

			$cache_path_rel = '/' . $cache_path . '/' . $_GET['size'] . '/s' . str_pad( $current_breackpoint, 4, '0', STR_PAD_LEFT ) . '_' . trim( $cache_slug, '_' ) . '/' . $requested_uri;

			$cache_file = $document_root . $cache_path_rel;

		// IMAGE CACHE PATCH }

		$the_filesize = '';

		/* Use the resolution value as a path variable and check to see if an image of the same name exists at that path */
		if ( file_exists( $cache_file ) ) { // it exists cached at that size

			// FILESIZE {

				$cache_file_url = '..' . $cache_path_rel;
				$the_filesize = filesize( $cache_file_url );
				$arr_units = array( 
					'B',
					'KB',
					'MB',
					'GB',
					'TB'
				 );

				for ( $i = 0; $the_filesize > 1024; $i++ ) {

					$the_filesize /= 1024;
				}

				$the_filesize = number_format( $the_filesize, 2, ',', '' ).' '.$arr_units[ $i ];

			// FILESIZE }

			if ( $watch_cache ) { // if cache watching is enabled, compare cache and source modified dates to ensure the cache isn't stale

				$cache_file = refreshCache( $source_file, $cache_file, $img_setup );
			}

			sendImage( $cache_file, $browser_cache );
		}

		/* It exists as a source file, and it doesn't exist cached - lets make one: */
		$file = generateImage( $source_file, $cache_file, $img_setup );

		sendImage( $file, $browser_cache );

	// PROCEDURE }
