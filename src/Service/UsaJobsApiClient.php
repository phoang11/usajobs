<?php

namespace Drupal\usajobs\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\ClientInterface;
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
   * USAJobs Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new UsaJobsApiClient object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelInterface $logger, ClientInterface $httpClient) {

    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->httpClient = $httpClient;
  }

  /**
   * Get's usajobs settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Get the settings config.
   */
  protected function config(): ImmutableConfig {
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
      'Organization' => $this->config()->get('organization_id'),
      'ResultsPerPage' => $this->config()->get('results_per_page'),
      'SortField' => $this->config()->get('sort_field'),
    ];
    $endpoint_url = self::USAJOBS_HOST_URL . self::USAJOBS_SEARCH_ENDPOINT;
    return $this->fetch($endpoint_url, $args);
  }

  /**
   * Get the Federal agency list from USAJOBs.
   */
  public function requestAgencyList() {
    $endpoint_url = self::USAJOBS_HOST_URL . self::USAJOBS_AGENCY_SUBELEMENTS;
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

      if (empty($parameters)) {
        $response = $this->httpClient->request('GET', $endpoint_url, [
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ],
        ]);
      }
      else {
        $response = $this->httpClient->request('GET', $endpoint_url, [
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => $this->config()->get('user_agent'),
            'Authorization-Key' => $this->config()->get('authorization_key'),
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
        $errorMessage = "Received a 401 response from the API.";
      }
      elseif ($e->getCode() == 404) {
        $errorMessage = "Received a 404 response from the API.";
      }
      else {
        $errorMessage = "";
      }

      $this->logger->error("Couldn't connect to USAJobs Search API: @message",
        ['@message' => $errorMessage . $e->getMessage()]);

    }

    catch (\Exception $e) {
      $this->logger->error("Couldn't connect to USAJobs API: @message",
        ['@message' => $e->getMessage()]);
    }

    return FALSE;
  }

}
