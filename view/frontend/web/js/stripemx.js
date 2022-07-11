define(["jquery", "jquery/ui"], function ($){
    var stripemx = {
      selectors:{
        code: '#gdw_stripemx', 
        details: '#stripemx-details',
        formpayment: '#stripemx-payment-form',
        holdername: '#stripemx-cardholder-name', 
        plans: '#stripemx-plans',
        results: '#stripemx-result',
        message: '#stripemx-status-message',
        card: '#stripemx-card-element',
        nextbotton: '#stripemx-card-button',
        toolbar: '.stripemx-container .toolbar',
        selectedplan: '#selected_plan',
        intentid: '#payment-intent-id'
      },
      general:{
        card: null,
        stripe: null,
        element: null,
        isDebug: true,
        publicKey: null,
        cardElement: null,
        showConfirmButton: false
      },
      response:{
          data: ''
      }
    } 
  
    stripemx.setLogs = function (title, data) {
      stripemx.general.isDebug ? console.log(title) : '';
      stripemx.general.isDebug ? console.log(data) : '';
    }

    stripemx.setError = function (error) {
      $(stripemx.selectors.message).html();
      $(stripemx.selectors.message).html(error).slideDown().delay(8000).slideUp();
    }

    stripemx.createFormCard = function() {
      stripemx.general.stripe = Stripe(stripemx.general.publicKey);
      stripemx.general.element = stripemx.general.stripe.elements();
      stripemx.general.cardElement = stripemx.general.element.create('card');
      stripemx.general.cardElement.mount(stripemx.selectors.card);
    }
  
    stripemx.checkMSI = function(params) {
      var meses = {url: params.url, data: params.totals}
      stripemx.general.stripe.createPaymentMethod({
        type: 'card', billing_details: params.data, card: stripemx.general.cardElement
      }).then(function (result) {
        if (typeof (result.error) != "undefined" && result.error !== null){
          stripemx.setError(result.error.message);
          stripemx.setLogs('checkMSI', result);
        }else{
          stripemx.general.card = result;
          stripemx.setLogs('checkMSI', result);
          stripemx.getMSI(meses, result);
        }
      });
    }

    stripemx.getMSI = function(meses, result){
      stripemx.setLogs('getMSI:meses', meses);
      stripemx.setLogs('getMSI:result', result);
      
      $.ajax({
        url: meses.url,
        type: 'POST',
        dataType: 'JSON',
        data: {
          payment: result,
          totals: meses.data
        },
        beforeSend: function () {
          $(stripemx.selectors.formpayment).css({ 'opacity': 0.4 });
        }
      }).done(function (response) {
        $(stripemx.selectors.selectedplan).find('option[data-val="meses"]').remove();
          if (response.available_plans) {
            $.each(response.available_plans, function (i, v) { 
              $(stripemx.selectors.selectedplan).append('<option value="' + v.count +'" data-val="meses">'+v.count+' meses</option>');
            });
            $(stripemx.selectors.intentid).val(response.intent_id);
            $(stripemx.selectors.nextbotton).hide();
            $(stripemx.selectors.plans).slideDown();
            $(stripemx.selectors.toolbar).slideDown();
          }
          if (response.error) { 
            stripemx.setError(response.error);
          }
        $(stripemx.selectors.formpayment).css({ 'opacity': 1 });
      }).fail(function (jqXHR) {
        $(stripemx.selectors.formpayment).css({ 'opacity': 1 });
        stripemx.setLogs('getMSI', jqXHR.responseText);
        stripemx.response.data = jqXHR.responseText;
      });
      
    }

    this.stripemx = stripemx;
});