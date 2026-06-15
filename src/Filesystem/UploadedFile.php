<?php

namespace Framework\Filesystem;

use Framework\Supports\Arr;
use JsonSerializable;

class UploadedFile extends File implements JsonSerializable
{
    protected string $original_name;
    protected string $mime_type;
    protected int $error;
    protected string $original_path;

    public function __construct(string $path, string $original_name, ?string $mime_type = null, ?int $error = null)
    {
        $this->original_name = $original_name;
        $this->mime_type = $mime_type ?? '';
        $this->error = $error ?? UPLOAD_ERR_OK;
        $this->original_path = $path;

        parent::__construct($path);
    }

    /**
     * Convert the uploaded file to an array.
     *
     * @return array
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
     */
    public function __toString()
    {
        return Arr::json_encode($this->to_array());
    }

    /**
     * Convert the uploaded file to a JSON string.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}