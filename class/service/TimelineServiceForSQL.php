<?php

use nadlib\HTTP\Session;

class TimelineServiceForSQL extends TimelineService
{

	public function getMonthBrowserLink($year, $month)
	{
		$session = new Session(Sources::class);
		$source = $session->get('source');
		return MonthBrowserDB::href2month($source, $year, $month);
	}

}
