$(function () {
    $('.js-image-field').on('change', function(e) {
        let wrapper = $(this).parents('.js-image-field-wrapper');
        wrapper.hide();

        let chosen_photo = wrapper.siblings('.chosen_photo');
        chosen_photo.removeClass('hidden');
        chosen_photo.find('.photo_name').html(' ' + $(e.target).val());
    });
    $('.chosen_photo a').on('click', function(e) {
        $('.js-image-field').trigger('click');
    });
});