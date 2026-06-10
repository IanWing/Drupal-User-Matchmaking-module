<?php

namespace Drupal\user_matchmaking\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *   id = "notification_bell_block",
 *   admin_label = @Translation("Notification Bell"),
 * )
 */
class NotificationBellBlock extends BlockBase
{

  public function build(): array
  {
    $account = \Drupal::currentUser();

    if ($account->isAnonymous()) {
      return [];
    }

    $field_notification = \Drupal::config('user_matchmaking.settings')->get('fields.notification');
    $user   = \Drupal::entityTypeManager()->getStorage('user')->load($account->id());
    $active = FALSE;

    if ($user && !empty($field_notification) && $user->hasField($field_notification)) {
      $active = (bool) $user->get($field_notification)->value;
    }
    return [
      '#theme' => 'user_matchmaking_notification_bell',
      '#active' => $active,
      '#attached' => [
        'library' => ['user_matchmaking/notification'],
      ],
      '#cache' => [
        'contexts' => ['user'],
        'max-age' => 0,
      ],
    ];
  }
}
