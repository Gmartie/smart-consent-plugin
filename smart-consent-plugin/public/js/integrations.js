document.addEventListener('click', function(e) {

  if (e.target.classList.contains('add_to_cart_button')) {

    trackEvent('add_to_cart', {
      currency: 'EUR',
      value: 29.99
    });

  }

});