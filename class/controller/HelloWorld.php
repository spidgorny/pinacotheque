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
		$content[] = $this->html->p('Source: ' . $source);
		$content[] = $this->html->img($sourceURL, ['width' => 256]);

		$meta = getimagesize($source);
		$content[] = '<div style="height: 256px">
		<figure style="
			display: block;
			background: #f0f0f0;
			position: relative; 
			border: solid 1px silver; 
			width: 256px; 
			height: 0; 
			margin: 0;
			padding-top: 66%">
			<div style="
				position: absolute; 
				top: 0; left: 0; 
				width: 100%; height: 100%"></div>
		</figure>
		</div>';
		return $this->template($content, [], __DIR__ . '/../../template/blank.phtml');
	}

}
