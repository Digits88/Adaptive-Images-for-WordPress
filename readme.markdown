Adaptive-Images for WordPress
=============================

Handle different image dimensions, aspect ratios (automatic cropping) and jpeg compression in relation to images, screen-resolutions and screen-densities.

* requires WordPress 3.5
* works on Multisites
* intended for use with content based on plugins like "advanced-custom-fields"
* works also with inserting images in the editor for now (not recommended)
* just highresolution images upload needed
* define imagesets with defined screen-resolutions and their image sizes
* function get_adaptive_image() returns HTML based on parameters like image-ID, name of the imageset and much more

Dependencies
----------------

* Browser with Javascript on and exepting Cookies
  JavaScript is needed for detecting the screens height and width. A Cookie is needed for temporarily storing the screens height and width and making this data aviable in the adaptive-images script. There will be a fallback serving fix defined image sizes if Javascript and/or Cookies are disabled.
* requires WordPress 3.5 or higher

  sgedrgherhg


