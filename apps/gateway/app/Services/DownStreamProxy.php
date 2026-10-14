<?php

namespace App\Services;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Throwable;

class DownstreamProxy
{
    public function forward(
        IncomingRequest $request,
        ResponseInterface $response,
        string $baseUrl,
        string $path
    ): ResponseInterface {
        $url =
            rtrim($baseUrl, '/') .
            '/' .
            ltrim($path, '/');

        $headers = [
            'Accept' => 'application/json',
        ];

        $authorization =
            $request->getHeaderLine('Authorization');

        if ($authorization !== '') {
            $headers['Authorization'] =
                $authorization;
        }

        $contentType =
            $request->getHeaderLine('Content-Type');

        if ($contentType !== '') {
            $headers['Content-Type'] =
                $contentType;
        }

        $options = [
            'headers' => $headers,
            'http_errors' => false,
            'timeout' => 20,
        ];

        $query = $request->getGet();

        if (!empty($query)) {
            $options['query'] = $query;
        }

        $body = $request->getBody();

        if ($body !== '') {
            $options['body'] = $body;
        }

        try {
            $client = Services::curlrequest(
                [],
                null,
                null,
                false
            );

            $downstream = $client->request(
                strtoupper($request->getMethod()),
                $url,
                $options
            );

            $downstreamContentType =
                $downstream->getHeaderLine(
                    'Content-Type'
                );

            $response->setStatusCode(
                $downstream->getStatusCode()
            );

            if ($downstreamContentType !== '') {
                $response->setHeader(
                    'Content-Type',
                    $downstreamContentType
                );
            }

            return $response->setBody(
                $downstream->getBody()
            );
        } catch (Throwable $e) {
            log_message(
                'error',
                'Gateway downstream error. URL: {url}. Exception: {exception}. Message: {message}',
                [
                    'url' => $url,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            return $response
                ->setStatusCode(502)
                ->setJSON([
                    'success' => false,
                    'message' => 'Downstream service is unavailable.',
                ]);
        }
    }
}
