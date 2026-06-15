<?php

namespace Framework\Filesystem;

use InvalidArgumentException;
use SplFileInfo;

class File extends SplFileInfo
{
    /**
     * Create a new file instance.
     *
     * @param string $path
     * @return void
     */
    public function __construct(string $path, bool $check_path = true)
    {
        if ($check_path && !file_exists($path)) {
            throw new InvalidArgumentException("File does not exist at path: {$path}");
        }

        parent::__construct($path);
    }

}