<?php
/**
 * Page Template for FAQ Manager
 * @package template
 * @copyright Copyright 2003 - 2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *  @version $Id: ZenExpert - https://zenexpert.com - Jan 29, 2026 $
 */
?>
<div class="centerColumn" id="faqDefault">

    <h1 id="faqDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

    <?php if (empty($faq_data)) { ?>
        <div class="alert alert-info"><?php echo TEXT_NO_FAQS; ?></div>
    <?php } else { ?>

        <div id="faqWrapper">
            <?php foreach ($faq_data as $category_id => $category) { ?>

                <div class="faqCategoryContainer">
                    <h2 class="faqCategoryTitle"><?php echo $category['name']; ?></h2>

                    <?php foreach ($category['items'] as $item) { ?>
                        <details class="faqItem">
                            <summary class="faqQuestion">
                                <?php echo $item['title']; ?>
                            </summary>
                            <div class="faqAnswer">
                                <?php echo $item['content']; ?>
                            </div>
                        </details>
                    <?php } // end foreach items ?>

                </div>
                <hr />

            <?php } // end foreach categories ?>
        </div>

    <?php } // end else ?>

    <div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
