<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 19-03-20 16:33
 */

namespace Engency\Validators;

use Illuminate\Support\Arr;
use Illuminate\Validation\Validator as IlluminateValidator;
use Validator as ValidatorValidator;

/**
 * Class Validator
 *
 * @package App\Validators
 */
abstract class Validator
{

    /**
     * @var \Illuminate\Validation\Validator
     */
    protected \Illuminate\Validation\Validator $validator;

    /**
     * Register this validator in order to use it
     */
    final public static function register()
    {
        $ruleName = self::getRuleName();
        ValidatorValidator::extend($ruleName, static::class . '@performValidation');

        if (method_exists(static::class, 'replacer')) {
            ValidatorValidator::replacer($ruleName, static::class . '@replacer');
        }
    }

    /**
     * @return string
     */
    final protected static function getRuleName() : string
    {
        $classPath = static::class;
        $className = Arr::last(explode('\\', $classPath));

        return strtolower(str_replace('Validator', '', $className));
    }

    /**
     * @param mixed                            $attribute
     * @param mixed                            $value
     * @param array                            $parameters
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return mixed
     */
    final public function performValidation($attribute, $value, array $parameters, IlluminateValidator $validator)
    {
        $this->validator = $validator;

        return $this->validate($attribute, $value, $parameters);
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     *
     * @return mixed
     */
    abstract public function validate($attribute, $value, array $parameters);

    /**
     * @param mixed $attribute
     * @param mixed $value
     *
     * @return bool
     */
    protected function isRequired($attribute, $value)
    {
        return $this->validator->hasRule($attribute, 'Required');
    }

    /**
     * @param mixed $attribute
     *
     * @return bool
     */
    protected function isNullable($attribute)
    {
        return $this->validator->hasRule($attribute, 'Nullable');
    }
}