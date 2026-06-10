<?php

namespace Drupal\user_matchmaking\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin form for User Matchmaking settings.
 */
class MatchmakingSettingsForm extends ConfigFormBase
{

  protected EntityFieldManagerInterface $entityFieldManager;

  public static function create(ContainerInterface $container): static
  {
    $instance = parent::create($container);
    $instance->entityFieldManager = $container->get('entity_field.manager');
    return $instance;
  }

  protected function getEditableConfigNames(): array
  {
    return ['user_matchmaking.settings'];
  }

  public function getFormId(): string
  {
    return 'user_matchmaking_settings_form';
  }

  /**
   * Returns select options for user fields of a given type.
   *
   * @param string $field_type  e.g. 'entity_reference', 'boolean'
   * @param string $target_type Optional target entity type filter (for entity_reference fields).
   */
  protected function getUserFieldOptions(string $field_type, string $target_type = ''): array
  {
    $definitions = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    $options = ['' => $this->t('Select a field')];

    foreach ($definitions as $name => $def) {
      if ($def->getType() !== $field_type) {
        continue;
      }
      if ($target_type && $def->getSetting('target_type') !== $target_type) {
        continue;
      }
      $label = $def->getLabel();
      $options[$name] = "{$label} ({$name})";
    }

    return $options;
  }

  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config('user_matchmaking.settings');

    // ---- Field mapping --------------------------------------------------- //
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Field Mapping'),
      '#open' => TRUE,
    ];

    $ref_options = $this->getUserFieldOptions('entity_reference');
    $bool_options = $this->getUserFieldOptions('boolean');

    $form['fields']['field_offers'] = [
      '#type' => 'select',
      '#title' => $this->t('Offers field'),
      '#description' => $this->t('User field that holds what a user <em>offers</em> (e.g. skills, expertise). Must be an entity reference field.'),
      '#options' => $ref_options,
      '#default_value' => $config->get('fields.offers'),
      '#required' => TRUE,
    ];

    $form['fields']['field_needs'] = [
      '#type' => 'select',
      '#title' => $this->t('Needs field'),
      '#description' => $this->t('User field that holds what a user <em>needs</em>. Must reference the same entity type as the Offers field.'),
      '#options' => $ref_options,
      '#default_value' => $config->get('fields.needs'),
      '#required' => TRUE,
    ];

    $form['fields']['field_notification'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification flag field'),
      '#description' => $this->t('Boolean field on the user account used to flag unread match notifications.'),
      '#options' => $bool_options,
      '#default_value' => $config->get('fields.notification'),
      '#required' => FALSE,
      '#empty_option' => $this->t('None'),
    ];

    // ---- Email settings -------------------------------------------------- //
    $form['mail'] = [
      '#type' => 'details',
      '#title' => $this->t('Email Settings'),
      '#open' => FALSE,
    ];

    $form['mail']['enable_emails'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Globally enable email notifications'),
      '#description' => $this->t('If checked, an email will be sent to whoever receives a match. Users will still be able to override this setting in their personal profile.'),
      '#default_value' => $config->get('mail.enable_emails'),
    ];

    $form['mail']['matches_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Matches page URL'),
      '#description' => $this->t('Absolute URL linked inside the offerer summary email.'),
      '#default_value' => $config->get('mail.matches_url'),
    ];

    // Seeker notification
    $form['mail']['seeker'] = [
      '#type' => 'details',
      '#title' => $this->t('Seeker notification (sent to the user whose needs were matched)'),
      '#open' => TRUE,
    ];
    $form['mail']['seeker']['seeker_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $config->get('mail.seeker_match_notification.subject'),
      '#required' => TRUE,
    ];
    $form['mail']['seeker']['seeker_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $config->get('mail.seeker_match_notification.body'),
      '#required' => TRUE,
      '#rows' => 8,
    ];
    $form['mail']['seeker']['seeker_tokens'] = [
      '#markup' => '<p><strong>' . $this->t('Available tokens:') . '</strong><br>' .
        '<code>[seeker:display-name]</code> - ' . $this->t('Seeker name') . '<br>' .
        '<code>[offerer:display-name]</code> - ' . $this->t('Offerer name') . '<br>' .
        '<code>[matched_terms]</code> - ' . $this->t('Comma-separated matched labels') . '<br>' .
        '<code>[offerer:url]</code> - ' . $this->t('Offerer profile URL') . '</p>',
    ];

    // Offerer summary.
    $form['mail']['offerer'] = [
      '#type' => 'details',
      '#title' => $this->t('Offerer summary (sent to the user whose offers triggered matches)'),
      '#open' => TRUE,
    ];
    $form['mail']['offerer']['offerer_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $config->get('mail.offerer_match_summary.subject'),
      '#required' => TRUE,
    ];
    $form['mail']['offerer']['offerer_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $config->get('mail.offerer_match_summary.body'),
      '#required' => TRUE,
      '#rows' => 8,
    ];
    $form['mail']['offerer']['offerer_tokens'] = [
      '#markup' => '<p><strong>' . $this->t('Available tokens:') . '</strong><br>' .
        '<code>[offerer:display-name]</code> - ' . $this->t('Offerer name') . '<br>' .
        '<code>[match_count]</code> - ' . $this->t('Number of matches') . '<br>' .
        '<code>[matched_users_list]</code> - ' . $this->t('Newline-separated list of seekers') . '<br>' .
        '<code>[matches_url]</code> - ' . $this->t('Matches page URL') . '</p>',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $this->config('user_matchmaking.settings')
      ->set('fields.offers', $form_state->getValue('field_offers'))
      ->set('fields.needs', $form_state->getValue('field_needs'))
      ->set('fields.notification', $form_state->getValue('field_notification'))
      ->set('mail.enable_emails', $form_state->getValue('enable_emails'))
      ->set('mail.matches_url', $form_state->getValue('matches_url'))
      ->set('mail.seeker_match_notification.subject', $form_state->getValue('seeker_subject'))
      ->set('mail.seeker_match_notification.body',  $form_state->getValue('seeker_body'))
      ->set('mail.offerer_match_summary.subject', $form_state->getValue('offerer_subject'))
      ->set('mail.offerer_match_summary.body', $form_state->getValue('offerer_body'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
