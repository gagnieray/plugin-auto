<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile class for galette Auto plugin
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
 * @since     Available since 0.7dev - 2009-03-16
 */

namespace GaletteAuto;

use Analog\Analog;
use Galette\Core\Db;
use Galette\Core\Plugins;
use Galette\Entity\Adherent;
use Zend\Db\Sql\Expression;
use GaletteAuto\Color;
use GaletteAuto\State;
use GaletteAuto\Finition;
use GaletteAuto\Body;
use GaletteAuto\Transmission;

/**
 * Automobile Transmissions class for galette Auto plugin
 *
 * @category  Plugins
 * @name      Auto
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class Auto
{
    const TABLE = 'cars';
    const PK = 'id_car';

    private $plugins;
    private $zdb;

    private $fields = array(
        'id_car'                        => 'integer',
        'car_name'                      => 'string',
        'car_registration'              => 'string',
        'car_first_registration_date'   => 'date',
        'car_first_circulation_date'    => 'date',
        'car_mileage'                   => 'integer',
        'car_comment'                   => 'string',
        'car_creation_date'             => 'date',
        'car_chassis_number'            => 'string',
        'car_seats'                     => 'integer',
        'car_horsepower'                => 'integer',
        'car_engine_size'               => 'integer',
        'car_fuel'                      => 'integer',
        Color::PK                       => 'integer',
        Body::PK                        => 'integer',
        State::PK                       => 'integer',
        Transmission::PK                => 'integer',
        Finition::PK                    => 'integer',
        Model::PK                       => 'integer',
        Adherent::PK                    => 'integer'
    );

    private $id;                       //identifiant
    private $registration;             //immatriculation
    private $name;                     //petit nom
    private $first_registration_date;  //date de première immatriculation
    private $first_circulation_date;   //date de prmière mise en service
    private $mileage;                  //kilométrage
    private $comment;                  //commentaire
    private $chassis_number;           //numéro de chassis
    private $seats;                    //nombre de places
    private $horsepower;               //puissance fiscale
    private $engine_size;              //cylindrée
    private $creation_date;            //date de création
    private $fuel;                     //carburant

    //External objects
    private $picture;                  //photo de la voiture
    private $finition;                 //niveau de finition
    private $color;                    //couleur
    private $model;                    //modèle
    private $transmission;             //type de transmission
    private $body;                     //carrosserie
    private $history;                  //historique
    private $owner;                    //propriétaire actuel
    private $state;                    //état actuel

    const FUEL_PETROL = 1;
    const FUEL_DIESEL = 2;
    const FUEL_GAS = 3;
    const FUEL_ELECTRICITY = 4;
    const FUEL_BIO = 5;

    private $propnames;                //textual properties names

    //do we have to fire an history entry?
    private $fire_history = false;

    //internal properties (not updatable outside the object)
    private $internals = array (
        'id',
        'creation_date',
        'history',
        'picture',
        'propnames',
        'internals',
        'fields',
        'fire_history',
        'plugins',
        'zdb'
    );
    private $errors = [];

    /**
     * Default constructor
     *
     * @param Plugins   $plugins Plugins
     * @param Db        $zdb     Database instance
     * @param ResultSet $args    A resultset row to load
     */
    public function __construct(Plugins $plugins, Db $zdb, $args = null)
    {
        $this->plugins = $plugins;
        $this->zdb = $zdb;

        $this->propnames = array(
            'name'                      => _T("name", "auto"),
            'model'                     => _T("model", "auto"),
            'registration'              => _T("registration", "auto"),
            'first_registration_date'   => _T("first registration date", "auto"),
            'first_circulation_date'    => _T("first circulation date", "auto"),
            'mileage'                   => _T("mileage", "auto"),
            'seats'                     => _T("seats", "auto"),
            'horsepower'                => _T("horsepower", "auto"),
            'engine_size'               => _T("engine size", "auto"),
            'color'                     => _T("color", "auto"),
            'state'                     => _T("state", "auto"),
            'finition'                  => _T("finition", "auto"),
            'transmission'              => _T("transmission", "auto"),
            'body'                      => _T("body", "auto")
        );

        $this->model = new Model($this->zdb);
        $this->color = new Color($this->zdb);
        $this->state = new State($this->zdb);

        $deps = array(
            'picture'   => false,
            'groups'    => false,
            'dues'      => false
        );
        $this->owner = new Adherent($this->zdb, null, $deps);
        $this->transmission = new Transmission($this->zdb);
        $this->finition = new Finition($this->zdb);
        $this->picture = new Picture($this->plugins);
        $this->body = new Body($this->zdb);
        $this->history = new History($this->zdb);
        if (is_object($args)) {
            $this->loadFromRS($args);
        }
    }

    /**
     * Loads a car from its id
     *
     * @param integer $id the identifiant for the car to load
     *
     * @return boolean
     */
    public function load($id)
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE);
            $select->where(
                array(
                    self::PK => $id
                )
            );

            $results = $this->zdb->execute($select);
            $this->loadFromRS($results->current());
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load car from id `' . $id .
                '` | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Populate object from a resultset row
     *
     * @param ResultSet $r a resultset row
     *
     * @return void
     */
    private function loadFromRS($r)
    {
        $pk = self::PK;
        $this->id = $r->$pk;
        $this->registration = $r->car_registration;
        $this->name = $r->car_name;
        $this->first_registration_date = $r->car_first_registration_date;
        $this->first_circulation_date = $r->car_first_circulation_date;
        $this->mileage = $r->car_mileage;
        $this->comment = $r->car_comment;
        $this->chassis_number = $r->car_chassis_number;
        $this->seats = $r->car_seats;
        $this->horsepower = $r->car_horsepower;
        $this->engine_size = $r->car_engine_size;
        $this->creation_date = $r->car_creation_date;
        $this->fuel = $r->car_fuel;
        //External objects
        $this->picture = new Picture($this->plugins, (int)$this->id);
        $fpk = Finition::PK;
        $this->finition->load((int)$r->$fpk);
        $cpk = Color::PK;
        $this->color->load((int)$r->$cpk);
        $mpk = Model::PK;
        $this->model->load((int)$r->$mpk);
        $tpk = Transmission::PK;
        $this->transmission->load((int)$r->$tpk);
        $bpk = Body::PK;
        $this->body->load((int)$r->$bpk);
        $opk = Adherent::PK;
        $this->owner->load((int)$r->$opk);
        $spk = State::PK;
        $this->state->load((int)$r->$spk);
        $this->history->load((int)$this->id);
    }

    /**
     * Return the list of available fuels
     *
     * @return array
     */
    public function listFuels()
    {
        $f = array(
            self::FUEL_PETROL       => _T("Petrol", "auto"),
            self::FUEL_DIESEL       => _T("Diesel", "auto"),
            self::FUEL_GAS          => _T("Gas", "auto"),
            self::FUEL_ELECTRICITY  => _T("Electricity", "auto"),
            self::FUEL_BIO          => _T("Bio", "auto")
        );
        return $f;
    }

    /**
     * Stores the vehicle in the database
     *
     * @param boolean $new true if it's a new record, false to update on
     *                     that already exists. Defaults to false
     *
     * @return boolean
     */
    public function store($new = false)
    {
        global $hist;

        if ($new) {
            $this->creation_date = date('Y-m-d');
        }

        try {
            $values = array();

            foreach ($this->fields as $k => $v) {
                switch ($k) {
                    case self::PK:
                        break;
                    case Color::PK:
                        $values[$k] = $this->color->id;
                        break;
                    case Body::PK:
                        $values[$k] = $this->body->id;
                        break;
                    case State::PK:
                        $values[$k] = $this->state->id;
                        break;
                    case Transmission::PK:
                        $values[$k] = $this->transmission->id;
                        break;
                    case Finition::PK:
                        $values[$k] = $this->finition->id;
                        break;
                    case Model::PK:
                        $values[$k] = $this->model->id;
                        break;
                    case Adherent::PK:
                        $values[$k] = $this->owner->id;
                        break;
                    default:
                        $propName = substr($k, 4, strlen($k));
                        switch ($v) {
                            case 'string':
                            case 'date':
                                $values[$k] = $this->$propName;
                                break;
                            case 'integer':
                                $values[$k] = (
                                    ($this->$propName != 0 && $this->$propName != '')
                                        ? $this->$propName
                                        : new Expression('NULL')
                                );
                                break;
                            default:
                                $values[$k] = $this->$propName;
                                break;
                        }
                        break;
                }
            }

            if ($new === true) {
                $insert = $this->zdb->insert(AUTO_PREFIX . self::TABLE);
                $insert->values($values);
                $add = $this->zdb->execute($insert);

                if ($add->count() > 0) {
                    if ($this->zdb->isPostgres()) {
                        $this->id = $this->zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . AUTO_PREFIX . 'cars_id_seq'
                        );
                    } else {
                        $this->id = $this->zdb->driver->getLastGeneratedValue();
                    }

                    // logging
                    $hist->add(
                        _T("New car added", "auto"),
                        strtoupper($this->name)
                    );
                } else {
                    $hist->add(_T("Fail to add new car.", "auto"));
                    throw new Exception(
                        'An error occured inserting new car!'
                    );
                }
            } else {
                $update = $this->zdb->update(AUTO_PREFIX . self::TABLE);
                $update->set($values)->where(
                    array(
                        self::PK => $this->id
                    )
                );
                $edit = $this->zdb->execute($update);
                //edit == 0 does not mean there were an error, but that there
                //were nothing to change
                if ($edit->count() > 0) {
                    $hist->add(
                        _T("Car updated", "auto"),
                        strtoupper($this->name)
                    );
                }
            }

            //if all goes well, we check to add an entry into car's history
            $h = $this->history->getLatest();
            if ($h !== false) {
                foreach ($h as $k => $v) {
                    if ($k != 'history_date' && $this->$k != $v) {
                        //if one has been modified, we flag to add an entry event
                        $this->fire_history = true;
                        break;
                    }
                }
            } elseif (!$new) {
                //no history entry... yet! Let's create one.
                $this->fire_history = true;
            }

            if ($this->fire_history) {
                $h_props = array();
                foreach ($this->history->fields as $prop) {
                    if ($prop != 'history_date') {
                        $h_props[$prop] = $this->$prop;
                    } else {
                        $h_props[$prop] = date('Y-m-d H:i:s');
                    }
                }
                $this->history->register($h_props);
                $this->fire_history = false;
            }

            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] An error has occured ' .
                (($new)?'inserting':'updating') . ' car | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * List object's properties
     *
     * @param boolean $restrict true to exclude $this->internals from returned
     *                          result, false otherwise. Default to false
     *
     * @return array
     */
    private function getAllProperties($restrict = false)
    {
        $result = array();
        foreach ($this as $key => $value) {
            if (!$restrict
                || ($restrict && !in_array($key, $this->internals))
            ) {
                $result[] = $key;
            }
        }
        return $result;
    }

    /**
     * Get object's properties. List only properties that can be modified
     *   externally (ie. not in $this->internals)
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->getAllProperties(true);
    }

    /**
     * Does the current car has a picture?
     *
     * @return boolean
     */
    public function hasPicture()
    {
        return $this->picture->hasPicture();
    }

    /**
     * Set car's owner to current logged user
     *
     * @param Login $login Login instance
     *
     * @return void
     */
    public function appropriateCar($login)
    {
        $this->owner->load($login->id);
    }

    /**
     * Returns plain text property name, generally used for translations
     *
     * @param string $name property name
     *
     * @return string property
     */
    public function getPropName($name)
    {
        if (isset($this->propnames[$name])) {
            return $this->propnames[$name];
        } else {
            throw new \UnexpectedValueException('Unknown propname ' . $name);
        }
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return false|object the called property
     */
    public function __get($name)
    {
        $forbidden = array();
        if (!in_array($name, $forbidden)) {
            switch ($name) {
                case self::PK:
                    return $this->id;
                    break;
                case Adherent::PK:
                    return $this->owner->id;
                    break;
                case Color::PK:
                    return $this->color->id;
                    break;
                case State::PK:
                    return $this->state->id;
                    break;
                case 'car_registration':
                    return $this->registration;
                    break;
                case 'first_registration_date':
                case 'first_circulation_date':
                case 'creation_date':
                    if ($this->$name != '') {
                        try {
                            $d = new \DateTime($this->$name);
                            return $d->format(_T("Y-m-d"));
                        } catch (\Exception $e) {
                            //oops, we've got a bad date :/
                            Analog::log(
                                'Bad date (' . $his->$name . ') | ' .
                                $e->getMessage(),
                                Analog::WARNING
                            );
                            return $this->$name;
                        }
                    }
                    break;

                    break;
                case Color::PK:
                    return $this->color->id;
                    break;
                case 'picture':
                    return $this->picture;
                    break;
                default:
                    if (isset($this->$name)) {
                        return $this->$name;
                    } elseif (!property_exists($this, $name)) {
                        Analog::log(
                            '[' . get_class($this) . '] Property ' . $name .
                            ' does not exists',
                            Analog::WARNING
                        );
                    }
                    break;
            }
        } else {
            Analog::log(
                '[' . get_class($this) . '] Unable to retrieve `' . $name . '`',
                Analog::INFO
            );
            return false;
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param object $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if (!in_array($name, $this->internals)) {
            switch ($name) {
                case 'finition':
                    $this->finition->load((int)$value);
                    break;
                case 'color':
                    $this->color->load((int)$value);
                    break;
                case 'model':
                    $this->model->load((int)$value);
                    break;
                case 'transmission':
                    $this->transmission->load((int)$value);
                    break;
                case 'body':
                    $this->body->load((int)$value);
                    break;
                case 'owner':
                    $this->owner->load((int)$value);
                    break;
                case 'state':
                    $this->state->load((int)$value);
                    break;
                default:
                    $this->$name = $value;
                    break;
            }
        } else {
            Analog::log(
                '[' . get_class($this) . '] Trying to set an internal property (`' .
                $name . '`)',
                Analog::INFO
            );
            return false;
        }
    }

    /**
     * Check posted values validity
     *
     * @param array $post All values to check, basically the $_POST array
     *                    after sending the form
     *
     * @return boolean
     */
    public function check($post)
    {
        $this->errors = [];
        /** TODO: make required fields dynamic, as in main Galette */
        $required = array(
            'name'                      => 1,
            'model'                     => 1,
            'first_registration_date'   => 1,
            'first_circulation_date'    => 1,
            'color'                     => 1,
            'state'                     => 1,
            'registration'              => 1,
            'body'                      => 1,
            'transmission'              => 1,
            'finition'                  => 1,
            'fuel'                      => 1
        );

        //check for required fields, and correct values
        foreach ($this->getProperties(true) as $prop) {
            $value = isset($post[$prop]) ? $post[$prop] : null;

            if (($value == '' || $value == null) && in_array($prop, array_keys($required))) {
                $this->errors[] = str_replace(
                    '%s',
                    '<a href="#' . $prop . '">' . $this->getPropName($prop) . '</a>',
                    _T("- Mandatory field %field empty.")
                );
                continue;
            }

            switch ($prop) {
                //string values, no check
                case 'name':
                case 'comment':
                //string values with special check?
                case 'chassis_number':
                case 'registration':
                    $this->$prop = $value;
                    break;
                //dates
                case 'first_registration_date':
                case 'first_circulation_date':
                    if (preg_match("@^([0-9]{2})/([0-9]{2})/([0-9]{4})$@", $value, $array_jours)) {
                        if (checkdate($array_jours[2], $array_jours[1], $array_jours[3])) {
                            $value = $array_jours[3].'-'.$array_jours[2].'-'.$array_jours[1];
                            $this->$prop = $value;
                        } else {
                            $this->errors[] = str_replace(
                                '%s',
                                $this->getPropName($prop),
                                _T("- Non valid date for %s!")
                            );
                        }
                    } else {
                        $this->errors[] = str_replace(
                            '%s',
                            $this->getPropName($prop),
                            _T("- Wrong date format for %s (dd/mm/yyyy)!")
                        );
                    }
                    break;
                //numeric values
                case 'mileage':
                case 'seats':
                case 'horsepower':
                case 'engine_size':
                    if (is_int((int)$value)) {
                        $this->$prop = $value;
                    } elseif ($value != '') {
                        $this->errors[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' .$this->getPropName($prop) . '</a>',
                            _T("- You must enter a positive integer for %s")
                        );
                    }
                    break;
                //constants
                case 'fuel':
                    if (in_array($value, array_keys($this->listFuels()))) {
                        $this->fuel = $value;
                    } else {
                        $this->errors[] = _T("- You must choose a fuel in the list");
                    }
                    break;
                //external objects
                case 'finition':
                case 'color':
                case 'model':
                case 'transmission':
                case 'body':
                case 'state':
                    if ($value > 0) {
                        $this->$prop->load($value);
                    } else {
                        $class = 'GaletteAuto\\' . ucwords($prop);
                        $name = $class::FIELD;
                        $this->errors[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' . $this->getPropName($name) . '</a>',
                            _T("- You must choose a %s in the list")
                        );
                    }
                    break;
                case 'owner':
                    $value = (int)$value;
                    if ($value > 0) {
                        $this->$prop->load($value);
                    } else {
                        $this->errors[] = _T("- you must attach an owner to this car");
                    }
                    break;
                default:
                    /** TODO: what's the default? */
                    Analog::log(
                        'Trying to edit an Auto property that is not handled in the source code! (prop is: ' .
                        $prop . ')',
                        Analog::ERROR
                    );
                    break;
            }//switch
        }//foreach

        // picture upload
        if (isset($_FILES['photo'])) {
            if ($_FILES['photo']['tmp_name'] != '') {
                if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
                    $res = $this->picture->store($_FILES['photo']);
                    if ($res < 0) {
                        switch ($res) {
                            case Picture::INVALID_FILE:
                                $patterns = array('|%s|', '|%t|');
                                $replacements = array(
                                    $this->picture->getAllowedExts(),
                                    htmlentities($this->picture->getBadChars())
                                );
                                $this->errors[] = preg_replace(
                                    $patterns,
                                    $replacements,
                                    _T("- Filename or extension is incorrect. Only %s files are allowed. File name should not contains any of: %t")
                                );
                                break;
                            case Picture::FILE_TOO_BIG:
                                $this->errors[] = preg_replace(
                                    '|%d|',
                                    Picture::MAX_FILE_SIZE,
                                    _T("File is too big. Maximum allowed size is %d")
                                );
                                break;
                            case Picture::MIME_NOT_ALLOWED:
                                /** FIXME: should be more descriptive */
                                $this->errors[] = _T("Mime-Type not allowed");
                                break;
                            case Picture::SQL_ERROR:
                            case Picture::SQL_BLOB_ERROR:
                                $this->errors[] = _T("An SQL error has occured.");
                                break;
                        }
                    }
                }
            }
        }

        //delete photo
        if (isset($post['del_photo'])) {
            if (!$this->picture->delete()) {
                $this->errors[]
                    = _T("An error occured while trying to delete car's photo");
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
