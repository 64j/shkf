shkf.ajaxOrder = function(recount, callback) {
  recount = recount || false;
  shkf.orderForm = document.getElementById(shkf['orderFormId']);
  if (shkf.orderForm) {
    var data = shkf.serialize(shkf.orderForm), xhr = new XMLHttpRequest();
    if (shkf.target.tagName === 'INPUT' || shkf.target.tagName === 'SELECT' || recount) {
      data += '&' + shkf.prefix + '-action=recount';
    }
    xhr.open('POST', document.location.href, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if (xhr.status === 200 && xhr.response) {
          var doc = (new DOMParser()).parseFromString(xhr.response, 'text/html');
          shkf.orderForm.innerHTML = doc.getElementById(shkf['orderFormId']) && doc.getElementById(shkf['orderFormId']).innerHTML || '';
          if (typeof callback === 'function') {
            callback.call(shkf);
          }
        }
      }
    };
    xhr.send(data);
  }
};

shkf.actions['order'] = function() {
  if (shkf.call('order.before')) {
    shkf.ajaxOrder(false, shkf.process);
  }
};

shkf.actions['delivery'] = function() {
  shkf.ajaxOrder(false, shkf.process);
};

shkf.actions['payment'] = function() {
  shkf.ajaxOrder(false, shkf.process);
};
