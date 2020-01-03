<?php

class About	extends AppController
{

	public function index()
	{
		$Parsedown = new Parsedown();

		$md = file_get_contents(__DIR__ . '/../../README.md');
		$content[] = '<div class="content">';
		$content[] = $Parsedown->text($md);
		$content[] = '</div>';
		return $this->template($content);
	}

}
