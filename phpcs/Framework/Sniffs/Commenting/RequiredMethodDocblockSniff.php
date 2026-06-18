<?php

require_once dirname(__DIR__) . '/Support/SniffHelper.php';

/**
 * Requires every method to have a docblock.
 *
 * @since 1.0.0
 */
class Framework_Sniffs_Commenting_RequiredMethodDocblockSniff implements PHP_CodeSniffer\Sniffs\Sniff
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
        $docblock_ptr = Framework_SniffHelper::find_preceding_docblock($tokens, $stack_ptr);

        if ($docblock_ptr !== null) {
            return;
        }

        $name_ptr = $phpcs_file->findNext(T_STRING, $stack_ptr + 1);
        $method_name = $name_ptr !== false ? $tokens[$name_ptr]['content'] : 'unknown';

        $phpcs_file->addError(
            'Method "%s" must have a docblock.',
            $stack_ptr,
            'MissingDocblock',
            [$method_name]
        );
    }
}
