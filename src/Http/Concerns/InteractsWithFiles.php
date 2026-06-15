<?php

namespace Framework\Http\Concerns;

use Framework\Filesystem\UploadedFile;

use function Framework\deep_get;

trait InteractsWithFiles
{
    /**
     * Whether the files have been loaded from the global $_FILES array.
     * For a same request, we need to load the files only once.
     *
     * @var bool
     */
    protected static bool $loaded = false;

    /**
     * The files array.
     *
     * @var array<string,UploadedFile>
     */
    protected static array $files = [];

    /**
     * Get all files from the request.
     *
     * @return array<string,UploadedFile>
     */
    public function all_files()
    {
        $files = !static::$loaded ? $this->load_files_from_global() : static::$files;

        return $files;
    }

    /**
     * Get a file from the request.
     *
     * @param string $key The key of the file.
     * @param mixed $default The default value if the file is not found.
     * 
     * @return UploadedFile|array<string,UploadedFile>
     */
    public function file(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->all_files();
        }

        return deep_get($this->all_files(), $key, $default);
    }

    /**
     * Create the files from the global $_FILES array.
     *
     * @return array
     */
    protected function load_files_from_global()
    {
        $files = $_FILES ?? [];
        $keys = array_keys($files);

        $files = array_map(function ($file) {
            if (isset($file['name']) && is_array($file['name'])) {
                return $this->convert_uploaded_files($file);
            }
            return $this->create_uploaded_file($file);
        }, $_FILES ?? []);

        static::$loaded = true;

        return static::$files = array_combine($keys, $files);
    }

    /**
     * Convert the uploaded files to an array of UploadedFile instances.
     *
     * @param array $files The array of files.
     *
     * @return array<string,UploadedFile>
     * 
     * @since 1.0.0
     */
    protected function convert_uploaded_files(array $files)
    {
        $results = [];
        $results = array_fill(
            0,
            count($files['name'] ?? []),
            ['name' => '', 'type' => '', 'tmp_name' => '', 'error' => 0, 'size' => 0],
        );
        $keys = ['name', 'type', 'tmp_name', 'error', 'size'];

        foreach ($keys as $key) {
            $this->process_item($files[$key], $key, $results);
        }

        return array_map([$this, 'create_uploaded_file'], $results);
    }

    /**
     * Process an item from the files array.
     *
     * @param array $item The item to process.
     * @param string $key The key of the item.
     * @param array &$results The results array.
     */
    protected function process_item(array $item, string $key, array &$results)
    {
        foreach ($item as $index => $value) {
            $results[$index][$key] = $value;
        }
    }

    /**
     * Check if a file exists in the request.
     *
     * @param string $key The key of the file.
     *
     * @return bool
     */
    protected function has_file(string $key)
    {
        return isset($this->files[$key]);
    }

    /**
     * Create a new uploaded file instance.
     *
     * @param array $file The file array from the $_FILES superglobal.
     * @return UploadedFile
     */
    protected function create_uploaded_file(array $file)
    {
        return new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error'], $file['size']);
    }
}