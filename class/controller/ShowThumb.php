<?php

class ShowThumb extends AppController
{

	protected $transparent1px = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

	public function __invoke()
	{
		$file = Request::getInstance()->getTrim('file');
		if (!$file) {
			header('Content-Type: image/png');
			return base64_decode($this->transparent1px);
		}

		return $file;
	}

}
