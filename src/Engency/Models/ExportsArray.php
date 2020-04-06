<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Trait ExportsArray
 *
 * @package Engency\Models
 */
trait ExportsArray
{

    private $exportOccasion = false;

    /**
     * @param bool $resolveRelations
     *
     * @return array
     */
    public function toArray(bool $resolveRelations = true) : array
    {
        return $this->toOccasionArray(null, $resolveRelations);
    }

    /**
     * @param string|null $occasion
     * @param bool        $resolveRelations
     *
     * @return array
     */
    public function toOccasionArray(?string $occasion = null, bool $resolveRelations = true) : array
    {
        $returnData = [];
        if ($occasion === null) {
            $occasion = $this->exportOccasion === false ? 'default' : $this->exportOccasion;
        }

        foreach ($this->getArrayFieldsForOccasion($occasion) as $field) {
            $returnData = array_merge($returnData, $this->parseField($field, $resolveRelations));
        }

        return $returnData;
    }

    /**
     * @param string $occasion
     *
     * @return array
     */
    private function getArrayFieldsForOccasion(string $occasion = 'default') : array
    {
        if (!isset(self::$exports) || !is_array(self::$exports)) {
            return [];
        }

        if (!isset(self::$exports[$occasion]) || !is_array(self::$exports[$occasion])) {
            $occasion = 'default';
        }

        return ( isset(self::$exports[$occasion]) && is_array(self::$exports[$occasion]) ) ? self::$exports[$occasion] : [];
    }

    /**
     * @param string|array $field
     * @param bool         $resolveRelations
     *
     * @return array
     */
    private function parseField($field, bool $resolveRelations) : array
    {
        if (!is_array($field)) {
            $field = [
                $field,
                [],
            ];
        }

        if (isset($field[1]['occasion'])) {
            return $this->parseFieldAsArray($field[0], $field[1], $resolveRelations);
        }

        $value = $this->{$field[0]};
        if ($resolveRelations && $value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if ($value instanceof Carbon) {
            $value = $this->parseFieldAsDate($field[0], $value, $field[1]);
        }

        return [$field[0] => $value];
    }

    /**
     * @param string $fieldName
     * @param array  $properties
     * @param bool   $resolveRelations
     *
     * @return array
     */
    private function parseFieldAsArray(string $fieldName, array $properties, bool $resolveRelations) : array
    {
        $subOccasion = $properties['occasion'];
        /** @var \Illuminate\Database\Query\Builder $q */
        $q = $this->$fieldName();
        if ($q instanceof BelongsTo || $q instanceof HasOne) {
            return [$fieldName => $this->parseFieldAsSingleRelation($q, $properties, $resolveRelations)];
        }

        $limit = isset($properties['limit']) ? ( (int) $properties['limit'] ) : -1;
        if (isset($properties['order'])) {
            $q = $q->orderBy(...$properties['order']);
        }

        $data = [];
        $q    = $q->limit($limit)->get();
        if ($resolveRelations) {
            $data[$fieldName] = $q->map(
                function ($item) use ($subOccasion) {
                    if ($item instanceof OccasionArrayModel) {
                        return $item->toOccasionArray($subOccasion);
                    }

                    /** @var Arrayable $item */
                    return $item->toArray();
                }
            )->toArray();
        } else {
            $data[$fieldName] = $q->map(
                function ($item) use ($subOccasion) {
                    if ($item instanceof OccasionArrayModel) {
                        $item->setOccasion($subOccasion);
                    }

                    return $item;
                }
            );
        }

        if (isset($properties['countFields'])) {
            $totalCount                          = $this->$fieldName()->count();
            $data[$properties['countFields'][0]] = $totalCount;
            $data[$properties['countFields'][1]] = $resolveRelations ? count($data[$fieldName]) : $data[$fieldName]->count();
        }

        return $data;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation $query
     * @param array                                            $properties
     * @param bool                                             $resolveRelations
     *
     * @return array|mixed
     */
    private function parseFieldAsSingleRelation(Relation $query, array $properties, bool $resolveRelations)
    {
        $occasion = $properties['occasion'] ?? null;
        $item     = $query->get()->first();
        if (!$resolveRelations) {
            if ($item instanceof OccasionArrayModel) {
                $item->setOccasion($occasion);
            }

            return $item;
        }

        if ($item instanceof OccasionArrayModel) {
            return $item->toOccasionArray($occasion);
        }

        return ( $item === null ) ? null : $item->toArray();
    }

    /**
     * @param string $fieldName
     * @param Carbon $date
     * @param array  $properties
     * @return string
     */
    private function parseFieldAsDate(string $fieldName, Carbon $date, array $properties) : string
    {
        if (isset($properties[0]['dateFormat'])) {
            return $date->format($properties[0]['dateFormat']);
        }

        return $date->toISOString();
    }

    /**
     * @param string $occasion
     *
     * @return $this
     */
    public function setOccasion(string $occasion = 'default')
    {
        $this->exportOccasion = $occasion;

        return $this;
    }
}
