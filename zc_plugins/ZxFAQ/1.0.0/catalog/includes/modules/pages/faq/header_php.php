<?php
/**
 * @package admin
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert - https://zenexpert.com - Jan 29, 2026 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

$breadcrumb->add(NAVBAR_TITLE);

$faq_data = [];

// Get Categories (bucket setup)
$categories_query = "SELECT c.faq_categories_id, cd.faq_categories_name
                     FROM " . TABLE_FAQ_CATEGORIES . " c
                     LEFT JOIN " . TABLE_FAQ_CATEGORIES_DESCRIPTION . " cd ON c.faq_categories_id = cd.faq_categories_id
                     WHERE cd.language_id = :langID
                     ORDER BY c.sort_order, cd.faq_categories_name";

$categories_query = $db->bindVars($categories_query, ':langID', $_SESSION['languages_id'], 'integer');
$categories = $db->Execute($categories_query);

while (!$categories->EOF) {
  $id = $categories->fields['faq_categories_id'];
  $faq_data[$id] = array(
      'name' => $categories->fields['faq_categories_name'],
      'items' => array()
  );
  $categories->MoveNext();
}

// Prepare "Uncategorized" bucket (ID 0)
$faq_data[0] = array(
    'name' => TEXT_UNCATEGORIZED_SECTION,
    'items' => array()
);

// Get FAQs
$faq_query = "SELECT f.faq_id, f.faq_category_id, fd.faq_title, fd.faq_content
              FROM " . TABLE_FAQ_ITEMS . " f
              LEFT JOIN " . TABLE_FAQ_ITEM_DESCRIPTIONS . " fd ON f.faq_id = fd.faq_id
              WHERE f.status = 1
              AND fd.language_id = :langID
              ORDER BY f.sort_order, fd.faq_title";

$faq_query = $db->bindVars($faq_query, ':langID', $_SESSION['languages_id'], 'integer');
$result = $db->Execute($faq_query);

while (!$result->EOF) {
  $cat_id = (int)$result->fields['faq_category_id'];

  // Check if the assigned category actually exists (it might have been deleted)
  // If it exists, add to that bucket. If not, add to Uncategorized (0).
  if (isset($faq_data[$cat_id])) {
    $faq_data[$cat_id]['items'][] = array(
        'title' => $result->fields['faq_title'],
        'content' => $result->fields['faq_content']
    );
  } else {
    $faq_data[0]['items'][] = array(
        'title' => $result->fields['faq_title'],
        'content' => $result->fields['faq_content']
    );
  }

  $result->MoveNext();
}

// Cleanup
// Remove empty categories so we don't display headers with no questions
foreach ($faq_data as $key => $data) {
  if (empty($data['items'])) {
    unset($faq_data[$key]);
  }
}

// Uncategorized is at the bottom
// If we have uncategorized items, removing and re-adding them puts them at the end of the array
if (isset($faq_data[0])) {
  $uncategorized = $faq_data[0];
  unset($faq_data[0]);
  $faq_data[0] = $uncategorized;
}
?>
