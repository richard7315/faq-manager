<?php
/**
 * FAQ Manager & Category Manager for Zen Cart
 * @package admin
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert - https://zenexpert.com - Jan 28, 2026 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$current_lang_id = (int)$_SESSION['languages_id'];

if (!empty($action)) {
    switch ($action) {
        case 'save_category':
        case 'insert_category':
            $cID = (isset($_GET['cID'])) ? (int)$_GET['cID'] : 0;
            $sort_order = (int)$_POST['sort_order'];
            $error = false;

            $languages = zen_get_languages();
            foreach ($languages as $lang) {
                $name = $_POST['category_name'][$lang['id']];
                if (empty($name)) continue;

                $check_sql = "SELECT faq_categories_id FROM ".TABLE_FAQ_CATEGORIES_DESCRIPTION."
                              WHERE faq_categories_name = '" . zen_db_input($name) . "'
                              AND language_id = '" . (int)$lang['id'] . "'";
                if ($action == 'save_category') $check_sql .= " AND faq_categories_id != '" . $cID . "'";

                $check = $db->Execute($check_sql);
                if ($check->RecordCount() > 0) {
                    $error = true;
                    $messageStack->add('Error: Category "' . $name . '" already exists.', 'error');
                }
            }

            if ($error == true) {
                $action = ($action == 'insert_category') ? 'new_category' : 'edit_category';
            } else {
                $sql_data_array = array('sort_order' => $sort_order);
                if ($action == 'insert_category') {
                    $sql_data_array['date_added'] = 'now()';
                    zen_db_perform(TABLE_FAQ_CATEGORIES, $sql_data_array);
                    $cID = $db->Insert_ID();
                } else {
                    $sql_data_array['last_modified'] = 'now()';
                    zen_db_perform(TABLE_FAQ_CATEGORIES, $sql_data_array, 'update', "faq_categories_id = '" . $cID . "'");
                }
                foreach ($languages as $lang) {
                    $name = $_POST['category_name'][$lang['id']];
                    $sql_desc = array('faq_categories_name' => $name);
                    $check = $db->Execute("SELECT faq_categories_id FROM ".TABLE_FAQ_CATEGORIES_DESCRIPTION." WHERE faq_categories_id = '" . $cID . "' AND language_id = '" . (int)$lang['id'] . "'");
                    if ($check->RecordCount() > 0) {
                        zen_db_perform(TABLE_FAQ_CATEGORIES_DESCRIPTION, $sql_desc, 'update', "faq_categories_id = '" . $cID . "' AND language_id = '" . (int)$lang['id'] . "'");
                    } else {
                        $sql_desc['faq_categories_id'] = $cID;
                        $sql_desc['language_id'] = (int)$lang['id'];
                        zen_db_perform(TABLE_FAQ_CATEGORIES_DESCRIPTION, $sql_desc);
                    }
                }
                zen_redirect(zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories&cID=' . $cID));
            }
            break;

        case 'delete_category_confirm':
            $cID = (int)$_GET['cID'];
            $db->Execute("UPDATE ".TABLE_FAQ_ITEMS." SET faq_category_id = 0 WHERE faq_category_id = '" . $cID . "'");
            $db->Execute("DELETE FROM ".TABLE_FAQ_CATEGORIES." WHERE faq_categories_id = '" . $cID . "'");
            $db->Execute("DELETE FROM ".TABLE_FAQ_CATEGORIES_DESCRIPTION." WHERE faq_categories_id = '" . $cID . "'");
            zen_redirect(zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories'));
            break;

        case 'insert':
        case 'save':
            if (isset($_GET['fID'])) $fID = (int)$_GET['fID'];
            $sort_order = (int)$_POST['sort_order'];
            $status = (int)$_POST['status'];
            $faq_category_id = (int)$_POST['faq_category_id'];

            $sql_data_array = array('sort_order' => $sort_order, 'status' => $status, 'faq_category_id' => $faq_category_id);

            if ($action == 'insert') {
                $sql_data_array['date_added'] = 'now()';
                zen_db_perform(TABLE_FAQ_ITEMS, $sql_data_array);
                $fID = $db->Insert_ID();
            } else {
                $sql_data_array['last_modified'] = 'now()';
                zen_db_perform(TABLE_FAQ_ITEMS, $sql_data_array, 'update', "faq_id = '" . (int)$fID . "'");
            }

            $languages = zen_get_languages();
            foreach ($languages as $lang) {
                $lang_id = $lang['id'];
                $title = $_POST['faq_title'][$lang_id];
                $content = $_POST['faq_content'][$lang_id];
                $sql_desc_array = array('faq_title' => $title, 'faq_content' => $content);

                $check = $db->Execute("SELECT faq_id FROM ".TABLE_FAQ_ITEM_DESCRIPTIONS." WHERE faq_id = '" . (int)$fID . "' AND language_id = '" . (int)$lang_id . "'");
                if ($check->RecordCount() > 0) {
                    zen_db_perform(TABLE_FAQ_ITEM_DESCRIPTIONS, $sql_desc_array, 'update', "faq_id = '" . (int)$fID . "' AND language_id = '" . (int)$lang_id . "'");
                } else {
                    $sql_desc_array['faq_id'] = $fID;
                    $sql_desc_array['language_id'] = $lang_id;
                    zen_db_perform(TABLE_FAQ_ITEM_DESCRIPTIONS, $sql_desc_array);
                }
            }
            zen_redirect(zen_href_link(FILENAME_FAQ_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'fID=' . $fID));
            break;

        case 'deleteconfirm':
            $fID = (int)$_GET['fID'];
            $db->Execute("DELETE FROM ".TABLE_FAQ_ITEMS." WHERE faq_id = '" . $fID . "'");
            $db->Execute("DELETE FROM ".TABLE_FAQ_ITEM_DESCRIPTIONS." WHERE faq_id = '" . $fID . "'");
            zen_redirect(zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page']));
            break;

        case 'setflag':
            $fID = (int)$_GET['fID'];
            $flag = ($_GET['flag'] == '1' ? 1 : 0);
            $db->Execute("UPDATE ".TABLE_FAQ_ITEMS." SET status = '" . $flag . "', last_modified = now() WHERE faq_id = '" . $fID . "'");
            zen_redirect(zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $fID));
            break;
    }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <div class="pull-right">
                        <?php
                        if ($action == 'list_categories' || strpos($action, '_category') !== false) {
                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER) . '" class="btn btn-default"><i class="glyphicon glyphicon-question-sign"></i> ' . BUTTON_SWITCH_TO_QUESTIONS . '</a>';
                        } else {
                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories') . '" class="btn btn-info"><i class="glyphicon glyphicon-folder-open"></i> ' . BUTTON_MANAGE_CATEGORIES .'</a>';
                        }
                        ?>
                    </div>
                    <h1><?php echo HEADING_TITLE; ?> <small><?php echo ($action == 'list_categories' || strpos($action, 'category') !== false) ? HEADING_TITLE_CATEGORIES : HEADING_TITLE_QUESTIONS; ?></small></h1>
                </div>
            </div>
        </div>

        <div class="row">

            <?php
            // Edit categories
            if ($action == 'new_category' || $action == 'edit_category') {
                $form_action = ($action == 'new_category') ? 'insert_category' : 'save_category';
                if ($action == 'edit_category' && isset($_GET['cID'])) {
                    $cID = (int)$_GET['cID'];
                    $cat = $db->Execute("SELECT * FROM ".TABLE_FAQ_CATEGORIES." WHERE faq_categories_id = '" . $cID . "'");
                    $cInfo = new objectInfo($cat->fields);
                } else {
                    $cInfo = new objectInfo(array());
                }
                ?>
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><?php echo ($action=='new_category' ? TEXT_HEADING_NEW_CATEGORY : TEXT_HEADING_EDIT_CATEGORY); ?></div>
                        <div class="panel-body">
                            <?php echo zen_draw_form('category_form', FILENAME_FAQ_MANAGER, 'action=' . $form_action . (isset($_GET['cID']) ? '&cID=' . $_GET['cID'] : ''), 'post', 'class="form-horizontal"'); ?>

                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo TABLE_HEADING_SORT_ORDER; ?></label>
                                <div class="col-sm-2">
                                    <?php echo zen_draw_input_field('sort_order', $cInfo->sort_order, 'class="form-control"'); ?>
                                </div>
                            </div>

                            <?php
                            $languages = zen_get_languages();
                            foreach ($languages as $lang) {
                                $cat_name = '';
                                if ($action == 'edit_category') {
                                    $desc = $db->Execute("SELECT faq_categories_name FROM ".TABLE_FAQ_CATEGORIES_DESCRIPTION." WHERE faq_categories_id = '" . $cID . "' AND language_id = '" . (int)$lang['id'] . "'");
                                    $cat_name = $desc->fields['faq_categories_name'];
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo $lang['name'] . ENTRY_CATEGORY_NAME; ?> </label>
                                    <div class="col-sm-6">
                                        <?php echo zen_draw_input_field('category_name[' . $lang['id'] . ']', $cat_name, 'class="form-control"'); ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-floppy-disk"></i> <?php echo BUTTON_SAVE; ?></button>
                                    <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories'); ?>" class="btn btn-default"><?php echo BUTTON_CANCEL; ?></a>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php
                // List categories
            } elseif ($action == 'list_categories' || $action == 'delete_category_ask') {
                ?>
                <div class="col-md-9">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr class="info">
                            <th><?php echo TABLE_HEADING_ID; ?></th>
                            <th><?php echo TABLE_HEADING_CATEGORY_NAME; ?></th>
                            <th class="text-center"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                            <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $cat_query_raw = "SELECT c.faq_categories_id, c.sort_order, cd.faq_categories_name
                              FROM ".TABLE_FAQ_CATEGORIES." c
                              LEFT JOIN ".TABLE_FAQ_CATEGORIES_DESCRIPTION." cd ON (c.faq_categories_id = cd.faq_categories_id AND cd.language_id = '" . (int)$current_lang_id . "')
                              ORDER BY c.sort_order, cd.faq_categories_name";
                        $categories = $db->Execute($cat_query_raw);

                        while (!$categories->EOF) {
                            if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories->fields['faq_categories_id']))) && !isset($cInfo)) {
                                $cInfo = new objectInfo($categories->fields);
                            }
                            $isActive = (isset($cInfo) && is_object($cInfo) && ($categories->fields['faq_categories_id'] == $cInfo->faq_categories_id));
                            $rowClass = $isActive ? 'success' : ''; // 'success' is green in BS3, 'info' is blue
                            ?>
                            <tr class="<?php echo $rowClass; ?>" onclick="document.location.href='<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories&cID=' . $categories->fields['faq_categories_id']); ?>'" style="cursor:pointer;">
                                <td><?php echo $categories->fields['faq_categories_id']; ?></td>
                                <td><?php echo $categories->fields['faq_categories_name']; ?></td>
                                <td class="text-center"><?php echo $categories->fields['sort_order']; ?></td>
                                <td class="text-right">
                                    <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=edit_category&cID=' . $categories->fields['faq_categories_id']); ?>" title="<?php echo BUTTON_EDIT; ?>"><i class="glyphicon glyphicon-edit action-icon"></i></a>
                                    <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=delete_category_ask&cID=' . $categories->fields['faq_categories_id']); ?>" title="<?php echo BUTTON_DELETE; ?>" class="text-danger"><i class="glyphicon glyphicon-trash action-icon"></i></a>
                                </td>
                            </tr>
                            <?php $categories->MoveNext(); } ?>
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=new_category'); ?>" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> <?php echo BUTTON_NEW_CATEGORY; ?></a>
                    </div>
                </div>

                <div class="col-md-3">
                    <?php
                    if (isset($cInfo) && is_object($cInfo)) {
                        $heading = $cInfo->faq_categories_name;
                        echo '<div class="panel panel-info">';
                        echo '<div class="panel-heading">' . $heading . '</div>';
                        echo '<div class="panel-body">';

                        if ($action == 'delete_category_ask') {
                            echo zen_draw_form('delete_cat', FILENAME_FAQ_MANAGER, 'action=delete_category_confirm&cID=' . $cInfo->faq_categories_id);
                            echo '<div class="alert alert-warning">' . sprintf(TEXT_ALERT_DELETE_CATEGORY, $cInfo->faq_categories_name) . '</div>';
                            echo '<p class="text-muted"><i>' . TEXT_ALERT_DELETE_CATEGORY_NOTE . '</i></p>';
                            echo '<div class="text-center">';
                            echo '<button type="submit" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i> ' . BUTTON_DELETE . '</button> ';
                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=list_categories&cID=' . $cInfo->faq_categories_id) . '" class="btn btn-default">' . BUTTON_CANCEL . '</a>';
                            echo '</div></form>';
                        } else {
                            echo '<div class="text-center">';
                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=edit_category&cID=' . $cInfo->faq_categories_id) . '" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i> ' . BUTTON_EDIT . '</a> ';
                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=delete_category_ask&cID=' . $cInfo->faq_categories_id) . '" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i> ' . BUTTON_DELETE . '</a>';
                            echo '</div>';
                        }
                        echo '</div></div>';
                    }
                    ?>
                </div>

                <?php
                // Edit FAQ Questions
            } else {

                // Add or edit Questions
                if ($action == 'new' || $action == 'edit') {
                    $form_action = ($action == 'new') ? 'insert' : 'save';
                    if ($action == 'edit' && isset($_GET['fID'])) {
                        $fID = (int)$_GET['fID'];
                        $faq = $db->Execute("SELECT * FROM ".TABLE_FAQ_ITEMS." WHERE faq_id = '" . $fID . "'");
                        $fInfo = new objectInfo($faq->fields);
                    } else {
                        $fInfo = new objectInfo(array());
                    }
                    ?>
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><?php echo ($action == 'new' ? TEXT_HEADING_NEW_FAQ : TEXT_HEADING_EDIT_FAQ); ?></div>
                            <div class="panel-body">
                                <?php echo zen_draw_form('faq_form', FILENAME_FAQ_MANAGER, 'action=' . $form_action . '&' . (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['fID']) ? 'fID=' . $_GET['fID'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"'); ?>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo TABLE_HEADING_CATEGORY; ?></label>
                                    <div class="col-sm-4">
                                        <?php
                                        $categories_array = array(array('id' => '0', 'text' => '-- None --'));
                                        $categories_query = $db->Execute("SELECT c.faq_categories_id, cd.faq_categories_name
                                                          FROM ".TABLE_FAQ_CATEGORIES." c
                                                          LEFT JOIN ".TABLE_FAQ_CATEGORIES_DESCRIPTION." cd ON c.faq_categories_id = cd.faq_categories_id
                                                          WHERE cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                                          ORDER BY c.sort_order, cd.faq_categories_name");
                                        while (!$categories_query->EOF) {
                                            $categories_array[] = array('id' => $categories_query->fields['faq_categories_id'],
                                                    'text' => $categories_query->fields['faq_categories_name']);
                                            $categories_query->MoveNext();
                                        }
                                        $selected_category = (isset($fInfo->faq_category_id)) ? $fInfo->faq_category_id : '0';
                                        echo zen_draw_pull_down_menu('faq_category_id', $categories_array, $selected_category, 'class="form-control"');
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo TABLE_HEADING_SORT_ORDER; ?></label>
                                    <div class="col-sm-2">
                                        <?php echo zen_draw_input_field('sort_order', $fInfo->sort_order, 'class="form-control"'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo TABLE_HEADING_STATUS;?></label>
                                    <div class="col-sm-4">
                                        <div class="radio">
                                            <label><input type="radio" name="status" value="1" <?php echo ($fInfo->status == '1' || $action=='new') ? 'checked' : ''; ?>> <?php echo IMAGE_ICON_STATUS_ON; ?></label>
                                            &nbsp;&nbsp;
                                            <label><input type="radio" name="status" value="0" <?php echo ($fInfo->status == '0') ? 'checked' : ''; ?>> <?php echo IMAGE_ICON_STATUS_OFF; ?></label>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <?php
                                $languages = zen_get_languages();
                                foreach ($languages as $lang) {
                                    if ($action == 'edit') {
                                        $desc = $db->Execute("SELECT * FROM ".TABLE_FAQ_ITEM_DESCRIPTIONS." WHERE faq_id = '" . $fID . "' AND language_id = '" . (int)$lang['id'] . "'");
                                        $faq_title = $desc->fields['faq_title'];
                                        $faq_content = $desc->fields['faq_content'];
                                    } else {
                                        $faq_title = '';
                                        $faq_content = '';
                                    }
                                    ?>
                                    <h4><?php echo zen_image(DIR_WS_LANGUAGES .  $lang['directory'] . '/images/' . $lang['image'], $lang['name']); ?> <?php echo $lang['name']; ?></h4>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo ENTRY_FAQ_TITLE; ?></label>
                                        <div class="col-sm-10">
                                            <?php echo zen_draw_input_field('faq_title[' . $lang['id'] . ']', $faq_title, 'class="form-control" placeholder="' . ENTRY_FAQ_TITLE_PLACEHOLDER . '"'); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo ENTRY_FAQ_CONTENT; ?></label>
                                        <div class="col-sm-10">
                                            <?php echo zen_draw_textarea_field('faq_content[' . $lang['id'] . ']', 'soft', '100%', '8', $faq_content, 'class="form-control"'); ?>
                                        </div>
                                    </div>
                                    <hr>
                                <?php } ?>

                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-floppy-disk"></i> <?php echo BUTTON_SAVE; ?></button>
                                        <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . ($_GET['page'] ?? 1) . (isset($_GET['fID']) ? '&fID=' . $_GET['fID'] : '')); ?>" class="btn btn-default"><?php echo BUTTON_CANCEL; ?></a>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    // List Questions
                } else {
                    ?>
                    <div class="col-md-9">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr class="info">
                                <th><?php echo TABLE_HEADING_ID; ?></th>
                                <th><?php echo HEADING_TITLE_QUESTIONS; ?></th>
                                <th><?php echo TABLE_HEADING_CATEGORY; ?></th>
                                <th class="text-center"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                                <th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
                                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $faq_query_raw = "SELECT f.faq_id, f.sort_order, f.status, fd.faq_title, cd.faq_categories_name
                              FROM ".TABLE_FAQ_ITEMS." f
                              LEFT JOIN ".TABLE_FAQ_ITEM_DESCRIPTIONS." fd ON (f.faq_id = fd.faq_id AND fd.language_id = '" . (int)$current_lang_id . "')
                              LEFT JOIN ".TABLE_FAQ_CATEGORIES_DESCRIPTION." cd ON (f.faq_category_id = cd.faq_categories_id AND cd.language_id = '" . (int)$current_lang_id . "')
                              ORDER BY f.sort_order, fd.faq_title";

                            $faq_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $faq_query_raw, $faq_query_numrows);
                            $faq = $db->Execute($faq_query_raw);

                            while (!$faq->EOF) {
                                if ((!isset($_GET['fID']) || (isset($_GET['fID']) && ($_GET['fID'] == $faq->fields['faq_id']))) && !isset($fInfo)) {
                                    $fInfo = new objectInfo($faq->fields);
                                }
                                $isActive = (isset($fInfo) && is_object($fInfo) && ($faq->fields['faq_id'] == $fInfo->faq_id));
                                $rowClass = $isActive ? 'success' : '';
                                ?>
                                <tr class="<?php echo $rowClass; ?>" onclick="document.location.href='<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $faq->fields['faq_id'] . '&action=edit'); ?>'" style="cursor:pointer;">
                                    <td><?php echo $faq->fields['faq_id']; ?></td>
                                    <td><?php echo $faq->fields['faq_title']; ?></td>
                                    <td><?php echo ($faq->fields['faq_categories_name'] ? $faq->fields['faq_categories_name'] : '<em class="text-muted">' . ENTRY_CATEGORY_NAME_NONE . '</em>'); ?></td>
                                    <td class="text-center"><?php echo $faq->fields['sort_order']; ?></td>
                                    <td class="text-center">
                                        <?php
                                        if ($faq->fields['status'] == '1') {
                                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=setflag&flag=0&fID=' . $faq->fields['faq_id'] . '&page=' . $_GET['page']) . '" class="text-success"><i class="glyphicon glyphicon-ok-circle" style="font-size:1.2em;"></i></a>';
                                        } else {
                                            echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'action=setflag&flag=1&fID=' . $faq->fields['faq_id'] . '&page=' . $_GET['page']) . '" class="text-danger"><i class="glyphicon glyphicon-ban-circle" style="font-size:1.2em;"></i></a>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $faq->fields['faq_id'] . '&action=edit'); ?>"><i class="glyphicon glyphicon-edit action-icon"></i></a>
                                        <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $faq->fields['faq_id'] . '&action=delete'); ?>" class="text-danger"><i class="glyphicon glyphicon-trash action-icon"></i></a>
                                    </td>
                                </tr>
                                <?php $faq->MoveNext(); } ?>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $faq_split->display_count($faq_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_FAQS); ?>
                                <br><?php echo $faq_split->display_links($faq_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo zen_href_link(FILENAME_FAQ_MANAGER, 'action=new'); ?>" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> <?php echo IMAGE_NEW_FAQ; ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <?php
                        if (isset($fInfo) && is_object($fInfo)) {
                            $heading = (isset($fInfo->faq_title)) ? $fInfo->faq_title : 'FAQ Info';
                            echo '<div class="panel panel-info">';
                            echo '<div class="panel-heading">' . $heading . '</div>';
                            echo '<div class="panel-body">';

                            if ($action == 'delete') {
                                echo zen_draw_form('faq_delete', FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $fInfo->faq_id . '&action=deleteconfirm');
                                echo '<div class="alert alert-warning">' . TEXT_INFO_DELETE_INTRO . '</div>';
                                echo '<p><b>' . $fInfo->faq_title . '</b></p>';
                                echo '<div class="text-center">';
                                echo '<button type="submit" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i> ' . BUTTON_DELETE . '</button> ';
                                echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $fInfo->faq_id) . '" class="btn btn-default">' . BUTTON_CANCEL . '</a>';
                                echo '</div></form>';
                            } else {
                                if (isset($fInfo->faq_title)) {
                                    echo '<div class="text-center" style="margin-bottom:15px;">';
                                    echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $fInfo->faq_id . '&action=edit') . '" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i> ' . BUTTON_EDIT . '</a> ';
                                    echo '<a href="' . zen_href_link(FILENAME_FAQ_MANAGER, 'page=' . $_GET['page'] . '&fID=' . $fInfo->faq_id . '&action=delete') . '" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i> ' . BUTTON_DELETE . '</a>';
                                    echo '</div>';
                                    echo '<p class="small"><strong>' . ENTRY_TEXT_CREATED .'</strong> ' . zen_date_short($fInfo->date_added) . '</p>';
                                    if (zen_not_null($fInfo->last_modified)) echo '<p class="small"><strong>' . ENTRY_TEXT_MODIFIED .'</strong> ' . zen_date_short($fInfo->last_modified) . '</p>';
                                }
                            }
                            echo '</div></div>';
                        }
                        ?>
                    </div>

                    <?php
                } // End inner if (FAQ Form vs Listing)
            } // End outer if (Category vs Questions)
            ?>
        </div>
    </div>
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    </body>
</html>
