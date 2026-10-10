<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Feature Redirect Priority
    |--------------------------------------------------------------------------
    |
    | Saat user masuk ke route tenant yang tidak tersedia di paket aktif,
    | middleware `tenant.feature` akan mencoba mengarahkan ke route pertama
    | yang benar-benar tersedia dan fitur paketnya aktif.
    |
    | Route yang belum terdaftar di aplikasi akan otomatis dilewati, jadi
    | daftar ini aman dipakai lintas tenant app yang belum lengkap.
    | Route universal seperti `home` atau `account.profile` bisa diletakkan
    | di atas tanpa key `feature`, sehingga semua role tenant punya titik
    | masuk yang aman sebelum diarahkan ke menu-menu khusus.
    |
    */
    'redirect_priority' => [
        ['route' => 'home'],
        ['route' => 'account.profile'],
        ['feature' => 'menu_dashboard', 'route' => 'dashboard'],
        ['feature' => 'menu_products', 'route' => 'products.index'],
        ['feature' => 'menu_categories', 'route' => 'categories.index'],
        ['feature' => 'menu_reports', 'route' => 'reports.index'],
        ['feature' => 'menu_subscription_center', 'route' => 'subscription.index'],
    ],
];
