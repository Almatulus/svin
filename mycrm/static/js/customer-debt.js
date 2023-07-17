$(function () {
    $('.js-pay-debt-button').on('click', showDebtPaymentModal);
});

window.toggleBalanceButton = function(balance){
    balance < 0 ? $('.js-pay-debt-button').show() : $('.js-pay-debt-button').hide();
    balance > 0 ? $('#js-order-use-deposit-button').show() : $('#js-order-use-deposit-button').hide();
};

window.showDebtPaymentModal = function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    let company_customer_id = $(this).data('company-customer');
    let company_customer_debt = $(this).data('debt');
    let reload = $(this).data('reload');

    bootbox.prompt({
        title: "Погашение долга (Общий долг: " + company_customer_debt + ")",
        inputType: 'number',
        callback: function (result) {
            if (result) {
                $.get(url, {
                    id: company_customer_id,
                    amount: result
                }).done(function (response) {
                    if (reload !== undefined) {
                        window.location.reload();
                    } else {
                        loadCustomer(company_customer_id);
                    }
                    $.jGrowl("Долг погашен", {group: 'flash_notice'});
                }).fail(function (response) {
                    $.jGrowl(response.responseJSON.message, {group: 'flash_alert'});
                });
            } else {
                $.jGrowl("Погашения долга отменено", {group: 'flash_alert'});
            }
        }
    });
};