<?php

class ApiController extends AppController
{

	public function __invoke()
	{
		try {
			$content = parent::__invoke();
			return $content;
		} catch (Exception $e) {
			if (function_exists('http_send_status')) {
				http_send_status(500);
			} else {
				header("HTTP/1.0 500 Error");
			}
			return new JSONResponse([
				'status' => get_class($e),
				'error' => $e->getMessage(),
				'trace' => $e->getTrace(),
			]);
		}
	}

}
