<?php

class MetaTest
{

//	protected $prefix = 'C:\Users\depidsvy\web\slawa2018\pinacotheque\data\thumbs';

	function __invoke()
	{
		$meta = new Meta([
			'FileName' => "04fXWgj.jpg",
			'FileDateTime' => 1437887184,
			'FileSize' => 373251,
			'FileType' => 2,
			'MimeType' => "image\/jpeg",
			'SectionsFound' => "",
			'COMPUTED' => [
				'html' => "width=\"2560\" height=\"1600\"",
				'Height' => 1600,
				'Width' => 2560,
				'IsColor' => 1,
			],
			'_path_' => 'C__Users_depidsvy_web_slawa2018_pinacotheque_data_ThomasGasson',
		]);
		$original = $meta->getOriginal();
		debug($meta->_path_, $original);
	}

}
