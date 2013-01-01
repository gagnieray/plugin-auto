<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Owners list
 * Make possible to change a car's owner
 *
 * This page can be loaded directly, or via ajax.
 * Via ajax, we do not have a full html page, but only
 * that will be displayed using javascript on another page
 *
 * PHP version 5
 *
 * Copyright © 2009-2013 The Galette Team
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
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2013 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id: owners.php 556 2009-03-13 06:48:49Z trashy $
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */

use Galette\Filters\MembersList;
use Galette\Repository\Members;

define('GALETTE_BASE_PATH', '../../');
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
if ( !$login->isLogged() ) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}

// check for ajax mode
$ajax = ( isset($_GET['ajax']) && $_GET['ajax'] == 'true' ) ? true : false;

if ( isset($_SESSION['galette'][PREFIX_DB . '_' . NAME_DB]['filters']['auto']['members']) ) {
    $varslist = unserialize($_SESSION['galette'][PREFIX_DB . '_' . NAME_DB]['filters']['auto']['members']);
} else {
    $varslist = new MembersList();
}

$members = new Galette\Repository\Members();
$owners = $members->getMembersList(true);

//Set the path to the current plugin's templates,
//but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->assign('ajax', $ajax);
$tpl->assign('owners', $owners);
$tpl->compile_id = AUTO_SMARTY_PREFIX;

if ( $ajax ) {
    $tpl->assign('mode', 'ajax');
    $tpl->display('owners.tpl', AUTO_SMARTY_PREFIX);
} else {
    $tpl->assign('page_title', _T("Owners"));
    $content = $tpl->fetch('owners.tpl', AUTO_SMARTY_PREFIX);
    $tpl->assign('content', $content);
    //Set path to main Galette's template
    $tpl->template_dir = $orig_template_path;
    $tpl->display('page.tpl', AUTO_SMARTY_PREFIX);
}
