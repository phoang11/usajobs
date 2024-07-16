<?php

namespace Drupal\usajobs\Form;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\usajobs\Service\UsaJobsApiClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class config form for USAJOBs module settings.
 */
class UsaJobsConfigForm extends ConfigFormBase {

  /**
   * USAJobs API Service.
   *
   * @var \Drupal\usajobs\Service\UsaJobsApiClientInterface
   */
  protected $usajobs;

  /**
   * UsaJobsConfigForm constructor.
   *
   * @param \Drupal\usajobs\Service\UsaJobsApiClientInterface $usajobs
   *   The usajobs data from API call.
   */
  public function __construct(UsaJobsApiClientInterface $usajobs) {
    $this->usajobs = $usajobs;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('usajobs.api_client'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'usajobs_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('usajobs.settings');

    $form['usajobs_basic'] = [
      '#title' => $this->t('Authentication'),
      '#type' => 'fieldset',
      '#description' => $this->t('Accessing the USAJOBS API will require an API Key. To request an API Key, please go the <a href="@api-request" target="_blank"> API Access Request page</a>.', ['@api-request' => 'https://developer.usajobs.gov/APIRequest/Index']),
    ];
    $form['usajobs_basic']['user_agent'] = [
      '#type' => 'email',
      '#title' => $this->t('User-Agent'),
      '#maxlength' => 64,
      '#size' => 64,
      '#description' => $this->t('The email address used when requesting the USAJobs API key.'),
      '#default_value' => $config->get('user_agent'),
      '#required' => TRUE,
    ];
    $form['usajobs_basic']['authorization_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization Key'),
      '#description' => $this->t('The Authorization key provided by USAJobs.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('authorization_key'),
      '#required' => TRUE,
    ];

    $form['usajobs_tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'tab_api',
    ];

    // Query Parameters tab.
    $form['query_tab'] = [
      '#title' => $this->t('Query Parameters'),
      '#type' => 'details',
      '#description' => $this->t('The query parameters for search USAJobs API. Visit the <a href="https://developer.usajobs.gov/api-reference/get-api-search">USAJobs API Reference</a> page for detailed information.'),
      '#group' => 'usajobs_tabs',
    ];

    $agency_sub_elements = $this->getAgencySubElements();
    asort($agency_sub_elements);
    $form['query_tab']['organization_id'] = [
      '#title' => $this->t('Organization'),
      '#type' => 'select',
      '#options' => $agency_sub_elements,
      '#description' => $this->t('Select the Organizations.'),
      '#default_value' => $config->get('organization_id'),
    ];

    $form['query_tab']['results_per_page'] = [
      '#type' => 'select',
      '#title' => $this->t('Results Per Page'),
      '#description' => $this->t('Select the number of results to display on block.'),
      '#options' => [
        5 => $this->t('5'),
        10 => $this->t('10'),
        15 => $this->t('15'),
        20 => $this->t('20'),
        25 => $this->t('25'),
        30 => $this->t('30'),
        40 => $this->t('40'),
        50 => $this->t('50'),
        100 => $this->t('100'),
        200 => $this->t('200'),
        300 => $this->t('300'),
        500 => $this->t('500'),
      ],
      '#default_value' => $config->get('results_per_page') ?: UsaJobsApiClientInterface::RESULTS_PER_PAGE,
    ];

    $form['query_tab']['sort_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort Field'),
      '#options' => [
        '' => $this->t('- None -'),
        'opendate' => $this->t('Position start date'),
        'closedate' => $this->t('Position end date'),
        'positiontitle' => $this->t('Position title'),
      ],
      '#description' => $this->t('Select the field to sort the results by.'),
      '#default_value' => $config->get('sort_field') ?: UsaJobsApiClientInterface::SORT_FIELD,
    ];

    // Field Data Source tab.
    $form['field_tab'] = [
      '#title' => $this->t('Field Data Source'),
      '#type' => 'details',
      '#description' => $this->t('Select the optional field data source to be displayed on your custom twig template file.'),
      '#group' => 'usajobs_tabs',
    ];

    $fields = [
      [
        'fieldName' => 'PositionID',
        'fieldDescription' => 'Job Announcement Number',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'PositionTitle',
        'fieldDescription' => 'Title of the job offering.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'PositionURI',
        'fieldDescription' => 'URI to view the job offering.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'PositionLocationDisplay',
        'fieldDescription' => 'Contains values for location name, country, country subdivision, city, latitude and longitude.',
        'fieldType' => 'Object',
      ],
      [
        'fieldName' => 'OrganizationName',
        'fieldDescription' => 'Name of the organization or agency offering the position.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'DepartmentName',
        'fieldDescription' => 'Name of the department within the organization or agency offering the position.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'JobCategory',
        'fieldDescription' => 'List of job category objects that contain values for name and code.',
        'fieldType' => 'Array',
      ],
      [
        'fieldName' => 'JobGrade',
        'fieldDescription' => 'List of job grade objects that contains an code value. This field is also known as Pay Plan.',
        'fieldType' => 'Array',
      ],
      [
        'fieldName' => 'PositionStartDate',
        'fieldDescription' => 'The date the job opportunity will be open to applications.',
        'fieldType' => 'Datetime',
      ],
      [
        'fieldName' => 'PositionEndDate',
        'fieldDescription' => 'Last date the job opportunity will be posted.',
        'fieldType' => 'Datetime',
      ],
      [
        'fieldName' => 'MinimumRange',
        'fieldDescription' => 'Minimum salary range for the job offering.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'MaximumRange',
        'fieldDescription' => 'Maximum salary range for the job offering.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'MajorDuties',
        'fieldDescription' => 'Description of the duties of the job.',
        'fieldType' => 'Text',
      ],
      [
        'fieldName' => 'JobSummary',
        'fieldDescription' => 'Summary of the job opportunity.',
        'fieldType' => 'Text',
      ],
      [
        'fieldName' => 'LowGrade',
        'fieldDescription' => 'Lowest potential grade level for the job opportunity.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'HighGrade',
        'fieldDescription' => 'Highest potential grade level for the job opportunity.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'SubAgencyName',
        'fieldDescription' => 'Name of the sub agency.',
        'fieldType' => 'String',
      ],
      [
        'fieldName' => 'TeleworkEligible',
        'fieldDescription' => 'Telework or not.',
        'fieldType' => 'Boolean',
      ],
      [
        'fieldName' => 'RemoteIndicator',
        'fieldDescription' => 'Remote or not.',
        'fieldType' => 'Boolean',
      ],
    ];

    $options = [];
    foreach ($fields as $field) {
      $options[$field['fieldName']] = [
        'fieldName' => $field['fieldName'],
        'fieldDescription' => $field['fieldDescription'],
        'fieldType' => $field['fieldType'],
      ];
    }

    $form['field_tab']['field_data_source'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('USAJobs data fields'),
      '#description' => $this->t('Field data source.'),
      '#header' => [
        'fieldName' => $this->t('Name'),
        'fieldDescription' => $this->t('Description'),
        'fieldType' => $this->t('Type'),
      ],
      '#options' => $options,
      '#default_value' => $config->get('field.field_data_source'),
      '#empty' => $this->t('No fields available'),
      '#js_select' => FALSE,
    ];

    $field_data_source_default = [
      'PositionTitle',
      'PositionURI',
      'PositionStartDate',
      'PositionEndDate',
      'PositionLocationDisplay',
      'MinimumRange',
      'MaximumRange',
    ];

    foreach ($field_data_source_default as $field) {
      $form['field_tab']['field_data_source'][$field]['#disabled'] = TRUE;
    }

    // Extras Settings tab.
    $form['extras_tab'] = [
      '#title' => $this->t('Extras Settings'),
      '#type' => 'details',
      '#description' => $this->t('Extras settings to tweak the results data.'),
      '#group' => 'usajobs_tabs',
    ];

    $form['extras_tab']['no_results_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('No Results Message'),
      '#description' => $this->t('Message to display when no results are found.'),
      '#default_value' => $config->get('no_results_message') ?: UsaJobsApiClientInterface::NO_RESULTS_MESSAGE,
    ];

    $form['extras_tab']['sub_agency_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sub Agency Name'),
      '#description' => $this->t('Filter the display results by the name of the sub-agency or hiring office.<br /> For example, enter "Healthcare and Insurance" as hiring office for OPM.'),
      '#default_value' => $config->get('sub_agency_name'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for valid email address.
    $email_validator = new EmailValidator();
    if (!$email_validator->isValid($form_state->getValue('user_agent'))) {
      $form_state->setErrorByName('user_agent', $this->t('The User-Agent must be a valid email address.'));
    }

    // Check for empty Authorization Key.
    if (empty($form_state->getValue('authorization_key'))) {
      $form_state->setErrorByName('authorization_key', $this->t('The Authorization Key is required.'));
    }

    // Check for HTML tags.
    if (strip_tags($form_state->getValue('no_results_message')) != $form_state->getValue('no_results_message')) {
      $form_state->setErrorByName('no_results_message', $this->t('The No Results Message field should not contain HTML tags.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('usajobs.settings')
      ->set('user_agent', $form_state->getValue('user_agent'))
      ->set('authorization_key', $form_state->getValue('authorization_key'))
      ->set('organization_id', $form_state->getValue('organization_id'))
      ->set('results_per_page', $form_state->getValue('results_per_page'))
      ->set('sort_field', $form_state->getValue('sort_field'))
      ->set('field.field_data_source', array_filter($form_state->getValue('field_data_source')))
      ->set('no_results_message', $form_state->getValue('no_results_message'))
      ->set('sub_agency_name', $form_state->getValue('sub_agency_name'))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'usajobs.settings',
    ];
  }

  /**
   * Get Agency Sub Elements.
   */
  protected function getAgencySubElements() {
    $agencies = $this->usajobs->getAgencyList();
    $agency_sub_elements = [];
    if ($agencies) {
      foreach ($agencies->data->CodeList[0]->ValidValue as $agency) {
        if ($agency->IsDisabled == 'No') {
          $agency_sub_elements[$agency->Code] = $agency->Value;
        }
      }
    }
    return $agency_sub_elements;
  }

}
