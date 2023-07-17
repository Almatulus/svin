window.orderDiscount = 0;
let paymentList = null;
let serviceProductsUrl = '/division/service/formula';
let commentsUrl = api_host + '/v2/comment/default/index';
let completeInputs = [];
let productCounter = 0;

function refreshComments(diagnosis_id, user_token) {
    $.each(completeInputs, function (category_id, completeInput) {
        if (completeInputs[category_id] !== undefined) {
            $.getJSON(commentsUrl, {
                "access-token": user_token,
                category_id: category_id,
                diagnosis_id: diagnosis_id
            }).done((data) => {
                completeInputs[category_id].destroy();
                completeInputs[category_id] = getAwesomeplete(
                    '#js-order_comment_' + category_id,
                    data.map(function (i) {
                        return i.comment;
                    })
                );
            });
        }
    });
}

function loadProducts(service_id, products) {
    products = products || null;
    if (!products) {
        $.get(serviceProductsUrl, {
            id: service_id
        }).done(function (response) {
            if (response.results) {
                $.each(response.results, function (key, product) {
                    renderProduct(product);
                    addPayments(product.price * product.quantity);
                });
                calcProductsTotal();
            }
        }).fail(function () {
            alertMessage("Произошла ошибка");
        });
    } else {
        $.each(products, function (key, product) {
            renderProduct(product);
            addPayments(product.price * product.quantity);
        });
        calcProductsTotal();
    }
}

function setupCompleteInput(category_id, user_token) {
    $.getJSON(commentsUrl, {
        "access-token": user_token,
        category_id: category_id
    }).done((data) => {
        let list = data.map(function (i) {
            return i.comment;
        });
        completeInputs[category_id] = getAwesomeplete('#js-order_comment_' + category_id, list);
        $('#js-order_comment_' + category_id).on('focus', function () {
            completeInputs[category_id].minChars = 0;
            completeInputs[category_id].evaluate();
            completeInputs[category_id].open();
        });
    });
}

function getAwesomeplete(selector, list) {
    return new Awesomplete(selector, {
        list: list,
        filter: function (text, input) {
            // remove teeth from input
            input = input.match(/[^: ]*$/)[0];
            return Awesomplete.FILTER_STARTSWITH(text, input.match(/[^;]*$/)[0]);
        },
        item: function (text, input) {
            return Awesomplete.ITEM(text, input.match(/[^; ]*$/)[0]);
        },
        replace: function (text) {
            // get teeth
            let teeth = this.input.value.match(/^(.*?):/);
            // remove teeth
            let before = this.input.value.match(/[^:]*$/)[0].trim();
            before = before.match(/^.+;\s*|/)[0];

            if (teeth) {
                before = teeth[0] + " " + before
            }

            this.input.value = before + text + "; ";
        },
        sort: function (a, b) {
            if (a.toUpperCase() == b.toUpperCase) {
                return 0;
            }
            return a.toUpperCase() < b.toUpperCase() ? -1 : 1;
        }
    });
}

function deleteEmptyRow(container) {
    container.getElementsByTagName('table')[0].getElementsByTagName('tbody')[0].children[0].remove();
}

function makeProductTextCell(className, text) {
    var cell = document.createElement('td');
    cell.className = className;
    if (typeof text !== 'undefined') {
        cell.appendChild(document.createTextNode(text));
    }
    return cell;
}

function makeProductCell(className, type, value, callback, name) {
    var cell = document.createElement('td');
    cell.className = className;
    var input = makeProductInput(type, value, callback, name);
    cell.appendChild(input);
    return cell;
}

function makeProductInput(type, value, callback, name) {
    let input = document.createElement('input');
    input.type = type;
    input.required = true;
    input.value = value || '';
    input.defaultValue = value || '';
    input.name = name || '';

    if (typeof callback == "function") {
        callback(input);
    }

    return input;
}

function initializeProductAutocomplete(element) {
    $(element).autocomplete({
        source: function (request, response) {
            jQuery.get('../warehouse/product/search', {
                search: request.term
            }, function (data) {
                response(data.results);
            });
        },
        minLength: 1,
        select: function (event, ui) {
            $(element).autocomplete("disable");
            selectTimetableProduct(event, ui.item);
        },
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li class='ui-menu-item'>")
            .append("<a>" + item.text + "</a>")
            .appendTo(ul);
    };
}

function updateProductPrice(event) {
    let quantity = $(this).closest('tr').find('.product-quantity > input').val();
    reducePayments(this.defaultValue * quantity);
    this.defaultValue = this.value;
    addPayments(this.value * quantity);
    calcProductsTotal();
}

function updateProductQuantity(event) {
    let element = $(event.target);
    let container = element.closest('.data_table');
    let index = container.attr('data-index');
    let row = element.closest('tr');
    let rowIndex = row.index();
    let stock_level = row.find('.product-stock-level').text();
    let price = row.find('.product-price > input').val();

    reducePayments(this.defaultValue * price);
    this.defaultValue = this.value;
    addPayments(this.value * price);

    if ($.isNumeric(stock_level) && this.value > Number(stock_level)) {
        element.popover({
            placement: 'left',
            content: 'Недостаточно товара на складе',
            trigger: 'manual'
        });
        element.popover('show');
    } else {
        element.popover('destroy');
    }

    calcProductsTotal();
}

function selectTimetableProduct(event, object) {
    var row = $(event.target).closest('tr');

    row.find('.ui-autocomplete-input').remove();
    row.find('.product_id-input').val(object.id);
    row.find('.product-name').append(object.text);
    row.find('.product-unit').text(object.unit);
    row.find('.product-price input').val(object.price).change();
    row.find('.product-stock-level').text(object.stock_level);
}

$('.tabs>.tabs-tab').click(function (event) {
    event.preventDefault();

    var element = $(event.target);
    if (element.hasClass('active')) {
        return false;
    }

    if (!element.hasClass('tabs-tab')) {
        element = element.closest('.tabs-tab');
    }

    $('.tabs>.tabs-tab').removeClass('active');
    element.addClass('active');

    var target = element.attr('data-target');
    $('#services, #products, #payments').hide();
    $('#' + target).show();
});

$("#order-productsprice").on("change paste keyup", function () {
    calcTotal();
    $('.order_payment_item').change();
});

function serviceDiscountChanged(e) {
    var discount = e.target.value;
    var oldDiscount = e.target.defaultValue;
    var price = $(this).closest('td').next('td').find('input[data-attr="price"]').val();

    var oldPrice = Number(price * (100 - oldDiscount) / 100);
    var newPrice = Number(price * (100 - discount) / 100);

    reducePayments(oldPrice);
    addPayments(newPrice);

    e.target.defaultValue = discount;

    calcServiceTotal();
}

function servicePriceChanged(e) {
    var price = e.target.value;
    var oldPrice = e.target.defaultValue;
    var discount = $(this).closest('td').prev('td').find('input[data-attr="discount"]').val();

    var oldSalePrice = Number(oldPrice * (100 - discount) / 100);
    var newSalePrice = Number(price * (100 - discount) / 100);

    reducePayments(oldSalePrice);
    addPayments(newSalePrice);

    e.target.defaultValue = price;

    calcServiceTotal();
}

function serviceQuantityChanged(e) {
    var quantity = e.target.value;
    var curRow = $(e.target).closest('tr');
    var price = curRow.data('service_price');
    var discount = curRow.find('input[data-attr="discount"]').val();

    var newPrice = Number(price * quantity);

    e.target.defaultValue = quantity;

    curRow.find('input[data-attr="price"]').val(newPrice).change();

    calcServiceTotal();
}

function calcServiceTotal() {
    var servicePrice = 0;
    $('input[data-attr="discount"]').filter(function () {
        var discount = $(this).val();
        var row = $(this).closest('tr');
        var price = row.find('input[data-attr="price"]').val();

        servicePrice += Number(price * (100 - discount) / 100);
    });
    $('#order-price').val(servicePrice).change();
}

function reducePayments(price) {
    $('.order_payment_item').each(function () {
        var itemPrice = $(this).val();
        if (itemPrice < price) {
            $(this).val(Math.max(0, itemPrice - price));
            price -= itemPrice;
        } else {
            $(this).val(itemPrice - price);
            price = 0;
        }
    });
    $('.order_payment_item').change();
}

function addPayments(price) {
    var payment = $('.order_payment_item:first');
    payment.val(price + parseInt(payment.val()));
    payment.change();
}

function calcProductsTotal() {
    let total = 0;
    $.each($('#products').find('.products-table').find('.product-row'), function (key, el) {
        let price = $(el).find('.product-price > input').val();
        let quantity = $(el).find('.product-quantity > input').val();
        total += Number(price * quantity);
    });
    $('#order-productsprice').val(total).change();
}

function calcTotal() {
    var servicesPrice = $('#order-price').val();
    var productsPrice = $('#order-productsprice').val();
    var total = Number(servicesPrice) + Number(productsPrice);
    $("#order-total-price").val(Math.round(total));
}

function removeService(e) {
    e.preventDefault();
    var rowsCount = document.getElementById('servicesTable').rows.length;
    var curRow = $(e.target).closest('tr');
    var curRowIndex = curRow.index();
    var price = curRow.find('input[data-attr=price]').val();
    var discount = curRow.find('input[data-attr=discount]').val();
    curRow.remove();

    calcServiceTotal();
    if (rowsCount == 1) {
        $('#tabbedTable').hide();
        $('#js-add-service').hide();
        $('#order-division_service_id').val('').change();
        $('.field-order-division_service_id .select2').show();
    } else {
        updateServiceIndexes(curRowIndex);
    }

    reducePayments(Number(price * (100 - discount) / 100));
}

function renderService(key, service) {
    var discount = service.discount === undefined ? orderDiscount : service.discount;
    var row = $('<tr></tr>');
    row.data('service_price', service.service_price);
    var firstCell = '<td class="order-service-column_name">' + service.service_name + '</td>';
    var secondCell = '<td></td>';
    var quantityInput = $('<input type="number" name="Order[services][' + key + '][quantity]" value="' + service.quantity
        + '" class="order-service-input form-control" data-key="' + key + '" data-attr="quantity">');
    var thirdCell = '<td></td>';
    var durationInput = $('<input type="number" name="Order[services][' + key + '][duration]" value="' + service.duration
        + '" class="order-service-input form-control" data-key="' + key + '" data-attr="duration">');
    var fourthCell = '<td></td>';
    var serviceIdInput = $('<input type="hidden" name="Order[services][' + key + '][division_service_id]" class="order-service-input"'
        + ' value="' + service.id + '" data-key="' + key + '" data-attr="division_service_id">');
    var orderServiceIdInput = $('<input type="hidden" name="Order[services][' + key + '][order_service_id]" class="order-service-input"'
        + ' value="' + service.order_service_id + '" data-key="' + key + '" data-attr="order_service_id">');
    var discountInput = $('<input type="number" name="Order[services][' + key + '][discount]" value="' + discount
        + '" class="order-service-input form-control" data-key="' + key + '" data-attr="discount">');
    var fifthCell = '<td></td>';
    var priceInput = $('<input type="number" name="Order[services][' + key + '][price]" value="' + service.price
        + '" class="order-service-input order-service-input_price form-control" data-key="' + key + '" data-attr="price">');
    var sixthCell = '<td></td>';
    var link = $('<a class=\"js-remove-service css-remove-service\" href=\"#\"><i class=\"icon sprite-delete\"></i></a>');

    link.click(removeService);
    discountInput.bind('change keyup paste', serviceDiscountChanged);
    priceInput.bind('change keyup paste', servicePriceChanged);
    quantityInput.bind('change keyup paste', serviceQuantityChanged);

    addPayments(service.price * (100 - discount) / 100);

    row.append(firstCell);
    row.append($(secondCell).append("кол-во ").append(quantityInput));
    row.append($(thirdCell).append("мин ").append(durationInput));
    row.append($(fourthCell).append(serviceIdInput).append(orderServiceIdInput).append("скидка: ").append(discountInput).append(" %"));
    row.append($(fifthCell).append("цена: ").append(priceInput).append("〒"));
    row.append($(sixthCell).append(link));
    return row;
}

function renderServices(services) {
    if (services && services.length > 0) {
        var servicesTable = $('#servicesTable');
        servicesTable.empty();
        var tabbedTable = $('#tabbedTable');
        $.each(services, function (key, service) {
            let row = renderService(key, service);
            servicesTable.append(row);
            // makeUsageTab(service.name, key, service.id, service.products);
        });
        tabbedTable.show();
        $('.field-order-division_service_id .select2').hide();
        $('#js-add-service').show();
    }
}

function renderContacts(contactCustomers) {
    if (contactCustomers && contactCustomers.length > 0) {
        let contacts_element = $('#order-contacts');
        $.each(contactCustomers, function (key, contactCustomer) {
            addContact(key, contactCustomer, contacts_element);
        });
    }
}

function addContact(key, contactCustomer, contacts_element) {
    let contact = '<div class="row">' +
        '    <input type="hidden" id="order_contact_id_' + key + '" class="form-control"' +
        '           name="Order[contacts]['+key+'][id]" value="' + contactCustomer.id + '">' +
        '    <div class="col-md-6">' +
        '        <input type="text" id="order_contact_name_' + key + '" class="form-control"' +
        '               name="Order[contacts]['+key+'][name]" autocomplete="off" value="' + contactCustomer.name + '">' +
        '        <p class="help-block help-block-error"></p>' +
        '    </div>' +
        '    <div class="col-md-5">' +
        '        <input type="text" id="order_contact_phone_' + key + '" class="form-control"' +
        '               name="Order[contacts]['+key+'][phone]" autocomplete="off"  value="' + contactCustomer.phone + '">' +
        '        <p class="help-block help-block-error"></p>' +
        '    </div>' +
        '    <div class="col-md-1">' +
        '        <button type="button" class="btn btn-danger pull-right js-delete-contact">&ndash;</button>' +
        '    </div>'
        '</div>';
    contacts_element.append(contact);
    initializeContactsAutocomplete('#order_contact_name_' + key, key, selectCustomerContact);
    initializeContactsAutocomplete('#order_contact_phone_' + key, key, selectCustomerContact);
    $("#order_contact_phone_" + key).inputmask({"mask":"+7 999 999 99 99"});
    $('.js-delete-contact').on('click', function () {
        $(this).closest('.row').remove();
    });
}

function selectCustomerContact(ui, key) {
    $('#order_contact_id_' + key).val(ui.item.id);
    $('#order_contact_name_' + key).val(ui.item.name);
    $('#order_contact_phone_' + key).val(ui.item.phone);
}

const searchUrl = '/timetable/search';
function initializeContactsAutocomplete(selector, key, callback) {
    let element = $(`${selector}`);
    if (element.length) {
        element.autocomplete({
            source: searchUrl,
            minLength: 1,
            select(event, ui) {
                callback(ui, key);
                return false;
            },
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>')
                .append(`<p style='padding: 6px 12px'>${item.name} ${item.lastname} ${item.phone} ` /* + item.customer_email */ + '</p>')
                .appendTo(ul);
        };
    }
}

$('#js-add-contact').on('click', function () {
    let contacts_container =$('#order-contacts');
    let contacts = contacts_container.find('.row').length;
    addContact(contacts + 1, {id: '', name: '', phone: ''}, contacts_container);
});

function updateServiceIndexes(index) {
    $('.order-service-input').filter(function () {
        var localIndex = $(this).attr('data-key');
        if (localIndex > index) {
            var newIndex = localIndex - 1;
            var newName = "Order[services][" + newIndex + "][" + $(this).attr('data-attr') + "]";
            $(this).attr('name', newName);
            $(this).attr('data-key', newIndex);
            return true;
        }
        return false;
    });
}

$('#js-add-service').click(function (e) {
    $(e.target).hide();
    $('.field-order-division_service_id .select2').show();
    $('#order-division_service_id').select2('open');
});

function PaymentList(event, payments) {
    this.payments = payments;
    this.total = 0;
    this.eventStatus = event.status;
}

const PAYMENT_TYPE_CASHBACK = 3;

PaymentList.prototype.render = function () {
    let content = "<table><tbody>";

    $.each(this.payments, function (key, payment) {

        let amount = payment.amount || 0;
        let isCashback = payment.type == PAYMENT_TYPE_CASHBACK;
        let colspan = 2;
        if (isCashback) {
            colspan = 1;
        }

        content += '<tr>';

        content += `<td><label for="order_payment_${payment.id}">${payment.name}</label></td>`;
        content += `<td colspan="${colspan}" >`;

        if (isCashback) {
            content += `<span class="payment-cashback">${amount}</span><div class="payment_cashback_input" style="display: none">`;
        }

        content += `<input name="Order[payments][${payment.id}][payment_id]" type="hidden" value="${payment.id}">`;
        content += `<input name="Order[payments][${payment.id}][amount]" type="text" value="${amount}" id="order_payment_${payment.id}" 
                class="order_payment_item">`;

        if (isCashback) {
            content += "<div>";
        }

        content += '</td>';

        if (isCashback) {
            content += `<td><a class="btn btn-default" id="js-order-use-cashback-button" style="${amount <= 0 ? 'display:none' : ''}">Использовать кэшбэк</a></td>`;
        }

        content += '</tr>';
    });

    let depisitButton = '<a class="btn btn-default" id="js-order-use-deposit-button">Использовать депозит</a>';
    content += '<tr><td><label>' + "Депозит" + '</label></td><td class="payment-deposit"></td><td>' + depisitButton + '</td></tr>';
    let debtButton = '<a class="btn btn-default js-pay-debt-button" id="js-order-pay-debt-button" href="/customer/customer/pay-debt" data-company-customer="0">Оплатить долг</a>';
    content += '<tr><td><label>' + "Долг" + '</label></td><td class="payment-debt"></td><td>' + debtButton + '</td></tr>';
    content += '</tbody></table>';

    return content;
};

PaymentList.prototype.addAmountListener = function () {
    var _this = this;
    $('.order_payment_item').on('change paste keyup', function (e) {
        var payment_id = $(this).closest('tr').find("input[type=hidden]").val();
        var payment = _this.getPayment(payment_id);
        payment.amount = $(this).val();
        _this.calculateTotal();
    });
}

PaymentList.prototype.getPayment = function (id) {
    var payment = this.payments.filter(function (payment) {
        return payment.id == id;
    });
    return payment ? payment[0] : null;
}

PaymentList.prototype.calculateTotal = function () {
    let total = 0;

    $.each(this.payments, function (key, payment) {
        let amount = payment.amount || 0;
        total += Number(amount);
    });
    this.total = total;

    $('#order-paid').val(this.total);
    let customerBalance = $('#payments').data('balance');
    this.calculateBalance(customerBalance);
};

PaymentList.prototype.calculateBalance = function (customerBalance) {
    let deposit = 0;
    let debt = 0;
    let orderTotal = $('#order-total-price').val();

    if (customerBalance < 0) {
        debt = Math.abs(customerBalance);
    } else {
        deposit = Math.abs(customerBalance);
    }

    if (this.eventStatus == 0 || this.eventStatus == undefined) {
        if (this.total > orderTotal) {
            deposit += this.total - orderTotal;
        } else if (this.total < orderTotal) {
            debt += orderTotal - this.total;
        }
    }

    $(".payment-deposit").text(deposit);
    $(".payment-debt").text(debt);
};

function renderPayments(event) {
    let division_id = event.division_id || $("#timetable-division_id").val();
    let payments = JSON.parse(JSON.stringify($("#timetable-division_id option[value=" + division_id + "]").data("payments")));

    paymentList = new PaymentList(event, payments);

    $.each(event.payments, function (key, order_payment) {
        let payment = paymentList.getPayment(order_payment.payment_id);
        if (payment !== undefined) {
            payment.amount = order_payment.amount;
        }
    });

    $('#payments').append(paymentList.render());
    paymentList.addAmountListener();
    paymentList.calculateTotal();

    $('#js-order-pay-debt-button').on('click', showDebtPaymentModal);
    $('#js-order-use-deposit-button').on('click', useDepositInTotalPrice);
}

$('#payments').on('click', '#js-order-use-cashback-button', function (e) {
    let cashback = $("#payments").find(".payment-cashback").text();
    $(".payment_cashback_input .order_payment_item").val(cashback);

    $('.order_payment_item').each(function () {
        let payment = $(this).val();
        let difference = Math.min(cashback, payment);

        cashback -= difference;
        payment -= difference;
        $(this).val(payment);
    });

    $("#payments").find(".payment-cashback").hide();
    $(this).hide();
    $(".payment_cashback_input").show();
});

function useDepositInTotalPrice() {
    let deposit = $(".payment-deposit").text();
    $('.order_payment_item').each(function () {
        let payment = $(this).val();
        let difference = Math.min(deposit, payment);
        deposit -= difference;
        payment -= difference;
        $(this).val(payment);
    });
    let debt = $(".payment-debt").text();
    let difference = Math.min(deposit, debt);
    deposit -= difference;
    debt -= difference;
    $(".payment-debt").text(debt);
    $(".payment-deposit").text(deposit);
    $(this).text('Депозит использован')
}

$('.js-new-referrer').click(function (e) {
    e.preventDefault();
    var href = $(this).attr('href');
    bootbox.prompt("Добавить направление", function (result) {
        if (result) {
            $.post(href, {"name": result})
                .done(function (response) {
                    addOption('order-referrer_id', response);
                    $('#order-referrer_id').trigger('change');
                })
                .fail(function (data) {
                    alertMessage("Произошла ошибка");
                });
        }
    });
});

$('#products').find('.js-add-product').click(addProduct);

$('#products').on('change', '.product-price > input', updateProductPrice);

$('#products').on('change', '.product-quantity > input', updateProductQuantity);

function renderProducts(products) {
    if (products && products.length > 0) {
        $.each(products, function (key, product) {
            renderProduct(product);
        });
        calcProductsTotal();
    }
}

function renderProduct(product) {
    let row = document.createElement('tr');
    row.className = 'product-row';

    let product_id = product ? product.product_id : null;
    let quantity = product ? product.quantity : 1;
    let purchase_price = product ? product.purchase_price : 0;
    let price = product ? product.price + '' : 0;
    let unit = product ? product.unit : "";
    let stock_level = product ? product.stock_level : "";

    let cell = null;
    if (product) {
        cell = makeProductTextCell("autocomplete-input-cell product-name", product.name);
    } else {
        cell = makeProductCell("autocomplete-input-cell product-name", 'text', '', initializeProductAutocomplete);
    }
    let productIdInput = makeProductInput('hidden', product_id, null, `Order[products][${productCounter}][product_id]`);
    productIdInput.className = 'product_id-input';
    cell.appendChild(productIdInput);
    row.appendChild(cell);

    cell = makeProductTextCell("product-unit", unit);
    row.appendChild(cell);

    cell = makeProductCell("product-price", 'number', price, null, `Order[products][${productCounter}][price]`);
    row.appendChild(cell);

    cell = makeProductCell("product-quantity error-popover", 'number', quantity, null, `Order[products][${productCounter}][quantity]`);
    row.appendChild(cell);

    cell = makeProductTextCell("product-stock-level", stock_level);
    row.appendChild(cell);

    cell = makeProductTextCell("delete-item-cell");
    let link = document.createElement('a');
    link.addEventListener('click', deleteProduct);
    let icon = document.createElement('i');
    icon.className = 'icon sprite-delete';
    link.appendChild(icon);
    cell.appendChild(link);
    row.appendChild(cell);

    $('.products-table').find('table').append(row);
    productCounter++;
}

function addProduct(event) {
    renderProduct();
    calcProductsTotal();
}

function deleteProduct(event) {
    let row = $(event.target).closest('tr');
    let quantity = row.find('.product-quantity > input').val();
    let price = row.find('.product-price > input').val();
    reducePayments(quantity * price);
    row.remove();
}