<?php

namespace Socrate;

class StaticPage
{

	function __construct()
	{

		set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

		$path = 'views/static_pages/' . $_GET['slug'] . '.php';

		if (!stream_resolve_include_path($path)) {
			abort(404);
		}
		
		require $path;
	}

}