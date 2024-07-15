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

  $config = \Drupal::config('usajobs.settings');

  // Default variables.
  $variables['job_title'] = HTML::escape($item->PositionTitle);
  $variables['job_url'] = UrlHelper::stripDangerousProtocols($item->PositionURI);
  $variables['job_start_date'] = isset($item->PositionStartDate) ? \Drupal::service('date.formatter')->format(strtotime($item->PositionStartDate), 'custom', 'F d, Y') : '';
  $variables['job_end_date'] = isset($item->PositionEndDate) ? \Drupal::service('date.formatter')->format(strtotime($item->PositionEndDate), 'custom', 'F d, Y') : '';
  $variables['job_location_display'] = HTML::escape($item->PositionLocationDisplay);
  $variables['job_salary_min'] = number_format($item->PositionRemuneration[0]->MinimumRange, 2);
  $variables['job_salary_max'] = number_format($item->PositionRemuneration[0]->MaximumRange, 2);

  // Optional variables.
  $position_id = $config->get('field.field_data_source.PositionID');
  if (isset($position_id)) {
    $variables['job_position_id'] = HTML::escape($item->PositionID);
  }

  $organization_name = $config->get('field.field_data_source.OrganizationName');
  if (isset($organization_name)) {
    $variables['job_organization_name'] = HTML::escape($item->OrganizationName);
  }

  $department_name = $config->get('field.field_data_source.DepartmentName');
  if (isset($department_name)) {
    $variables['job_department_name'] = HTML::escape($item->DepartmentName);
  }

  $job_category = $config->get('field.field_data_source.JobCategory');
  if (isset($job_category)) {
    $variables['job_category_name'] = HTML::escape($item->JobCategory[0]->Name);
    $variables['job_category_code'] = HTML::escape($item->JobCategory[0]->Code);
  }

  $job_grade = $config->get('field.field_data_source.JobGrade');
  if (isset($job_grade)) {
    $variables['job_grade'] = HTML::escape($item->JobGrade[0]->Code);
  }

  $major_duties = $config->get('field.field_data_source.MajorDuties');
  if (isset($major_duties)) {
    $variables['job_major_duties'] = HTML::normalize($item->UserArea->Details->MajorDuties[0]);
  }

  $job_summary = $config->get('field.field_data_source.JobSummary');
  if (isset($job_summary)) {
    $variables['job_summary'] = HTML::escape($item->UserArea->Details->JobSummary);
  }

  $low_grade = $config->get('field.field_data_source.LowGrade');
  if (isset($low_grade)) {
    $variables['job_low_grade'] = HTML::escape($item->UserArea->Details->LowGrade);
  }

  $high_grade = $config->get('field.field_data_source.HighGrade');
  if (isset($high_grade)) {
    $variables['job_high_grade'] = HTML::escape($item->UserArea->Details->HighGrade);
  }

  $sub_agency = $config->get('field.field_data_source.SubAgencyName');
  if (isset($sub_agency) && isset($item->SubAgency)) {
    $variables['job_sub_agency'] = HTML::escape($item->SubAgency);
  }

  $telework_eligible = $config->get('field.field_data_source.TeleworkEligible');
  if (isset($telework_eligible)) {
    $variables['job_telework_eligible'] = $item->UserArea->Details->TeleworkEligible;
  }

  $remote_indicator = $config->get('field.field_data_source.RemoteIndicator');
  if (isset($remote_indicator)) {
    $variables['job_remote_indicator'] = $item->UserArea->Details->RemoteIndicator;
  }
}
