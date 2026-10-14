<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireBearerToken implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        $authorization =
            $request->getHeaderLine(
                'Authorization'
            );

        if (
            $authorization === ''
            || !preg_match(
                '/^Bearer\s+\S+$/i',
                $authorization
            )
        ) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'Bearer authentication token is required.',
                ]);
        }

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        return null;
    }
}