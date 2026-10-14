<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group(
    'api/v1',
    [
        'namespace' =>
        'App\Controllers\Api\V1',
    ],
    static function ($routes) {

        // ==============================================
        // Public
        // ==============================================

        $routes->get(
            'health',
            'GatewayController::health'
        );

        $routes->post(
            'auth/login',
            'GatewayController::login'
        );

        // ==============================================
        // Protected
        // ==============================================

        $routes->group(
            '',
            ['filter' => 'bearer'],
            static function ($routes) {

                // Auth
                $routes->get(
                    'me',
                    'GatewayController::me'
                );

                $routes->post(
                    'auth/logout',
                    'GatewayController::logout'
                );

                // Users
                $routes->get(
                    'users',
                    'GatewayController::users'
                );

                $routes->post(
                    'users',
                    'GatewayController::users'
                );

                $routes->get(
                    'users/(:segment)',
                    'GatewayController::user/$1'
                );

                $routes->patch(
                    'users/(:segment)',
                    'GatewayController::user/$1'
                );

                $routes->patch(
                    'users/(:segment)/status',
                    'GatewayController::userStatus/$1'
                );

                $routes->patch(
                    'users/(:segment)/role',
                    'GatewayController::userRole/$1'
                );

                $routes->get(
                    'roles',
                    'GatewayController::roles'
                );

                // Assets
                $routes->get(
                    'assets',
                    'GatewayController::assets'
                );

                $routes->post(
                    'assets',
                    'GatewayController::assets'
                );

                $routes->post(
                    'assets/bulk',
                    'GatewayController::assetsBulk'
                );

                $routes->get(
                    'assets/availability',
                    'GatewayController::assetAvailability'
                );

                $routes->get(
                    'assets/(:segment)',
                    'GatewayController::assetDetail/$1'
                );

                $routes->patch(
                    'assets/(:segment)',
                    'GatewayController::assetDetail/$1'
                );

                $routes->patch(
                    'assets/(:segment)/status',
                    'GatewayController::assetStatus/$1'
                );

                $routes->get(
                    'assets/(:segment)/history',
                    'GatewayController::assetHistory/$1'
                );

                // Asset batches
                $routes->get(
                    'asset-batches',
                    'GatewayController::assetBatches'
                );

                $routes->get(
                    'asset-batches/(:segment)',
                    'GatewayController::assetBatch/$1'
                );
            }
        );

        $routes->get('debug/downstream', static function () {
            return service('response')->setJSON([
                'auth' => env('AUTH_USER_SERVICE_URL'),
                'asset' => env('ASSET_SERVICE_URL'),
            ]);
        });
    }
);
