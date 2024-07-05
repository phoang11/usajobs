<?php

namespace Drupal\usajobs\Service;

/**
 * Provides USAJobs API.
 *
 * @package Drupal\usajobs
 */
interface UsaJobsApiClientInterface {

  /**
   * The usajobs config name.
   */
  const USAJOBS_CONFIG_NAME = 'usajobs.settings';

  /**
   * The number of results per page.
   */
  const RESULTS_PER_PAGE = 10;

  /**
   * The default sort field.
   */
  const SORT_FIELD = 'closedate';

  /**
   * The USAJobs Job Search endpoint.
   */
  const USAJOBS_SEARCH_ENDPOINT = '/api/Search';

  /**
   * The USAJobs Agency Subelements endpoint .
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
