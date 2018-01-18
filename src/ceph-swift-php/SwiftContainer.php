<?php

namespace Liushuangxi\Ceph;

/**
 * Class SwiftContainer
 * @package Liushuangxi\Ceph
 */
class SwiftContainer
{
    /**
     * @var SwiftClient
     */
    public $client = null;

    /**
     * SwiftContainer constructor.
     * @param $client SwiftClient
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed|\Psr\Http\Message\StreamInterface
     */
    public function listContainers()
    {
        return $this->listObjects('');
    }

    /**
     * @param $container
     * @param array $params
     * @return mixed|\Psr\Http\Message\StreamInterface
     */
    public function listObjects($container, $params = ['format' => 'json'])
    {
        $response = $this->client->request(
            'GET',
            $container,
            [
                'query' => $params
            ]
        );

        if (isset($params['format']) && $params['format'] == 'json') {
            return @json_decode($response->getBody(), true);
        } else {
            return $response->getBody();
        }
    }

    /**
     * @param $container
     * @param array $headers
     * @return bool
     */
    public function createContainer($container, $headers = [])
    {
        if (empty($headers)) {
            $headers = [
                'X-Container-Read' => $this->client->config['auth-user'],
                'X-Container-Write' => $this->client->config['auth-user']
            ];
        }

        $response = $this->client->request(
            'PUT',
            $container,
            [
                'headers' => $headers
            ]
        );

        if (is_null($response)) {
            return false;
        }

        return $this->isExistContainer($container);
    }

    /**
     * @param $container
     * @return bool
     */
    public function isExistContainer($container)
    {
        $response = $this->client->request(
            'HEAD',
            $container
        );

        if (!is_null($response) && !empty($response->getHeaders())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $container
     * @return bool
     */
    public function deleteContainer($container)
    {
        $response = $this->client->request(
            'DELETE',
            $container
        );

        return !$this->isExistContainer($container);
    }

    /**
     * @param $headers
     */
    public function updateACLs($headers)
    {

    }

    /**
     * @param $headers
     */
    public function updateMetas($headers)
    {

    }
}
