<?php
// This file is part of Exabis Library
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Library is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

namespace block_exalib;

const CATEGORY_TAGS = 1;

const CAP_USE = 'use';
const CAP_MANAGE_REVIEWERS = 'manage_reviewers';
const CAP_MANAGE_CONTENT = 'manage_content';
const CAP_MANAGE_CATS = 'manage_cats';
const CAP_COURSE_SETTINGS = 'course_settings';

const ITEM_STATE_NEW = -2;
const ITEM_STATE_IN_REVIEW = -1;

function get_string($identifier, $component = null, $a = null) {
	$manager = get_string_manager();

	if ($component === null)
		$component = 'block_exalib';

	if (\block_exalib_course_settings::alternative_wording() && $manager->string_exists('alt_'.$identifier, $component))
		return $manager->get_string('alt_'.$identifier, $component, $a);

	if ($manager->string_exists($identifier, $component))
		return $manager->get_string($identifier, $component, $a);

	return $manager->get_string($identifier, '', $a);
}