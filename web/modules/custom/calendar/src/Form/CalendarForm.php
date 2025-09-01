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
    $form['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Row to First Table'),
      '#submit' => ['::addRow'],
      '#ajax' => [
        'callback' => '::ajaxAddRowCallback',
        'wrapper' => 'calendar-form-wrapper',
      ],
    ];

    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#ajax' => [
        'callback' => '::ajaxAddTableCallback',
        'wrapper' => 'calendar-form-wrapper',
      ],
    ];
    if (!$form_state->has('calendar_tables')) {
      $saved_tables = $config->get('calendar_tables');
      if (empty($saved_tables)) {
        $saved_tables = [
          [['year' => date('Y')]],
        ];
      }
      $form_state->set('calendar_tables', $saved_tables);
    }

    $tables = $form_state->get('calendar_tables');

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

    $saved_data = $config->get('calendar_data', []);

    $form['#prefix'] = '<div id="calendar-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Build each table
    foreach ($tables as $table_index => $rows) {
      $form['table_' . $table_index] = [
        '#type' => 'table',
        '#header' => $header,
        '#prefix' => '<h3>' . $this->t('Table @num', ['@num' => $table_index + 1]) . '</h3>',
        '#suffix' => '<br>',
      ];

      foreach ($rows as $row_index => $row) {
        $year = $row['year'];
        $form['table_' . $table_index][$row_index]['year'] = [
          '#markup' => $year,
        ];

        foreach (['jan','feb','mar','q1','apr','may','jun','q2','jul','aug','sep','q3','oct','nov','dec','q4','ytd'] as $month) {
          $form['table_' . $table_index][$row_index][$month] = [
            '#type' => 'number',
            '#title_display' => 'invisible',
            '#default_value' => isset($saved_data[$table_index][$row_index][$month]) ? $saved_data[$table_index][$row_index][$month] : '',
            '#attributes' => ['style' => 'width:100px;'],
          ];
        }
      }
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

  public function ajaxAddTableCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  public function addTable(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('calendar_tables');
    $current_year = date('Y');

    $tables[] = [
      ['year' => $current_year]
    ];

    $form_state->set('calendar_tables', $tables);

    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_tables', $tables);
    $config->save();

    $form_state->setRebuild(TRUE);
  }


    public function addRow(array &$form, FormStateInterface $form_state) {
      $tables = $form_state->get('calendar_tables');
      $first_table = $tables[0];

      $first_row = reset($first_table);
      $next_year = isset($first_row['year']) ? $first_row['year'] - 1 : date('Y');

      // Add the new row to the top of the table
      array_unshift($first_table, ['year' => $next_year]);
      $tables[0] = $first_table;

      $form_state->set('calendar_tables', $tables);

      $config = $this->configFactory->getEditable('calendar.settings');
      $config->set('calendar_tables', $tables);
      $config->save();

      $form_state->setRebuild(TRUE);
    }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      $values = $form_state->getValue('data');
      \Drupal::logger('calendar')->notice('<pre>@data</pre>', [
        '@data' => print_r($values, TRUE),
      ]);
      $i = 0;
      foreach ($values as $value) {
        foreach ($value as $month) {
          if (empty($month)) {
            $i++;
          }
        }
      }
      
      if ($i > 0) {
        $form_state->setErrorByName('calendar', $this->t('Invalid table. Please fill in ALL the values!'));
      }
    }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('calendar_tables');
    $values = [];
    foreach ($tables as $table_index => $rows) {
      $values[$table_index] = $form_state->getValue('table_' . $table_index);
    }

    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_tables', $tables);
    $config->set('calendar_data', $values);
    $config->save();

    \Drupal::messenger()->addMessage($this->t('Saved successfully.'));
  }
}