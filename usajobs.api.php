<?php

/**
 * @file
 * Hooks related to USAJobs module.
 */

// phpcs:disable

/**
 * @addtogroup hooks
 * @{
 */


/**
 * Allows modules to alter the jobs data before it is rendered by the UsaJobsBlock.
 *
 * @param array $jobs
 *   The array of job data fetched from the USAJobs API.
 * @param \Drupal\usajobs\Plugin\Block\UsaJobsBlock $block
 *   The UsaJobsBlock instance.
 */
function hook_usajobs_pre_render_jobs_alter(&$jobs, $block) {
  // Example implementation.
  // Remove jobs that don't meet certain criteria.
  foreach ($jobs as $key => $job) {
    if ($job->MatchedObjectDescriptor->PositionEndDate < time()) {
      unset($jobs[$key]);
    }
  }
}