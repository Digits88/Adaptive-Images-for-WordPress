<?php
    
    include( $themepath . 'config/config.php');
                                    
    $config['cache_path']           = 'adaptive-images-cache'; 
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
    
    $config['breakpoints'] = array(
        'mobile' => 0, 
        'medium' => 1000,
        'large' => 1300
    );
    
    /* example
        
        // defining the image-width relating to breakpoints
    
            // by value:
            $setup['name']['breakpoints'][0]['w'] = 300;
            $setup['name']['breakpoints'][1000]['w'] = 600;
    
            // by $config['breakpoints'] names:
            $setup['name']['breakpoints']['mobile']['w'] = 300;
            $setup['name']['breakpoints']['medium']['w'] = 600;
    
        // you can also define a height only
    
            $setup['name']['breakpoints']['large']['h'] = 400;
    
        // you can also define width and height
        
            $setup['name']['breakpoints']['large']['w'] = 400;
            $setup['name']['breakpoints']['large']['h'] = 400;
    
        // you can also define a aspect-ratio depending on a width or height
    
            $setup['name']['ratio'] = '2:1';
    
        // defining the jpeg-quality
    
            $setup['name']['jpg_quality'] = 95;
            $setup['name']['jpg_quality_retina'] = 40;
    
        // defining the amount of sharpening
    
            $setup['name']['sharpen']['amount'] = 40;
     */
?>