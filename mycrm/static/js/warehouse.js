let products = [];

$(function () {
    let modalForm = {
        className: null,
        dialogButtons: {
            success: {
                label: 'Сохранить',
                className: 'btn-primary'
            },
            danger: {
                label: 'Отмена',
                className: 'btn-default'
            }
        },
        submit: function (success) {
            let form = $('#' + modalForm.className + '-form');
            let url = form.attr('action');
            let self = this;
            $.post(url, form.serialize())
                .done(function (response) {
                    response = JSON.parse(response);
                    if (response.errors) {
                        modalForm.setErrors(response.errors)
                    }
                    if (response.status == 'success') {
                        if (response.data) {
                            success(response.data.id, response.data.name)
                        } else {
                            success()
                        }
                    }
                }).fail(function () {
                alertMessage('Произошла ошибка')
            })
        },
        setErrors: function (errors) {
            $.each(errors, function (key, value) {
                let element = $(`.field-${modalForm.className}-${key}`);
                element.addClass('has-error');
                element.find('.help-block').text(value[0])
            })
        }
    };

    function setDialogCallback(modelName) {
        let callback;
        if (modelName == 'customer') {
            callback = function () {
                let hideModal = function () {
                    bootbox.hideAll()
                };
                modalForm.submit(hideModal);
                return false
            }
        } else if (modelName == 'companycontractor') {
            callback = function () {
                let hideModal = function (key, value) {
                    bootbox.hideAll();
                    addOption('delivery-contractor_id', key, value);
                    $('#delivery-contractor_id').trigger('change')
                };
                modalForm.submit(hideModal);
                return false
            }
        } else {
            callback = function () {
                let hideModal = function (key, value) {
                    bootbox.hideAll();
                    addOption('product-' + modalForm.className + '_id', key, value)
                };
                modalForm.submit(hideModal);
                return false
            }
        }
        modalForm.dialogButtons.success.callback = callback
    }

    $('.stock_new_entity_link').click(function (e) {
        e.preventDefault();
        let url = $(e.target).attr('href');
        let className = $(e.target).data('model');
        let title = $(e.target).data('title');
        loadModal(className, url, title)
    });

    function loadModal(className, url, title) {
        $.get(url).done(function (response) {
            modalForm.className = className;
            setDialogCallback(className);
            dialogMessage(response, modalForm.dialogButtons, title)
        }).fail(function () {
            alertMessage('Произошла ошибка')
        })
    }

    function addOption(selectId, key, value) {
        let element = $(`#${selectId}`);
        element.append($('<option></option>').attr('value', key).text(value));
        element.val(key)
    }

    $('.product-quantity').change(function (e) {
        handleProductAttrChange(e, 'quantity');

        let element = $(event.target);
        let stock_level = element.closest('tr').find('.product-stock-level').text();
        if ($.isNumeric(stock_level) && element.val() > Number(stock_level)) {
            element.popover({
                placement: 'left',
                content: 'Недостаточно товара на складе',
                trigger: 'manual'
            });
            element.popover('show')
        } else {
            element.popover('destroy')
        }
    });

    $('.product-price').change(function (e) {
        handleProductAttrChange(e, 'price')
    });

    $('.product-discount').change(function (e) {
        handleProductAttrChange(e, 'discount')
    });

    function handleProductAttrChange(e, attrName) {
        let id = $(e.target).closest('tr').find('select').attr('id');
        let key = getProductKey(id);
        products[key].data[attrName] = $(e.target).val();
        let productCost = getProductCost(products[key].data.price, products[key].data.quantity, products[key].data.discount);
        $(e.target).closest('tr').find('.product-cost').text(productCost);
        calcWarehouseTotal()
    }

    $('.dynamicform_wrapper_products').on('afterInsert', function (e, item) {
        $(item).find('.product-quantity').val(1);
        $(item).find('.product-discount').val(0);
        $(item).find('.product-quantity').change(function (e) {
            handleProductAttrChange(e, 'quantity')
        });
        $(item).find('.product-price').change(function (e) {
            handleProductAttrChange(e, 'price');
        });
        $(item).find('.product-discount').change(function (e) {
            handleProductAttrChange(e, 'discount');
        });
    });

    $('.dynamicform_wrapper_products').on('beforeDelete', function (e, item) {
        let id = $(item).find('select').attr('id');
        let key = getProductKey(id);
        products.splice(key, 1)
    });

    $('.dynamicform_wrapper_products').on('afterDelete', function (e, item) {
        calcWarehouseTotal()
    });

    saleInit();

    function saleInit() {
        let saleItems = $('#model-products').data('products');
        $.each(saleItems, function (key, data) {
            products[key] = new Product(data)
        })
    }
});

function Product(data) {
    this.data = data
}

function initSelect2Loading(a, b) {
    initS2Loading(a, b);
    $('span.select2.select2-container').addClass('input-sm');
    initS2SelectHandler(a);
}

function initSelect2DropStyle(id, kvClose, ev) {
    initS2Open(id, kvClose, ev)
}

function initS2SelectHandler(id) {
    $('#' + id).on('select2:select', function (e) {
        selectWarehouseProduct(e)
    })
}

function selectWarehouseProduct(evt) {
    let args = evt.params.data;
    let row = $(evt.target).closest('tr');
    let quantity = row.find('.product-quantity').val();
    let discount = row.find('.product-discount').val();

    if (quantity === '') {
        quantity = 0
    }
    if (discount === '') {
        discount = 0;
    }

    args.quantity = quantity;
    args.discount = discount;

    let id = $(evt.target).attr('id');
    let key = getProductKey(id);
    products[key] = new Product(args);

    fillCells(row, args);

    calcWarehouseTotal()
}

function fillCells(row, args) {
    row.find('.product-unit').text(args.unit);
    row.find('.product-price').val(args.price).change();
    row.find('.product-discount').val(args.discount).change();
    row.find('.product-cost').text(getProductCost(args.price, args.quantity, args.discount));
    row.find('.product-stock-level').text(args.stock_level)
}

function calcWarehouseTotal() {
    let totalSum = 0;
    let subtotal = 0;
    let totalTax = 0;

    $.each(products, function (key, product_data) {
        let tax = 0;
        let product = product_data.data !== undefined ? product_data.data : product_data;
        let total = getProductCost(product.price, product.quantity, product.discount);
        if ($.isNumeric(product.vat) && product.vat !== 0) {
            tax = +(total / product.vat).toFixed(2)
        }
        totalSum += total;
        totalTax += tax;
        subtotal += (total - tax)
    });

    $('.products-total').text(totalSum);
    $('.sale-subtotal').text(subtotal);
    $('.sale-tax').text(totalTax);

    $('#sale-paid').val(totalSum)
}

function getProductKey(id) {
    let index = id.indexOf('-');
    return id.substr(index + 1).replace('-product_id', '')
}

function getProductCost(price, quantity, discount) {
    return Math.round(
        (discount ? ( (100 - discount) / 100 ) : 1 ) * (+quantity) * (+price)
    );
}