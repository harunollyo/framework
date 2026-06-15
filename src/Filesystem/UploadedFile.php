<?php

namespace Framework\Filesystem;

use Exception;
use Framework\Supports\Arr;
use JsonSerializable;

use function Framework\Polyfill\str_starts_with;

class UploadedFile extends File implements JsonSerializable
{
    /**
     * The original name of the uploaded file.
     *
     * @var string
     */
    protected string $original_name;

    /**
     * The MIME type of the uploaded file.
     *
     * @var string
     */
    protected string $mime_type;

    /**
     * The error of the uploaded file.
     *
     * @var int
     */
    protected int $error;

    /**
     * The original path of the uploaded file.
     *
     * @var string
     */
    protected string $original_path;

    /**
     * The size of the uploaded file.
     *
     * @var int
     */
    protected int $size;

    /**
     * Create a new uploaded file instance.
     *
     * @param string $path
     * @param string $original_name
     * @param string|null $mime_type
     * @param int|null $error
     */
    public function __construct(string $path, string $original_name, ?string $mime_type = null, ?int $error = null, ?int $size = null)
    {
        $this->original_name = $original_name;
        $this->mime_type = $mime_type ?? '';
        $this->error = $error ?? UPLOAD_ERR_OK;
        $this->original_path = $path;
        $this->size = $size ?? 0;

        parent::__construct($path);
    }

    /**
     * Get the original name of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function get_client_original_name()
    {
        return $this->original_name;
    }

    /**
     * Get the original extension of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function get_client_original_extension()
    {
        return pathinfo($this->original_name, \PATHINFO_EXTENSION);
    }

    /**
     * Get the original MIME type of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function get_client_original_mime_type()
    {
        return $this->mime_type ?? '';
    }

    /**
     * Get the original path of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function get_client_original_path()
    {
        return $this->original_path ?? '';
    }

    /**
     * Get the error of the uploaded file.
     *
     * @return int
     * 
     * @since 1.0.0
     */
    public function get_error()
    {
        return $this->error;
    }

    /**
     * Check if the uploaded file is valid.
     *
     * @return bool
     * 
     * @since 1.0.0
     */
    public function is_valid()
    {
        $is_ok = \UPLOAD_ERR_OK === $this->error;

        return $is_ok && is_uploaded_file($this->getPathname());
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $directory
     * @param string|null $name
     * 
     * @return string
     * 
     * @throws Exception
     * 
     * @since 1.0.0
     */
    public function move(string $directory, ?string $name = null)
    {
        if ($this->is_valid()) {
            $target = $this->get_target_file($directory, $name);

            set_error_handler(static function ($type, $msg) use (&$error) {$error = $msg; });

            try {
                $moved = move_uploaded_file($this->getPathname(), $target);
            } finally {
                restore_error_handler();
            }

            if (!$moved) {
                throw new Exception(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error ?? '')));
            }

            @chmod($target, 0o666 & ~umask());

            return $target;
        }

        throw new Exception($this->get_error_message());
    }

    /**
     * Get the error message of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function get_error_message()
    {
        return $this->error !== \UPLOAD_ERR_OK ? $this->get_exception_message() : '';
    }

    /**
     * Get the exception message of the uploaded file.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    protected function get_exception_message()
    {
        static $errors = [
            \UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
            \UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            \UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            \UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            \UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
        ];

        $error_code = $this->error;
        $max_file_size = \UPLOAD_ERR_INI_SIZE === $error_code ? $this->get_max_file_size() / 1024 : 0;
        $message = $errors[$error_code] ?? 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->get_client_original_name(), $max_file_size);
    }

    /**
     * Get the maximum file size of the uploaded file.
     *
     * @return int
     * 
     * @since 1.0.0
     */
    protected function get_max_file_size()
    {
        $size_post_max = $this->parse_file_size(ini_get('post_max_size'));
        $size_upload_max = $this->parse_file_size(ini_get('upload_max_filesize'));

        return min($size_post_max ?: \PHP_INT_MAX, $size_upload_max ?: \PHP_INT_MAX);
    }

    /**
     * Parse the file size.
     *
     * @param string $size
     * @return int
     * 
     * @since 1.0.0
     */
    protected function parse_file_size(string $size)
    {
        if ($size === '') {
            return 0;
        }

        $size = strtolower(trim($size));
        
        $max = ltrim($size, '+');

        if (str_starts_with($max, '0x')) {
            $max = intval($max, 16);
        } elseif (str_starts_with($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = intval($max);
        }

        switch (substr($size, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }

    /**
     * Convert the uploaded file to an array.
     *
     * @return array
     * 
     * @since 1.0.0
     */
    public function to_array()
    {
        return [
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'error' => $this->error,
            'original_path' => $this->original_path,
        ];
    }

    /**
     * Convert the uploaded file to a string.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function __toString()
    {
        return Arr::json_encode($this->to_array());
    }

    /**
     * Convert the uploaded file to a JSON string.
     *
     * @return string
     * 
     * @since 1.0.0
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}