<?php

class FetchImgur
{

	protected $context;

	function __construct($context)
	{
		$this->context = $context;
	}

	function __invoke()
	{
		$set = json_decode(file_get_contents(__DIR__ . '/../data/imgur.json'));
		foreach ($set->album_images->images as $i) {
			$url = 'https://i.imgur.com/' . $i->hash . $i->ext;
			echo $url, PHP_EOL;
			$destination = __DIR__ . '/../data/ThomasGasson/' . $i->hash . $i->ext;
			if (file_exists($destination)) {
				continue;
			}
			file_put_contents($destination,
				file_get_contents($url, false, $this->context));
		}
	}

}
