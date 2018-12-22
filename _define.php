<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Main GaletteAuto plugin configuration
 *
 * PHP version 5
 *
 * Copyright © 2009-2014 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
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
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-10-10
 */

$this->register(
    'Galette Auto',                         //Name
    'Plugin to manage Automobile clubs',    //Short description
    'Johan Cwiklinski',                     //Author
    '1.4.0',                                //Version
    '0.9.2',                                //Galette compatible version
    'auto',                                 //routing name
    '2018-12-22',                           //Release date
    [ //routes permissions
        'vehiclesList'      => 'groupmanager',
        'myVehiclesList'    => 'member',
        'vehicleEdit'       => 'member',
        'ajaxModels'        => 'member',
        'doVehicleEdit'     => 'member',
        'batch-vehicleslist'=> 'member',
        'removeVehicle'     => 'member',
        'removeVehicles'    => 'member',
        'doRemoveVehicle'   => 'member',
        'vehicleHistory'    => 'member',
        'modelsList'        => 'groupmanager',
        'modelEdit'         => 'groupmanager',
        'doModelEdit'       => 'groupmanager',
        'removeModel'       => 'staff',
        'removeModels'      => 'staff',
        'doRemoveModel'     => 'staff',
        'batch-modelslist'  => 'staff',
        'brandsList'        => 'staff',
        'colorsList'        => 'staff',
        'statesList'        => 'staff',
        'finitionsList'     => 'staff',
        'bodiesList'        => 'staff',
        'transmissionsList' => 'staff',
        'propertyEdit'      => 'staff',
        'doPropertyEdit'    => 'staff',
        'propertyShow'      => 'staff',
        'batch-propertieslist' => 'staff',
        'removeProperty'    => 'staff',
        'removeProperties'  => 'staff',
        'doRemoveProperty'  => 'staff'
    ]
);
