<?php

namespace Framework\Http\Concerns;

use Framework\Filesystem\UploadedFile;

use function Framework\deep_get;

trait InteractsWithFiles
{
    /**
     * The files uploaded in the request.
     *
     * @var array<string,UploadedFile>
     */
    protected array $files = [];

    /**
     * Get all files from the request.
     *
     * @return array<string,UploadedFile>
     */
    public function all_files()
    {
        return $this->files;
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
     * @return void
     */
    protected function load_files_from_global()
    {
        if (empty($_FILES)) {
            return;
        }

        $files = [];

        foreach ($_FILES as $key => $file) {
            $files[$key] = $this->create_uploaded_file($file);
        }

        $this->files = $files;
    }

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
        return new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
    }
}