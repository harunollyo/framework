<?php

namespace Framework\Contracts;

interface Exportable
{
    /**
     * Export data as an DTO instance.
     *
     * @return \Framework\DTO|mixed
     * @throws \Exception
     * */
    public function export();
}
