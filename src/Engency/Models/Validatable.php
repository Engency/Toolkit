<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use Illuminate\Validation\ValidationException;
use Request;

/**
 * Trait Validatable
 *
 * @package Engency\Models
 */
trait Validatable
{

    /**
     * @var \Illuminate\Validation\Validator|null
     */
    private static $validator        = null;
    private static $validationErrors = [];

    /**
     * @param array       $data
     * @param string|null $occasion
     *
     * @return static
     * @throws ValidationException
     *
     */
    public static function validateAndCreateNew(array $data, ?string $occasion = 'default')
    {
        self::validate($data, $occasion);

        // validation successfull
        $instance = new static();
        $instance->fill($data);
        $instance->save();
        $instance->fill($data);

        return $instance;
    }

    /**
     * Validate data for this model. Method will throw a ValidationException if the data is invalid
     *
     * @param array       $data
     * @param string|null $occasion
     *
     * @throws ValidationException
     */
    public static function validate(array $data, ?string $occasion = 'default')
    {
        // get rules which belong to occasion
        $rules = self::getValidatorRule($occasion);
        if (count($rules) === 0) {
            return;
        }

        $success = self::validateWithRules($data, $rules);
        if (!$success) {
            if (Request::hasSession()) {
                Request::flash();
            }
            throw new ValidationException(self::$validator);
        }
    }

    /**
     * Get the validation rules of the model for a specific occasion
     *
     * @param string $occasion
     *
     * @return array containing the rules
     */
    private static function getValidatorRule(string $occasion) : array
    {
        if (isset(self::$rules) && is_array(self::$rules) && isset(self::$rules[$occasion]) && is_array(self::$rules[$occasion])) {
            return self::$rules[$occasion];
        }

        return [];
    }

    /**
     * @param array $data
     * @param array $rules
     *
     * @return bool
     */
    public static function validateWithRules(array $data, array $rules) : bool
    {
        $validator = \Validator::make($data, $rules);

        if ($validator->fails()) {
            // failed validation, there were validation errors
            self::$validator = $validator;

            return false;
        }

        // passed validation
        return true;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     *
     * @return $this
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     */
    abstract public function fill(array $attributes);

    /**
     * @param array $options
     *
     * @return mixed
     */
    abstract public function save(array $options = []);

    /**
     * Get the validation errors. Please call validate first in order to generate errors
     *
     * @return string
     */
    public static function getValidationErrors() : string
    {
        $messages = [];
        if (( self::$validator === null )) {
            return '';
        }

        foreach (self::$validator->messages()->getMessages() as $field => $errors) {
            foreach ($errors as $error) {
                $messages[] = $error;
            }
        }

        return implode(', ', $messages);
    }

    /**
     * @param array       $data
     * @param string|null $occasion
     *
     * @throws ValidationException
     */
    public function validateAndUpdate(array $data, ?string $occasion = 'default')
    {
        if (!$this->validateUpdate($data, $occasion)) {
            Request::flash();
            throw new ValidationException(self::$validator);
        }

        $this->fill($data);
        $this->save();
    }

    /**
     * @param array       $data
     * @param string|null $occasion
     *
     * @return bool
     */
    public function validateUpdate(array $data, ?string $occasion = 'default') : bool
    {
        $rules = self::getValidatorRule($occasion);
        $rules = $this->getRulesWithExceptCurrentInstance($rules);

        return self::validateWithRules($data, $rules);
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    private function getRulesWithExceptCurrentInstance(array $rules) : array
    {
        foreach ($rules as &$rule) {
            if (strpos($rule, 'unique') === false) {
                continue;
            }

            $ruleParticles = explode('|', $rule);
            foreach ($ruleParticles as &$ruleParticle) {
                if (strpos($ruleParticle, 'unique') === false || substr_count($ruleParticle, ',') !== 1) {
                    continue;
                }

                $primaryKey   = $this->primaryKey;
                $ruleParticle .= ',' . $this->{$primaryKey} . ',' . $primaryKey;
            }
            $rule = implode('|', $ruleParticles);
        }

        return $rules;
    }
}
