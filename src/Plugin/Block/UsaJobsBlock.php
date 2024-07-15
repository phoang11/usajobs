<?php

namespace Drupal\usajobs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\usajobs\Service\UsaJobsApiClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'UsaJobsBlock' block.
 *
 * @Block(
 *  id = "usajobs_block",
 *  admin_label = @Translation("USAJobs Listing"),
 * )
 */
class UsaJobsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The USAJobs API client.
   *
   * @var \Drupal\usajobs\Service\UsaJobsApiClientInterface
   */
  protected $usajobs;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private RendererInterface $renderer;

  /**
   * UsaJobsBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\usajobs\Service\UsaJobsApiClientInterface $usa_jobs
   *   The usajobs data from API call.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UsaJobsApiClientInterface $usa_jobs, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->usajobs = $usa_jobs;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('usajobs.api_client'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('renderer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $config = $this->configFactory->get('usajobs.settings');
    $markup = '';

    $jobs = $this->usajobs->getJobs();
    if (!$jobs) {
      $markup = $this->t("Couldn't connect to USAJobs API.");
    }

    if (!empty($jobs)) {
      $jobs = $jobs->data->SearchResult->SearchResultItems;
      $this->moduleHandler->alter('usajobs_pre_render_jobs', $jobs);

      $sub_agency = $config->get('sub_agency_name');
      if (!empty($sub_agency)) {
        $jobs = $this->alterJobsData($jobs, $sub_agency);
      }

      foreach ($jobs as $job) {
        $job_item = [
          '#theme' => 'usajobs_item',
          '#item' => $job->MatchedObjectDescriptor,
        ];
        $markup .= $this->renderer->render($job_item);
      }
    }

    if (empty($markup)) {
      $markup = $config->get('no_results_message');
    }

    $build = [
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'usajobs/usajobs',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

  /**
   * Filter results by Sub Agency Name.
   *
   * @param array $jobs
   *   The array of job data fetched from the USAJobs API.
   * @param string $sub_agency
   *   The sub-agency name to filter the jobs by.
   *
   * @return array
   *   The filtered array of job data.
   */
  private function alterJobsData(array &$jobs, string $sub_agency) {
    $filteredJobs = [];

    foreach ($jobs as $key => $job) {
      if (isset($job->MatchedObjectDescriptor->SubAgency) && strpos($job->MatchedObjectDescriptor->SubAgency, $sub_agency) !== FALSE) {
        $filteredJobs[$key] = $job;
      }
    }

    return $filteredJobs;
  }

}
