<?php

class TimelineServiceForSQL extends TimelineService
{

	public function renderMonth($year, $month, ArrayPlus $byMonth)
    {
        return parent::renderMonth($year, $month, $byMonth);
    }

}
