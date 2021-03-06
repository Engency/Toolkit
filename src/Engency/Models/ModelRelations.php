<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use Illuminate\Support\Arr;

/**
 * Trait ModelRelations
 *
 * @package Engency\Models
 */
trait ModelRelations
{

    /**
     * @return string
     */
    abstract protected function getModelName() : string;

    /**
     * @param string $className
     *
     * @return string
     */
    private function getNamespaceFromClassName(string $className)
    {
        if (strpos($className, 'App\\') === 0 || strpos($className, 'Modules\\') === 0) {
            return '\\' . $className;
        }

        return '\\App\\Models\\' . $className;
    }

    /**
     * @param string|null $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);

        if ($foreignKey === null) {
            $foreignKey = $this->getModelName() . 'Id';
        }

        if ($localKey === null) {
            $localKey = $this->getModelName() . 'Id';
        }

        return parent::hasOne($namespace, $foreignKey, $localKey);
    }

    /**
     * @param string|null $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);

        if ($foreignKey === null) {
            $foreignKey = $this->getModelName() . 'Id';
        }

        if ($localKey === null) {
            $localKey = $this->getModelName() . 'Id';
        }

        return parent::hasMany($namespace, $foreignKey, $localKey);
    }

    /**
     * @param string|null $related
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param string|null $relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);
        $related   = lcfirst(Arr::last(explode('\\', $namespace)));

        if ($foreignKey === null) {
            $foreignKey = $related . 'Id';
        }

        if ($ownerKey === null) {
            $ownerKey = $related . 'Id';
        }

        return parent::belongsTo($namespace, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param string|null $related
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @param string|null $relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToMany(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    )
    {
        $namespace = $this->getNamespaceFromClassName($related);
        $related   = Arr::last(explode('\\', $namespace));

        $self = $this->getModelName();
        if ($table === null) {
            if (strcmp($self, lcfirst($related)) < 0) {
                $table = ucfirst($self) . 'Has' . $related;
            } else {
                $table = $related . 'Has' . ucfirst($self);
            }
        }

        if ($foreignPivotKey === null) {
            $foreignPivotKey = $self . 'Id';
        }

        if ($relatedPivotKey === null) {
            $relatedPivotKey = lcfirst($related) . 'Id';
        }

        return parent::belongsToMany(
            $namespace,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relation
        );
    }
}
