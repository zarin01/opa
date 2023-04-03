/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1);


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(2);

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./assets/js/components/payment/StripePaymentForm.js


function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var StripePaymentForm = /*#__PURE__*/function () {
  /**
   * @param $wrapper
   * @param globalEventDispatcher
   * @param buildingBlockName
   * @param useProductionKey
   */
  function StripePaymentForm($wrapper, globalEventDispatcher, buildingBlockName) {
    var useProductionKey = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

    _classCallCheck(this, StripePaymentForm);

    this.$wrapper = $wrapper;
    this.globalEventDispatcher = globalEventDispatcher;
    this.stripeKey = this.$wrapper.attr('data-stripe-publishable-key');
    this.instantiate().instantiateElements().unbindEvents().bindEvents();
  }
  /**
   * Call like this.selectors
   */


  _createClass(StripePaymentForm, [{
    key: "bindEvents",
    value: function bindEvents() {
      this.$wrapper.on('submit', StripePaymentForm._selectors.form, this.handleFormSubmit.bind(this));
      return this;
    }
  }, {
    key: "unbindEvents",
    value: function unbindEvents() {
      this.$wrapper.off('submit', StripePaymentForm._selectors.form);
      return this;
    }
    /**
     * @return {StripePaymentForm}
     */

  }, {
    key: "instantiate",
    value: function instantiate() {
      this.stripe = Stripe(this.stripeKey);
      this.elements = this.stripe.elements();
      return this;
    }
  }, {
    key: "instantiateElements",

    /**
     * @return {StripePaymentForm}
     */
    value: function instantiateElements() {
      var card = this.card = this.elements.create('card', {
        style: StripePaymentForm.cardStyles()
      });
      card.mount('#card');
      return this;
    }
    /**
     * @param e
     */

  }, {
    key: "handleFormSubmit",
    value: function handleFormSubmit(e) {
      var _this = this;

      e.preventDefault();
      var $form = $(e.currentTarget);
      var tokenData = {
        address_line1: $form.find('input[name="address_line_1"]').val(),
        address_city: $form.find('input[name="address_city"]').val(),
        address_state: $form.find('input[name="address_state"]').val(),
        address_zip: $form.find('input[name="address_zip"]').val()
      };
      this.stripe.createToken(this.card, tokenData).then(function (result) {
        if (result.error) {
          // Inform the customer that there was an error.
          var errorElement = document.getElementById('card-errors');
          errorElement.textContent = result.error.message;
        } else {
          // Send the token to your server.
          _this.stripeTokenHandler(result.token);
        }
      });
    }
  }, {
    key: "stripeTokenHandler",
    value: function stripeTokenHandler(token) {
      var self = this; // Insert the token ID into the form so it gets submitted to the server

      var stripeToken = document.getElementById('stripeToken');
      stripeToken.value = token.id; // Collect Form info

      var $form = $('#payment-form');
    
      
      var formData = new FormData($form.get(0)); // Submit the form to the server
     
      $.ajax({
        url: localized.ajax_url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
      }).then(function (data, textStatus, jqXHR) {
        if (data.success === true) {
          self.paymentSuccessful(data);
        } else if (data.success === false) {
          self.paymentFailed(data);
        }
      })["catch"](function (jqXHR) {
        self.paymentFailed(null, "Server error.  Please try again later.");
      });
    }
  }, {
    key: "paymentSuccessful",
    value: function paymentSuccessful(response) {
      var message = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      console.log('success', response);
      //location.reload();
      window.location.href= '/thankyou/?orderId='+response.registration;
    }
  }, {
    key: "paymentFailed",
    value: function paymentFailed(response) {
      var message = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      console.log('failed', response);
    }
  }], [{
    key: "cardStyles",
    value: function cardStyles() {
      return {
        base: {
          color: '#32325d',
          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
          fontSmoothing: 'antialiased',
          fontSize: '16px',
          '::placeholder': {
            color: '#aab7c4'
          }
        },
        invalid: {
          color: '#fa755a',
          iconColor: '#fa755a'
        }
      };
    }
  }, {
    key: "_selectors",
    get: function get() {
      return {
        form: '#payment-form'
      };
    }
  }]);

  return StripePaymentForm;
}();

/* harmony default export */ var payment_StripePaymentForm = (StripePaymentForm);
// CONCATENATED MODULE: ./assets/js/components/payment/index.js


(function ($) {
  window.$ = $;
  $(document).ready(function () {
    if (document.getElementById('js-payment-form-widget')) {
      new payment_StripePaymentForm($('#js-payment-form-widget'));
    }
  });
})(jQuery);

/***/ })
/******/ ]);