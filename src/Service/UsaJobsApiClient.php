<?php

namespace Drupal\usajobs\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
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
   * USAJobs Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

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
   *   Config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelInterface $logger) {

    $this->configFactory = $config_factory;
    $this->logger = $logger;

    // Get the config.
    $config = $this->configFactory->get('usajobs.settings');

    // Build the config for the REST API Client.
    $this->clientConfig = [
      'Host' => $config->get('host'),
      'User-Agent' => $config->get('user_agent'),
      'Authorization-Key' => $config->get('authorization_key'),
      'organization_id' => $config->get('organization_id'),
      'results_per_page' => $config->get('results_per_page'),
      'sort_field' => $config->get('sort_field'),
      'sort_direction' => $config->get('sort_direction'),
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
   * {@inheritDoc}
   */
  public function getJobs() {
    return $this->requestJobs();
  }

  /**
   * {@inheritDoc}
   */
  public function getAgencyList() {
    return $this->requestAgencyList();
  }

  /**
   * Return the USAJobs data from API request call.
   */
  public function requestJobs() {
    $args = [
      'Organization' => $this->clientConfig['organization_id'],
      'ResultsPerPage' => $this->clientConfig['results_per_page'],
      'SortField' => $this->clientConfig['sort_field'],
    ];
    $endpoint_url = $this->clientConfig['Host'] . self::USAJOBS_SEARCH_ENDPOINT;
    return $this->fetch($endpoint_url, $args);
  }

  /**
   * Get the Federal agency list from USAJOBs.
   */
  public function requestAgencyList() {
    $endpoint_url = $this->clientConfig['Host'] . self::USAJOBS_AGENCY_SUBELEMENTS;
    return $this->fetch($endpoint_url);
  }

  /**
   * Fetch data from USAJOBS.gov.
   *
   * @param string $endpoint_url
   *   The complete endpoint url of API call.
   * @param array $parameters
   *   The API call parameters.
   */
  private function fetch($endpoint_url, array $parameters = []) {

    try {
      $client = \Drupal::httpClient();
      if (empty($parameters)) {
        $response = $client->get($endpoint_url, [
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ],
        ]);
      }
      else {
        $response = $client->get($endpoint_url, [
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => $this->clientConfig['User-Agent'],
            'Authorization-Key' => $this->clientConfig['Authorization-Key'],
          ],
          'query' => $parameters,
        ]);
      }

      if (!$response) {
        throw new \Exception('Empty Response');
      }
      $status_code = $response->getStatusCode();
      if ($status_code < 200 || $status_code > 299) {
        $this->logger->error("Couldn't connect to USAJobs Search API: @message",
          ['@message' => $response->getReasonPhrase()]);
        return FALSE;
      }

      $results = new JsonResponse([
        'success' => TRUE,
        'data' => json_decode($response->getBody()),
      ]);

      $results = $results->getContent();

      return json_decode($results);
    }
    catch (RequestException $e) {
      if ($e->getCode() == 401) {
        $this->logger->error("Couldn't connect to USAJobs Search API:
        Received a 401 response from the API. @message",
          ['@message' => $e->getMessage()]);
      }
      elseif ($e->getCode() == 404) {
        $this->logger->error("Couldn't connect to USAJobs Search API:
        Received a 404 response from the API. @message",
          ['@message' => $e->getMessage()]);
      }
      else {
        $this->logger->error("Couldn't connect to USAJobs Search API:
        @message", ['@message' => $e->getMessage()]);
      }

    }

    catch (\Exception $e) {
      $this->logger->error("Couldn't connect to USAJobs API: @message",
        ['@message' => $e->getMessage()]);
    }

    return FALSE;
  }

}
