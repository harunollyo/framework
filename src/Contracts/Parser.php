<?php

namespace Framework\Contracts;

interface Parser
{
    /**
     * Parse the content.
     *
     * @param string $content
     * @return string
     */
    public function parse(string $content): string;
}
