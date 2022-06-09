<?php

return [
    [
        'key'   => 'dashboard',
        'name'  => 'Dashboard',
        'route' => null,
        'sort'  => 1,
        "module"=>'Dashboard'
    ],
    [
        'key'   => 'dashboard.dashboard',
        'name'  => 'Dashboard',
        'route' => 'admin.dashboard.index',
        'sort'  => 1,
        "module"=>'Dashboard'
    ],

    //PRODUCT PERMISSION
    [
        'key'   => 'catalog.products.index',
        'name'  => "List Product ",
        'route' => 'admin.catalog.products.index',
        'sort'  => 1,
        'module' => 'Product'
    ],
    [
        'key'   => 'catalog.products.store',
        'name'  => 'Create Product',
        'route' => 'admin.catalog.products.store',
        'module' => 'Product'
    ],
    [
        'key'   => 'catalog.products.show',
        'name'  => 'Show Product',
        'route' => 'admin.catalog.products.show',
        'sort'  => 3,
        'module' => 'Product'
    ],
    [
        'key'   => 'catalog.products.update',
        'name'  => 'Update Product',
        'route' => 'admin.catalog.products.update',
        'sort'  => 4,
        'module' => 'Product'
    ],
    [
        'key'   => 'catalog.products.delete',
        'name'  => 'Delete Product',
        'route' => 'admin.catalog.products.delete',
        'sort'  => 5,
        'module' => 'Product'
    ],


    //CATEGORY PERMISSION
    [
        'key'   => 'catalog.categories.index',
        'name'  => 'List Categories',
        'route' => 'admin.catalog.categories.index',
        'sort'  => 1,
        'module' => 'Category'
    ],
    [
        'key'   => 'catalog.categories.store',
        'name'  => 'Create Category',
        'route' => 'admin.catalog.categories.store',
        'sort'  => 2,
        'module' => 'Category'
    ],
    [
        'key'   => 'catalog.categories.show',
        'name'  => 'Show Category',
        'route' => 'admin.catalog.categories.index',
        'sort'  => 3,
        'module' => 'Category'
    ],
    [
        'key'   => 'catalog.categories.update',
        'name'  => 'Update Category',
        'route' => 'admin.catalog.categories.update',
        'sort'  => 4,
        'module' => 'Category'
    ],
    [
        'key'   => 'catalog.categories.delete',
        'name'  => 'Delete Category',
        'route' => 'admin.catalog.categories.delete',
        'sort'  => 5,
        'module' => 'Category'
    ],

    // ACTIVITY
    [
        'key'   => 'core.activities.index',
        'name'  => 'List activities',
        'route' => 'admin.activities.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.activities.show',
        'name'  => 'Show activities',
        'route' => 'admin.activities.show',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.activities.delete',
        'name'  => 'Delete activities',
        'route' => 'admin.activities.delete',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.activities.bulk-delete',
        'name'  => 'Bulk Delete activities',
        'route' => 'admin.activities.bulk-delete',
        'sort'  => 1,
        'module' => 'Core'
    ],


    //LOCALE
    [
        'key'   => 'core.locales.index',
        'name'  => 'List locales',
        'route' => 'admin.locales.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.locales.store',
        'name'  => 'Create locales',
        'route' => 'admin.locales.store',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.locales.show',
        'name'  => 'Show locales',
        'route' => 'admin.locales.show',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.locales.update',
        'name'  => 'Update locales',
        'route' => 'admin.locales.update',
        'sort'  => 4,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.locales.delete',
        'name'  => 'Delete locales',
        'route' => 'admin.locales.delete',
        'sort'  => 5,
        'module' => 'Core'
    ],

    //CURRENCY
    [
        'key'   => 'core.currencies.index',
        'name'  => 'List currencies',
        'route' => 'admin.currencies.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.currencies.store',
        'name'  => 'Create currencies',
        'route' => 'admin.currencies.store',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.currencies.show',
        'name'  => 'Show currencies',
        'route' => 'admin.currencies.show',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.currencies.update',
        'name'  => 'Update currencies',
        'route' => 'admin.currencies.update',
        'sort'  => 4,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.currencies.delete',
        'name'  => 'Delete currencies',
        'route' => 'admin.currencies.delete',
        'sort'  => 5,
        'module' => 'Core'
    ],

    //EXCHANGE RATE
    [
        'key'   => 'core.exchange_rates.index',
        'name'  => 'List Exchange Rates',
        'route' => 'admin.exchange_rates.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.exchange_rates.store',
        'name'  => 'Create Exchange Rates',
        'route' => 'admin.exchange_rates.store',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.exchange_rates.show',
        'name'  => 'Show Exchange Rates',
        'route' => 'admin.exchange_rates.show',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.exchange_rates.update',
        'name'  => 'Update Exchange Rates',
        'route' => 'admin.exchange_rates.update',
        'sort'  => 4,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.exchange_rates.delete',
        'name'  => 'Delete Exchange Rates',
        'route' => 'admin.exchange_rates.delete',
        'sort'  => 5,
        'module' => 'Core'
    ],

    //STORE
    [
        'key'   => 'core.stores.index',
        'name'  => 'List stores',
        'route' => 'admin.stores.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.stores.store',
        'name'  => 'Create stores',
        'route' => 'admin.stores.store',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.stores.show',
        'name'  => 'Show stores',
        'route' => 'admin.stores.show',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.stores.update',
        'name'  => 'Update stores',
        'route' => 'admin.stores.update',
        'sort'  => 4,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.stores.delete',
        'name'  => 'Delete stores',
        'route' => 'admin.stores.delete',
        'sort'  => 5,
        'module' => 'Core'
    ],

     //CHANNEL
     [
        'key'   => 'core.channels.index',
        'name'  => 'List channels',
        'route' => 'admin.channels.index',
        'sort'  => 1,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.channels.store',
        'name'  => 'Create channels',
        'route' => 'admin.channels.store',
        'sort'  => 2,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.channels.show',
        'name'  => 'Show channels',
        'route' => 'admin.channels.show',
        'sort'  => 3,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.channels.update',
        'name'  => 'Update channels',
        'route' => 'admin.channels.update',
        'sort'  => 4,
        'module' => 'Core'
    ],
    [
        'key'   => 'core.channels.delete',
        'name'  => 'Delete channels',
        'route' => 'admin.channels.delete',
        'sort'  => 5,
        'module' => 'Core'
    ],


    //WEBSITES
    [
        'key'   => 'core.websites.index',
        'name'  => 'List Website',
        'route' => 'core.websites.index',
        'sort'  => 1,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.websites.store',
        'name'  => 'Create Website',
        'route' => 'core.websites.store',
        'sort'  => 2,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.websites.show',
        'name'  => 'Show Website',
        'route' => 'core.websites.show',
        'sort'  => 3,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.websites.update',
        'name'  => 'Update Website',
        'route' => 'core.websites.update',
        'sort'  => 4,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.websites.delete',
        'name'  => 'Delete Website',
        'route' => 'core.websites.delete',
        'sort'  => 5,
        "module" => 'Core'
    ],

    //CONFIGURATION
    [
        'key'   => 'core.configurations.index',
        'name'  => 'List configurations',
        'route' => 'core.configurations.index',
        'sort'  => 1,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.configurations.store',
        'name'  => 'Create configurations',
        'route' => 'core.configurations.store',
        'sort'  => 2,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.configurations.show',
        'name'  => 'Show configurations',
        'route' => 'core.configurations.show',
        'sort'  => 3,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.configurations.update',
        'name'  => 'Update configurations',
        'route' => 'core.configurations.update',
        'sort'  => 4,
        "module" => 'Core'
    ],
    [
        'key'   => 'core.configurations.delete',
        'name'  => 'Delete configurations',
        'route' => 'core.configurations.delete',
        'sort'  => 5,
        "module" => 'Core'
    ],

    //ATTRIBUTE FAMILIES
    [
        'key'   => 'catalog.families.index',
        'name'  => 'List Attribute Family',
        'route' => 'admin.catalog.families.index',
        'sort'  => 1,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.families.store',
        'name'  => 'Create Attribute Family',
        'route' => 'admin.catalog.families.store',
        'sort'  => 2,
        'module' => 'Attribute'
    ],

    [
        'key'   => 'catalog.families.show',
        'name'  => 'Show Attribute Family',
        'route' => 'admin.catalog.families.show',
        'sort'  => 3,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.families.update',
        'name'  => 'Update Attribute Family',
        'route' => 'admin.catalog.families.update',
        'sort'  => 4,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.families.delete',
        'name'  => 'Delete Attribute Family',
        'route' => 'admin.catalog.families.delete',
        'sort'  => 5,
        'module' => 'Attribute'
    ],

    //ATTRIBUTE GROUPS
    [
        'key'   => 'catalog.attribute-groups.index',
        'name'  => 'List Attribute Group',
        'route' => 'admin.catalog.attribute-groups.index',
        'sort'  => 1,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attribute-groups.store',
        'name'  => 'Create Attribute Group',
        'route' => 'admin.catalog.attribute-groups.store',
        'sort'  => 2,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attribute-groups.show',
        'name'  => 'Show Attribute Group',
        'route' => 'admin.catalog.attribute-groups.show',
        'sort'  => 3,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attribute-groups.update',
        'name'  => 'Update Attribute Group',
        'route' => 'admin.catalog.attribute-groups.update',
        'sort'  => 4,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attribute-groups.delete',
        'name'  => 'Delete Attribute Group',
        'route' => 'admin.catalog.attribute-groups.delete',
        'sort'  => 5,
        'module' => 'Attribute'
    ],
    //Attribute
    [
        'key'   => 'catalog.attributes.index',
        'name'  => 'List Attributes',
        'route' => 'admin.catalog.attributes.index',
        'sort'  => 1,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attributes.store',
        'name'  => 'Create Attributes',
        'route' => 'admin.catalog.attributes.store',
        'sort'  => 2,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attributes.show',
        'name'  => 'Show Attributes',
        'route' => 'admin.catalog.attributes.show',
        'sort'  => 3,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attributes.update',
        'name'  => 'Update Attributes',
        'route' => 'admin.catalog.attributes.update',
        'sort'  => 4,
        'module' => 'Attribute'
    ],
    [
        'key'   => 'catalog.attributes.delete',
        'name'  => 'Delete Attributes',
        'route' => 'admin.catalog.attributes.delete',
        'sort'  => 5,
        'module' => 'Attribute'
    ],

    //CUSTOMERS
    [
        'key'   => 'customers.customers.list',
        'name'  => 'List customers',
        'route' => 'admin.customers.index',
        'sort'  => 1,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.store',
        'name'  => 'Create customers',
        'route' => 'admin.customers.store',
        'sort'  => 2,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.show',
        'name'  => 'Show Customer Detail',
        'route' => 'admin.customers.show',
        'sort'  => 3,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.update',
        'name'  => 'Update Customer Detail',
        'route' => 'admin.customers.update',
        'sort'  => 4,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.delete',
        'name'  => 'Delete Customer Detail',
        'route' => 'admin.customers.delete',
        'sort'  => 5,
        'module' => 'Customers'
    ],

    //CUSTOMER-GROUP
    [
        'key'   => 'customers.groups.list',
        'name'  => 'List customers groups',
        'route' => 'admin.groups.index',
        'sort'  => 1,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.store',
        'name'  => 'Create customer group',
        'route' => 'admin.groups.store',
        'sort'  => 2,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.show',
        'name'  => 'Show Customer Group',
        'route' => 'admin.groups.show',
        'sort'  => 3,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.update',
        'name'  => 'Update Customer Group',
        'route' => 'admin.groups.update',
        'sort'  => 4,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.customers.delete',
        'name'  => 'Delete Customer Group',
        'route' => 'admin.groups.delete',
        'sort'  => 5,
        'module' => 'Customers'
    ],

    //CUSTOMER-ADDRESS
    [
        'key'   => 'customers.address.list',
        'name'  => 'List customers address',
        'route' => 'admin.customers.address.index',
        'sort'  => 1,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.address.store',
        'name'  => 'Create customer address',
        'route' => 'admin.customers.address.store',
        'sort'  => 2,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.address.show',
        'name'  => 'Show Customer address',
        'route' => 'admin.customers.address.show',
        'sort'  => 3,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.address.update',
        'name'  => 'Update Customer address',
        'route' => 'admin.customers.address.update',
        'sort'  => 4,
        'module' => 'Customers'
    ],
    [
        'key'   => 'customers.address.delete',
        'name'  => 'Delete Customer address',
        'route' => 'admin.customers.address.delete',
        'sort'  => 5,
        'module' => 'Customers'
    ],

    //USER
    [
        'key'   => 'admin.roles.list',
        'name'  => 'List roles',
        'route' => 'admin.roles.index',
        'sort'  => 1,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.roles.store',
        'name'  => 'Create role',
        'route' => 'admin.roles.store',
        'sort'  => 2,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.roles.show',
        'name'  => 'Show role',
        'route' => 'admin.roles.show',
        'sort'  => 3,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.roles.update',
        'name'  => 'Update a role',
        'route' => 'admin.roles.update',
        'sort'  => 4,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.roles.delete',
        'name'  => 'Delete a role',
        'route' => 'admin.roles.delete',
        'sort'  => 5,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.users.list',
        'name'  => 'List  admins',
        'route' => 'admin.users.index',
        'sort'  => 1,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.users.store',
        'name'  => 'Create user',
        'route' => 'admin.users.store',
        'sort'  => 2,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.users.show',
        'name'  => 'Show user',
        'route' => 'admin.users.show',
        'sort'  => 3,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.users.update',
        'name'  => 'Update user',
        'route' => 'admin.users.update',
        'sort'  => 4,
        'module' => 'User'
    ],
    [
        'key'   => 'admin.users.delete',
        'name'  => 'Delete user',
        'route' => 'admin.users.delete',
        'sort'  => 5,
        'module' => 'User'
    ],

     //BRAND
     [
        'key'   => 'admin.brands.index',
        'name'  => 'List Brand',
        'route' => 'admin.brands.index',
        'sort'  => 1,
        'module' => 'Brand'
    ],
    [
        'key'   => 'admin.brands.store',
        'name'  => 'Create Brand',
        'route' => 'admin.brands.store',
        'sort'  => 2,
        'module' => 'Brand'
    ],
    [
        'key'   => 'admin.brands.show',
        'name'  => 'Show Brand',
        'route' => 'admin.brands.show',
        'sort'  => 3,
        'module' => 'Brand'
    ],
    [
        'key'   => 'admin.brands.update',
        'name'  => 'Update Brand',
        'route' => 'admin.brands.update',
        'sort'  => 4,
        'module' => 'Brand'
    ],
    [
        'key'   => 'admin.brands.delete',
        'name'  => 'Delete Brand',
        'route' => 'admin.brands.delete',
        'sort'  => 5,
        'module' => 'Brand'
    ],

    //Review
    [
        'key'   => 'admin.reviews.index',
        'name'  => 'List Review',
        'route' => 'admin.reviews.index',
        'sort'  => 1,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.reviews.store',
        'name'  => 'Create Review',
        'route' => 'admin.reviews.store',
        'sort'  => 2,
        'module' => 'Review'
    ],

    [
        'key'   => 'admin.reviews.show',
        'name'  => 'Show Review',
        'route' => 'admin.reviews.show',
        'sort'  => 3,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.reviews.update',
        'name'  => 'Update Review',
        'route' => 'admin.reviews.update',
        'sort'  => 4,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.reviews.delete',
        'name'  => 'Delete Review',
        'route' => 'admin.reviews.delete',
        'sort'  => 5,
        'module' => 'Review'
    ],

    //REVIEW VOTES
    [
        'key'   => 'admin.review_votes.index',
        'name'  => 'List Review Votes',
        'route' => 'admin.review_votes.index',
        'sort'  => 1,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_votes.store',
        'name'  => 'Create Review Votes',
        'route' => 'admin.review_votes.store',
        'sort'  => 2,
        'module' => 'Review'
    ],

    [
        'key'   => 'admin.review_votes.show',
        'name'  => 'Show Review Votes',
        'route' => 'admin.review_votes.show',
        'sort'  => 3,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_votes.update',
        'name'  => 'Update Review Votes',
        'route' => 'admin.review_votes.update',
        'sort'  => 4,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_votes.delete',
        'name'  => 'Delete Review Votes',
        'route' => 'admin.review_votes.delete',
        'sort'  => 5,
        'module' => 'Review'
    ],

     //REVIEW REPLIES
    [
        'key'   => 'admin.review_replies.index',
        'name'  => 'List Review Reply',
        'route' => 'admin.review_replies.index',
        'sort'  => 1,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_replies.store',
        'name'  => 'Create Review Reply',
        'route' => 'admin.review_replies.store',
        'sort'  => 2,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_replies.show',
        'name'  => 'Show Review Reply',
        'route' => 'admin.review_replies.show',
        'sort'  => 3,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_replies.update',
        'name'  => 'Update Review Reply',
        'route' => 'admin.review_replies.update',
        'sort'  => 4,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.review_replies.delete',
        'name'  => 'Delete Review Reply',
        'route' => 'admin.review_replies.delete',
        'sort'  => 5,
        'module' => 'Review'
    ],

    // REVIEW PENDING AND VERIFY
    [
        'key'   => 'admin.reviews.pending',
        'name'  => 'List Review Pending',
        'route' => 'admin.reviews.pending',
        'sort'  => 1,
        'module' => 'Review'
    ],
    [
        'key'   => 'admin.reviews.verify',
        'name'  => 'Delete Review Verify',
        'route' => 'admin.reviews.verify',
        'sort'  => 1,
        'module' => 'Review'
    ],

    //URL REWRITE
    [
        'key'   => 'admin.url-rewrites.index',
        'name'  => 'List URL Rewrite',
        'route' => 'admin.url-rewrites.index',
        'sort'  => 1,
        'module' => 'URL Rewrite'
    ],

    [
        'key'   => 'admin.url-rewrites.store',
        'name'  => 'Create URL Rewrite',
        'route' => 'admin.url-rewrites.store',
        'sort'  => 2,
        'module' => 'URL Rewrite'
    ],

    [
        'key'   => 'admin.url-rewrites.show',
        'name'  => 'Show URL Rewrite',
        'route' => 'admin.url-rewrites.show',
        'sort'  => 3,
        'module' => 'URL Rewrite'
    ],
    [
        'key'   => 'admin.url-rewrites.update',
        'name'  => 'Update URL Rewrite',
        'route' => 'admin.url-rewrites.update',
        'sort'  => 4,
        'module' => 'URL Rewrite'
    ],
    [
        'key'   => 'admin.url-rewrites.delete',
        'name'  => 'Delete URL Rewrite',
        'route' => 'admin.url-rewrites.delete',
        'sort'  => 5,
        'module' => 'URL Rewrite'
    ],

    //COUPON MODULE
    [
        'key'   => 'admin.coupons.index',
        'name'  => 'List Coupon',
        'route' => 'admin.coupons.index',
        'sort'  => 1,
        'module' => 'Coupon'
    ],

    [
        'key'   => 'admin.coupons.store',
        'name'  => 'Create Coupon',
        'route' => 'admin.coupons.store',
        'sort'  => 2,
        'module' => 'Coupon'
    ],

    [
        'key'   => 'admin.coupons.show',
        'name'  => 'Show Coupon',
        'route' => 'admin.coupons.show',
        'sort'  => 3,
        'module' => 'Coupon'
    ],
    [
        'key'   => 'admin.coupons.update',
        'name'  => 'Update Coupon',
        'route' => 'admin.coupons.update',
        'sort'  => 4,
        'module' => 'Coupon'
    ],
    [
        'key'   => 'admin.coupons.delete',
        'name'  => 'Delete Coupon',
        'route' => 'admin.coupons.delete',
        'sort'  => 5,
        'module' => 'Coupon'
    ],

    //EMAIL TEMPLATES MODULE
    [
        'key'   => 'admin.email-templates.index',
        'name'  => 'List Email Template',
        'route' => 'admin.email-templates.index',
        'sort'  => 1,
        'module' => 'Email Template'
    ],
    [
        'key'   => 'admin.email-templates.store',
        'name'  => 'Create Email Template',
        'route' => 'admin.email-templates.store',
        'sort'  => 2,
        'module' => 'Email Template'
    ],
    [
        'key'   => 'admin.email-templates.show',
        'name'  => 'Show Email Template',
        'route' => 'admin.email-templates.show',
        'sort'  => 3,
        'module' => 'Email Template'
    ],
    [
        'key'   => 'admin.email-templates.update',
        'name'  => 'Update Email Template',
        'route' => 'admin.email-templates.update',
        'sort'  => 4,
        'module' => 'Email Template'
    ],
    [
        'key'   => 'admin.email-templates.delete',
        'name'  => 'Delete Email Template',
        'route' => 'admin.email-templates.delete',
        'sort'  => 5,
        'module' => 'Email Template'
    ],

    //CLUB HOUSE MODULE
    [
        'key'   => 'admin.clubhouses.index',
        'name'  => 'List ClubHouse',
        'route' => 'admin.clubhouses.index',
        'sort'  => 1,
        'module' => 'ClubHouse'
    ],

    [
        'key'   => 'admin.clubhouses.store',
        'name'  => 'Create ClubHouse',
        'route' => 'admin.clubhouses.store',
        'sort'  => 2,
        'module' => 'ClubHouse'
    ],

    [
        'key'   => 'admin.clubhouses.show',
        'name'  => 'Show ClubHouse',
        'route' => 'admin.clubhouses.show',
        'sort'  => 3,
        'module' => 'ClubHouse'
    ],
    [
        'key'   => 'admin.clubhouses.update',
        'name'  => 'Update ClubHouse',
        'route' => 'admin.clubhouses.update',
        'sort'  => 4,
        'module' => 'ClubHouse'
    ],
    [
        'key'   => 'admin.clubhouses.delete',
        'name'  => 'Delete ClubHouse',
        'route' => 'admin.clubhouses.delete',
        'sort'  => 5,
        'module' => 'ClubHouse'
    ],

    //PAGE MODULE
    [
        'key'   => 'admin.pages.index',
        'name'  => 'List Page',
        'route' => 'admin.pages.index',
        'sort'  => 1,
        'module' => 'Page'
    ],

    [
        'key'   => 'admin.pages.store',
        'name'  => 'Create Page',
        'route' => 'admin.pages.store',
        'sort'  => 2,
        'module' => 'Page'
    ],

    [
        'key'   => 'admin.pages.show',
        'name'  => 'Show Page',
        'route' => 'admin.pages.show',
        'sort'  => 3,
        'module' => 'Page'
    ],
    [
        'key'   => 'admin.pages.update',
        'name'  => 'Update Page',
        'route' => 'admin.pages.update',
        'sort'  => 4,
        'module' => 'Page'
    ],
    [
        'key'   => 'admin.pages.delete',
        'name'  => 'Delete Page',
        'route' => 'admin.pages.delete',
        'sort'  => 5,
        'module' => 'Page'
    ],

    //TAX MODULE
//    Tax Rate
    [
        'key'   => 'tax.taxes.rates.index',
        'name'  => 'List Tax Rate',
        'route' => 'admin.taxes.rates.index',
        'sort'  => 1,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.store',
        'name'  => 'Create Tax Rate',
        'route' => 'admin.taxes.rates.store',
        'sort'  => 2,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.show',
        'name'  => 'Show Tax Rate',
        'route' => 'admin.taxes.rates.show',
        'sort'  => 3,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.update',
        'name'  => 'Update Tax Rate',
        'route' => 'admin.taxes.rates.update',
        'sort'  => 4,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.delete',
        'name'  => 'Delete Tax Rate',
        'route' => 'admin.taxes.rates.delete',
        'sort'  => 5,
        'module' => 'Tax'
    ],
//    Customer Tax Group
    [
        'key'   => 'tax.taxes.groups.customers.index',
        'name'  => 'List Customer Tax Group',
        'route' => 'admin.taxes.groups.customers.index',
        'sort'  => 1,
        'module' => 'Tax'
    ],

    [
        'key'   => 'tax.taxes.rates.store',
        'name'  => 'Create Customer Tax Group',
        'route' => 'admin.taxes.groups.customers.store',
        'sort'  => 2,
        'module' => 'Tax'
    ],

    [
        'key'   => 'tax.taxes.groups.customers.show',
        'name'  => 'Show Customer Tax Group',
        'route' => 'admin.taxes.groups.customers.show',
        'sort'  => 3,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.groups.customers.update',
        'name'  => 'Update Customer Tax Group',
        'route' => 'admin.taxes.groups.customers.update',
        'sort'  => 4,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.groups.customers.delete',
        'name'  => 'Delete Customer Tax Group',
        'route' => 'admin.taxes.groups.customers.delete',
        'sort'  => 5,
        'module' => 'Tax'
    ],
//    Product Tax Group
    [
        'key'   => 'tax.taxes.groups.products.index',
        'name'  => 'List Product Tax Group',
        'route' => 'admin.taxes.groups.products.index',
        'sort'  => 1,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.store',
        'name'  => 'Create Product Tax Group',
        'route' => 'admin.taxes.groups.products.store',
        'sort'  => 2,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.groups.products.show',
        'name'  => 'Show Product Tax Group',
        'route' => 'admin.taxes.groups.products.show',
        'sort'  => 3,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.groups.products.update',
        'name'  => 'Update Product Tax Group',
        'route' => 'admin.taxes.groups.products.update',
        'sort'  => 4,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.groups.products.delete',
        'name'  => 'Delete Product Tax Group',
        'route' => 'admin.taxes.groups.products.delete',
        'sort'  => 5,
        'module' => 'Tax'
    ],
//    Tax Rule
    [
        'key'   => 'tax.taxes.rules.index',
        'name'  => 'List Tax Rule',
        'route' => 'admin.taxes.rules.index',
        'sort'  => 1,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rates.store',
        'name'  => 'Create Tax Rule',
        'route' => 'admin.taxes.rules.store',
        'sort'  => 2,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rules.show',
        'name'  => 'Show Tax Rule',
        'route' => 'admin.taxes.rules.show',
        'sort'  => 3,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rules.update',
        'name'  => 'Update Tax Rule',
        'route' => 'admin.taxes.rules.update',
        'sort'  => 4,
        'module' => 'Tax'
    ],
    [
        'key'   => 'tax.taxes.rules.delete',
        'name'  => 'Delete Tax Rule',
        'route' => 'admin.taxes.rules.delete',
        'sort'  => 5,
        'module' => 'Tax'
    ],
];

