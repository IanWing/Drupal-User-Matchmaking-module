<?php

namespace Drupal\user_matchmaking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends ControllerBase {

  public function clear(Request $request): JsonResponse {
    $user = $this->currentUser();

    if ($user->isAnonymous()) {
      return new JsonResponse(['status' => 'error'], 403);
    }

    $field_notification = \Drupal::config('user_matchmaking.settings')->get('fields.notification');
    $account = $this->entityTypeManager()->getStorage('user')->load($user->id());

    if (!empty($field_notification) && $account->hasField($field_notification)) {
      $account->set($field_notification, 0)->save();
    }

    return new JsonResponse(['status' => 'ok']);
  }
}