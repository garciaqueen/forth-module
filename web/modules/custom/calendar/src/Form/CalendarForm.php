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

  private function calculateQuarterlyValue($m1, $m2, $m3) {

    $m1 = ($m1 === '' || $m1 === NULL) ? 0 : (float) $m1;
    $m2 = ($m2 === '' || $m2 === NULL) ? 0 : (float) $m2;
    $m3 = ($m3 === '' || $m3 === NULL) ? 0 : (float) $m3;
    
    if ($m1 == 0 && $m2 == 0 && $m3 == 0) {
      return NULL;
    }
    
    $result = (($m1 + $m2 + $m3) + 1) / 3;
    
    return round($result / 0.05) * 0.05;
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

        // All fields in the correct order
        foreach (['jan','feb','mar','q1','apr','may','jun','q2','jul','aug','sep','q3','oct','nov','dec','q4','ytd'] as $field) {
          if (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'])) {
            // Regular month fields
            $form['table_' . $table_index][$row_index][$field] = [
              '#type' => 'number',
              '#title_display' => 'invisible',
              '#default_value' => isset($saved_data[$table_index][$row_index][$field]) ? $saved_data[$table_index][$row_index][$field] : '',
              '#attributes' => ['style' => 'width:100px;'],
            ];
          } else {
            // Quarter and YTD fields
            $value = '';
            if (isset($saved_data[$table_index][$row_index][$field]) && $saved_data[$table_index][$row_index][$field] !== NULL) {
              $value = number_format($saved_data[$table_index][$row_index][$field], 2);
            }
            
            $form['table_' . $table_index][$row_index][$field] = [
              '#type' => 'textfield',
              '#title_display' => 'invisible',
              '#default_value' => $value,
              '#attributes' => [
                'style' => 'width:100px; background-color: #f5f5f5;',
                'readonly' => 'readonly'
              ],
            ];
          }
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
    $tables = $form_state->get('calendar_tables');
    $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

    $periods = [];

    foreach ($tables as $table_index => $rows) {
      $values = $form_state->getValue('table_' . $table_index);
      if (!is_array($values)) continue;
      foreach ($values as $row_index => $row) {
        //Знаходимо всі заповнені місяці у рядку
        $filled = [];
        foreach ($months as $m) {
          if ($row[$m] !== '' && $row[$m] !== NULL) {
            $filled[] = $m;
          }
        }

        if ($filled) {
          $first = reset($filled);
          $last = end($filled);

          $expected_range = array_slice(
            $months,
            array_search($first, $months),
            array_search($last, $months) - array_search($first, $months) + 1
          );

          foreach ($expected_range as $m) {
            if ($row[$m] === '' || $row[$m] === NULL) {
              $form_state->setErrorByName(
                "table_{$table_index}][{$row_index}]",
                $this->t('Row @r in Table @t has missing month(s) between @f and @l.', [
                  '@r' => $row_index + 1,
                  '@t' => $table_index + 1,
                  '@f' => ucfirst($first),
                  '@l' => ucfirst($last),
                ])
              );
            }
          }

          $periods[] = [$first, $last];
        }
      }
    }

    //Перевірка, що всі періоди однакові між таблицями
    if ($periods) {
      $base = $periods[0];
      foreach ($periods as $p) {
        if ($p !== $base) {
          $form_state->setErrorByName('calendar_form',
            $this->t('All tables must have the same filled period (e.g. @bf–@bl).', [
              '@bf' => ucfirst($base[0]),
              '@bl' => ucfirst($base[1]),
            ])
          );
          break;
        }
      }
    }

    foreach ($tables as $table_index => $rows) {
      $years = [];
      foreach ($rows as $row) {
        if (isset($row['year'])) {
          $years[] = (int) $row['year'];
        }
      }
      
      rsort($years);
      
      for ($i = 0; $i < (count($years) - 1); $i++) {
        if (($years[$i] - $years[$i + 1]) != 1) {
          $form_state->setErrorByName('calendar_form', $this->t('There\'s a gap between years!'));
          break 2;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('calendar_tables');
    $values = [];

    foreach ($tables as $table_index => $rows) {
      $table_values = $form_state->getValue('table_' . $table_index);

      foreach ($table_values as $row_index => &$row) {
        $quarters = [
          'q1' => ['jan', 'feb', 'mar'],
          'q2' => ['apr', 'may', 'jun'],
          'q3' => ['jul', 'aug', 'sep'],
          'q4' => ['oct', 'nov', 'dec'],
        ];

        // Calculate quarterly values using the new formula
        foreach ($quarters as $q => $months) {
          $m1 = isset($row[$months[0]]) && $row[$months[0]] !== '' ? (float) $row[$months[0]] : 0;
          $m2 = isset($row[$months[1]]) && $row[$months[1]] !== '' ? (float) $row[$months[1]] : 0;
          $m3 = isset($row[$months[2]]) && $row[$months[2]] !== '' ? (float) $row[$months[2]] : 0;
          
          $row[$q] = $this->calculateQuarterlyValue($m1, $m2, $m3);
        }

        $q_values = [];
        foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
          $q_values[] = ($row[$q] !== NULL) ? $row[$q] : 0; // NULL → 0
        }

        $ytd_raw = (array_sum($q_values) + 1) / 4;

        // Якщо всі квартали порожні (NULL), тоді YTD = NULL
        $row['ytd'] = (array_sum($q_values) === 0) ? NULL : round($ytd_raw, 2);
      }

      $values[$table_index] = $table_values;
    }

    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_tables', $tables);
    $config->set('calendar_data', $values);
    $config->save();

    \Drupal::messenger()->addMessage($this->t('Saved successfully.'));
  }
}