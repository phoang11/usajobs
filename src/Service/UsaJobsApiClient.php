<?php

namespace Drupal\usajobs\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UsaJobsApiClient to handle USAJobs API request data.
 */
class UsaJobsApiClient implements UsaJobsApiClientInterface {


  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The config used to instantiate the REST API client.
   *
   * @var array
   */
  private $clientConfig;

  /**
   * Constructs a new UsaJobsApiClient object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_default, LoggerChannelFactoryInterface $logger_factory) {

    $this->configFactory = $config_factory;
    $this->cacheDefault = $cache_default;
    $this->loggerFactory = $logger_factory;

    // Get the config.
    $config = $this->config();

    // Build the config for the REST API Client.
    $this->clientConfig = [
      'Host' => $config->get('host'),
      'User-Agent' => $config->get('user_agent'),
      'Authorization-Key' => $config->get('authorization_key'),
      'organization_id' => $config->get('organization_id'),
      'Accept' => 'application/json',
    ];
  }

  /**
   * Get's usajobs settings.
   *
   * @return string
   *   Get the settings config name.
   */
  protected function config() {
    return $this->configFactory->get(self::USAJOBS_CONFIG_NAME);
  }

  /**
   * @inheritDoc
   */
  public function getJobs() {
    return $this->requestJobs();
  }

  /**
   * @inheritDoc
   */
  public function getAgencyList() {
    return $this->requestAgencyList();
  }

  /**
   * @return mixed
   *   Return the USAJobs data from API request call.
   */
  public function requestJobs() {
    $args = [
      'Organization' => $this->clientConfig['organization_id'],
    ];
    $endpoint_url = 'https://' . $this->clientConfig['Host'] . self::USAJOBS_SEARCH_ENDPOINT;
    return $this->fetch($endpoint_url, $args);
  }

  /**
   * @return mixed
   *   Get the Federal agency list from USAJOBs.
   */
  public function requestAgencyList() {
    $endpoint_url = 'https://' . $this->clientConfig['Host'] . self::USAJOBS_AGENCY_SUBELEMENTS;
    return $this->fetch($endpoint_url);
  }

  /**
   * @param string $endpoint_url
   *   The complete endpoint url of API call.
   * @param array $parameters
   *   The API call parameters.
   *
   * @return bool|array
   *   Fetch data from USAJOBS.gov.
   */
  private function fetch($endpoint_url, array $parameters = []) {

    try {
      $client = \Drupal::httpClient();
      $response = $client->get($endpoint_url, [
        'headers' => $this->clientConfig,
        'query' => $parameters,
      ]);

      $results = new JsonResponse([
        'success' => TRUE,
        'data' => json_decode($response->getBody()),
      ]);

      $results = $results->getContent();

      return json_decode($results);
    }
    catch (RequestException $e) {
      watchdog_exception('usajobs', $e);
      return FALSE;
    }
  }

}
