<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class \Engency\Models
 *
 * @property int    $Id
 * @property string $Name
 * @method static \Illuminate\Database\Eloquent\Builder orderBy( $column, $direction = 'asc' )
 * @method static \Illuminate\Database\Eloquent\Builder where( $column, $operator = null, $value = null, $boolean ='and' )
 * @method static \Illuminate\Database\Eloquent\Builder whereNull( $column, $boolean = 'and', $not = false )
 * @method static \Illuminate\Database\Eloquent\Builder whereIn( $column, $values, $boolean = 'and', $not = false )
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn( $column, $values, $boolean = 'and' )
 * @method static \Illuminate\Database\Eloquent\Builder withTrashed()
 * @method static static find( $id, $columns = ['*'] )
 * @method static static first()
 * @method static static create( array $attributes = [] )
 * @method static static insert( array $attributes = [] )
 * @method static static firstOrCreate( array $attributes, array $values = [] )
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHas( $relation, \Closure $callback = null,$operator = '>=', $count = 1 )
 * @method static \Illuminate\Database\Eloquent\Builder select( $columns = ['*'] )
 * @package Engency\Models
 */
abstract class Model extends EloquentModel
{

    use SoftDeletes;
    use ModelReflection, ModelRelations;

    protected static $exports = [];

    public $timestamps = true;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $model = $this->getModelName();

        $this->table      = $model;
        $this->primaryKey = $model . 'Id';

        parent::__construct($attributes);
    }

    /**
     * @param string|null $identifier
     *
     * @return Model|null
     */
    public static function resolveFromRouteParameter(?string $identifier) : ?Model
    {
        return static::find((int) $identifier);
    }

    /**
     * @return int
     */
    public function getIdAttribute()
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Get the queueable relationships for the entity.
     *
     * @return array
     */
    public function getQueueableRelations()
    {
        return [];
    }

    /**
     * @return static
     */
    public static function getLastInserted() : self
    {
        try {
            $selfReflection = new \ReflectionClass(static::class);

            /** @var static $value */
            $value = static::orderBy($selfReflection->getShortName() . 'Id', 'DESC')->first();
        } catch (\Exception $e) {
        }

        return $value ?? null;
    }
}
