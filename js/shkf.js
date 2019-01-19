var shkf = (function(options) {
  var __ = function() {
    this.prefix = options['prefix'] || 'shkf';
    this.initial = false;
    this.callback = {};
    this.target = null;
    this.parent = null;
    this.action = null;
    this.event = null;
    this.carts = {};
    this.key = null;
    this.id = null;
  };

  __.prototype.init = function(options) {
    var el = document.getElementById(options.id);
    if (!el) {
      return;
    }
    this.carts[options.id] = options;
    el = document.getElementById('options_' + this.prefix + '_' + options.id);
    el.parentElement.removeChild(el);
  };

  __.prototype.setEvents = function() {

    for (var k in this.carts) {
      if (this.carts.hasOwnProperty(k)) {
        if (this.carts[k]['async']) {
          this.ajax({
            carts: this.carts
          });
          break;
        }
      }
    }

    document.addEventListener('click', function(e) {
      shkf.event = e;
      shkf.target = shkf.event.target;
      if (shkf.target.tagName === 'INPUT') {
        return;
      }
      var pf = 'data-' + shkf.prefix + '-', s = shkf.searchAttribute(shkf.target.attributes, pf);
      if (s) {
        shkf.event.preventDefault();
        shkf.action = s.replace(pf, '');
        if (typeof shkf.actions[shkf.action] === 'function') {
          shkf.actions[shkf.action]();
        }
      }
    });

    document.addEventListener('change', function(e) {
      shkf.event = e;
      shkf.target = shkf.event.target;
      if (shkf.target.tagName !== 'INPUT') {
        return;
      }
      var pf = 'data-' + shkf.prefix + '-', s = shkf.searchAttribute(shkf.target.attributes, pf);
      if (s) {
        shkf.event.preventDefault();
        shkf.action = s.replace(pf, '');
        if (typeof shkf.actions[shkf.action] === 'function') {
          shkf.actions[shkf.action]();
        }
      }
    });

    document.addEventListener('keyup', function(e) {
      shkf.event = e;
      shkf.target = shkf.event.target;
      if (shkf.target.tagName !== 'INPUT') {
        return;
      }
      if (shkf.target.hasAttribute('data-' + shkf.prefix + '-count')) {
        shkf.event.preventDefault();
        var step = parseFloat(shkf.target.getAttribute('data-' + shkf.prefix + '-step') || shkf.target.getAttribute('step') || 1);
        shkf.target.value = shkf.setCount(shkf.target.value.replace(/[^0-9+.]/g, ''), step, false);
      }
    });
  };

  __.prototype.getParentElement = function(name) {
    this.parent = null;
    if (typeof name !== 'undefined') {
      this.parent = this.target.closest('[data-' + this.prefix + '-' + name + ']');
    } else {
      this.parent = this.target.closest('[data-' + this.prefix + '-key]') || this.target.closest('[data-' + this.prefix + '-id]');
    }
    if (this.parent) {
      this.key = shkf.parent.getAttribute('data-' + this.prefix + '-key') || this.parent.getAttribute('data-' + this.prefix + '-id');
      this.id = this.key.split('#')[0];
    }
    return this.parent;
  };

  __.prototype.call = function(a) {
    var c = shkf.callback[a], t = typeof c;
    if (t !== 'undefined') {
      arguments[0] = shkf;
      return t === 'function' ? c.apply(shkf, arguments) : c;
    }
    return true;
  };

  __.prototype.actions = {
    add: function() {
      if (shkf.getParentElement() && shkf.call('add.before')) {
        shkf.process();
      }
    },
    del: function() {
      if (shkf.getParentElement('key') && shkf.call('del.before')) {
        shkf.process();
      }
    },
    count: function() {
      var pf = 'data-' + shkf.prefix, el, step;
      if (shkf.getParentElement() && shkf.call('count.before')) {
        el = shkf.parent.querySelector('[' + pf + '-count]');
        if (el) {
          step = el.getAttribute('step') || el.getAttribute(pf + '-step') || 1;
          if (shkf.action === 'minus') {
            step = -step;
          }
          if (el.tagName === 'INPUT') {
            el.value = shkf.setCount(el.value, step, shkf.action !== 'count');
          } else if (el.hasAttribute(pf + '-count')) {
            el.setAttribute(pf + '-count', shkf.setCount(el.getAttribute(pf + '-count'), step, shkf.action !== 'count'));
          } else {
            el.innerHTML = shkf.setCount(el.innerHTML, step, shkf.action !== 'count');
          }
        }
        if (shkf.getParentElement('key')) {
          shkf.process();
        }
      }
    },
    minus: function() {
      this.count();
    },
    plus: function() {
      this.count();
    },
    empty: function() {
      if (shkf.call('empty.before')) {
        shkf.process();
      }
    }
  };

  __.prototype.setCount = function(count, step, math) {
    count = parseFloat(count);
    step = parseFloat(step) || 1;
    math = typeof math !== 'undefined' ? math : true;
    if (math) {
      count += step;
    }
    if (count <= 0) {
      count = Math.abs(step);
    }
    return count.toFixed(this.numberOfCharactersAfterPoint(step));
  };

  __.prototype.numberOfCharactersAfterPoint = function(n) {
    return n.toString().includes('.') ? n.toString().split('.').pop().length : 0;
  };

  __.prototype.process = function() {
    if (this.call('process.before')) {
      this.ajax(this.setAction(), {
        carts: this.carts
      });
    }
  };

  __.prototype.update = function(action, data) {
    this.action = action || this.action;
    var params = this.prefix + '-action=' + this.action;
    data = this.objectToParams(data);
    params += data ? '&' + data : '';
    this.ajax(params, {
      carts: this.carts
    });
  };

  __.prototype.setAction = function() {
    var out = this.prefix + '-action=' + this.action;
    var el = this.getParentElement();
    if (el) {
      out += '&' + this.prefix + '-key=' + this.key;
      out += '&' + this.prefix + '-id=' + this.id;
      var elCount = el.querySelector('[data-' + this.prefix + '-count]');
      if (elCount) {
        var count = 0;
        if (elCount.tagName === 'INPUT') {
          count = parseFloat(elCount.value);
        } else {
          count = parseFloat(elCount.innerHTML);
        }
        out += '&' + this.prefix + '-count=' + count;
      }
      out += '&' + this.serialize(el);
    }

    return out;
  };

  __.prototype.ajax = function(params, data) {
    var _this = this;

    if (typeof params === 'function') {
      params = '';
    }

    if (typeof params === 'object') {
      data = params;
      params = '';
    }

    if (typeof data === 'function') {
      data = '';
    } else {
      data = this.objectToParams(data) || '';
    }

    if (typeof params === 'string') {
      data += (data ? '&' : '?') + params;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'assets/modules/shkf/ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-REQUESTED-WITH', 'XMLHttpRequest');
    xhr.responseType = 'json';
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        var response = xhr.response, k;
        if (xhr.status === 200 && response) {
          console.log(response);
          if (typeof response['carts'] !== 'undefined') {
            for (var cartId in response['carts']) {
              if (response['carts'].hasOwnProperty(cartId)) {
                var cartElement = document.getElementById(cartId);
                if (!cartElement) {
                  continue;
                }
                var cart = response['carts'][cartId];
                cart['cart'] = response['cart'];
                cart['cart']['cart.id'] = cartId;

                if (typeof cart['html'] !== 'undefined') {
                  cartElement.outerHTML = cart['html'];
                  var s = [], _s = '', b, c = /<script[^>]*>([\s\S]*?)<\/script>/gi;
                  while ((b = c.exec(cart['html']))) {
                    s.push(b[1]);
                  }
                  _s = s.join('\n');
                  if (_s) {
                    /** @namespace window.execScript */
                    (window.execScript) ? window.execScript(_s) : window.setTimeout(_s, 0);
                  }
                } else {
                  var tpl;
                  if (response['cart']['cart.count']) {
                    if (!cart['items']) {
                      cart['items'] = {};
                    }
                    for (var k in response['items']) {
                      if (response['items'].hasOwnProperty(k)) {
                        for (var kk in cart['items'][k]) {
                          if (cart['items'][k].hasOwnProperty(kk)) {
                            cart['items'][k][kk] = response['items'][k][kk];
                          }
                        }
                      }
                    }
                    if (typeof _this.carts[cartId]['tpl'] !== 'undefined') {
                      for (k in cart['items']) {
                        if (cart['items'].hasOwnProperty(k)) {
                          cart['cart']['cart.wrap'] += _this.tpl(_this.carts[cartId]['tpl'], cart['items'][k]);
                        }
                      }
                    }
                    tpl = _this.carts[cartId]['ownerTPL'];
                  } else {
                    tpl = _this.carts[cartId]['noneTPL'];
                  }
                  tpl = _this.tpl(tpl, cart['cart'], true);
                  if (typeof tpl === 'object') {
                    cartElement.parentElement.replaceChild(tpl, cartElement);
                  } else {
                    for (k in cart['cart']) {
                      if (cart['cart'].hasOwnProperty(k)) {
                        var alias = _this.prefix + '-' + cartId + '-' + k.replace(/\./g, '-'), el = cartElement.querySelector('.' + alias);
                        if (el) {
                          el.innerHTML = cart['cart'][k];
                        }
                        el = cartElement.parentElement.querySelector('[data-' + alias + ']');
                        if (el) {
                          el.setAttribute('data-' + alias, cart['cart'][k]);
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }

        _this.call(_this.action + '.after', response, xhr);
        _this.call('process.after', response, xhr);
      }
    };
    xhr.send(data);
  };

  __.prototype.searchAttribute = function(a, search) {
    if (a.length) {
      for (var i = 0; i < a.length; i++) {
        if (~a[i].localName.search(search)) {
          return a[i].localName;
        }
      }
    }
  };

  __.prototype.objectToParams = function(data) {
    if (typeof data === 'object') {
      data = Object.keys(data).map(function(k) {
        if (typeof data[k] === 'object') {
          return encodeURIComponent(k) + '=' + encodeURIComponent(JSON.stringify(data[k]));
        } else {
          return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
        }
      }).join('&');
    }

    return data;
  };

  __.prototype.serialize = function(form) {
    var serialized = [];
    if (form.tagName !== 'FORM') {
      form.elements = form.querySelectorAll('input, select, textarea, button');
    }
    for (var i = 0; i < form.elements.length; i++) {
      var field = form.elements[i];
      if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue;
      if (field.type === 'select-multiple') {
        for (var n = 0; n < field.options.length; n++) {
          if (!field.options[n].selected) continue;
          serialized.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.options[n].value));
        }
      } else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
        serialized.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value));
      }
    }
    return serialized.join('&');
  };

  __.prototype.tpl = function(template, data, isDom, cleanKeys) {
    if (typeof template == 'undefined') {
      return;
    }
    data = data || {};
    isDom = isDom || false;
    if (typeof cleanKeys === 'undefined') {
      cleanKeys = true;
    }
    var html = template.replace(/\[\+(.*?)\+\]/g, function(str, key) {
      var value = data[key];
      return (value === null || value === undefined) ? (cleanKeys ? '' : str) : value;
    });
    if (typeof data === 'boolean') {
      isDom = data;
    }
    if (isDom) {
      var fragment = document.createElement('div');
      fragment.innerHTML = html;
      return fragment.children.length ? fragment.children[0] : html;
    } else {
      return html;
    }
  };

  return new __(options);
});

document.addEventListener('DOMContentLoaded', function() {
  shkf.setEvents();
});

// Elements prototype
(function(ELEMENT) {
  /** @namespace ELEMENT.msMatchesSelector */
  /** @namespace ELEMENT.oMatchesSelector */
  /** @namespace ELEMENT.mozMatchesSelector */
  ELEMENT.matches = ELEMENT.matches || ELEMENT.mozMatchesSelector || ELEMENT.msMatchesSelector || ELEMENT.oMatchesSelector || ELEMENT.webkitMatchesSelector;
  ELEMENT.closest = ELEMENT.closest || function closest(selector) {
    if (!this) {
      return null;
    }
    if (this.matches(selector)) {
      return this;
    }
    if (!this.parentElement) {
      return null;
    } else {
      return this.parentElement.closest(selector);
    }
  };
}(Element.prototype));
