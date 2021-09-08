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
   * The USAJobs search endpoint.
   */
  const USAJOBS_SEARCH_ENDPOINT = '/api/Search';

  /**
   * The USAJobs codelist endpoint.
   */
  const USAJOBS_AGENCY_SUBELEMENTS = '/api/codelist/agencysubelements';

  /**
   * Retrieves jobs information.
   *
   * @return \Drupal\usajobs\Job
   *   The jobs data from API call.
   */
  public function getJobs();

  /**
   * Retrieves agencies list.
   *
   * @return \Drupal\usajobs\AgencyList
   *   The agencies list data from API call.
   */
  public function getAgencyList();

}
