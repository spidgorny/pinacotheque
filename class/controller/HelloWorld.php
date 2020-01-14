<?php

class HelloWorld extends AppController
{

	protected $html;

//	protected $fileRoot = __DIR__ . '/../../test/ThomasGasson';
	protected $fileRoot = __DIR__ . '/../../test/Stefan';

	protected $files = [];

	/** @var DBInterface */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		$this->html = new HTML();

//		$files = scandir($this->fileRoot);
//		$this->files = array_filter($files, static function ($file) {
//			return $file[0] !== '.';
//		});
//		$this->files = array_slice($this->files, 0, 24);

		$source = Source::findByID($this->db, 1);
		$this->files = $source->getFiles([
			'type' => 'file',
		], 'ORDER BY rand() LIMIT 10');
	}

	public function __invoke()
	{
//		$sourceURL = 'test/ThomasGasson/0oQZbcD.jpg';
		$sourceURL = 'test/Stefan/IMG_5473.JPG';
//		$row[] = $this->html->p('Source: ' . $source);
		$source = __DIR__ . '/../../' . $sourceURL;
		$content[] = $this->renderAspectRatio($sourceURL, $source);

//		$sourceURL2 = 'test/exif-samples/jpg/Fujifilm_FinePix_E500.jpg';
//		$content[] = $this->renderAspectRatio($sourceURL2, __DIR__ . '/../../' . $sourceURL2);

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
		$gradient = $ip->getCornerColorsAsHex();
		$ph = new Placeholder($width, $height);
		$row[] = $ph->getPlaceholder(null, $gradient);

		// too slow
//		$ip = ImageParser::fromFile($source);
//		$gradient = $ip->getQuadrantColorsAsHex();
		$gradientQ = [];
		$file = MetaForSQL::findOne($this->db, [
//			'source' => 4,
			'path' => new SQLLikeContains(basename($source)),
		]);
		if ($file) {
			$this->log(__METHOD__, basename($source), $file->id, $file->path, $file->colors);
			$gradientQ = $file->colors;
			$ph = new Placeholder($width, $height);
			$row[] = $ph->getPlaceholder(null, $gradientQ);
		} else {
			$this->log(__METHOD__, $this->db->getLastQuery() . '');
		}

		$ph = new Placeholder($width, $height);
		$row[] = $ph->getPlaceholder($sourceURL, $gradient);

		$cornerColors = $ip->getCornerColors();
		$row[] = $this->html->pre(json_encode($cornerColors, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$row[] = $this->html->pre(json_encode($gradientQ, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
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

	/**
	 * @param MetaForSQL[] $files
	 * @return array
	 */
	public function renderImagePlaceholders(array $files): array
	{
		$content = [];
		foreach ($files as $file) {
			if (!$file->isImage()) {
				continue;
			}
//			$source = path_plus($this->fileRoot, $file);
			$source = $file->getFullPath();
			llog($source);
			[$width, $height] = getimagesize($source);
//			$sourceURL = 'test/ThomasGasson/' . $file;
			$sourceURL = $file->getThumbnail('ShowThumb?file=');

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
			$placeholder = $ph->getPlaceholder(null, $file->colors);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);

			$ph = new Placeholder($width, $height);
			$placeholder = $ph->getPlaceholder($sourceURL, $gradient);
			$content[] = $this->html->div($placeholder, '', [
				'style' => [
					'display' => 'inline-block',
				]
			]);
		}
		return $content;
	}

}
