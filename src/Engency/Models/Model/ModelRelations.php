<?php
/**
 * This file is part of one or more VZC products.
 * All rights belong to Van Zetten consultants
 *
 * @author    frank <f.kuipers@vanzettenconsultants.nl>
 * @copyright 2019 Van Zetten consultants
 * @since     File available since 22-5-19 16:32
 */

namespace Engency\Models\Model;

/**
 * Trait ModelRelations
 *
 * @package Engency\Models\Model
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Builder
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);
        $related   = array_last(explode('\\', $namespace));

        if ($foreignKey === null) {
            $foreignKey = $this->getModelName() . 'Id';
        }

        if ($localKey === null) {
            $localKey = $related;
        }

        return parent::hasOne($namespace, $foreignKey, $localKey);
    }

    /**
     * @param string|null $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Builder
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);

        if ($foreignKey === null) {
            $foreignKey = $this->getModelName();
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Illuminate\Database\Eloquent\Builder
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $namespace = $this->getNamespaceFromClassName($related);
        $related   = array_last(explode('\\', $namespace));

        if ($foreignKey === null) {
            $foreignKey = $related;
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
        $related   = array_last(explode('\\', $namespace));

        $self = $this->getModelName();
        if ($table === null) {
            if (strcmp($self, $related) < 0) {
                $table = $self . 'Has' . $related;
            } else {
                $table = $related . 'Has' . $self;
            }
        }

        if ($foreignPivotKey === null) {
            $foreignPivotKey = $self;
        }

        if ($relatedPivotKey === null) {
            $relatedPivotKey = $related;
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
