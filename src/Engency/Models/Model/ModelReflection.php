<?php
/**
 * This file is part of one or more VZC products.
 * All rights belong to Van Zetten consultants
 *
 * @author    frank <f.kuipers@vanzettenconsultants.nl>
 * @copyright 2019 Van Zetten consultants
 * @since     File available since 22-5-19 16:28
 */

namespace Engency\Models\Model;

use ReflectionClass;
use ReflectionException;

/**
 * Trait ModelReflection
 *
 * @package Engency\Models\Model
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
     * @param \App\Models\Model|mixed $object
     *
     * @return bool
     */
    public static function isInstance($object)
    {
        return is_a($object, static::class) && $object->exists;
    }
}
