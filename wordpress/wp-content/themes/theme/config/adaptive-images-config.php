<?php
    
    include( $themepath . 'config/config.php');
                                    
    $config['cache_path']           = 'ai-cache'; 
    $config['jpg_quality']          = 95; // 100 to 0
    $config['jpg_quality_retina']   = 50; // 100 to 0
    $config['watch_cache']          = TRUE;
    $config['browser_cache']        = 60 * 60 * 24; // period of time in second, the images will stay in cache of browsers
    $config['prevent_cache']        = FALSE; // images will resized on every image request
    $config['debug_mode']           = FALSE; // insert the image dimensions, the ratio and the device-width into the image
    $config['sharpen']['status']    = TRUE; // enables sharpen
    $config['sharpen']['amount']    = 20; // 0 is none, 30 is pleasant, max is 500
    
    if ( config_site_is_type( 'test' ) ) {
    
        $config['browser_cache']    = 1; // period of time in second, the images will stay in cache of browsers
        $config['prevent_cache']    = TRUE; // images will resized on every image request
        $config['debug_mode']       = TRUE; // insert the image dimensions, the filesize and the ratio
    }
    
    $config['resolutions'] = array(
        'mobile' => 0, 
        'medium' => 1000,
        'large' => 1300
    );
    
    /* Documentation
        
        Defining the image-width relating to resolution
    
            // resolution by value:
            $setup['name']['resolutions'][0]['w'] = 300;
            $setup['name']['resolutions'][1000]['w'] = 600;
    
            // resolution by $config['resolutions'] names:
            $setup['name']['resolutions']['mobile']['w'] = 300;
            $setup['name']['resolutions']['medium']['w'] = 600;
    
    
        We can also define a height only
    
            $setup['name']['resolutions']['large']['h'] = 400;
    
    
        We can also define width and height
        
            $setup['name']['resolutions']['large']['w'] = 400;
            $setup['name']['resolutions']['large']['h'] = 400;
    
    
        We can also define a aspect-ratio depending on a width or height
    
            $setup['name']['ratio'] = '2:1';
    
    
        Defining the jpeg-quality
    
            $setup['name']['jpg_quality'] = 95;
            $setup['name']['jpg_quality_retina'] = 40;
    
    
        Defining the amount of sharpening
    
            $setup['name']['sharpen']['amount'] = 40;
            
            
        Disabling retina
        
            We can disable the retina because in some cases, the image will be larger 
            then the max size that a device can handle.
            http://www.williammalone.com/articles/html5-javascript-ios-maximum-image-size/
        
            $setup['name']['retina'] = false;
            
            
        Image filters
        
            Now we can add multiple php image filters.
            http://php.net/manual/en/function.imagefilter.php
            http://www.phpied.com/image-fun-with-php-part-2
            
            $setup['name']['filter'][IMG_FILTER_GRAYSCALE] = true;
            $setup['name']['filter'][IMG_FILTER_COLORIZE] = array(0, 0, 100);
            
            
        Cropping Rules

            Now we can change the way cropping behaves.
            Default value is false and causes centered cropping (center center)
            "top" causes (top center) and is good for website screenshots
            More cropping options will follow.
            
            $setup['name']['crop'] = 'top';
            
     */
?>