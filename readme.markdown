Adaptive-Images for WordPress
=============================
This is not just the WordPress version of the original Adaptive-Images by Mat Wilcox. This is an extended version with which you can configure individually the responsive behavior of each image in the layout.

theme/config/adaptive-images-config.php
```php
$setup = array(
  'banner' => array(
    'resolutions' => array(
      '0' => array(
        'w' => 480 // the image will have a physical width of 480px on screens with less then 480px width
      ),
      '480' => array(
        'w' => 768 // the image will have a physical width of 768px on screens with minimum 480px and less then 768p width
      ),
      '768' => array(
        'w' => 1024
      ),
      '1024' => array(
        'w' => 1200
       )
    )
   )
);
```
theme/page.php – or any other presentation file
```php
echo get_adaptive_image( array(
  'name' => 'banner', 
  'id' => {image-ID}, 
) );
```
Reqirements
-----------
* requires a current version of WordPress
* works on Multisites
* intended for use together width plugins like "Advanced-Custom-Fields"

Limitations
-----------
* may also works with inserting images in the wysiwyg-editor for now but not recommended

Dependencies
------------
* Browser with Javascript enabled and exepting Cookies
  JavaScript is needed for detecting the screens height and width. A Cookie is needed for temporarily storing the screens height and width and making this data aviable in the adaptive-images script. There will be a fallback serving fix defined image sizes if Javascript and/or Cookies are disabled.

    



