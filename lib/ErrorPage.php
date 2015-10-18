<?php

namespace Socrate;

use Pimple\Container;
use Exception;

/**
 * Display and error page and send the HTTP status code
 */
class ErrorPage
{

	/**
	 * @container 	Pimple\Container 	A Pimple\Container instance
	 * @statusCode	int					A valid HTTP status code
	 * @error 		Exception 			A PHP Exception
	 */	
	function __construct(Container $container, $statusCode, Exception $error = null) 
	{

		$this->container = $container;
		$this->statusCode = $statusCode;
		$this->error = $error;
	
		// Set the status code
		header('x-error: 1', true, $this->statusCode);

		// Clean the buffer and start a new one
		while (ob_get_level()) ob_end_clean();
		ob_start();

		// Render the response
		$view = 'views/errors/' . (int) $statusCode . '.php';

		// Is there a user defined view for the status code?
		if (stream_resolve_include_path($view)) {
			die(require $view);

		} else {
			// Rendere a default error view
			die(require __DIR__.'/views/errors/generic_error.php');
		}

	}
}