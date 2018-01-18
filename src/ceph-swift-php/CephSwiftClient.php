<?php

namespace Liushuangxi\Ceph;

use GuzzleHttp\Client;

/**
 * Class CephSwiftClient
 * @package Liushuangxi\Ceph
 */
class CephSwiftClient
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Client|null
     */
    private $client = null;

    const URL_AUTH = '/auth';

    /**
     * CephSwiftClient constructor.
     *
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @param $config
     * host         127.0.0.1:1234
     * auth-user    demo
     * auth-key     demo-key
     *
     * @throws \Exception
     */
    public function __construct($config)
    {
        foreach (['host', 'auth-user', 'auth-key'] as $key) {
            if (!isset($config[$key])) {
                throw new \Exception("Ceph Config $key Not Exist");
            }
        }

        $this->config = $config;

        $this->client = new Client([
            'base_uri' => $config['host'],
        ]);
    }

    /**
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @return array
     */
    private function auth()
    {
        try {
            $response = $this->client->request(
                'GET',
                self::URL_AUTH,
                [
                    'headers' => [
                        'X-Auth-User' => $this->config['auth-user'],
                        'X-Auth-Key' => $this->config['auth-key'],
                    ]
                ]
            );

            $headers = $response->getHeaders();

            $auth = [
                'url' => isset($headers['X-Storage-Url'][0]) ? $headers['X-Storage-Url'][0] : '',
                'token' => isset($headers['X-Storage-Token'][0]) ? $headers['X-Storage-Token'][0] : '',
            ];

            return $auth;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function request($method, $uri = '', array $options = [])
    {
        try {
            $response = $this->client->request(
                $method,
                $uri,
                $options
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}