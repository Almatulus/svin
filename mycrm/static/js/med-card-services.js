$(function() {

    $('#js-med-card-add-service').click(function (e) {
        $(e.target).hide();
        $('.field-medcard-services .select2').show();
        $('#medcard-services').select2('open');
    });

    window.medCardServicesSelectEvent = function(e) {
        let servicesTable = $('#med_card_services_table');

        let key = document.getElementById('med_card_services_table').rows.length;
        let service = {
            division_service_id: e.params.data.id,
            duration: e.params.data.options.duration,
            name: e.params.data.name,
            price: e.params.data.options.price,
            quantity: 1,
            division_service_name: e.params.data.options.service_name,
            service_price: e.params.data.options.price,
        };
        let row = renderMedCareService(key, service);
        servicesTable.append(row);

        $('.field-medcard-services .select2').hide();
        $('#js-med-card-add-service').show();
        $('#med_card_services_tabbed_table').show();
        $('#js-med-card-tab-services .control-label').hide();

        calcMedCareServiceTotal();
    };

    window.medCardServicesCloseEvent = function(e) {
        let rowsCount = document.getElementById('med_card_services_table').rows.length;
        if (rowsCount >= 1) {
            $('.field-medcard-services .select2').hide();
            $('#js-med-card-add-service').show();
        }
    };

    window.renderMedCardServices = function (services) {
        if (services && services.length > 0) {
            let servicesTable = $('#med_card_services_table');
            let tabbedTable = $('#med_card_services_tabbed_table');
            $.each(services, function (key, service) {
                servicesTable.append(renderMedCareService(key, service));
            });
            tabbedTable.show();
            $('.field-medcard-services .select2').hide();
            $('#js-med-card-add-service').show();
            calcMedCareServiceTotal();
        }
    };

    function renderMedCareService(key, service) {
        let discount = service.discount || orderDiscount;
        let row = $('<tr></tr>');
        row.data('service_price', service.service_price);
        let firstCell = '<td class="order-service-column_name">' + service.division_service_name + '</td>';
        let secondCell = '<td></td>';
        let quantityInput = $('<input type="number" name="MedCard[services][' + key + '][quantity]" value="' + service.quantity
            + '" class="order-service-input form-control" data-key="' + key + '" data-attr="quantity">');
        let thirdCell = '<td></td>';
        let serviceIdInput = $('<input type="hidden" name="MedCard[services][' + key + '][division_service_id]" class="order-service-input"'
            + ' value="' + service.division_service_id + '" data-key="' + key + '" data-attr="division_service_id">');
        let orderServiceIdInput = $('<input type="hidden" name="MedCard[services][' + key + '][order_service_id]" class="order-service-input"'
            + ' value="' + service.order_service_id + '" data-key="' + key + '" data-attr="order_service_id">');
        let discountInput = $('<input type="number" name="MedCard[services][' + key + '][discount]" value="' + discount
            + '" class="order-service-input form-control" data-key="' + key + '" data-attr="discount">');
        let fifthCell = '<td></td>';
        let priceInput = $('<input type="number" name="MedCard[services][' + key + '][price]" value="' + service.price
            + '" class="order-service-input order-service-input_price form-control" data-key="' + key + '" data-attr="price">');
        let sixthCell = '<td></td>';
        let link = $('<a class=\"js-remove-service css-remove-service\" href=\"#\"><i class=\"icon sprite-delete\"></i></a>');

        link.click(removeMedCareService);
        discountInput.bind('change keyup input', serviceMedCareDiscountChanged);
        priceInput.bind('change keyup input', serviceMedCareChanged);
        quantityInput.bind('change keyup input', serviceMedCareQuantityChanged);

        row.append(firstCell);
        row.append($(secondCell).append("кол-во ").append(quantityInput));
        row.append($(thirdCell).append(serviceIdInput).append(orderServiceIdInput).append("скидка: ").append(discountInput).append(" %"));
        row.append($(fifthCell).append("цена: ").append(priceInput).append("〒"));
        row.append($(sixthCell).append(link));
        return row;
    }

    function removeMedCareService(e) {
        e.preventDefault();
        let rowsCount = document.getElementById('med_card_services_table').rows.length;
        let curRow = $(e.target).closest('tr');
        let price = curRow.find('input[data-attr=price]').val();
        let discount = curRow.find('input[data-attr=discount]').val();
        curRow.remove();

        if (rowsCount == 1) {
            $('#med_card_services_tabbed_table').hide();
            $('#js-med-card-add-service').hide();
            $('.field-medcard-services .select2').show();
        }
        calcMedCareServiceTotal();
    }

    function serviceMedCareChanged(e) {
        e.target.defaultValue = e.target.value;
        calcMedCareServiceTotal();
    }

    function serviceMedCareDiscountChanged(e) {
        let curRow = $(e.target).closest('tr');

        let quantity = curRow.find('input[data-attr="quantity"]').val();
        let price = curRow.data('service_price');
        let discount = e.target.value;

        let newPrice = Number(price * quantity * (100 - discount) / 100);

        curRow.find('input[data-attr="price"]').val(newPrice).change();

        calcMedCareServiceTotal();
    }

    function serviceMedCareQuantityChanged(e) {
        let quantity = e.target.value;
        let curRow = $(e.target).closest('tr');
        let price = curRow.data('service_price');
        let discount = curRow.find('input[data-attr="discount"]').val();

        let newPrice = Number(price * quantity * (100 - discount) / 100);

        e.target.defaultValue = quantity;

        curRow.find('input[data-attr="price"]').val(newPrice).change();

        calcMedCareServiceTotal();
    }

    function calcMedCareServiceTotal() {
        let servicePrice = 0;
        $('#js-med-card-tab-services').find('input[data-attr="discount"]').filter(function () {
            let discount = $(this).val();
            let row = $(this).closest('tr');
            let price = row.find('input[data-attr="price"]').val();
            let quantity = row.find('input[data-attr="quantity"]').val();

            servicePrice += Number(price);
        });
        $('#medcard-price').val(servicePrice).change();
    }
});
