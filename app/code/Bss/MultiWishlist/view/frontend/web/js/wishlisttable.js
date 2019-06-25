 require([
    'jquery',
    'mage/mage',
    'Magento_Customer/js/customer-data'
], function ($, mage, customerData) {
    decorateTable = $('#wishlist-table');
    $.localStorage.set('mage-cache-timeout', 0);
});
