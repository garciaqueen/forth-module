<?php


namespace Drupal\calendar\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form class for Calendar entries.
 */
class CalendarForm extends FormBase {
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
    $form['#prefix'] = '<div id="calendar-form-wrapper">';
    $form['#suffix'] = '</div>';
    $header = [];
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

    $rows = ['...', 2026];

    $form['data'] = [
      '#type' => 'table',
      '#header' => $header,
    ];  

  foreach ($rows as $year) {
    $form['data'][$year]['year'] = [
      '#markup' => $year,
    ];
    $form['data'][$year]['jan'] = [
      '#type' => 'number',
      '#title' => $this->t('January'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['feb'] = [
      '#type' => 'number',
      '#title' => $this->t('February'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['mar'] = [
      '#type' => 'number',
      '#title' => $this->t('March'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['q1'] = [
      '#type' => 'number',
      '#title' => $this->t('Q1 total'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['apr'] = [
      '#type' => 'number',
      '#title' => $this->t('April'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['may'] = [
      '#type' => 'number',
      '#title' => $this->t('May'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['jun'] = [
      '#type' => 'number',
      '#title' => $this->t('June'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['q2'] = [
      '#type' => 'number',
      '#title' => $this->t('Q2 total'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['jul'] = [
      '#type' => 'number',
      '#title' => $this->t('July'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['aug'] = [
      '#type' => 'number',
      '#title' => $this->t('August'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['sep'] = [
      '#type' => 'number',
      '#title' => $this->t('September'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['q3'] = [
      '#type' => 'number',
      '#title' => $this->t('Q3 total'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['oct'] = [
      '#type' => 'number',
      '#title' => $this->t('October'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['nov'] = [
      '#type' => 'number',
      '#title' => $this->t('November'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['dec'] = [
      '#type' => 'number',
      '#title' => $this->t('December'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['q4'] = [
      '#type' => 'number',
      '#title' => $this->t('Q4 total'),
      '#title_display' => 'invisible',
    ];
    $form['data'][$year]['ytd'] = [
      '#type' => 'number',
      '#title' => $this->t('Year to Date'),
      '#title_display' => 'invisible',
    ];
  }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;  
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('data');
    \Drupal::messenger()->addMessage(print_r($values, TRUE));
  }
}