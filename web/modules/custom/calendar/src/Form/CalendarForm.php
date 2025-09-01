<?php

namespace Drupal\calendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form class for Calendar entries.
 */
class CalendarForm extends FormBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a CalendarForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_form';
  }   
  
  /**
   * {@inheritdoc}
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('calendar.settings');

    if (!$form_state->has('calendar_rows')) {
      $saved_rows = $config->get('calendar_rows');
      if (empty($saved_rows)) {
        $saved_rows = [
          ['year' => 2025],
        ];
      }
      $form_state->set('calendar_rows', $saved_rows);
    }

    $rows = $form_state->get('calendar_rows');

    $form['#prefix'] = '<div id="calendar-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Row'),
      '#submit' => ['::addRow'],
      '#ajax' => [
        'callback' => '::ajaxAddRowCallback',
        'wrapper' => 'calendar-form-wrapper',
      ],
    ];

    $header = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];

    $form['data'] = [
      '#type' => 'table',
      '#header' => $header,
    ];  

    $saved_data = $config->get('calendar_data', []);
    
    foreach ($rows as $index => $row) {
      $year = $row['year'];
      
      $form['data'][$index]['year'] = [
        '#markup' => $year,
      ];
      $form['data'][$index]['jan'] = [
        '#type' => 'number',
        '#title' => $this->t('January'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['jan']) ? $saved_data[$index]['jan'] : (isset($row['jan']) ? $row['jan'] : ''),
      ];
      $form['data'][$index]['feb'] = [
        '#type' => 'number',
        '#title' => $this->t('February'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['feb']) ? $saved_data[$index]['feb'] : (isset($row['feb']) ? $row['feb'] : ''),
      ];
      $form['data'][$index]['mar'] = [
        '#type' => 'number',
        '#title' => $this->t('March'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['mar']) ? $saved_data[$index]['mar'] : (isset($row['mar']) ? $row['mar'] : ''),
      ];
      $form['data'][$index]['q1'] = [
        '#type' => 'number',
        '#title' => $this->t('Q1 total'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['q1']) ? $saved_data[$index]['q1'] : (isset($row['q1']) ? $row['q1'] : ''),
      ];
      $form['data'][$index]['apr'] = [
        '#type' => 'number',
        '#title' => $this->t('April'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['apr']) ? $saved_data[$index]['apr'] : (isset($row['apr']) ? $row['apr'] : ''),
      ];
      $form['data'][$index]['may'] = [
        '#type' => 'number',
        '#title' => $this->t('May'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['may']) ? $saved_data[$index]['may'] : (isset($row['may']) ? $row['may'] : ''),
      ];
      $form['data'][$index]['jun'] = [
        '#type' => 'number',
        '#title' => $this->t('June'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['jun']) ? $saved_data[$index]['jun'] : (isset($row['jun']) ? $row['jun'] : ''),
      ];
      $form['data'][$index]['q2'] = [
        '#type' => 'number',
        '#title' => $this->t('Q2 total'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['q2']) ? $saved_data[$index]['q2'] : (isset($row['q2']) ? $row['q2'] : ''),
      ];
      $form['data'][$index]['jul'] = [
        '#type' => 'number',
        '#title' => $this->t('July'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['jul']) ? $saved_data[$index]['jul'] : (isset($row['jul']) ? $row['jul'] : ''),
      ];
      $form['data'][$index]['aug'] = [
        '#type' => 'number',
        '#title' => $this->t('August'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['aug']) ? $saved_data[$index]['aug'] : (isset($row['aug']) ? $row['aug'] : ''),
      ];
      $form['data'][$index]['sep'] = [
        '#type' => 'number',
        '#title' => $this->t('September'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['sep']) ? $saved_data[$index]['sep'] : (isset($row['sep']) ? $row['sep'] : ''),
      ];
      $form['data'][$index]['q3'] = [
        '#type' => 'number',
        '#title' => $this->t('Q3 total'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['q3']) ? $saved_data[$index]['q3'] : (isset($row['q3']) ? $row['q3'] : ''),
      ];
      $form['data'][$index]['oct'] = [
        '#type' => 'number',
        '#title' => $this->t('October'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['oct']) ? $saved_data[$index]['oct'] : (isset($row['oct']) ? $row['oct'] : ''),
      ];
      $form['data'][$index]['nov'] = [
        '#type' => 'number',
        '#title' => $this->t('November'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['nov']) ? $saved_data[$index]['nov'] : (isset($row['nov']) ? $row['nov'] : ''),
      ];
      $form['data'][$index]['dec'] = [
        '#type' => 'number',
        '#title' => $this->t('December'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['dec']) ? $saved_data[$index]['dec'] : (isset($row['dec']) ? $row['dec'] : ''),
      ];
      $form['data'][$index]['q4'] = [
        '#type' => 'number',
        '#title' => $this->t('Q4 total'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['q4']) ? $saved_data[$index]['q4'] : (isset($row['q4']) ? $row['q4'] : ''),
      ];
      $form['data'][$index]['ytd'] = [
        '#type' => 'number',
        '#title' => $this->t('Year to Date'),
        '#title_display' => 'invisible',
        '#default_value' => isset($saved_data[$index]['ytd']) ? $saved_data[$index]['ytd'] : (isset($row['ytd']) ? $row['ytd'] : ''),
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;  
  }

  // Ajax callback to refresh the entire form
  public function ajaxAddRowCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  // Add row submit handler
  public function addRow(array &$form, FormStateInterface $form_state) {
    $rows = $form_state->get('calendar_rows');

    $first_row = reset($rows);
    $next_year = isset($first_row['year']) ? $first_row['year'] - 1 : 2026;

    array_unshift($rows, ['year' => $next_year]);
    $form_state->set('calendar_rows', $rows);

    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_rows', $rows);
    $config->save();

    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('data');
    $rows = $form_state->get('calendar_rows');
    
    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_rows', $rows);
    $config->set('calendar_data', $values);
    $config->save();
    
    \Drupal::messenger()->addMessage($this->t('Calendar data has been saved.'));
  }
}