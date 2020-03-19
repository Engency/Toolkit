<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 19-03-20 16:59
 */

namespace Engency\Validators;

/**
 * Class YoutubeUrlValidator
 *
 * @package Engency\Validators
 */
class YoutubeUrlValidator extends Validator
{

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     *
     * @return bool|mixed
     */
    public function validate($attribute, $value, array $parameters)
    {
        if ($this->isNullable($attribute) && ( $value === null || (int) $value === -1 )) {
            // the attribute is nullable and the input is null, so pass
            return true;
        }

        return self::getIdFromYoutubeUrl($value) !== null;
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public static function getIdFromYoutubeUrl(string $url) : ?string
    {
        $regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';

        if (preg_match($regex, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}