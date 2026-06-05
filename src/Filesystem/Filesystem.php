<?php

namespace Framework\Filesystem;

use Framework\Exceptions\NotFoundException;
use Framework\Supports\Traits\Macroable;
use WP_Filesystem_Base;

class Filesystem
{
    use Macroable;

    /**
     * The WordPress filesystem.
     *
     * @var WP_Filesystem_Base
     */
    protected $filesystem;

    /**
     * Create a new filesystem instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();

        global $wp_filesystem;

        $this->filesystem = $wp_filesystem;

        if (!$this->filesystem instanceof WP_Filesystem_Base) {
            throw new \RuntimeException('WordPress filesystem is not available.');
        }
    }

    /**
     * Check if the given path is a file.
     *
     * @param string $file
     * @return bool
     */
    public function is_file($file)
    {
        return $this->filesystem->is_file($file);
    }

    /**
     * Find path names matching a pattern.
     *
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    public function glob($pattern, $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * Get the base name of a path.
     *
     * @param string $path
     * @return string
     */
    public function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Get the file name of a path.
     *
     * @param string $path
     * @return string
     */
    public function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get the file extension of a path.
     *
     * @param string $path
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function copy($path, $target)
    {
        return $this->filesystem->copy($path, $target, true, FS_CHMOD_FILE);
    }

    /**
     * Move a file to a new location.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function move($path, $target)
    {
        return $this->filesystem->move($path, $target, true);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $success = true;

        foreach ($paths as $path) {
            if (!$this->filesystem->delete($path)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get or set permissions of a file or directory.
     *
     * @param string $path
     * @param int|null $mode
     * @return mixed
     */
    public function chmod($path, $mode = null)
    {
        if ($mode) {
            return $this->filesystem->chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public function append($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $this->get($path) . $data);
        }

        return $this->put($path, $data);
    }

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public function prepend($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public function put($path, $data)
    {
        if (!$this->exists($path)) {
            $this->make_dir($path);
        }

        return $this->filesystem->put_contents($path, $data, FS_CHMOD_FILE);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string
     *
     * @throws \Framework\Exceptions\NotFoundException
     */
    public function get($path)
    {
        if (!$this->is_file($path)) {
            throw new NotFoundException(sprintf("file does not exists at [%s]", $path));
        }

        $contents = $this->filesystem->get_contents($path);

        if ($contents === false) {
            throw new NotFoundException(sprintf("file does not exists at [%s]", $path));
        }

        return $contents;
    }

    /**
     * Get the contents of a JSON file and decode it.
     *
     * @param string $path
     * @param int $flags
     * @return array
     */
    public function json($path, $flags = 0)
    {
        return json_decode($this->get($path), true, 512, $flags);
    }

    /**
     * Calculate the hash of a file.
     *
     * @param string $path
     * @param string $algorithm
     * @return string
     */
    public function hash($path, $algorithm = 'md5')
    {
        return hash_file($algorithm, $path);
    }

    /**
     * Determine if a file or directory exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        return $this->filesystem->exists($path);
    }

    /**
     * Determine if a file or directory does not exist.
     *
     * @param string $path
     * @return bool
     */
    public function missing($path)
    {
        return !$this->exists($path);
    }

    /**
     * Get the directory name of a path.
     *
     * @param string $path
     * @return string
     */
    public function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Get the file type of a path.
     *
     * @param string $path
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the MIME type of a path.
     *
     * @param string $path
     * @return string
     */
    public function mime_type($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     * @return int|false
     */
    public function size($path)
    {
        return $this->filesystem->size($path);
    }

    /**
     * Determine if a file is a directory.
     *
     * @param string $path
     * @return bool
     */
    public function is_directory($path)
    {
        return $this->filesystem->is_dir($path);
    }

    /**
     * Determine if a file is readable.
     *
     * @param string $path
     * @return bool
     */
    public function is_readable($path)
    {
        return $this->filesystem->is_readable($path);
    }

    /**
     * Determine if a file is writable.
     *
     * @param string $path
     * @return bool
     */
    public function is_writable($path)
    {
        return $this->filesystem->is_writable($path);
    }

    /**
     * Get the last modified time of a file.
     *
     * @param string $path
     * @return int|false
     */
    public function last_modified($path)
    {
        return $this->filesystem->mtime($path);
    }

    /**
     * Make a directory.
     *
     * @param string $path
     * @return bool
     */
    public function make_dir($path)
    {
        $path = pathinfo($path, PATHINFO_EXTENSION) !== ''
            ? $this->dirname($path)
            : $path;

        if ($this->exists($path)) {
            return true;
        }

        return wp_mkdir_p($path);
    }

    /**
     * Make a file or directory.
     *
     * @param string $path
     * @return bool
     */
    public function make(string $path)
    {
        if ($this->exists($path)) {
            return;
        }

        if ($this->is_directory($path)) {
            $this->make_dir($path);
            return;
        }

        return $this->filesystem->touch($path);
    }
}
