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

namespace GaletteAuto\tests\units;

use Galette\GaletteTestCase;

/**
 * State tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class State extends GaletteTestCase
{
    protected int $seed = 20240130141727;

    /**
     * Cleanup after each test method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $delete = $this->zdb->delete(AUTO_PREFIX . \GaletteAuto\State::TABLE);
        $this->zdb->execute($delete);
        parent::tearDown();
    }

    /**
     * Test empty
     *
     * @return void
     */
    public function testEmpty(): void
    {
        $state = new \GaletteAuto\State($this->zdb);
        $this->assertSame('State', $state->getFieldLabel());

        $this->assertCount(0, $state->getList());
        $this->assertSame('0 state', $state->displayCount());
    }

    /**
     * Test add and update
     *
     * @return void
     */
    public function testCrud(): void
    {
        $state = new \GaletteAuto\State($this->zdb);
        //ensure the table is empty
        $this->assertCount(0, $state->getList());

        //Add new state
        $state->value = 'Good';
        $this->assertTrue($state->store(true));
        $first_id = $state->id;

        $this->assertCount(1, $state->getList());
        $listed_state = $state->getList()[0];
        $this->assertInstanceOf(\ArrayObject::class, $listed_state);
        $this->assertGreaterThan(0, $listed_state->id_state);
        $this->assertSame('Good', $listed_state->state);
        $this->assertSame('1 state', $state->displayCount());

        //add another one
        $state = new \GaletteAuto\State($this->zdb);
        $state->value = 'Wrec';
        $this->assertTrue($state->store(true));
        $id = $state->id;

        $this->assertCount(2, $state->getList());
        $this->assertSame('2 states', $state->displayCount());

        $state = new \GaletteAuto\State($this->zdb);
        $this->assertTrue($state->load($id));
        $state->value = 'Wreck';
        $this->assertTrue($state->store());

        $this->assertCount(2, $state->getList());
        $this->assertSame('2 states', $state->displayCount());

        $state = new \GaletteAuto\State($this->zdb);
        $this->assertTrue($state->delete([$first_id]));
        $list = $state->getList();
        $this->assertCount(1, $list);
        $last_state = $list[0];
        $this->assertSame($id, (int)$last_state->id_state);
    }

    /**
     * Test load error
     *
     * @return void
     */
    public function testLoadError(): void
    {
        $state = new \GaletteAuto\State($this->zdb);
        $this->assertFalse($state->load(999));
    }

    /**
     * Test getClassName
     *
     * @return void
     */
    public function testGetClassName(): void
    {
        $this->assertSame('\\' . \GaletteAuto\State::class, \GaletteAuto\State::getClassForPropName('state'));
    }
}
