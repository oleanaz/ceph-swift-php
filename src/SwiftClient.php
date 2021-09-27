<?php

namespace Liushuangxi\Ceph;

use GuzzleHttp\Client;
use Liushuangxi\Ceph\Traits\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class SwiftClient
 *
 * @package Liushuangxi\Ceph
 */
class SwiftClient
{
    use Logger;

    /**
     * @var string
     */
    const URL_AUTH = '/auth';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var Client|null
     */
    public $client = null;

    /**
     * SwiftClient constructor.
     *
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @param $config
     * host             127.0.0.1:1234
     * auth-user        auth-user
     * auth-key         auth-key
     *
     * temp-url-key     key
     *
     * @throws \Exception
     */
    public function __construct($config, LoggerInterface $logger = null)
    {
        foreach (['host', 'auth-user', 'auth-key'] as $key) {
            if (!isset($config[$key])) {
                throw new \Exception("Ceph Config $key Not Exist");
            }
        }

        $this->config = $config;
        $this->client = new Client();
        $this->logger = $logger;
        $auth         = $this->auth(
            $config['host'],
            $config['auth-user'],
            $config['auth-key'],
            $config['version']
        );

        if (!$auth) {
            throw new \Exception('Ceph Auth Failed');
        }
    }

    /**
     * http://docs.ceph.org.cn/radosgw/swift/auth/
     *
     * @param $host
     * @param $authUser
     * @param $authKey
     * @param $version
     *
     * @return bool
     */
    public function auth($host, $authUser, $authKey, $version)
    {
        try {
            $host = str_replace(['https://', 'http://'], ['', ''], $host);

            $response = $this->client->request(
                'GET',
                $host . self::URL_AUTH,
                [
                    'headers' => [
                        'X-Auth-User' => $authUser,
                        'X-Auth-Key'  => $authKey,
                    ],
                ]
            );

            $headers      = $response->getHeaders();
            $storageUrl   = 'X-Storage-Url';
            $storageToken = 'X-Storage-Token';

            if ((int)$version == 1) {
                if (isset($headers[$storageUrl][0]) && isset($headers[$storageToken][0])) {
                    $this->baseUrl = $headers[$storageUrl][0];
                    $this->token   = $headers[$storageToken][0];

                    return true;
                }
            } elseif ((int)$version == 2) {
                $storageUrl   = strtolower($storageUrl);
                $storageToken = strtolower($storageToken);
                if (isset($headers[$storageUrl][0]) && isset($headers[$storageToken][0])) {
                    $this->baseUrl = $headers[$storageUrl][0];
                    $this->token   = $headers[$storageToken][0];

                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->error('Ceph Auth Failed', [
                'exception' => $e,
                'host'      => $host,
                'authUser'  => $authUser,
            ]);

            return false;
        }

        return false;
    }

    /**
     * http://docs.ceph.org.cn/radosgw/swift/containerops/
     *
     * @return SwiftContainer
     */
    public function container()
    {
        return new SwiftContainer($this);
    }

    /**
     * @return SwiftObject
     */
    public function object()
    {
        return new SwiftObject($this);
    }

    /**
     * http://docs.ceph.org.cn/radosgw/swift/tempurl/
     *
     * @return SwiftUrl
     */
    public function url()
    {
        return new SwiftUrl($this);
    }

    /**
     * @param        $method
     * @param string $uri
     * @param array  $options
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
            $this->error('Request failed', [
                'exception' => $e,
                'method'    => $method,
                'uri'       => $uri,
            ]);

            return null;
        }
    }

    /**
     * @return string
     */
    public function getAuthUser(): string
    {
        return $this->config['auth-user'];
    }

    /**
     * @return string
     */
    public function getTempUrl(): string
    {
        return $this->config['temp-url-key'] ?? '';
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->config['host'] ?? '';
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->config['bucket'] ?? '';
    }
}
