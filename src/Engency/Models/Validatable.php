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
     * @param array       $data
     * @param string|null $occasion
     *
     * @return static
     * @throws ValidationException
     *
     */
    public static function validateAndCreateNew(array $data, ?string $occasion = 'default')
    {
        $validated = self::validateDataBeforeCreate($data, $occasion);

        // validation succeeded and passed data is stored in $validated

        $instance = new static();
        $instance->fill($validated);
        $instance->save();
        $instance->fill($validated);

        return $instance;
    }

    /**
     * Validate data for this model. Method will throw a ValidationException if the data is invalid
     *
     * @param array       $data
     * @param string|null $occasion
     * @return array
     * @throws ValidationException
     */
    public static function validateDataBeforeCreate(array $data, ?string $occasion = 'default') : array
    {
        // get rules which belong to occasion
        $rules = self::getValidatorRule($occasion);
        if (count($rules) === 0) {
            return $data;
        }

        return self::validateWithRules($data, $rules);
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
     * @return array
     * @throws ValidationException
     */
    public static function validateWithRules(array $data, array $rules) : array
    {
        $validator = \Validator::make($data, $rules);
        if ($validator->fails()) {
            if (Request::hasSession()) {
                Request::flash();
            }
            throw new ValidationException($validator);
        }

        return $validator->validated();
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
     * @param array       $data
     * @param string|null $occasion
     *
     * @throws ValidationException
     */
    public function validateAndUpdate(array $data, ?string $occasion = 'default')
    {
        $validatedData = $this->validateDataBeforeUpdate($data, $occasion);

        // validation succeeded and passed data is stored in $validated

        $this->fill($validatedData);
        $this->save();
    }

    /**
     * @param array       $data
     * @param string|null $occasion
     * @return array
     * @throws ValidationException
     */
    public function validateDataBeforeUpdate(array $data, ?string $occasion = 'default') : array
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
