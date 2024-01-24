<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Finitions class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2014 The Galette Team
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
 *
 * @category  Plugins
 * @package   GaletteAuto
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      https://galette.eu
 * @since     Available since 0.7dev - 2009-03-16
 */

namespace GaletteAuto;

use Galette\Core\Db;

/**
 * Automobile Finitions class for galette Auto plugin
 *
 * @category  Plugins
 * @name      Finition
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     Available since 0.7dev - 2009-03-16
 */
class Finition extends AbstractObject
{
    public const TABLE = 'finitions';
    public const PK = 'id_finition';
    public const FIELD = 'finition';
    public const NAME = 'finitions';

    /**
     * Default constructor
     *
     * @param Db       $zdb Database instance
     * @param ?integer $id  finition's id to load. Defaults to null
     */
    public function __construct(Db $zdb, int $id = null)
    {
        parent::__construct(
            $zdb,
            self::TABLE,
            self::PK,
            self::FIELD,
            self::NAME,
            $id
        );
    }

    /**
     * Get field label
     *
     * @return string
     */
    public function getFieldLabel(): string
    {
        return _T('Finition', 'auto');
    }

    /**
     * Get property route name
     *
     * @return string
     */
    public function getRouteName(): string
    {
        return 'finition';
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name)
    {
        if ($name == self::FIELD) {
            return parent::__get('value');
        } else {
            return parent::__get($name);
        }
    }

    /**
     * Get localized count string for object list
     *
     * @return string
     */
    protected function getLocalizedCount(): string
    {
        return _Tn(
            '%count finition',
            '%count finitions',
            $this->getCount(),
            'auto'
        );
    }
}
