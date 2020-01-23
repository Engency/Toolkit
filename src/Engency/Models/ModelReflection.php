<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use ReflectionClass;
use ReflectionException;

/**
 * Trait ModelReflection
 *
 * @package Engency\Models
 */
trait ModelReflection
{
    /**
     * @var string|null
     */
    protected $modelName = null;

    /**
     * @return string
     */
    protected function getModelName() : string
    {
        if ($this->modelName === null) {
            try {
                $selfReflection  = new ReflectionClass(static::class);
                $this->modelName = $selfReflection->getShortName();
            } catch (ReflectionException $e) {
                // this theoratically won't ever happen
                return null;
            }
        }

        return $this->modelName;
    }

    /**
     * @param Model|mixed $object
     *
     * @return bool
     */
    public static function isInstance($object)
    {
        return is_a($object, static::class) && $object->exists;
    }
}
