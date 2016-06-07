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

if (!defined('BLOCK_EXALIB_IS_ADMIN_MODE')) {
	define('BLOCK_EXALIB_IS_ADMIN_MODE', 0);
}

require __DIR__.'/inc.php';


if (BLOCK_EXALIB_IS_ADMIN_MODE) {
	block_exalib_require_global_cap(\block_exalib\CAP_MANAGE_CONTENT);
} else {
	block_exalib_require_global_cap(\block_exalib\CAP_USE);
}


$urloverview = new moodle_url('/blocks/exalib');
$urlpage = block_exalib_new_moodle_url();
$urlsearch = new moodle_url($urlpage, array('page' => null, 'q' => null, 'category_id' => null));
$urladd = new moodle_url($urlpage, array('show' => 'add'));
$urlcategory = new moodle_url($urlpage, array('page' => null, 'q' => null, 'category_id' => null));

$PAGE->set_url($urlpage);

$categoryid = optional_param('category_id', '', PARAM_INT);
$filterid = 0;

/* $FILTER_CATEGORY = $DB->get_record("block_exalib_category", array('id' => $filterid));
 if ($FILTER_CATEGORY) $PAGE->navbar->add($FILTER_CATEGORY->name); */
/*
if (BLOCK_EXALIB_IS_ADMIN_MODE) {
    $PAGE->navbar->add(get_string('administration', 'block_exalib'), 'admin.php');
}
*/


$mgr = new block_exalib_category_manager(BLOCK_EXALIB_IS_ADMIN_MODE);
$currentcategory = $mgr->getcategory($categoryid);
$currentcategorysubids = $currentcategory ? $currentcategory->self_inc_all_sub_ids : array(-9999);
$currentcategoryparents = $mgr->getcategoryparentids($categoryid);

if (BLOCK_EXALIB_IS_ADMIN_MODE) {
	require('admin.actions.inc.php');
}

$perpage = 20;
$page = optional_param('page', 0, PARAM_INT);

$items = null;
$pagingbar = null;
$show = null;

if (BLOCK_EXALIB_IS_ADMIN_MODE) {
	$sqlwhere = "";
} else {
	$sqlwhere = "AND item.online > 0
        AND (item.online_from=0 OR item.online_from IS NULL
        OR (item.online_from <= ".time()."
        AND item.online_to >= ".time()."))";
}

if ($q = optional_param('q', '', PARAM_TEXT)) {
	$show = 'search';

	$q = trim($q);

	$qparams = preg_split('!\s+!', $q);

	$sqljoin = "";
	$sqlparams = array();

	if ($currentcategory) {
		$sqlwhere .= " AND item.id IN (".join(',', [0] + $currentcategory->item_ids_inc_subs).")";
	}

	foreach ($qparams as $i => $qparam) {
		$search_fields = [
			'item.link', 'item.source', 'item.file', 'item.name', 'item.authors',
			'item.abstract', 'item.content', 'item.link_titel', "c$i.name",
		];

		$sqljoin .= " LEFT JOIN {block_exalib_item_category} ic$i ON item.id=ic$i.item_id";
		$sqljoin .= " LEFT JOIN {block_exalib_category} c$i ON ic$i.category_id=c$i.id";
		$sqlwhere .= " AND ".$DB->sql_concat_join("' '", $search_fields)." LIKE ?";
		$sqlparams[] = "%".$DB->sql_like_escape($qparam)."%";
	}

	$sql = "SELECT COUNT(DISTINCT item.id)
		FROM {block_exalib_item} AS item
		$sqljoin
		WHERE 1=1 $sqlwhere
	";
	$count = $DB->get_field_sql($sql, $sqlparams);

	$pagingbar = new paging_bar($count, $page, $perpage, $urlpage);

	$sql = "SELECT item.*
    FROM {block_exalib_item} item
    $sqljoin
    WHERE 1=1 $sqlwhere
    GROUP BY item.id
    ORDER BY name";

	$items = $DB->get_records_sql($sql, $sqlparams, $page * $perpage, $perpage);

} elseif ($currentcategory) {
	$show = 'category';

	$count = $currentcategory->cnt_inc_subs;

	$pagingbar = new paging_bar($count, $page, $perpage, $urlpage);

	$sql = "
		SELECT item.*
		FROM {block_exalib_item} item
		WHERE 1=1
			AND item.id IN (".join(',', [0] + $currentcategory->item_ids_inc_subs).")
			$sqlwhere
		ORDER BY GREATEST(time_created,time_modified) DESC
    ";

	$items = $DB->get_records_sql($sql, array(), $page * $perpage, $perpage);
} else {
	// Latest changes.
	$show = 'latest_changes';

	$sql = "
		SELECT item.*
		FROM {block_exalib_item} AS item
		WHERE 1=1 $sqlwhere
		ORDER BY GREATEST(time_created,time_modified) DESC
	";

	$items = $DB->get_records_sql($sql, array(), 0, 20);
}

$output = block_exalib_get_renderer();

echo $output->header(BLOCK_EXALIB_IS_ADMIN_MODE ? 'tab_manage_content' : null);

?>
	<div class="block_exalib_lib">

		<?php

		/*
		if (false && !$filterid) {
				?>
				<h1 class="libary_head"><?php echo get_string('welcome', 'block_exalib');  ?></h1>


				<div class="libary_top_cat">
					<a class="exalib-blue-cat-lib" href="<?php echo $CFG->wwwroot; ?>/blocks/exalib/index.php?category_id=11"><?php echo get_string('abstracts', 'block_exalib')?></a>
					<a class="exalib-blue-cat-lib" href="<?php echo $CFG->wwwroot; ?>/blocks/exalib/index.php?category_id=12"><?php echo get_string('documents', 'block_exalib')?></a>
					<a class="exalib-blue-cat-lib" href="<?php echo $CFG->wwwroot; ?>/blocks/exalib/index.php?category_id=13"><?php echo get_string('images', 'block_exalib')?></a>
					<a class="exalib-blue-cat-lib" href="<?php echo $CFG->wwwroot; ?>/blocks/exalib/index.php?category_id=14"><?php echo get_string('podcasts', 'block_exalib')?></a>
					<a class="exalib-blue-cat-lib" href="<?php echo $CFG->wwwroot; ?>/blocks/exalib/index.php?category_id=15"><?php echo get_string('webcasts', 'block_exalib')?></a>


				</div>


				<!-- <div class="library_filter_main">
		<a href="index.php?category_id=11">
			<img src="<? echo $CFG->wwwroot; ?>/pluginfile.php/213/course/section/154/ecoo_abstracts.png" height="43" width="212" /></a>
		<a href="index.php?category_id=12">
			<img src="<? echo $CFG->wwwroot; ?>/pluginfile.php/213/course/section/154/ecoo_documents.png" height="43" width="212" /></a>
		<a href="index.php?category_id=13">
			<img src="<? echo $CFG->wwwroot; ?>/pluginfile.php/213/course/section/154/ecoo_images.png" height="43" width="212" /></a>
		<a href="index.php?category_id=14">
			<img src="<? echo $CFG->wwwroot; ?>/pluginfile.php/213/course/section/154/ecoo_podcasts.png" height="43" width="212" /></a>
		<a href="index.php?category_id=15">
			<img src="<? echo $CFG->wwwroot; ?>/pluginfile.php/213/course/section/154/ecoo_webcasts.png" height="43" width="212" /></a>
				</div> -->

				<div class="library_result library_result_main">

		<?php
			if (!$q):
		?>
					<br /><br /><br />
					<form method="get" action="search.php">
						<input name="q" type="text" value="<?php p($q) ?>" style="width: 240px;" class="libaryfront_search" />
						<input value="<?php echo get_string('search', 'block_exalib'); ?>" type="submit" class="libaryfront_searchsub">
					</form>
		<?php
			else:
		?>
					<form method="get" action="search.php">
						<input name="q" type="text" value="<?php p($q) ?>" style="width: 240px;" class="libaryfront_search" />
						<input value="<?php echo get_string('search', 'block_exalib'); ?>" type="submit" class="libaryfront_searchsub">
					</form>
		<?php
			endif;
		?>

		<?php
			if ($items !== null) {
				echo '<h1 class="library_result_heading">'.get_string('results', 'block_exalib').'</h1>';

				if (!$items) {
					echo get_string('noitemsfound', 'block_exalib');
				} else {
					if ($pagingbar) {
						echo $output->render($pagingbar);
					}
					print_items($items);
					if ($pagingbar) {
						echo $output->render($pagingbar);
					}
				}
			}
		?>
				</div>
				<?php
				echo $output->footer();
				exit;
		}
		*/

		if ($currentcategory) {
			$PAGE->set_heading(get_string('heading', 'block_exalib').': '.$currentcategory->name);
		}

		?>
		<div class="library_categories">

			<form method="get" action="<?php echo $urlsearch; ?>">
				<?php echo html_writer::input_hidden_params($urlsearch); ?>
				<input name="q" type="text" value="<?php p($q) ?>"/>
				<?php if ($currentcategory): ?>
					<select name="category_id">
						<option value="<?php echo $currentcategory->id; ?>">
							<?php echo get_string('inthiscat', 'block_exalib'); ?></option>
						<option value="0"><?php echo get_string('wholelib', 'block_exalib'); ?></option>
					</select>
					<?php
				endif;
				?>
				<input value="<?php echo get_string('search', 'block_exalib') ?>" type="submit">
			</form>

			<?php

			echo '<div id="exalib-categories"><ul>';
			echo $mgr->walktree(null, function($cat, $suboutput) {
				global $urlcategory, $categoryid, $currentcategoryparents;

				if (!BLOCK_EXALIB_IS_ADMIN_MODE && !$cat->cnt_inc_subs) {
					// Hide empty categories.
					return;
				}

				$output = '<li id="exalib-menu-item-'.$cat->id.'" class="'.
					($suboutput ? 'isFolder' : '').
					(in_array($cat->id, $currentcategoryparents) ? ' isExpanded' : '').
					($cat->id == $categoryid ? ' isActive' : '').'">';
				$output .= '<a class="library_categories_item_title"
        href="'.$urlcategory->out(true, array('category_id' => $cat->id)).'">'.$cat->name.' ('.$cat->cnt_inc_subs.')'.'</a>';

				if ($suboutput) {
					$output .= '<ul>'.$suboutput.'</ul>';
				}

				echo '</li>';

				return $output;
			});
			echo '</ul></div>';

			?>
		</div>
		<div class="library_result">

			<?php

			if (BLOCK_EXALIB_IS_ADMIN_MODE) {
				?><input type="button" href="<?php echo $urladd; ?>"
						 onclick="document.location.href=this.getAttribute('href');"
						 value="<?php echo get_string('newentry', 'block_exalib') ?>" /><?php
			}

			if ($show == 'latest_changes') {
				echo '<h1 class="library_result_heading">'.get_string('latest', 'block_exalib').'</h1>';
			} else {
				echo '<h1 class="library_result_heading">'.get_string('results', 'block_exalib').'</h1>';
			}

			if (!$items) {
				echo get_string('noitemsfound', 'block_exalib');
			} else {
				if ($pagingbar) {
					echo $output->render($pagingbar);
				}
				$output->item_list(BLOCK_EXALIB_IS_ADMIN_MODE ? 'admin' : 'public', $items);
				if ($pagingbar) {
					echo $output->render($pagingbar);
				}
			}

			?>
		</div>
	</div>
<?php
echo $output->footer();
