<?php

return [
    'modules' => [
        'repair' => [
            'provider' => \App\Modules\Repair\Providers\RepairServiceProvider::class,
            'enabled' => true,
            'display_name' => 'Repair',
            'category' => 'maintenance',
            'icon' => 'bi-wrench-adjustable',
            'description' => 'Machine damage report and repair management',
            'sidebar' => [
                [
                    'title' => 'Dashboard',
                    'route' => 'repair.dashboard',
                    'icon' => 'bi-grid-fill',
                    'permission' => null,
                ],
                [
                    'title' => 'Reports',
                    'icon' => 'bi-file-earmark-text',
                    'permission' => 'repair.view',
                    'children' => [
                        [
                            'title' => 'All Reports',
                            'route' => 'repair.reports.index',
                            'permission' => 'repair.view',
                        ],
                        [
                            'title' => 'Create New',
                            'route' => 'repair.reports.create',
                            'permission' => 'repair.create',
                        ],
                    ],
                ],
                [
                    'title' => 'Machines',
                    'route' => 'repair.machines.index',
                    'icon' => 'bi-gear',
                    'permission' => 'repair.view',
                ],
            ],
        ],
    ],

    'categories' => [
        'maintenance' => [
            'name' => 'Maintenance',
            'icon' => 'bi-tools',
            'modules' => ['repair', 'scheduling', 'preventive', 'sparepart', 'personnel'],
        ],
        'hr' => [
            'name' => 'Human Resources',
            'icon' => 'bi-people',
            'modules' => ['recruitment', 'placement', 'development', 'leave'],
        ],
        'production' => [
            'name' => 'Production & Warehouse',
            'icon' => 'bi-box-seam',
            'modules' => ['ppic', 'warehouse'],
        ],
        'quality' => [
            'name' => 'Quality Management',
            'icon' => 'bi-shield-check',
            'modules' => ['qc', 'qa'],
        ],
        'commercial' => [
            'name' => 'Commercial & Business',
            'icon' => 'bi-briefcase',
            'modules' => ['purchasing', 'sales', 'marketing'],
        ],
        'administration' => [
            'name' => 'Administration',
            'icon' => 'bi-clipboard-data',
            'modules' => ['attendance', 'overtime', 'procurement', 'utility'],
        ],
        'general_affair' => [
            'name' => 'General Affair',
            'icon' => 'bi-building',
            'modules' => ['inventory', 'asset'],
        ],
        'engineering' => [
            'name' => 'Production Engineering',
            'icon' => 'bi-cpu',
            'modules' => ['production_management', 'development_engineering'],
        ],
    ],
];
