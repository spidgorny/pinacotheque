<?php

class ImgProxy extends AppController
{

	protected $thumbsPath;

	public function __construct($thumbsPath)
	{
		$this->thumbsPath = $thumbsPath;
	}

	public function __invoke()
	{
		$request = Request::getInstance();
		$path = $request->getTrim('path');
		$path = $thumbsPath . '/' . $path;
//		debug($path, file_exists($path));
		if (file_exists($path)) {
			$ext = pathinfo($path, PATHINFO_EXTENSION);
			if (in_array($ext, $this->validImages)) {
				header('Content-Type: image/jpeg'); // browser will handle the differences
				readfile($path);
				exit;
			} else {
				$gd = imagecreate(256, 256);
				$foreground = imagecolorallocate($gd, 255, 255, 255);
				$background = imagecolorallocate($gd, 0, 0, 0);
				imagepstext($gd, $path, 1, 16, $foreground, $background, 0, 0);
				header('Content-Type: image/png');
				imagepng($gd);
				exit;
			}
		}
	}

}
