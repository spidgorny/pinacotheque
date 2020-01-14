<?php

class Placeholder
{

	protected $width;
	protected $height;
	protected $aspectRatio;
	protected $w;
	protected $h;

	public function __construct($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
		$this->aspectRatio = $width / $height;
		if ($this->aspectRatio >= 1) {
			$this->w = 256;
			$this->h = 256 / $this->aspectRatio;
		} else {
			$this->w = 256 * $this->aspectRatio;
			$this->h = 256;
		}
	}

	public function getPlaceholder($imgSrc = null, $gradient = []): string
	{
		$img = '';
		if ($imgSrc) {
			$avgColor = 'transparent';
			if ($gradient) {
//				$avgColor = $this->getAverageColor($gradient);
			}
			$img = '<img src="' . $imgSrc . '" style="
				width: ' . $this->w . 'px; 
				height: ' . $this->h . 'px; 
				object-fit: contain;
				background-color: '.$avgColor.';
				margin: auto; 
			" title="' . $this->getTitle() . '"/>';
		}

		$id = uniqid('div', false);
		$img = '<div id="'.$id.'" style="
			width: ' . $this->w . 'px; 
			height: ' . $this->h . 'px;
			object-fit: contain;
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
		</div>';
	}

	public function getTitle()
	{
		$content[] = '[' . $this->width . 'x' . $this->height . ']';
		$content[] = 'Aspect ratio: ' . $this->aspectRatio;
		$content[] = '[' . $this->w . 'x' . $this->h . ']';
		return implode(PHP_EOL, $content);
	}

}
