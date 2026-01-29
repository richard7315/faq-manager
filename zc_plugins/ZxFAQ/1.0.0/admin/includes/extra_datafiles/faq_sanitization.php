<?php

if (class_exists ('AdminRequestSanitizer') && method_exists ('AdminRequestSanitizer', 'getInstance')) {
    $faq_sanitizer = AdminRequestSanitizer::getInstance();
    $faq_sanitizer->addSimpleSanitization ('PRODUCT_DESC_REGEX', array ( 'faq_title', 'faq_content', 'faq_categories_name' ));
}
