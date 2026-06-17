<?php
/**
 * Contract for handling file uploads as WordPress media attachments.
 * Defines upload returning structured attachment metadata including sizes and mime info.
 * Abstracts media library integration behind a testable interface.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface Uploader
{
    /**
     * File upload as attachemnt and get all attachment's info with meta data
     *
     * @param array $files The array of files from the $_FILES superglobal.
     * @param int $parent_post_id
     *
     * @return array<int, array{
     *     id: int,
     *     filename: string,
     *     url: string,
     *     sizes: array<string, array{
     *         height: int,
     *         width: int,
     *         url: string,
     *         orientation: string
     *     }>,
     *     height: int,
     *     width: int,
     *     filesize: int,
     *     mime: string,
     *     type: string,
     *     thumb: array{
     *         src: string,
     *         width: int,
     *         height: int
     *     }|null,
     *     author: int,
     *     author_name: string,
     *     date: string
     * }>
     * 
     * @throws Exception
     */
    public function upload(array $files, int $parent_post_id = 0);
}
