window.addEventListener('load', () => {
  const URL = "http://lh:8000/order";

  // Base ordering function
  const sendOrder = ($form, orderType) => {
    const formData = new FormData($form);
    formData.append('orderType', orderType);
    fetch(URL, {
      method: 'POST',
      body: formData
    });
  }

  // Personal
  window.sendOrderIndividual = e => sendOrder(e.target, 'individual');
  const $formIndividual = document.querySelector('.uc-orderIndividual form');
  $formIndividual.dataset.successCallback = 'sendOrderIndividual';

  // Group
  window.sendOrderGroup = e => sendOrder(e.target, 'group');
  const $formGroup = document.querySelector('.uc-orderGroup form');
  $formGroup.dataset.successCallback = 'sendOrderGroup';
})