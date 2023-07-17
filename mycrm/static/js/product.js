$(document).ready(function()
{
    let actionWidget = $('.product-index');

    let all = false;

    // Widget actions
    {
        $(actionWidget).on('change','input[type=checkbox]',function(event)
        {
            let keys = $('#products').yiiGridView('getSelectedRows');
            toggleButtons(keys.length > 0);
        });

        function toggleButtons(isEnabled)
        {
            let buttons = $('.js-selected');
            if(isEnabled) {
                buttons.removeClass('disabled');
            } else {
                buttons.addClass('disabled');
            }
        }
    }

    let products;
    actionWidget.on('click','.js-selected',function() {
        all = false;
        products = $('#products').yiiGridView('getSelectedRows');
    });


    {
        // Modals
        actionWidget.on('click', '.js-button-delete:not(.disabled)', function() {
            if(confirm("Вы уверены что хотите удалить данные товары?") === true) {
                if (products){
                    deleteProducts(products);
                }
            }
        });

        let deleteURL = 'batch-delete';

        function deleteProducts(products) {
            let data = { products };
            $.post(deleteURL, data, function(msg) {
                $('#products-container').addClass('loading');
                location.reload();
            });
        }
    }
});
