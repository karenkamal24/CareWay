<?php

return [
    'shield_resource' => [
        'should_register_navigation' => true, 
        'slug' => 'shield/roles', 
        'navigation_sort' => -1,
        'navigation_badge' => true, 
        'navigation_group' => true, 
        'show_model_path' => true, 
    ],

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin', // اسم الدور الأعلى صلاحية
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user', // المستخدم العادي
    ],

    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'widget_chart', 
            'view_chart', 
            
         
            
        ],
    ],

    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
        'Role' => true,
        'User' => true,
        'Permission' => true,
    ],

    'discovery' => [
        'discover_all_resources' => true,
        'discover_all_widgets' => true, 
        'discover_all_pages' => true, 
    ],
];
