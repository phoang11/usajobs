<?php

namespace Drupal\usajobs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\usajobs\Service\UsaJobsApiClientInterface;

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
   * Drupal\usajobs\Service\UsaJobsApiClientInterface definition.
   *
   * @var Drupal\usajobs\Service\UsaJobsApiClientInterface
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

    $jobs = $this->usajobs->getJobs();
    $jobs = $jobs->data->SearchResult->SearchResultItems;
    $markup = '';
    foreach ($jobs as $job) {
      $job_item = [
        '#theme' => 'usajobs_item',
        '#item' => $job->MatchedObjectDescriptor,
      ];
      $markup .= \Drupal::service('renderer')->render($job_item);
    }

    if (empty($markup)) {
      $markup = $this->t('There are no vacancy announcements at this time..');
    }

    $build = [
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'usajobs/usajobs',
        ],
      ],
    ];

    return $build;
  }

}
