<?php

/**
 * @file
 * Preprocessors and theme functions of USAJobs module.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;

/**
 * Prepares variables for usajobs item templates.
 *
 * Default template: usajobs-item.html.twig.
 *
 * By default this function performs special preprocessing to create a separate
 * variable for the title base field. This preprocessing is skipped if:
 * - a module makes the field's display configurable via the field UI by means
 *   of BaseFieldDefinition::setDisplayConfigurable()
 * - AND the additional entity type property
 *   'enable_base_field_custom_preprocess_skipping' has been set using
 *   hook_entity_type_build().
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An array of elements to display in view mode.
 */
function template_preprocess_usajobs_item(&$variables) {
  $item = $variables['elements']['#item'];

  $variables['job_title'] = $item->PositionTitle;
  $variables['job_url'] = UrlHelper::stripDangerousProtocols($item->PositionURI);
  $variables['job_apply_url'] = UrlHelper::stripDangerousProtocols($item->ApplyURI[0]);
  $variables['job_organization'] = HTML::escape($item->OrganizationName);
  $variables['job_location'] = HTML::escape($item->PositionLocationDisplay);
  $variables['job_salary_min'] = number_format($item->PositionRemuneration[0]->MinimumRange, 2);
  $variables['job_salary_max'] = number_format($item->PositionRemuneration[0]->MaximumRange, 2);

  $variables['job_start_date'] = '';
  if (isset($item->PositionStartDate)) {
    $variables['job_start_date'] = \Drupal::service('date.formatter')->format(strtotime($item->PositionStartDate), 'custom', 'F d, Y');
  }

  $variables['job_end_date'] = '';
  if (isset($item->PositionEndDate)) {
    $variables['job_end_date'] = \Drupal::service('date.formatter')->format(strtotime($item->PositionEndDate), 'custom', 'F d, Y');
  }

}
