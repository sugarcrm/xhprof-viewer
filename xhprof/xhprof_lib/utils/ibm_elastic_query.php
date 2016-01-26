<?php
namespace IBMXHProfElastic;

class Query {
    protected $query;
    protected $time;
    protected $backtrace;

    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getBacktrace()
    {
        return $this->backtrace;
    }


}
