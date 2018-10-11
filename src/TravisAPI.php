<?php
namespace NibyNool\PHPTravis;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\ResultInterface;

/**
 * Class TravisAPI
 *
 * @package NibyNool\PHPTravis
 *
 * @method ResultInterface getActive(array $parameters)
 * @method ResultInterface getBranch(array $parameters)
 * @method ResultInterface listBranches(array $parameters)
 * @method ResultInterface getBuild(array $parameters)
 * @method ResultInterface cancelBuild(array $parameters)
 * @method ResultInterface restartBuild(array $parameters)
 * @method ResultInterface listBuilds(array $parameters)
 * @method ResultInterface findBuilds(array $parameters)
 */
class TravisAPI
{
    /** @var Client $client GuzzleHttp client */
    protected $client;
    /** @var Description $description Guzzle service description */
    protected $description;
    /** @var GuzzleClient $guzzleClient Guzzle service client */
    protected $guzzleClient;

    /** @var array $defaultConfig Default Guzzle configuration */
    private static $defaultConfig = [
        'headers' => [
            'Travis-API-Version' => '3',
            'User-Agent' => 'NibyNool PHP API',
        ],
    ];
    /** @var array $baseUrls Base URLs for accessing Travis */
    private static $baseUrls = [
        'openSource' => 'https://api.travis-ci.org',
        'private'    => 'https://api.travis-ci.com'
    ];

    /**
     * TravisAPI constructor.
     *
     * @param string $token Travis CI token to be used for authentication
     * @param bool $openSource If the open source or private version of Travis is being used
     * @param array $config Guzzle configuration overrides
     */
    public function __construct($token, $openSource = true, $config = [])
    {
        $config = array_merge_recursive(self::$defaultConfig, ['headers' => ['Authorization' => 'token '.$token]], $config);
        $this->client = new Client($config);
        $serviceDescription = \json_decode(file_get_contents(__DIR__.'/service.json'), true);
        $serviceDescription['baseUrl'] = !$openSource ? self::$baseUrls['private'] : self::$baseUrls['openSource'];
        $this->description = new Description($serviceDescription);
        $this->guzzleClient = new GuzzleClient($this->client, $this->description);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return ResultInterface|mixed
     */
    public function __call($method, $arguments)
    {
        $command = $this->guzzleClient->getCommand($method, $arguments[0]);
        return $this->guzzleClient->execute($command);
    }
}