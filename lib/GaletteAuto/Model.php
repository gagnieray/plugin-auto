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

namespace GaletteAuto;

use Analog\Analog;
use ArrayObject;
use Galette\Core\Db;
use Laminas\Db\ResultSet\ResultSet;

/**
 * Automobile Models class for galette Auto plugin
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property integer $id
 * @property string  $model
 * @property Brand   $brand
 */
class Model
{
    public const TABLE = 'models';
    public const PK = 'id_model';
    public const FIELD = 'model';

    protected int $id;
    protected string $model;
    protected Brand $brand;

    /** @var string[] */
    private array $errors;
    private Db $zdb;

    /**
     * Default constructor
     *
     * @param Db                   $zdb  Database instance
     * @param ArrayObject|int|null $args model's id to load or ResultSet. Defaults to null
     */
    public function __construct(Db $zdb, ArrayObject|int|null $args = null)
    {
        $this->zdb = $zdb;
        $this->brand = new Brand($zdb);

        if ($args instanceof ArrayObject) {
            $this->loadFromRS($args);
        } elseif (is_int($args)) {
            $this->load($args);
        }
    }

    /**
     * Load a model
     *
     * @param integer $id Id for the model we want
     *
     * @return boolean
     */
    public function load(int $id): bool
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE);
            $select->where(
                array(
                    self::PK => $id
                )
            );

            $results = $this->zdb->execute($select);
            $result = $results->current();
            if (!$result instanceof ArrayObject) {
                throw new \RuntimeException('Model not found');
            }
            $this->loadFromRS($result);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load model from id `' . $id .
                '` | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Populate object from a resultset row
     *
     * @param ArrayObject $r the resultset row
     *
     * @return void
     */
    private function loadFromRS(ArrayObject $r): void
    {
        $this->id = (int)$r->id_model;
        $this->model = $r->model;
        $id_brand = Brand::PK;
        $this->brand->load((int)$r->$id_brand);
    }

    /**
     * Store current model
     *
     * @param boolean $new New record or existing one
     *
     * @return boolean
     */
    public function store(bool $new = false): bool
    {
        try {
            $values = array(
                'model'     => $this->model,
                Brand::PK   => $this->brand->id
            );
            if ($new) {
                $insert = $this->zdb->insert(AUTO_PREFIX . self::TABLE);
                $insert->values($values);
                $this->zdb->execute($insert);
                /** @phpstan-ignore-next-line */
                $this->id = (int)$this->zdb->driver->getLastGeneratedValue(
                    $this->zdb->isPostgres() ?
                        PREFIX_DB . AUTO_PREFIX . self::TABLE . '_id_seq'
                        : null
                );
            } else {
                $update = $this->zdb->update(AUTO_PREFIX . self::TABLE);
                $update->set($values)->where(
                    array(
                        self::PK => $this->id
                    )
                );
                $this->zdb->execute($update);
            }
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot store model' .
                ' values `' . $this->id . '`, `' . implode('`, `', $values) . '` | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Delete some models
     *
     * @param array $ids Array of models id to delete
     *
     * @return boolean
     */
    public function delete(array $ids): bool
    {
        try {
            $delete = $this->zdb->delete(AUTO_PREFIX . self::TABLE);
            $delete->where->in(self::PK, $ids);
            $this->zdb->execute($delete);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot delete models from ids `' .
                implode(' - ', $ids) . '` | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    /**
     * Global isset method
     * Required for twig to access properties via __get
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }

    /**
     * Check posted values validity
     *
     * @param array $post All values to check, basically the $_POST array
     *                    after sending the form
     *
     * @return boolean
     */
    public function check(array $post): bool
    {
        $this->errors = [];
        if (!isset($post['brand']) || $post['brand'] == -1) {
            $this->errors[] = _T("- You must select a brand!", "auto");
        } else {
            $this->brand = new Brand($this->zdb, (int)$post['brand']);
        }

        if (!isset($post['model']) || $post['model'] == '') {
            $this->errors[] = _T("- You must provide a value!", "auto");
        } else {
            $this->model = $post['model'];
        }
        return count($this->errors) === 0;
    }

    /**
     * Get errors
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set brand from ID
     *
     * @param integer $id Brand ID
     *
     * @return self
     */
    public function setBrand(int $id): self
    {
        $this->brand = new Brand($this->zdb, $id);
        return $this;
    }
}
