<?php

namespace Drupal\usajobs\Service;

/**
 * Provides USAJobs API.
 *
 * @package Drupal\usajobs
 */
interface UsaJobsApiClientInterface {

  /**
   * The base URL of the USAJobs API.
   */
  const USAJOBS_HOST_URL = 'https://data.usajobs.gov';

  /**
   * The name of the USAJobs configuration.
   */
  const USAJOBS_CONFIG_NAME = 'usajobs.settings';

  /**
   * No results message.
   */
  const NO_RESULTS_MESSAGE = 'Currently, there are no job openings available.';

  /**
   * The number of results per page.
   */
  const RESULTS_PER_PAGE = 10;

  /**
   * The default sort field.
   */
  const SORT_FIELD = 'closedate';

  /**
   * The search endpoint of the USAJobs API.
   */
  const USAJOBS_SEARCH_ENDPOINT = '/api/Search';

  /**
   * The agency subelements endpoint of the USAJobs API.
   */
  const USAJOBS_AGENCY_SUBELEMENTS = '/api/codelist/agencysubelements';

  /**
   * Retrieves jobs information.
   */
  public function getJobs();

  /**
   * Retrieves the Federal agency list.
   */
  public function getAgencyList();

}
