<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Services\DownstreamProxy;
use CodeIgniter\HTTP\ResponseInterface;

class GatewayController extends BaseController
{
    private DownstreamProxy $proxy;

    private string $authService;
    private string $assetService;

    public function __construct()
    {
        $this->proxy =
            new DownstreamProxy();

        $this->authService =
            (string) env(
                'AUTH_USER_SERVICE_URL',
                'http://127.0.0.1:8101'
            );

        $this->assetService =
            (string) env(
                'ASSET_SERVICE_URL',
                'http://127.0.0.1:8102'
            );
    }

    private function auth(
        string $path
    ): ResponseInterface {
        return $this->proxy->forward(
            $this->request,
            $this->response,
            $this->authService,
            '/api/v1/' . $path
        );
    }

    private function asset(
        string $path
    ): ResponseInterface {
        return $this->proxy->forward(
            $this->request,
            $this->response,
            $this->assetService,
            '/api/v1/' . $path
        );
    }

    public function health(): ResponseInterface
    {
        return $this->response->setJSON([
            'success' => true,
            'service' => 'api-gateway',
            'status' => 'ok',
        ]);
    }

    // =====================================================
    // Auth
    // =====================================================

    public function login(): ResponseInterface
    {
        return $this->auth('auth/login');
    }

    public function logout(): ResponseInterface
    {
        return $this->auth('auth/logout');
    }

    public function me(): ResponseInterface
    {
        return $this->auth('me');
    }

    // =====================================================
    // Users
    // =====================================================

    public function users(): ResponseInterface
    {
        return $this->auth('users');
    }

    public function user(
        string $id
    ): ResponseInterface {
        return $this->auth(
            'users/' . $id
        );
    }

    public function userStatus(
        string $id
    ): ResponseInterface {
        return $this->auth(
            'users/' .
            $id .
            '/status'
        );
    }

    public function userRole(
        string $id
    ): ResponseInterface {
        return $this->auth(
            'users/' .
            $id .
            '/role'
        );
    }

    public function roles(): ResponseInterface
    {
        return $this->auth('roles');
    }

    // =====================================================
    // Assets
    // =====================================================

    public function assets(): ResponseInterface
    {
        return $this->asset('assets');
    }

    public function assetsBulk(): ResponseInterface
    {
        return $this->asset(
            'assets/bulk'
        );
    }

    public function assetDetail(
        string $id
    ): ResponseInterface {
        return $this->asset(
            'assets/' . $id
        );
    }

    public function assetStatus(
        string $id
    ): ResponseInterface {
        return $this->asset(
            'assets/' .
            $id .
            '/status'
        );
    }

    public function assetHistory(
        string $id
    ): ResponseInterface {
        return $this->asset(
            'assets/' .
            $id .
            '/history'
        );
    }

    public function assetAvailability(): ResponseInterface
    {
        return $this->asset(
            'assets/availability'
        );
    }

    // =====================================================
    // Asset Batch
    // =====================================================

    public function assetBatches(): ResponseInterface
    {
        return $this->asset(
            'asset-batches'
        );
    }

    public function assetBatch(
        string $id
    ): ResponseInterface {
        return $this->asset(
            'asset-batches/' . $id
        );
    }
}