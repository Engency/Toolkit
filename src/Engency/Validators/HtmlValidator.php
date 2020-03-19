<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 19-03-20 16:50
 */

namespace Engency\Validators;

use tidy;

/**
 * Class YoutubeUrlValidator
 *
 * @package App\Validators
 */
class HtmlValidator extends Validator
{

    protected static array $forbiddenTags = ['script', 'style', 'meta', 'a', 'video', 'img'];

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

        return self::getValidHtml($value) !== null;
    }

    /**
     * @param string $html
     * @param array  $allowedTags
     *
     * @return string
     */
    public static function getValidHtml(string $html, array $allowedTags = []) : string
    {
        $valid = ( new tidy() )->repairString(
            $html,
            [
                'clean'          => true,
                'output-xhtml'   => true,
                'show-body-only' => true,
                'hide-comments'  => true,

            ],
            'utf8'
        );

        foreach (static::$forbiddenTags as $forbiddenTag) {
            if (in_array($forbiddenTag, $allowedTags)) {
                continue;
            }
            $valid = preg_replace('#<' . $forbiddenTag . '(.*?)>(.*?)</' . $forbiddenTag . '>#is', '', $valid);
            $valid = preg_replace('/<' . $forbiddenTag . '.*?>/is', '', $valid);
        }

        return $valid;
    }
}
