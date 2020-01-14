<?php

class HelloWorld extends AppController
{

	protected $html;

	public function __construct()
	{
		parent::__construct();
		$this->html = new HTML();
	}

	public function __invoke()
	{
		$sourceURL = 'test/ThomasGasson/0oQZbcD.jpg';
		$source = __DIR__ . '/../../' . $sourceURL;
//		$content[] = $this->html->p('Source: ' . $source);
		[$width, $height] = getimagesize($source);
		$content[] = $this->html->img($sourceURL, ['width' => 256]).
			'<div>[' . $width . 'x' . $height . ']</div>';

		$content[] = $this->getPlaceholder(256, 256);

		$content[] = $this->getPlaceholder($width, $height, $sourceURL);

		$sourceURL2 = 'test/exif-samples/jpg/Fujifilm_FinePix_E500.jpg';
		[$width, $height] = getimagesize($sourceURL2);
		$content[] = $this->getPlaceholder($width, $height, $sourceURL2);

		$content = '<table><tr><td>'.implode('</td><td>', $content).'</td></tr></table>';

		return $this->template($content, [], __DIR__ . '/../../template/blank.phtml');
	}

	public function getPlaceholder($width, $height, $imgSrc = null)
	{
		$aspectRatio = $width / $height;
		if ($aspectRatio >= 1) {
			$width = 256;
			$height = 256 / $aspectRatio;
		} else {
			$width = 256 * $aspectRatio;
			$height = 256;
		}
		$img = '<div style="
			background: #f0f0f0;
			border: solid 1px silver; 
			width: ' . $width . 'px; 
			height: ' . $height . 'px;
			object-fit: contain;
			background: lightblue;
			"></div>';
		if ($imgSrc) {
			$img = '<img src="' . $imgSrc . '" style="
				width: '.$width.'px; 
				height: ' . $height . 'px; 
				object-fit: contain;
				background-color: pink;
				margin: auto; 
			" title="'.$aspectRatio.'"/>';
		}
		return '<div style="width: 256px; height: 256px; overflow: hidden; background: #eee">
		'.$img.'
		</div>'
			. '[' . $width . 'x' . $height . ']';
	}

}
