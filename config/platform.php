<?php

return [
    'tenant_database_driver' => env('PLATFORM_TENANT_DATABASE_DRIVER', 'mysql'),
    'tenant_database_directory' => env('PLATFORM_TENANT_DATABASE_DIRECTORY', storage_path('app/tenants/databases')),
    'tenant_base_domain' => env('PLATFORM_TENANT_BASE_DOMAIN'),
    'tenant_app_path' => env('PLATFORM_TENANT_APP_PATH', realpath(base_path('../poslive')) ?: base_path('../poslive')),
    'tenant_app_php_binary' => env('PLATFORM_TENANT_APP_PHP_BINARY', PHP_BINARY),
    'tenant_bootstrap_input_directory' => env(
        'PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY',
        dirname(base_path()).DIRECTORY_SEPARATOR.'.tenant-bootstrap-input'
    ),
    'tenant_app_bootstrap_url' => env('PLATFORM_TENANT_APP_BOOTSTRAP_URL'),
    'tenant_app_bootstrap_token' => env('PLATFORM_TENANT_APP_BOOTSTRAP_TOKEN'),
    'tenant_mysql_host' => env('PLATFORM_TENANT_DB_HOST', '127.0.0.1'),
    'tenant_mysql_port' => env('PLATFORM_TENANT_DB_PORT', '3306'),
    'tenant_mysql_admin_database' => env('PLATFORM_TENANT_DB_ADMIN_DATABASE', 'mysql'),
    'tenant_mysql_admin_username' => env('PLATFORM_TENANT_DB_ADMIN_USERNAME', 'root'),
    'tenant_mysql_admin_password' => env('PLATFORM_TENANT_DB_ADMIN_PASSWORD', ''),
    'tenant_mysql_database' => env('PLATFORM_TENANT_DB_DATABASE'),
    'tenant_mysql_database_prefix' => env('PLATFORM_TENANT_DB_NAME_PREFIX', env('CPANEL_DATABASE_PREFIX')),
    'tenant_mysql_runtime_username' => env('PLATFORM_TENANT_DB_RUNTIME_USERNAME', env('PLATFORM_TENANT_DB_ADMIN_USERNAME', 'root')),
    'tenant_mysql_runtime_password' => env('PLATFORM_TENANT_DB_RUNTIME_PASSWORD', env('PLATFORM_TENANT_DB_ADMIN_PASSWORD', '')),
    'tenant_mysql_manage_databases' => filter_var(env('PLATFORM_TENANT_DB_MANAGE_DATABASES', true), FILTER_VALIDATE_BOOLEAN),
    'tenant_mysql_manage_users' => filter_var(env('PLATFORM_TENANT_DB_MANAGE_USERS', true), FILTER_VALIDATE_BOOLEAN),
    'tenant_mysql_runtime_grant_hosts' => array_values(array_filter(array_map(
        static fn (string $host) => trim($host),
        explode(',', (string) env('PLATFORM_TENANT_DB_RUNTIME_GRANT_HOSTS', '127.0.0.1,localhost'))
    ))),
    'cpanel_api_enabled' => filter_var(env('CPANEL_API_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'cpanel_host' => env('CPANEL_HOST'),
    'cpanel_port' => env('CPANEL_PORT', '2083'),
    'cpanel_username' => env('CPANEL_USERNAME'),
    'cpanel_token' => env('CPANEL_TOKEN'),
    'cpanel_database_user' => env('CPANEL_DATABASE_USER'),
    'cpanel_verify_ssl' => filter_var(env('CPANEL_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
    'cpanel_timeout' => env('CPANEL_TIMEOUT', 30),
];
