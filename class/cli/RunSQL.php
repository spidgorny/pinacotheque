<?php

class RunSQL
{

    protected $db;

    public function __construct(DBLayerSQLite $db)
    {
        $this->db = $db;
    }

    public function __invoke()
    {
        $file = $_SERVER['argv'][2];
        $sql = file_get_contents($file);
        echo $sql, PHP_EOL;
        $res = $this->db->perform($sql);
        echo $res, PHP_EOL;
    }

}
