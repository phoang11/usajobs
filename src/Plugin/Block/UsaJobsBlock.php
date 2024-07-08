<?php

namespace Drupal\usajobs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The USAJobs API client.
   *
   * @var \Drupal\usajobs\Service\UsaJobsApiClientInterface
   */
  protected $usajobs;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UsaJobsApiClientInterface $usa_jobs) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->usajobs = $usa_jobs;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('usajobs.api_client'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $markup = '';

    $jobs = $this->usajobs->getJobs();
    if (!$jobs) {
      $markup = $this->t("Couldn't connect to USAJobs API.");

    }

    if (!empty($jobs)) {
      $jobs = $jobs->data->SearchResult->SearchResultItems;
      // Allow other modules to alter the jobs data.
      \Drupal::moduleHandler()->alter('usajobs_pre_render_jobs', $jobs, $this);
      foreach ($jobs as $job) {
        $job_item = [
          '#theme' => 'usajobs_item',
          '#item' => $job->MatchedObjectDescriptor,
        ];
        $markup .= \Drupal::service('renderer')->render($job_item);
      }
    }
    if (empty($markup)) {
      $markup = $this->t('Currently, there are no job openings available.');
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

}
