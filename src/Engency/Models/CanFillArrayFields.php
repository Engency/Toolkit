<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait CanFillArrayFields
 *
 * @package Engency\Models
 * @property array $attributes
 * @property array $arrayFillables
 * @property bool  $exists
 */
trait CanFillArrayFields
{

    /**
     * Set a given attribute on the model.
     *
     * @param string|null $key
     * @param mixed       $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->_prependSetDateAttribute($key, $value);
        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            return $this->{$method}($value);
        } elseif (( is_array($value) || $value instanceof Collection ) && method_exists($this, $key)) {
            $this->checkAndFillArrayAttribute($key, $value);

            return $this;
        }

        $this->_prependSetJsonAttribute($key, $value);

        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if the given attribute is a date or date castable.
     *
     * @param string|null $key
     *
     * @return bool
     */
    abstract protected function isDateAttribute($key);

    /**
     * Convert a DateTime to a storable string.
     *
     * @param \DateTime|int $value
     *
     * @return string
     */
    abstract public function fromDateTime($value);

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string|null $key
     *
     * @return bool
     */
    abstract public function hasSetMutator($key);

    /**
     * Determine whether a value is JSON castable for inbound manipulation.
     *
     * @param string|null $key
     *
     * @return bool
     */
    abstract protected function isJsonCastable($key);

    /**
     * Cast the given attribute to JSON.
     *
     * @param string|null $key
     * @param mixed       $value
     *
     * @return string
     */
    abstract protected function castAttributeAsJson($key, $value);

    /**
     * Set a given JSON attribute on the model.
     *
     * @param string|null $key
     * @param mixed       $value
     *
     * @return $this
     */
    abstract public function fillJsonAttribute($key, $value);

    /**
     * @param string $key
     * @param mixed  $value
     */
    private function _prependSetDateAttribute(string $key, &$value)
    {
        if ($value && $this->isDateAttribute($key)) {
            $value = $this->fromDateTime($value);
        }
    }

    /**
     * @param string                               $key
     * @param array|\Illuminate\Support\Collection $value
     */
    private function checkAndFillArrayAttribute(string $key, $value)
    {
        if ($this->exists) {
            $this->fillArrayAttribute($key, ( $value instanceof Collection ) ? $value : collect($value));
        }
    }

    /**
     * @param string                         $key
     * @param \Illuminate\Support\Collection $value
     */
    private function fillArrayAttribute(string $key, Collection $value)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
        $relation   = $this->{$key}();
        $primaryKey = $relation->getRelated()->getKeyName();
        $toAdd      = [];

        $items = $value->mapWithKeys(
            function ($item) use ($key, $primaryKey, &$toAdd) {
                if (!isset($item[$primaryKey]) || $item[$primaryKey] === -1) {
                    $toAdd[] = $item;

                    return [];
                }

                $primary = (int) $item[$primaryKey];
                $scope   = $this->getScopeForArrayFillable($key);

                /** @var Model $instance */
                $instance = $scope->find($primary);
                if (!Model::isInstance($instance)) {
                    return [];
                }

                unset($item[$primaryKey]);

                return [$primary => $item];
            }
        );

        if ($relation instanceof HasMany) {
            $this->syncHasMany($key, $items, $toAdd, $this->guessRelatedPrimaryFromRelation($key));
        } else {
            $relation->sync($items);
        }
    }

    /**
     * @param string $key
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getScopeForArrayFillable(string $key) : Builder
    {
        $method = 'get' . Str::studly($key) . 'ScopeAsQuery';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        // Use the list of existing nodes as scope
        return $this->{$key}()->getRelated()->whereNull('Deleted');
    }

    /**
     * @param string                         $key
     * @param \Illuminate\Support\Collection $items
     * @param array                          $itemsToAdd
     * @param string                         $foreignPrimary
     *
     * @return void
     */
    private function syncHasMany(string $key, Collection $items, array $itemsToAdd, string $foreignPrimary)
    {
        $this->{$key}()->whereNotIn($foreignPrimary, $items->keys())->delete();

        // create new items
        $itemsToCreate = collect($itemsToAdd)->map(
            function ($value) use ($key) {
                $value[$this->{$key}()->getForeignKeyName()] = $this->getIdAttribute();

                return $value;
            }
        )->toArray();
        $this->{$key}()->insert($itemsToCreate);

        if ($items->count() == 0) {
            return;
        }

        // update existing items
        $items->each(
            function (array $item, int $subKey) use ($key, $foreignPrimary) {
                $this->{$key}()->where($foreignPrimary, $subKey)->update($item);
            }
        );
    }

    /**
     * @param string $relationName
     *
     * @return string
     */
    private function guessRelatedPrimaryFromRelation(string $relationName) : string
    {
        if (substr($relationName, -1, 1) === 's') {
            // remove the appending 's'
            $relationName = substr($relationName, 0, ( strlen($relationName) - 1 ));
        }

        return lcfirst($relationName . 'Id');
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    private function _prependSetJsonAttribute(string $key, &$value)
    {
        if ($this->isJsonCastable($key) && $value !== null) {
            $value = $this->castAttributeAsJson($key, $value);
        }
    }
}
