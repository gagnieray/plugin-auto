<?php

/**
 * Copyright © 2003-2024 The Galette Team
 *
 * This file is part of Galette (https://galette.eu).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace GaletteAuto\Repository;

use Galette\Core\Db;
use Galette\Core\Preferences;
use Galette\Core\Login;
use Galette\Repository\Repository;
use GaletteAuto\Model;
use GaletteAuto\Brand;
use GaletteAuto\Filters\ModelsList;
use Analog\Analog;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;

/**
 * Models repository management
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class Models extends Repository
{
    public const TABLE = Model::TABLE;
    public const PK = Model::PK;

    private int $count;

    /**
     * Main constructor
     *
     * @param Db          $zdb         Database instance
     * @param Preferences $preferences Galette preferences
     * @param Login       $login       Logged in instance
     * @param ModelsList  $filters     Filters
     */
    public function __construct(Db $zdb, Preferences $preferences, Login $login, ModelsList $filters)
    {
        parent::__construct($zdb, $preferences, $login, null, 'GaletteAuto', AUTO_PREFIX);
        $this->setFilters($filters);
    }

    /**
     * Get the list of all models
     *
     * @param integer $brandId   Optional brand we want models for
     * @param boolean $as_object Whether to return an array of objects or a ResultSet
     *
     * @return array<int, Model>|ResultSet
     */
    public function getList(int $brandId = null, bool $as_object = true): array|ResultSet
    {
        $select = $this->buildSelect();

        if ($brandId !== null) {
            $select->where(
                array(
                    'm.' . Brand::PK => $brandId
                )
            );
        } else {
            $this->filters->setLimits($select);
        }
        $results = $this->zdb->execute($select);

        if ($as_object) {
            $models = array();
            foreach ($results as $r) {
                $pk = self::PK;
                $models[$r->$pk] = new Model($this->zdb, $r);
            }
            return $models;
        } else {
            return $results;
        }
    }

    /**
     * Builds the SELECT statement
     *
     * @return Select SELECT statement
     */
    private function buildSelect(): Select
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE, 'm');
            $select->join(
                array('b' => PREFIX_DB . AUTO_PREFIX . Brand::TABLE),
                'm.' . Brand::PK . '= b.' . Brand::PK
            );
            $select->order(self::buildOrderClause());
            $this->proceedCount($select);

            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for models | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }

    /**
     * Builds the order clause
     *
     * @return array SQL ORDER clause
     */
    private function buildOrderClause(): array
    {
        $order = array();

        switch ($this->filters->orderby) {
            case ModelsList::ORDERBY_BRAND:
                $order[] = 'b.brand ' . $this->filters->ordered;
                break;
            default:
            case ModelsList::ORDERBY_MODEL:
                $order[] = 'm.model ' . $this->filters->ordered;
                break;
        }

        return $order;
    }

    /**
     * Count contributions from the query
     *
     * @param Select $select Original select
     *
     * @return void
     */
    private function proceedCount(Select $select): void
    {
        try {
            $countSelect = clone $select;
            $countSelect->reset($countSelect::COLUMNS);
            $countSelect->reset($countSelect::JOINS);
            $countSelect->reset($countSelect::ORDER);
            $countSelect->columns(
                array(
                    self::PK => new Expression('COUNT(' . self::PK . ')')
                )
            );

            $results = $this->zdb->execute($countSelect);
            $result = $results->current();

            $k = self::PK;
            $this->count = $result->$k;

            if ($this->count > 0) {
                $this->filters->setCounter($this->count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count models | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }

    /**
     * Add default values in database
     *
     * @param bool $check_first Check first if it seems initialized, defaults to true
     *
     * @return bool
     */
    public function installInit(bool $check_first = true): bool
    {
        return true;
    }

    /**
     * Get count for current query
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
