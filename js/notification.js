(function (Drupal, once) {
  Drupal.behaviors.notificationBell = {
    attach(context) {
      const bell = once('notification-bell', '#notification_bell', context)[0];
      if (!bell) return;

      bell.addEventListener('click', () => {
        fetch('/notification/clear', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'ok') {
            bell.classList.remove('active');
          }
        });
      });
    }
  };
})(Drupal, once);