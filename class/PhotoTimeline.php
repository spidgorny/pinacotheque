<?php

class PhotoTimeline
{

	public function __invoke()
	{
		$set = new MetaSet(getFlySystem(__DIR__.'/../data/thumbs'));
//		debug($set->size());
		$times = $set->groupBy('FileDateTime');
		$times = ArrayPlus::create($times);
		$byDate = $times->reindex(function ($key, $val) {
			return is_int($key)
				? date('Y-m-d H:i', $key)
				: $key;
		});
		debug(array_keys($byDate->getData()));
		debug($byDate->countEach());
		return 'asd';
	}

}
