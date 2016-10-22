<?php
namespace KSS\Vimeo\Utility;

/*                                                                        *
 * This script belongs to the Flow package "Flow".                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Helper class to convert strings with umlaute
 */
class Umlaute
{

    /**
     * Converts the first character of each word to uppercase and all remaining characters
     * to lowercase.
     *
     * @param  string $str The string to convert
     * @return string The converted string
     */
    public function convertAccentAndBlankspace($string)
    {
        if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
            $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
        }

        return strtr($string, [" " => "-"]);
    }
}
