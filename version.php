<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * scorm version information.
 *
 * @package    block_exalib
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 * @author       Daniel Prieler <dprieler@gtn-solutions.com>
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015033103;      // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2013111801;    // Requires this Moodle version
$plugin->component = 'block_exalib'; // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_RC; // MATURITY_STABLE.
$plugin->release = 'v2.6-r1';
