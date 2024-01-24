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

namespace GaletteAuto\Filters;

use Galette\Core\Pagination;
use Laminas\Db\Sql\Select;

/**
 * Autos list filters and paginator
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class AutosList extends Pagination
{
    /**
     * Returns the field we want to default set order to
     *
     * @return string field name
     */
    protected function getDefaultOrder(): string
    {
        return 'car_name';
    }

    /**
     * Add SQL limit
     *
     * @param Select $select Original select
     *
     * @return self
     */
    public function setLimit(Select $select): self
    {
        $this->setLimits($select);
        return $this;
    }


    /**
     * Build href
     *
     * @param int $page Page
     *
     * @return string
     */
    protected function getHref(int $page): string
    {
        $args = [
            'option'    => 'page',
            'value'     => $page
        ];

        if ($this->view->getEnvironment()->getGlobals()['cur_subroute']) {
            $args['type'] = $this->view->getEnvironment()->getGlobals()['cur_subroute'];
        }

        if ($this->view->getEnvironment()->getGlobals()['cur_route'] === 'memberVehiclesList') {
            $args['id'] = $this->view->getEnvironment()->getGlobals()['cur_subroute'];
        }

        $href = $this->routeparser->urlFor(
            $this->view->getEnvironment()->getGlobals()['cur_route'],
            $args
        );
        return $href;
    }
}
