<?php

/**
 * Shared helpers for Framework PHPCS sniffs.
 *
 * @since 1.0.0
 */
class Framework_SniffHelper
{
    /**
     * PHP-native method names exempt from snake_case enforcement.
     *
     * @var string[]
     * @since 1.0.0
     */
    protected static $native_methods = [
        'jsonSerialize',
        'offsetExists',
        'offsetGet',
        'offsetSet',
        'offsetUnset',
        'getIterator',
        'count',
    ];

    /**
     * Determine whether a method name is exempt from snake_case rules.
     *
     * @param string $name Method name.
     *
     * @return bool
     * @since 1.0.0
     */
    public static function is_native_method($name)
    {
        if (strpos($name, '__') === 0) {
            return true;
        }

        return in_array($name, static::$native_methods, true);
    }

    /**
     * Determine whether a name uses snake_case.
     *
     * @param string $name Identifier name.
     *
     * @return bool
     * @since 1.0.0
     */
    public static function is_snake_case($name)
    {
        return (bool) preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $name);
    }

    /**
     * Determine whether a name uses SCREAMING_SNAKE_CASE.
     *
     * @param string $name Constant name.
     *
     * @return bool
     * @since 1.0.0
     */
    public static function is_screaming_snake_case($name)
    {
        return (bool) preg_match('/^[A-Z][A-Z0-9]*(_[A-Z0-9]+)*$/', $name);
    }

    /**
     * Find the docblock token index immediately preceding a given token.
     *
     * @param array $tokens File tokens.
     * @param int   $index  Target token index.
     *
     * @return int|null
     * @since 1.0.0
     */
    public static function find_preceding_docblock(array $tokens, $index)
    {
        $previous = $index - 1;

        while ($previous >= 0 && $tokens[$previous]['code'] === T_WHITESPACE) {
            --$previous;
        }

        if ($previous >= 0 && $tokens[$previous]['code'] === T_DOC_COMMENT_OPEN_TAG) {
            return $previous;
        }

        return null;
    }

    /**
     * Parse a docblock into structured sections.
     *
     * @param string $content Docblock raw content without open/close tags.
     *
     * @return array
     * @since 1.0.0
     */
    public static function parse_docblock($content)
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $description_lines = [];
        $tags = [];
        $current_tag = null;

        foreach ($lines as $line) {
            $line = preg_replace('/^\s*\*\s?/', '', $line);
            $line = rtrim($line);

            if ($line === '' && $current_tag === null && empty($description_lines)) {
                continue;
            }

            if (preg_match('/^@(\S+)\s*(.*)$/', $line, $matches)) {
                $current_tag = $matches[1];
                $tags[$current_tag][] = $matches[2];

                continue;
            }

            if ($current_tag === null) {
                if ($line !== '' || !empty($description_lines)) {
                    $description_lines[] = $line;
                }

                continue;
            }

            $tags[$current_tag][count($tags[$current_tag]) - 1] .= ' ' . $line;
        }

        return [
            'description' => trim(implode("\n", $description_lines)),
            'tags'        => $tags,
        ];
    }

    /**
     * Extract docblock content from token stack.
     *
     * @param array $tokens File tokens.
     * @param int   $index  Docblock open tag index.
     *
     * @return string
     * @since 1.0.0
     */
    public static function get_docblock_content(array $tokens, $index)
    {
        $content = '';

        for ($i = $index; $i < count($tokens); ++$i) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
                break;
            }

            $content .= $tokens[$i]['content'];
        }

        $content = preg_replace('/^\/\*\*/', '', $content);
        $content = preg_replace('/\*\/\s*$/', '', $content);

        return $content;
    }

    /**
     * Return docblock body lines keyed by line number.
     *
     * @param array $tokens File tokens.
     * @param int   $open   Docblock open tag index.
     *
     * @return array
     * @since 1.0.0
     */
    public static function get_docblock_body_lines(array $tokens, $open)
    {
        $content = static::get_docblock_content($tokens, $open);
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $mapped = [];

        foreach ($lines as $index => $line) {
            $mapped[$index] = $line;
        }

        return $mapped;
    }
}
