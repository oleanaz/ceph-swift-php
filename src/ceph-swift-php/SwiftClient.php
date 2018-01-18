<?php

namespace Liushuangxi\Ceph;

use GuzzleHttp\Client;

/**
 * Class SwiftClient
 * @package Liushuangxi\Ceph
 */
class SwiftClient
{
    /**
     *
     */
    const URL_AUTH = '/auth';

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var Client|null
     */
    private $client = null;

    /**
     * @var string
     */
    private $baseUrl = '';

    /**
     * @var string
     */
    private $token = '';

    /**
     * SwiftClient constructor.
     *
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @param $config
     * host         127.0.0.1:1234
     * auth-user    demo-user
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

        $this->client = new Client();

        $auth = $this->auth($config['host'], $config['auth-user'], $config['auth-key']);
        if (!$auth) {
            throw new \Exception("Ceph Auth Failed");
        }
    }

    /**
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @param $host
     * @param $authUser
     * @param $authKey
     *
     * @return bool
     */
    private function auth($host, $authUser, $authKey)
    {
        try {
            $host = str_replace(['https://', 'http://', '/'], ['', '', ''], $host);

            $response = $this->client->request(
                'GET',
                $host . self::URL_AUTH,
                [
                    'headers' => [
                        'X-Auth-User' => $authUser,
                        'X-Auth-Key' => $authKey,
                    ]
                ]
            );

            $headers = $response->getHeaders();

            if (isset($headers['X-Storage-Url'][0]) && isset($headers['X-Storage-Token'][0])) {
                $this->baseUrl = $headers['X-Storage-Url'][0];
                $this->token = $headers['X-Storage-Token'][0];

                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return SwiftContainer
     */
    public function container()
    {
        return new SwiftContainer($this);
    }

    /**
     * @param $method
     * @param string $uri
     * @param array $options
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function request($method, $uri = '', array $options = [])
    {
        $options['headers']['X-Auth-Token'] = $this->token;

        try {
            return $this->client->request(
                $method,
                $this->baseUrl . "/" . trim($uri),
                $options
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
