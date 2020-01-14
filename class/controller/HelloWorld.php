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
		$this->files = array_slice($this->files, 0, 100);
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

	public function renderAspectRatio($sourceURL, $source)
	{
		[$width, $height] = getimagesize($source);
		$row[] = $this->html->img($sourceURL, ['width' => 256]) .
			'<div>[' . $width . 'x' . $height . ']</div>';

		$row[] = $this->getPlaceholder(256, 256);

		$row[] = $this->getPlaceholder($width, $height, $sourceURL);

		$cornerColors = $this->getCornerColors($source);
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

	public function getPlaceholder($width, $height, $imgSrc = null, $gradient = [])
	{
		$aspectRatio = $width / $height;
		if ($aspectRatio >= 1) {
			$width = 256;
			$height = 256 / $aspectRatio;
		} else {
			$width = 256 * $aspectRatio;
			$height = 256;
		}

		$img = '';
		if ($imgSrc) {
			$img = '<img src="' . $imgSrc . '" style="
				width: ' . $width . 'px; 
				height: ' . $height . 'px; 
				object-fit: contain;
				background-color: pink;
				margin: auto; 
			" title="' . $aspectRatio . '"/>';
		}

		$id = uniqid('div', false);
		$img = '<div id="'.$id.'" style="
			border: solid 1px silver; 
			width: ' . $width . 'px; 
			height: ' . $height . 'px;
			object-fit: contain;
			/*background: lightblue;*/
			">'.$img.'</div>';
		if ($gradient) {
			$img .= '
			<style>
			#' . $id . ' {
				width: 256px; 
				height: 256px;
				/* left (top => bottom) */
				background: linear-gradient(to bottom, '.$gradient[00].', '.$gradient[10].');
			}
				
			#' . $id . '::after {
				content: "";
				position: absolute;
				width: inherit;
				height: inherit;
				/* top-right => bottom-right */
				background: linear-gradient(to bottom, '.$gradient[01].', '.$gradient[11].');				
				/* keep right, transparent left */
				-webkit-mask-image: linear-gradient(to left, '.$gradient[11].', transparent);	
			}
			</style>';
		}
		return '<div style="width: 256px; height: 256px; overflow: hidden; background: #eee">
		' . $img . '
		</div>'	. '[' . $width . 'x' . $height . ']';
	}

	public function getCornerColors($imageFile)
	{
		$manager = new \Intervention\Image\ImageManager();
		$image = $manager->make($imageFile);
		$colors = [];
		$colors[00] = $image->pickColor(0, 0);
		$colors[01] = $image->pickColor($image->width() - 1, 0);
		$colors[10] = $image->pickColor(0, $image->height() - 1);
		$colors[11] = $image->pickColor($image->width() - 1, $image->height() - 1);
		foreach ($colors as &$color) {
			unset($color[3]);    // alpha
		}
		return $colors;
	}

	public function renderImagePlaceholders(array $files)
	{
		$content = [];
		foreach ($files as $file) {
			$source = path_plus($this->fileRoot, $file);
			[$width, $height] = getimagesize($source);
			$sourceURL = 'test/ThomasGasson/' . $file;

			$corners = $this->getCornerColors($source);
			$gradient = array_map(static function ($color) {
				return Color::fromRGBArray($color)->getCSS();
			}, $corners);
			$placeholder = $this->getPlaceholder($width, $height, null, $gradient);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);

			$placeholder = $this->getPlaceholder($width, $height, $sourceURL);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);
		}
		return $content;
	}

}
