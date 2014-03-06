<?php

	/* configurate the domains für develepment and produktion mode */

	function config_data() {

		$config['domains'][ $_SERVER["HTTP_HOST"] ] = 'live';
		$config['domains']['test.domain.com'] = 'test'; // @TODO: replace with your test domain like 'test.domain.com'

		$config['wordpressfolder'] = '/'; // @TODO: if you use wordpress inside a folder change it like '/wordpress/'
		
		return $config;
	}

	/* please do not change the code below */

	function config_get_curr_blog_id () {

		 global $current_blog;

		 if ( isset( $current_blog ) ) {

			 return $current_blog->blog_id;
		 }
		 else {

			 return 1;
		 }
	}

	function config_site_is_type( $type ) {

		$config = config_data();

		if ( isset( $config['domains'][ $_SERVER["HTTP_HOST"] ] ) && $config['domains'][ $_SERVER["HTTP_HOST"] ] == $type ) {

			return true;
		}
		else {

			return false;
		}
	}

?>
