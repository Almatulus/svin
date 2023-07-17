$(function () {
    $('#menu-toggler').click(function() {
        if ($('body').hasClass('sidebar-active')) {
            $('body').removeClass('sidebar-active');
        } else {
            $('body').addClass('sidebar-active');
            $('body').css('overflow', 'visible');
        }
    });

    $('button[title], .titled[title]').qtip({
        style: 'higher-zindex'
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('#btn-show-more').on('click', function(e) {
        var target = $(e.target);
        var toggle = target.attr('data-toggle');
        if (toggle != '') {
            target.attr('data-toggle', '');
            $('.optional').removeClass("hidden");
        } else {
            target.attr('data-toggle', 'optional');
            $('.optional').addClass("hidden");
        }
    });

    $(document).ajaxStart(function() {
      $("#loading_indicator").show();
    });

    $(document).ajaxStop(function() {
      $("#loading_indicator").hide();
    });

    $('.filter-per-page').change(function(e) {
        var element = $(e.target);
        var value = element.val();
        var name = element.attr('name');
        window.location.href = updateQueryStringParameter(window.location.href, name, value);
    });

    function updateQueryStringParameter(uri, key, value) {
      var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
      var separator = uri.indexOf('?') !== -1 ? "&" : "?";
      if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
      }
      else {
        return uri + separator + key + "=" + value;
      }
    }

    var divisionContent = $(".division-form");
    var addItemBtn = divisionContent.find('a.add-item');
    if (addItemBtn.length) {
        var removeItem = function (e) {
            var element = $(e.target);
            var targetID = element.attr('data-target');
            var targetElement = $("#" + targetID);
            targetElement.remove();
        };
        var removeItemBtn = divisionContent.find('.remove-item');
        removeItemBtn.click(removeItem);

        var clone = function (e) {
            var element = $(e.target);
            var targetID = element.attr('data-target');
            var targetElement = $("#" + targetID);
            var parentElement = targetElement.closest(".controls");
            var cloneElement = targetElement.clone(false);

            var lastElement = $('.field-division-phones:last');
            var lastElementID = lastElement.attr('id');
            var newID = "phones-" + (Number(lastElementID.replace("phones-", "")) + 1);

            var button = cloneElement.find(".add-item");
            button.removeClass('add-item btn-primary');
            button.addClass('remove-item btn-danger');
            button.attr('data-target', newID);
            button.find('.fa').prop('class', 'fa fa-minus');
            button.click(removeItem);

            cloneElement.prop("id", newID);
            cloneElement.find("label").text("");
            cloneElement.find('input').val("").inputmask("+7 999 999 99 99", {"clearIncomplete": true});

            cloneElement.appendTo(parentElement);
        };
        addItemBtn.click(clone);
    }

    function ModalForm(id, classname) {
        this.id = id;
        this.classname = classname;
    }

    ModalForm.prototype.submit = function (success) {
        var form = $("#" + this.id);
        var url = form.attr('action');
        var self = this;
        $.post(url, form.serialize())
            .done(function (response) {
                response = JSON.parse(response);
                if (response.errors) {
                    self.setErrors(response.errors);
                }
                if (response.status == "success") {
                    success();
                }
            }).fail(function () {
            alertMessage("Произошла ошибка");
        });
    }

    ModalForm.prototype.setErrors = function (errors) {
        var self = this;
        $.each(errors, function (key, value) {
            $('.field-' + self.classname + '-' + key).find('.help-block').text(value[0]);
        });
    }

    var editCategoriesBtn = $('#btn-edit-categories');
    if (editCategoriesBtn.length) {
        var addCategoryBtn = $('.btn-add-category');
        var dialogButtons = {
            success: {
                label: "Сохранить",
                className: "btn-primary",
                callback: function () {
                    var modalForm = new ModalForm("category-form", 'servicecategory');
                    var success = function () {
                        bootbox.hideAll();
                        SERVICE.refreshTree();
                    };
                    modalForm.submit(success);
                    return false;
                }
            },
            danger: {
                label: "Отмена",
                className: "btn-default"
            }
        };
        var SERVICE = {
            addButtonsListeners: function () {
                var editCategoryBtn = $('.btn-edit-category');
                var deleteCategoryBtn = $('.btn-delete-category');
                editCategoryBtn.click(SERVICE.editCategory);
                deleteCategoryBtn.click(SERVICE.confirmDelete);
            },
            confirmDelete: function (e) {
                var message = "Вы уверены? Удаление приведет к потере данных."
                var callback = function () {
                    SERVICE.deleteCategory(e);
                };
                confirmMessage(message, callback);
            },
            deleteCategory: function (e) {
                var url = $(e.target).attr('data-url');
                $.post(url).done(function (response) {
                    SERVICE.refreshTree();
                }).fail(function () {
                    alertMessage("Произошла ошибка при удалении");
                });
            },
            editCategory: function (e) {
                var url = $(e.target).attr('data-url');
                $.get(url).done(function (response) {
                    dialogMessage(response, dialogButtons, "Категория");
                }).fail(function () {
                    alertMessage("Произошла ошибка");
                });
            },
            loadTree: function (e) {
                var url = editCategoriesBtn.attr('data-url');
                $.get(url).done(function (response) {
                    editCategoriesBtn.remove();
                    SERVICE.modifySidenav(response);
                    SERVICE.showSidenavButtons();
                    SERVICE.addButtonsListeners();
                }).fail(function () {
                    alertMessage("Произошла ошибка");
                });
            },
            modifySidenav: function (content) {
                var sidenav = $('#sidenav');
                sidenav.find('.tree_options').remove();
                sidenav.find('.tree').replaceWith(content);
                sidenav.css('background-color', "rgb(255, 255, 255)");
            },
            refreshTree: function () {
                var url = editCategoriesBtn.attr('data-url');
                $.get(url).done(function (response) {
                    var sidenav = $('#sidenav');
                    sidenav.find('.simple-tree').replaceWith(response);
                    SERVICE.addButtonsListeners();
                }).fail(function () {
                    alertMessage("Произошла ошибка");
                });
            },
            showSidenavButtons: function () {
                addCategoryBtn.show();
                $('.btn-done').show();
            }
        };
        editCategoriesBtn.click(SERVICE.loadTree);
        addCategoryBtn.click(SERVICE.editCategory);
    }

    $('#fullscreen').click(function(e){ $(this).fadeOut(); });

    $('#js-edit-cash').click(function(e) {
        e.preventDefault();
        $('#cash-modal').modal('show');
    });

    $('#js-transfer-cash').click(function (e) {
        e.preventDefault();
        $('#cash-transfer-modal').modal('show');
    });

    $('#js-delete-cash').click(e => {
        e.preventDefault();
        let url = e.target.href;
        confirmMessage("Вы уверены, что хотите удалить данную кассу?", result => {
            if (result) {
                $.post({ url: url, dataType: 'json'}).done(response => {
                    if (response.error) {
                        $.jGrowl(response.error, { group: 'flash_alert'});
                    } else {
                        $.jGrowl(response.message, { group: 'flash_notice'});
                        window.location.href = "index";
                    }
                }).fail(err => $.jGrowl("Произошла ошибка при удалении. ", { group: 'flash_alert'}));
            }
        });
    });
});

function addNewPosition() {
    var value = prompt('Введите новое значение для списка');

    $.post("/company/position/add/",
        {
            name: value
        },
        function (data) {
            if (data.success) {
                let select = $("#staff-company_position_ids");
                let option = $('<option></option>').attr('selected', true).text(data.name).val(data.id);
                option.appendTo(select);
                select.trigger('change.select2');
            } else console.log(data.message);
        }, 'json');
};

function add_hours_to_hovered_slots(view) {
    if (view.name != "month") {
        var column_width = get_column_width();
        var slats = $(".fc-view:visible .fc-slats");
        var axis_width = slats.find(".fc-axis:first").outerWidth();
        var a = slats.offset().left + axis_width;

        $(".fc-time-helper").remove();

        slats.find('td.fc-widget-content:not(".fc-axis")').unbind("mousenter").unbind("mousemove").unbind("mouseleave").on("mouseenter mousemove", function (f) {
            var b = Math.max(Math.floor((f.pageX - a) / column_width));
            var left = ((column_width * b) + axis_width);
            var time = $(this).parent().attr('data-time').substring(0, 5);
            $(this).html("<div class='fc-time-helper' style='left: " + left + "px'>" + time + " Новая запись</div>");
        }).on("mouseleave", function () {
            $(".fc-time-helper").remove();
        });
    }
}

function get_column_width() {
    return ($(".fc-view:visible .fc-time-grid .fc-bg td.fc-day:first").outerWidth());
}

if (typeof bootbox !== 'undefined') {
    bootbox.addLocale('ru', {
        OK : 'Продолжить',
        CANCEL : 'Отменить',
        CONFIRM : 'Подтвердить'
    });
    bootbox.setDefaults({locale: 'ru'});
}

function alertMessage(message, callback) {
    var dialog = bootbox.alert(message, callback);
}

function dialogMessage(message, buttons, title) {
    var dialog = bootbox.dialog({
        message: message,
        title: title,
        buttons: buttons
    });
    initDialog(dialog);
}

function confirmMessage(message, callback) {
    var dialog = bootbox.confirm({
        // size: 'small',
        message: message,
        callback: callback
    });
}

function initDialog(dialog) {
    dialog.init(function () {
        dialog.attr('tabindex', "");
    });
}

function initColorPicker(selector) {
    var colorSelect = $(selector);
    colorSelect.simplecolorpicker({picker: true})
        .on('change', function() {
            $('.simplecolorpicker.icon').prop('class', 'simplecolorpicker icon ' + $(this).val());
        });

    $('.simplecolorpicker.icon').prop('class', 'simplecolorpicker icon ' + colorSelect.val());

    $('span.color').each(function() {
        var item = $(this);
        item.prop('class', item.attr('data-color'));
    });

    $('body>span.simplecolorpicker').prop('class', 'simplecolorpicker picker color');
}

var formatRepoSelection = function (item) {
    var text = item.element.innerHTML;
    var backColor = $(item.element).attr("back-color");
    var fontColor = $(item.element).attr("font-color");
    var ans = "<span class='select2-selection__choice__name' style='background-color: " + backColor + "; color: " + fontColor + "'><i class='fa fa-tag'></i> " + text + "</span>";
    return ans;
}

function initializeTree(selector, source, loadError) {
    var opts = {
        source: source, // initial source
        checkbox: true,
        icon: false,
        expanded: true,
        selectMode: 3,
        strings:  {loading: "Загрузка...", loadError: "Произошла ошибка!", moreData: "Еще...", noData: "Нет данных."}
    };
    if (loadError) {
        opts.loadError = loadError;
    }
    $(selector).fancytree(opts);
}

function printElem(elem) {
    let mywindow = window.open('', 'PRINT');

    mywindow.document.write('<html><head><title>' + document.title + '</title>');
    mywindow.document.write('</head><body >');

    mywindow.document.write(elem.html());
    mywindow.document.write('</body></html>');

    mywindow.print();
    mywindow.close();

    return true;
}

$.fn.capitalize = function () {

    //iterate through each of the elements passed in, `$.each()` is faster than `.each()
    $.each(this, function () {

        //split the value of this input by the spaces
        var split = this.value.split(' ');

        //iterate through each of the "words" and capitalize them
        for (var i = 0, len = split.length; i < len; i++) {
            split[i] = split[i].charAt(0).toUpperCase() + split[i].slice(1).toLowerCase();
        }

        //re-join the string and set the value of the element
        this.value = split.join(' ');
    });
    return this;
};

function addOption(selectId, object) {
    $('#' + selectId).append($("<option></option>")
        .attr("value", object.id)
        .text(object.name));
}