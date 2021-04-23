<?php

namespace lighth7015\LaravelPHPTAL;

use PHPTAL_Filter as Filter;

class PHPTALFilterChain implements Filter {
    /**
     *
     * @var array
     */
    protected $filters = [];

    /**
     *
     * @param PHPTAL_Filter $filter
     */
    public function add(PHPTAL_Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     *
     * @param string $source
     */
    public function filter($source)
    {
        foreach ($this->filters as $filter) {
            $source = $filter->filter($source);
        }
        return $source;
    }
}
