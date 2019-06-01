<?php

class ImgProxy extends AppController
{

	protected $dataPath;

	public static function href2img(Meta $meta)
	{
		// ImgProxy?path= /Volume/photos & SomeFolder/SomeFile.jpg
		$url = ImgProxy::href([
			'path' => $meta->_path_,
			'file' => $meta->getFilename(),
		]);
		return $url;
	}

	/**
	 * ImgProxy constructor.
	 * @param $dataPath
	 */
	public function __construct($dataPath)
	{
		$this->dataPath = $dataPath;
	}

	public function __invoke()
	{
		$request = Request::getInstance();
//		debug($_REQUEST);
		$path = $request->getTrim('path');
		$path = str_replace('__', ':/', $path);

		$pathParts = trimExplode('/', $path);
		$sourceBase = first($pathParts);
		$sourceJson = path_plus($this->dataPath, $sourceBase, 'source.json');
		//debug($sourceBase, $sourceJson);

		if (is_file($sourceJson)) {
			$text = file_get_contents($sourceJson);
			$json = json_decode($text);
			$sourcePath = $json->source;
			// $sourceBase is already in the $sourcePath
			$path = path_plus($sourcePath, implode('/', array_slice($pathParts, 1)));
		} else {
			throw new \League\Flysystem\FileNotFoundException($sourceJson);
		}

		$filename = $request->getTrim('file');
		$path .= '/' . $filename; // without decoding
		// this is a link to the original file not a thumbnail
		//$path = $this->thumbsPath . '/' . $path;
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
		} else {
			debug($path);
			$newPath = [];
			$pathParts = trimExplode('/', $path);
			foreach ($pathParts as $plus) {
				$newPath[] = $plus;
				$strPath = '/' . implode('/', $newPath);
				$isDir = is_dir($strPath);
				$isFile = is_file($strPath);
				debug($strPath, $isDir, $isFile, $isFile ? @filesize($strPath) : null, glob($strPath . '/*', GLOB_ONLYDIR));
				if (!$isDir && !$isFile) {
					break;
				}
			}
		}
	}

}
