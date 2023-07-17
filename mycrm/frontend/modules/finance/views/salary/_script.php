<?php

$js = <<<JS

    $('.payroll-percent').on('change keyup', function(e) {
        var element = $(e.target);
        var value = element.val();
        if (!value) {
            $(this).val(0).change();
            return false;
        }
        var salaryInput = $('#salary');
        var salary = salaryInput.val();
        var servicePrice = element.closest('tr').find(".service-price").text();
        var serviceMode = element.closest('tr').find(".service-service_mode").text();
        var serviceQuantity = element.closest('tr').find(".service-quantity").text();

        var paymentInput = element.closest('tr').find('.payroll-amount');
        var payment = paymentInput.val();
        salary = salary - payment;

        servicePrice = servicePrice.replace(String.fromCharCode(160), "");
        
        if (+serviceMode === 1) {
            payment = value * serviceQuantity; 
        } else {
            payment = Number(servicePrice) / 100 * value;
        }
        
        salary = salary + payment;

        paymentInput.val(payment);
        salaryInput.val(salary);
        $('.payment-amount-total').text(salary);
    });

    $('.payroll-amount').on('change keyup', function(e) {
        var element = e.target;
        var curPayment = element.value;
        if (!curPayment) {
            $(this).val(0).change();
            return false;
        }
        var oldPayment = element.defaultValue;
        element.defaultValue = curPayment;

        var salaryInput = $('#salary');
        var salary = salaryInput.val();
        var servicePrice = $(element).closest('tr').find(".service-price").text();
        var serviceMode = $(element).closest('tr').find(".service-service_mode").text();
        var serviceQuantity = $(element).closest('tr').find(".service-quantity").text();

        var percentInput = $(element).closest('tr').find('.payroll-percent');
        var percent = percentInput.val();
        
        servicePrice = servicePrice.replace(String.fromCharCode(160), "");
        
        salary = salary - Number(oldPayment) + Number(curPayment);
        if (+serviceMode === 1) {
            percent = Math.round(curPayment / serviceQuantity);
        } else {
            percent = Math.round(curPayment * 100 / servicePrice);
        }

        percentInput.val(percent);
        salaryInput.val(Math.round(salary));
        $('.payment-amount-total').text(salary);
    });
JS;

$this->registerJs($js);