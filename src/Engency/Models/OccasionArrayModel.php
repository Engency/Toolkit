<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Models;

/**
 * Interface OccasionArrayable
 *
 * @package Engency\Models
 */
interface OccasionArrayModel
{

    /**
     * @param string $occasion
     * @param bool   $resolveRelations
     *
     * @return array
     */
    public function toOccasionArray(string $occasion = 'default', bool $resolveRelations = true);

    /**
     * @param string $occasion
     *
     * @return $this
     */
    public function setOccasion(string $occasion = 'default');
}
