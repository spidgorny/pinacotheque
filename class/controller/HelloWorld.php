<?php

class HelloWorld extends AppController
{

	protected $html;

	protected $fileRoot = __DIR__ . '/../../test/ThomasGasson';

	protected $files = [];

	public function __construct()
	{
		parent::__construct();
		$this->html = new HTML();
		$files = scandir($this->fileRoot);
		$this->files = array_filter($files, static function ($file) {
			return $file[0] !== '.';
		});
		$this->files = array_slice($this->files, 0, 24);
	}

	public function __invoke()
	{
		$sourceURL = 'test/ThomasGasson/0oQZbcD.jpg';
//		$row[] = $this->html->p('Source: ' . $source);
		$source = __DIR__ . '/../../' . $sourceURL;
		$content[] = $this->renderAspectRatio($sourceURL, $source);
		$sourceURL2 = 'test/exif-samples/jpg/Fujifilm_FinePix_E500.jpg';
		$content[] = $this->renderAspectRatio($sourceURL2, __DIR__ . '/../../' . $sourceURL2);

		$content[] = $this->renderImagePlaceholders($this->files);

		return $this->template($content, [], __DIR__ . '/../../template/blank.phtml');
	}

	public function renderAspectRatio($sourceURL, $source): array
	{
		[$width, $height] = getimagesize($source);
		$row[] = $this->html->img($sourceURL, ['width' => 256]) .
			'<div>[' . $width . 'x' . $height . ']</div>';

		$ph = new Placeholder(256, 256);
		$row[] = $ph->getPlaceholder();

		$ip = ImageParser::fromFile($source);
		$cornerColors = $ip->getCornerColors();
		$gradient = array_map(static function ($color) {
			return Color::fromRGBArray($color)->getCSS();
		}, $cornerColors);
		$ph = new Placeholder($width, $height);
		$row[] = $ph->getPlaceholder(null, $gradient);

		$ip = ImageParser::fromFile($source);
		$cornerColors = $ip->getQuadrantColors();
		$gradient = array_map(static function ($color) {
			return Color::fromRGBArray($color)->getCSS();
		}, $cornerColors);
		$ph = new Placeholder($width, $height);
		$row[] = $ph->getPlaceholder(null, $gradient);

		$ph = new Placeholder($width, $height);
		$row[] = $ph->getPlaceholder($sourceURL);

		$row[] = $this->html->pre(json_encode($cornerColors, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$colorDivs = array_map(static function ($color) {
			return HTMLTag::div('', [
				'style' => [
					'display' => 'inline-block',
					'width' => '32px',
					'height' => '32px',
					'background' => (new Color(array_slice($color, 0, 3)))->getCSS(),
				]]);
		}, $cornerColors);
		$row[] = $this->html->div(implode(' ', $colorDivs), '', ['style' => 'width: 70px']);

		$content[] = '<table><tr><td>' . implode('</td><td>', $row) . '</td></tr></table>';
		return $content;
	}

	public function renderImagePlaceholders(array $files): array
	{
		$content = [];
		foreach ($files as $file) {
			$source = path_plus($this->fileRoot, $file);
			[$width, $height] = getimagesize($source);
			$sourceURL = 'test/ThomasGasson/' . $file;

			$ip = ImageParser::fromFile($source);
			$corners = $ip->getCornerColors();
			$gradient = array_map(static function ($color) {
				return Color::fromRGBArray($color)->getCSS();
			}, $corners);

			$ph = new Placeholder($width, $height);
			$placeholder = $ph->getPlaceholder(null, $gradient);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);

			$ph = new Placeholder($width, $height);
			$placeholder = $ph->getPlaceholder($sourceURL);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);
		}
		return $content;
	}

}
