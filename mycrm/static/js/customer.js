$(document).ready(function()
{
    var searchButton = '.js-customers-search';
    var fetchedCustomersSelector = 'input[name="js-fetched-customers"]';
    var gridviewSelector = '#js-customers-gridview';
    var customersCountSelector = 'input[name="js-customers-count"]';

    var actionWidget = $('.customer-index');
    var modalCategory = '#js-modal-category';
    var modalCategoryItems = '#js-modal-category-items';
    var modalCategorySubmit = '#js-modal-category-submit';

    var modalRequest = '#js-modal-request';
    var modalRequestItems = '#js-modal-request-items';
    var modalRequestSubmit = '#js-modal-request-submit';

    var all = false;

    // Widget actions
    {
        $(actionWidget).on('change','input[type=checkbox]',function(event)
        {
            var keys = $('#customers').yiiGridView('getSelectedRows');
            toggleButtons(keys.length > 0);
        });

        function toggleButtons(isEnabled)
        {
            var buttons = $('.js-selected');
            if(isEnabled) {
                buttons.removeClass('disabled');
            } else {
                buttons.addClass('disabled');
            }
        }
    }

    // Export function
    var exportURL = 'export';
    actionWidget.on('click','#js-export-fetched', function() {
        downloadURL(exportURL + '?mode=0');
    });
    actionWidget.on('click','#js-export-all', function() {
        downloadURL(exportURL + '?mode=1');
    });

    var customers;
    actionWidget.on('click','.js-selected',function() {
        all = false;
        customers = $('#customers').yiiGridView('getSelectedRows');
        $('.js-modal-customer-size').text(customers.length);
    });

    actionWidget.on('click','.js-fetched',function() {
        all = false;
        customers = $(fetchedCustomersSelector).val();
        customers = JSON.parse(customers);
        $('.js-modal-customer-size').text(customers.length);
    });
    actionWidget.on('click', '.js-all', function () {
        all = true;
        var customersCount = $(customersCountSelector).val();
        $('.js-modal-customer-size').text(customersCount);
    });

    {
        // Modals
        actionWidget.on('click', '.js-button-request:not(.disabled)', function() {
            $(modalRequest).modal('show');
        });
        actionWidget.on('click', '.js-button-category:not(.disabled)', function() {
            $(modalCategory).modal('show');
        });
        actionWidget.on('click', '.js-button-delete:not(.disabled)', function() {
            if(confirm("Вы уверены что хотите удалить данных клиентов?") == true) {
                if(customers) customersDelete(customers);
            } else {
                //alert("You pressed Cancel!");
            }
        });
        actionWidget.on('click', '.js-merge-selected:not(.disabled)', function () {
            let message = "<table id='merged-customers' class='table table-bordered'><thead><tr><th></th><th>ID</th><th>Клиент</th></tr></thead>";
            customers.forEach((customer_id) => {
                let row = $('#customers-container').find('table tr[data-key=' + customer_id + ']').clone();
                row.find('td:gt(2)').remove();
                $(row).find('input').attr({'type': 'radio', 'name': 'primary_merge_id'});
                message += '<tr>' + row.html() + '</tr>'
            });
            message += '</table>';

            dialogMessage(message, {
                success: {
                    label: "Сохранить",
                    className: "btn-primary",
                    callback: function () {
                        if (customers) {
                            customersMerge(customers);
                        }
                    }
                },
                danger: {
                    label: "Отмена",
                    className: "btn-default"
                }
            }, "Выберите основного клиента");

        });

        $(modalCategorySubmit).on('click', function() {
            if(customers) customersAddCategory(customers);
        });
        $(modalRequestSubmit).on('click', function() {
            if (customers || all) customersSendRequest(customers);
        });

        $('#js-modal-request-text').keyup(function() {
            let symbols = $('.js-modal-request-symbols');
            let count = $('.js-modal-request-count');
            let message = $(this).val();
            symbols.text(message.length);

            let numberOfSms = 0;
            let hasNonLatinCharacters = message.search(/[^a-zA-z0-9._?~!@#$%^&*()`\[\]{}\"\';:,.\/<>?| ]/);
            if (hasNonLatinCharacters !== -1) {
                numberOfSms = parseInt(message.length / 70) + 1;
            } else {
                numberOfSms = parseInt(message.length / 160) + 1;
            }
            count.text(numberOfSms);
        });

        var categoryURL = 'add-categories';
        var requestURL = 'send-request';
        var deleteURL = 'delete-customers';
        function customersAddCategory(customers)
        {
            var categories = $(modalCategoryItems).val();
            var data = {
                'customers': customers,
                'categories': categories
            };
            $.post(categoryURL, data, function(msg) {
                // alert("Прибыли данные: " + msg);
                $(modalCategory).modal('hide');

                $('#customers-container').addClass('loading');
                $.pjax.reload({
                    container:"#pjax-container",
                    timeout: 10000
                });
            });
        }

        function customersSendRequest(customers) {
            var message = $('#js-modal-request-text').val();
            var data = {
                'all': all,
                'customers': customers,
                'message': message
            };
            $.post(requestURL, data, function(response) {
                // alert("Прибыли данные: " + msg);
                $(modalRequest).modal('hide');

                if (response.status == 'success') {
                    $.jGrowl(response.message, { group: "flash_notice"});
                    $('#customers-container').addClass('loading');
                    $.pjax.reload({
                        container:"#pjax-container",
                        timeout: 10000
                    });
                } else {
                    $.jGrowl(response.message, { group: "flash_alert"});
                }

            });
        }

        function customersDelete(customers) {
            var data = {
                'customers': customers
            };
            $.post(deleteURL, data, function(msg) {
                // alert("Прибыли данные: " + msg);
//                $(modalRequest).modal('hide');

                $('#customers-container').addClass('loading');
                $.pjax.reload({
                    container:"#pjax-container",
                    timeout: 10000
                });
            });
        }

        function customersMerge(customers) {
            let primaryCustomer = $('#merged-customers').find('input[name=primary_merge_id]:checked').val();

            if (!primaryCustomer) {
                return false;
            }

            let data = {
                'customer_ids': customers.filter((customer_id) => customer_id != primaryCustomer)
            };


            $.post(`merge?id=${primaryCustomer}`, data)
                .done(function (msg) {
                    $.jGrowl(msg, {group: "flash_notice"});

                    $('#customers-container').addClass('loading');

                    location.reload();
                })
                .fail(function () {
                    $.jGrowl("Произошла ошибка", {group: "flash_alert"});
                });
        }
    }

    var $idown;  // Keep it outside of the function, so it's initialized once.
    window.downloadURL = function(url) {
        if ($idown) {
            $idown.attr('src',url);
        } else {
            $idown = $('<iframe>', { id:'idown', src:url }).hide().appendTo('body');
        }
    }

    // When search button is pressed
    actionWidget.on('click',searchButton, function() {
        $('#customers-container').addClass('loading');
    });
});
