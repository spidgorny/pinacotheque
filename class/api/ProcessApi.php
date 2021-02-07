<?php

class ProcessApi extends ApiController
{

	public function index()
	{
		$method = $this->request->getMethod();
		$params = $this->request->getURLLevels();
		array_shift($params);	// ProcessApi
		return $this->$method(...$params);
	}

	public function TEST(...$params)
	{
		llog($params);
		return new JSONResponse($params);
	}

	public function GET($id = null)
	{
		llog('id', $id);
		return new JSONResponse([
			'id' => $id,
		]);
	}

}
