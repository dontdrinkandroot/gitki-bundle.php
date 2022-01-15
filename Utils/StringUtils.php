<?php

namespace Dontdrinkandroot\GitkiBundle\Utils;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class StringUtils
{
    /**
     * Checks if a string starts with another string.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The string to search.
     *
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * Checks if a string ends with another string.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The string to search.
     *
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * Get the first character of a string.
     *
     * @param string $str The string to get the first character of.
     *
     * @return string|false The first character or false if not found.
     */
    public static function getFirstChar($str)
    {
        $length = strlen($str);
        if ($length === 0) {
            return false;
        }

        return substr($str, 0, 1);
    }

    /**
     * Get the last character of a string.
     *
     * @param string false The string to get the last character of.
     *
     * @return string|null The last character or false if not found.
     */
    public static function getLastChar(string $str)
    {
        $length = strlen($str);
        if ($length === 0) {
            return false;
        }

        return substr($str, $length - 1, 1);
    }
}
