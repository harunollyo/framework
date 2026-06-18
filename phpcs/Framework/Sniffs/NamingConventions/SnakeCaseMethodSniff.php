<?php

require_once dirname(__DIR__) . '/Support/SniffHelper.php';

/**
 * Enforces snake_case method names except PHP-native methods.
 *
 * @since 1.0.0
 */
class Framework_Sniffs_NamingConventions_SnakeCaseMethodSniff implements PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     * @since 1.0.0
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer\Files\File $phpcs_file The file being scanned.
     * @param int                        $stack_ptr  The position of the current token.
     *
     * @return void
     * @since 1.0.0
     */
    public function process(PHP_CodeSniffer\Files\File $phpcs_file, $stack_ptr)
    {
        $tokens = $phpcs_file->getTokens();
        $name_ptr = $phpcs_file->findNext(T_STRING, $stack_ptr + 1);

        if ($name_ptr === false) {
            return;
        }

        $method_name = $tokens[$name_ptr]['content'];

        if (Framework_SniffHelper::is_native_method($method_name)) {
            return;
        }

        if (Framework_SniffHelper::is_snake_case($method_name)) {
            return;
        }

        $phpcs_file->addError(
            'Method "%s" must use snake_case naming.',
            $name_ptr,
            'NotSnakeCase',
            [$method_name]
        );
    }
}
