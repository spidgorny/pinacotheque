<?php

class SourceForm extends AppController
{

	public function getForm(array $data)
	{
		$form = new HTMLFormTable([
			'name' => [
				'label' => 'Name',
				'beforeLabel' => '<div class="field">',
				'afterLabel' => '</div>',
				'wrap' => '<div class="control">|</div>',
				'more' => [
					'class' => 'input',
				],
			],
			'path' => [
				'label' => 'Path',
				'beforeLabel' => '<div class="field">',
				'afterLabel' => '</div>',
				'wrap' => '<div class="control">|</div>',
				'more' => [
					'class' => 'input',
				],
			],
			'thumbRoot' => [
				'label' => 'Thumb Root',
				'beforeLabel' => '<div class="field">',
				'afterLabel' => '</div>',
				'wrap' => '<div class="control">|</div>',
				'more' => [
					'class' => 'input',
				],
			],
		]);
		$form->fill($data);
		$form->defaultBR = true;
		$form->formMore = [
			'style' => 'width: 100%',
		];
		$form->showForm();
		return $form;
	}
}
