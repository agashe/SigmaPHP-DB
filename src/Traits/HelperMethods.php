<?php

namespace SigmaPHP\DB\Traits;

/**
 * Helper Methods Trait.
 */
trait HelperMethods
{
    /**
     * Add quotes for string values.
     *
     * @param string $value
     * @return string
     */
    public function addQuotes($value)
    {
        // exclude the null / numeric / SQL functions
        return !empty($value) && (
            (preg_match('/[a-zA-Z]+\(/', $value) !== 0) ||
            (strtoupper($value) == 'CURRENT_TIMESTAMP') ||
            strtolower($value) == 'null' ||
            is_numeric($value)) ? $value : "'" . addslashes($value ?? '') . "'";
    }

    /**
     * Concatenate array of tokens into one comma separated line.
     *
     * @param array $tokens
     * @param bool $addQuotes
     * @return string
     */
    public function concatenateTokens($tokens, $addQuotes = false)
    {
        if ($addQuotes) {
            $tokens = array_map(function ($token) {
                return $this->addQuotes($token);
            }, $tokens);
        }

        return rtrim(implode(",", $tokens), ",");
    }
}
