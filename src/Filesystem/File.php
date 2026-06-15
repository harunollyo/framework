<?php

namespace Framework\Filesystem;

use Exception;
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

    /**
     * Move the file to a new location.
     *
     * @param string $directory
     * @param string|null $name
     * 
     * @return static
     * @throws Exception
     * 
     * @since 1.0.0
     */
    public function move(string $directory, ?string $name = null)
    {
        $target = $this->get_target_file($directory, $name);

        set_error_handler(static function ($type, $msg) use (&$error) {$error = $msg; });

        try {
            $renamed = rename($this->getPathname(), $target);
        } finally {
            restore_error_handler();
        }

        if (!$renamed) {
            throw new Exception(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error ?? '')));
        }

        @chmod($target, 0o666 & ~umask());

        return $target;
    }

    /**
     * Get the contents of the file.
     *
     * @return string
     * 
     * @throws Exception
     * 
     * @since 1.0.0
     */
    public function get_content()
    {
        $content = file_get_contents($this->getPathname());

        if ($content === false) {
            throw new Exception(sprintf('Unable to read the file "%s".', $this->getPathname()));
        }

        return $content;
    }

    /**
     * Get the target file.
     *
     * @param string $directory
     * @param string|null $name
     * 
     * @return static
     * 
     * @throws Exception
     * 
     * @since 1.0.0
     */
    protected function get_target_file(string $directory, ?string $name = null)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0o777, true) && !is_dir($directory)) {
            if (is_file($directory)) {
                throw new Exception(sprintf('Unable to create the "%s" directory. A similar named file exists.', $directory));
            }

            throw new Exception(sprintf('Unable to create the "%s" directory.', $directory));
        } elseif (!is_writable($directory)) {
            throw new Exception(sprintf('Unable to write in the "%s" directory.', $directory));
        }

        $target = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . ($name === null ? $this->getBasename() : $this->get_name($name));

        return new static($target, false);
    }

    /**
     * Get the name of the file.
     *
     * @param string $name
     * @return string
     * 
     * @since 1.0.0
     */
    protected function get_name(string $name)
    {
        $original_name = str_replace('\\', '/', $name);
        $position = strrpos($original_name, '/');

        return $position === false ? $original_name : substr($original_name, $position + 1);
    }
}