<?php

namespace Drupal\calendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Calendar form with tables of yearly/monthly/quarterly data.
 */
class CalendarForm extends FormBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a CalendarForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
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
   * Calculates a quarterly value based on three monthly inputs.
   * If all months are empty/0, returns NULL.
   * Otherwise uses formula ((m1+m2+m3)+1)/3 rounded to nearest 0.05.
   */
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

    // Initialize tables in form state if not already set
    if (!$form_state->has('calendar_tables')) {
      $saved_tables = $config->get('calendar_tables');
      if (empty($saved_tables)) {
        $saved_tables = [
          [['year' => date('Y')]], // Start with one table and current year
        ];
      }
      $form_state->set('calendar_tables', $saved_tables);
    }

    $tables = $form_state->get('calendar_tables');

    // Table header definition
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

    // Wrapper for AJAX
    $form['#prefix'] = '<div id="calendar-form-wrapper">';
    $form['#suffix'] = '</div>';

    // --- Buttons above all tables ---
    $form['buttons_row'] = [
      '#type' => 'container',
      '#attributes' => ['style' => 'display:flex; gap:10px; margin-bottom:15px;'],
    ];

    // Add Table button
    $form['buttons_row']['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#ajax' => [
        'callback' => '::ajaxAddTableCallback',
        'wrapper' => 'calendar-form-wrapper',
      ],
    ];

    // Add Row buttons for each table
    foreach ($tables as $table_index => $rows) {
      $form['buttons_row']['table_' . $table_index . '_add_row'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Row to Table @num', ['@num' => $table_index + 1]),
        '#submit' => ['::addRowToTable'],
        '#ajax' => [
          'callback' => '::ajaxAddRowCallback',
          'wrapper' => 'calendar-form-wrapper',
        ],
        '#table_index' => $table_index,
      ];
    }

    // --- Build each table ---
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

        // Add month, quarter, and YTD fields
        foreach (['jan','feb','mar','q1','apr','may','jun','q2','jul','aug','sep','q3','oct','nov','dec','q4','ytd'] as $field) {
          if (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'])) {
            $form['table_' . $table_index][$row_index][$field] = [
              '#type' => 'number',
              '#title_display' => 'invisible',
              '#default_value' => isset($saved_data[$table_index][$row_index][$field]) ? $saved_data[$table_index][$row_index][$field] : '',
              '#attributes' => ['style' => 'width:100px;'],
            ];
          } else {
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
                'readonly' => 'readonly',
              ],
            ];
          }
        }
      }
    }

    // --- Save button below all tables ---
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#prefix' => '<div style="margin-top:15px;">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * Ajax callback after adding a row.
   */
  public function ajaxAddRowCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Ajax callback after adding a table.
   */
  public function ajaxAddTableCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Adds a new table to the form.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $tables = $form_state->get('calendar_tables');
    $current_year = date('Y');
    $tables[] = [
      ['year' => $current_year],
    ];
    $form_state->set('calendar_tables', $tables);

    $config = $this->configFactory->getEditable('calendar.settings');
    $config->set('calendar_tables', $tables);
    $config->save();

    $form_state->setRebuild(TRUE);
  }

  /**
   * Adds a new row to a specific table.
   */
  public function addRowToTable(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $table_index = $triggering_element['#table_index'];

    $tables = $form_state->get('calendar_tables');
    $table = $tables[$table_index];

    $first_row = reset($table);
    $next_year = isset($first_row['year']) ? $first_row['year'] - 1 : date('Y');

    array_unshift($table, ['year' => $next_year]);
    $tables[$table_index] = $table;

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

        foreach ($quarters as $q => $months) {
          $m1 = isset($row[$months[0]]) && $row[$months[0]] !== '' ? (float) $row[$months[0]] : 0;
          $m2 = isset($row[$months[1]]) && $row[$months[1]] !== '' ? (float) $row[$months[1]] : 0;
          $m3 = isset($row[$months[2]]) && $row[$months[2]] !== '' ? (float) $row[$months[2]] : 0;

          $row[$q] = $this->calculateQuarterlyValue($m1, $m2, $m3);
        }

        $q_values = [];
        foreach (['q1', 'q2', 'q3', 'q4'] as $q) {
          $q_values[] = ($row[$q] !== NULL) ? $row[$q] : 0;
        }

        $ytd_raw = (array_sum($q_values) + 1) / 4;
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
