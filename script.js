window.addEventListener('load', () => {
  const URL = "https://api.mbartschool.ru/order.php?create";

  // Base ordering function
  const sendOrder = ($form, orderType) => {
      console.log($form);
    const formData = new FormData($form);
    formData.append('orderType', orderType);
    fetch(URL, {
      method: 'POST',
      body: formData
    })
    .then(res=> res.json())
    .then(data=> window.location.href = data['PaymentURL']);
  }

  // Personal
  window.sendOrderIndividual = e => sendOrder(e.context, 'individual');
  const $formIndividual = document.querySelector('.uc-orderIndividual form');
  $formIndividual.dataset.successCallback = 'sendOrderIndividual';

  // Group
  window.sendOrderGroup = e => sendOrder(e.context, 'group');
  const $formGroup = document.querySelector('.uc-orderGroup form');
  $formGroup.dataset.successCallback = 'sendOrderGroup';
})