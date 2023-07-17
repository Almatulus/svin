$(function() {
    let visits = [];
    let savedTeeth = [];
    let currentVisit = null;
    let selectedTeeth = [];
    const DIAGNOSIS_MOBILITY = 8;
    const abbreviations = $('.order-tooth').data('abbreviations');
    const colors = $('.order-tooth').data('colors');

    window.loadMedCards = function(company_customer_id) {
        $.getJSON(api_host + '/v2/order', {
            company_customer_id: company_customer_id,
            'access-token': user_token,
            expand: 'medCard,staff,customer,staff_position',
            pagination: false
        }).done(function (data) {
            $.each(data, function(key, visit) {
                visits[visit.id] = visit;
                $('#med-exams tbody').append(renderVisit(visit));
            });
            addExamListeners();
        });
    };

    function renderVisit(visit) {
        let buttonLabel = 'Создать дневник';
        if (isMedCardCreated(visit)) {
            buttonLabel = 'Открыть дневник';
        }

        return `
            <tr>
                <td>${moment(visit.datetime).format('DD MMMM YYYY HH:mm')}</td>
                <td>${visit.staff_position}</td>
                <td>${visit.staff.fullname}</td>
                <td><a class="js-open-med-exam" href="#" data-visit-id="${visit.id}">${buttonLabel}</a></td>
            </tr>
        `;
    }

    function findPreviousCreatedMedExam(visit) {
        return visits
            .filter(item => isMedCardCreated(item) && item.datetime < visit.datetime)
            .reduce((prevItem, curItem) => {
                return prevItem && prevItem.datetime > curItem.datetime ? prevItem : curItem;
            }, undefined);
    }

    function addExamListeners() {
        $(".js-open-med-exam").click(function(e) {
            e.preventDefault();
            let visit_id = $(e.target).data('visit-id');
            currentVisit = visits[visit_id];
            let tabs = [];
            let teeth = [];
            let comments = [];
            let childTeeth = [];
            let isCompleted = false;

            if (!isMedCardCreated(currentVisit)) {
                let previousVisit = findPreviousCreatedMedExam(currentVisit);
                if (previousVisit) {
                    teeth = previousVisit.medCard.tabs.map(tab => tab.teeth)
                        .reduce((flat, toFlatten) => flat.concat(toFlatten), []);
                    childTeeth = previousVisit.medCard.tabs.map(tab => tab.childTeeth)
                        .reduce((flat, toFlatten) => flat.concat(toFlatten), []);
                    // tabs = previousVisit.medCard.tabs;
                }
            } else {
                teeth = currentVisit.medCard.tabs.map(tab => tab.teeth)
                    .reduce((flat, toFlatten) => flat.concat(toFlatten), []);
                childTeeth = currentVisit.medCard.tabs.map(tab => tab.childTeeth)
                    .reduce((flat, toFlatten) => flat.concat(toFlatten), []);
                tabs = currentVisit.medCard.tabs;
                savedTeeth = teeth.map(tooth => tooth.teeth_num);
            }

            selectTeeth(teeth);
            if (childTeeth.length > 0) {
                enableChildTeeth();
            }
            renderTabs(tabs);

            $("#medcard-modal").show("modal");
        });
    }

    function closeMedcard() {
        // close medcard
        $("#medcard-modal").hide("modal");
        // reset selected teeth
        $('.selected-teeth').text("");
        // empty teeth table
        $('.medcard-tabs > tbody').html("");
        // reset current visit and unselect teeth
        currentVisit = null;
        unselectTeeth();
    }

    function isMedCardCreated(visit) {
        return visit.medCard;
    }

    window.clearMedCards = function() {
        visits = [];
        $('#med-exams > tbody').html('');
    }

    $('.js-close-medcard').click(closeMedcard);

    $('.js-print-medcard').click(function(e) {

        $('.print-division-logo').hide();
        $('#print-division-logo-' + currentVisit.division_id).show();

        let page_header = $('#js-print-header').html();
        if (isMedCardCreated(currentVisit)) {
            let phone = (company_phone == '') ? '' : `Телефон: <b>${company_phone}</b>`;
            let customer_phone = (currentVisit.customer.phone == '') ? '' : `Телефон: <b>${currentVisit.customer.phone}</b>`;
            customer_phone = '';

            let content = `<div style='width: 700px; margin: 0 auto;'>
                <table width='100%'>
                    <tr>
                        <td colspan="2" align="center">
                            ${page_header}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align='right'>Дата приема: <b>${moment(currentVisit.datetime).format('DD MMMM YYYY HH:mm')}</b></td>
                    </tr>
                    <tr>
                        <td>Пациент: <b>${currentVisit.customer.fullname}</b></td>
                        <td align='right'>Врач: <b>${currentVisit.staff.fullname}</b></td>
                    </tr>
                    <tr>
                        <td>${customer_phone}</td>
                        <td align='right'>${phone}</td>
                    </tr>
                </table>
                <br>
                <h3 align='CENTER'><strong>Дневник истории болезни № ${currentVisit.medCard.number}</strong></h3>
            `;
            content += getPrintTeethSection();
            content += getPrintCommentsSection();
            content += getPrintServicesSection();
            content += getPrintFooter(currentVisit);
            content += "</table></div>";

            printElem($(content));
        }
    });

    function getPrintTeethSection() {
        let start = [18, 21, 55, 61, 48, 31, 85, 71];
        let end = [11, 28, 51, 65, 41, 38, 81, 75];
        let right = [18, 55, 48, 85];
        let output = "";

        let teeth = currentVisit.medCard.tabs.map(tab => tab.teeth)
            .reduce((flat, toFlatten) => flat.concat(toFlatten), []);
        let child_teeth = teeth.filter((item) => {
            return getIsChildTooth(item.teeth_num);
        });

        $.each($('.teeth-view .order-tooth-img'), function (ind, item) {
            let toothNumber = parseInt(item.getAttribute('class').replace(/[^\d]*/, ''));

            let isChildTooth = getIsChildTooth(toothNumber);
            if (!isChildTooth || (isChildTooth && child_teeth.length > 0)) {
                let tooth = teeth.find(item => item.teeth_num == toothNumber);
                let diagnosis = tooth ? tooth.diagnosis_id : null;

                let color = colors[diagnosis];
                let abbreviation = getAbbreviation(diagnosis) || '&nbsp;';

                if (start.indexOf(toothNumber) > -1) {
                    let textAlign = 'text-align: left;';
                    if (right.indexOf(toothNumber) > -1) {
                        textAlign = 'text-align: right;';
                    }
                    output += `<div style="width: 50%; display: inline-block; box-sizing: border-box; padding: 4px 10px; ${textAlign}">`;
                }

                output += renderPrintTooth(toothNumber, abbreviation, color);

                if (end.indexOf(toothNumber) > -1) {
                    output += '</div>'
                }
            }
        });

        return output;
    }

    function renderPrintTooth(toothNumber, abbreviation, color) {
        let output = '<div style="display: inline-block; height: 45px; text-align: center; width: 35px; vertical-align: top;">';

        if (toothNumber < 29 || (toothNumber >= 51 && toothNumber <= 65)) {
            output += `<span style="font-weight: 700; font-size: 13px;">${toothNumber}</span>`;
        }

        // Tooth image
        output += '<span style="background-color: ' + color + ';display: inline-block;-webkit-print-color-adjust: exact;">'
            + '<img src="/image/teeth.png" height="35" width="35" /></span>';
        let image_style = 'margin-top: -13px; font-weight: 700; font-size: 12px; top: -16px; position: relative; display: block;';
        output += `<span style="${image_style}">${abbreviation}</span>`;
        if (toothNumber > 70 || (toothNumber >= 31 && toothNumber <= 48)) {
            output += `<span style="font-weight: 700; font-size: 13px;">${toothNumber}</span>`;
        }
        output += "</div>";

        return output;
    }

    function getIsChildTooth(tooth_number) {
        let child_ranges = [[55, 51], [65, 61], [85, 81], [75, 71]];

        return child_ranges.reduce((result, range) => {
            return result || (range[0] >= tooth_number && range[1] <= tooth_number);
        }, false);
    }

    function getPrintFooter(currentVisit) {
        let output = '';
        output += '<table style="float: right; padding-top: 20px"><tr>' +
            '<td align="right">' + currentVisit.staff.fullname + '</td>' +
            '</tr><tr>' +
            '<td style="border-bottom: 1px solid #000000; padding-top: 20px"></td>' +
            '</tr></table>';
        return output;
    }

    function getPrintServicesSection() {
        let services = currentVisit.medCard.tabs.map(tab => tab.services)
            .reduce((flat, toFlatten) => flat.concat(toFlatten), []);

        let output ='';
        if (services.length > 0) {
            output = '<h4 align="center"><strong>Услуги</strong></h4>';
            output +='<table border="1" width="100%" cellspacing="0" cellpadding="10">';
            output += '<tr>';
            output += '<th>Название</th>';
            output += '<th>Количество</th>';
            output += '<th>Скидка, %</th>';
            output += '<th>Итого за услугу, тг.</th>';
            output += '</tr>';
            output += services.reduce((prevItem, curItem) => {
                let row = `<tr>`;
                row += `<td>${curItem.division_service_name}</td>`;
                row += `<td align="center">${curItem.quantity}</td>`;
                row += `<td align="center">${curItem.discount}</td>`;
                row += `<td align="center">${curItem.price}</td>`;
                row += `</tr>`;
                return prevItem + row;
            }, "");
            output += '</table><br/>';

            let total = services.reduce((prevItem, curItem) => {
                return prevItem + Number(curItem.price);
            }, 0);
            output += '<strong>Итого: ' + total + ' тг.</strong>';
        }
        return output;
    }

    function getPrintCommentsSection() {
        let blocks = [];
        let comments = currentVisit.medCard.tabs.map(tab => tab.comments)
            .reduce((flat, toFlatten) => flat.concat(toFlatten), []);

        comments.forEach(comment => {
            if (typeof blocks[comment.category_id] !== 'undefined') {
                blocks[comment.category_id] += " " + comment.comment + ";";
            } else {
                blocks[comment.category_id] = comment.comment + ";";
            }
        });

        return blocks.reduce((prevItem, curItem, category_id) => {
            let title = $('#comment-heading-' + category_id + ' a').text();
            let text = curItem.replace(/^[0-9, :;]*/, '');
            return text == '' ? prevItem : prevItem + `<p><strong>${title}</strong></p><p>${curItem}</p>`;
        }, "");
    }

    //////////////////////////
    function renderTabs(tabs) {
        $('.medcard-tabs > tbody').html(
            tabs.reduce((prevItem, curItem) => {
                return prevItem + renderTab(curItem);
            }, "")
        );
        addMedCardTabListeners('.medcard-tabs > tbody');
    }

    function renderTab(tab) {
        let data = [];
        if (tab.teeth.length > 0) {
            data.push(`Детали: ${tab.teeth.map(tooth => tooth.teeth_num).join(', ')}`);
        }
        if (tab.services.length > 0) {
            data.push(`Услуги: ${tab.services.map(service => service.division_service_name).join(', ')}`);
        }

        return `<tr data-tab-id="${tab.id}">
            <td>
                ${data.map(item => item).join('<hr/>')}
            </td>
            <td class="text-right">
                <a href="#" class='js-edit-medcard-tab' data-tab-id="${tab.id}"><i class="fa fa-pencil"></i></a>
                <a href="#" class='js-delete-medcard-tab' data-tab-id="${tab.id}"><i class="fa fa-trash"></i></a>
            </td>
        </tr>`;
    }

    $('.js-create-medcard-tab').click(function(e) {
        $('.teeth-form .order-tooth-img').html('<img src="/image/teeth.png" width="35" height="35"/>').css('background-color', '');
        $('.js-save-medcard-tab').removeData('tab-id');
        $('#medcard-tab-modal').find('.modal-title').text('Новая область');
        hideFormTeeth(savedTeeth);
        showMedCardTab();
    });

    $('.js-teeth-history').click(function(e) {
        let teeth = $(this).data('teeth')
        $('#js-teeth-number').html(teeth)
        let company_customer_id = $('.order-tooth-img-' + teeth).data('company_customer_id');

        if (company_customer_id !== undefined) {
            $.getJSON('/med-card/default/history', {
                teeth: teeth,
                company_customer_id: company_customer_id
            }).done(function (data) {
                $('#medcard-teeth-history').find('.modal-body').html(getHistoryTable(data));
            });
        } else {
            $('#medcard-teeth-history').find('.modal-body').html(getHistoryTable([]));
        }

        $('#medcard-modal').hide('modal');
        $("#medcard-teeth-history").show("modal");
    });

    function getHistoryTable(data) {
        let history = '<table class="table table-bordered">' +
            '    <tr>' +
            '        <th>Время</th>' +
            '        <th>Сотрудник</th>' +
            '        <th>Диагноз</th>' +
            '    </tr>';

        if (!$.isArray(data) || !data.length) {
            history +=
                '    <tr>' +
                '        <td colspan="3" align="center">Нет изменении</td>' +
                '    </tr>';
        } else {
            data.forEach(info => {
                let datetime = moment(info.datetime).format('DD MMMM YYYY HH:mm')
                history +=
                    '    <tr>' +
                    '        <td>' + datetime + '</td>' +
                    '        <td>' + info.staff_name + '</td>' +
                    '        <td>' + info.diagnosis_name + '</td>' +
                    '    </tr>';
            });
        }

        history += '</table>';
        return history;
    }

    function showMedCardTab() {
        $('#medcard-modal').hide('modal');
        $("#medcard-tab-modal").show("modal");
    }

    function hideFormTeeth(teeth) {
        teeth.forEach(toothNumber => {
            let teethForm = $('.teeth-form');
            let toothWrapper = teethForm.find('.order-tooth-wrapper-' + toothNumber);
            toothWrapper.find('.tooth-number').hide();
            toothWrapper.find('.order-tooth-img').hide();
            toothWrapper.find(':input[name^="MedCard[teeth][' + toothNumber + ']"]').prop('disabled', true);
        });
    }

    function showFormTeeth() {
        $('.teeth-form .order-tooth-wrapper .order-tooth-img').show();
        $('.teeth-form .order-tooth-wrapper .tooth-number').show();
        $('.teeth-form .order-tooth-wrapper :input').prop('disabled', false);
    }

    $('.js-save-medcard-tab').click(function(e) {
        let tab_id = $('.js-save-medcard-tab').data('tab-id');
        if (tab_id) {
            editMedCardTab(tab_id);
        } else {
            createMedCardTab();
        }
    });

    $('.js-close-teeth-history').on('click', function(){
        $('#medcard-teeth-history').hide('modal');
        $('#medcard-teeth-history').find('.modal-body').html('');
        $('#medcard-modal').show('modal');
    });

    $('.js-close-medcard-tab').click(closeMedCardTab);

    function createMedCardTab() {
        $.post({
            url: '/med-card/default/create?order_id=' + currentVisit.id,
            data: $('#medcard-tab-form').serialize(),
            dataType: 'json'
        })
            .done(function(response) {
                if (response.errors && response.errors.teeth) {
                    $.jGrowl(response.errors.teeth, { group: 'flash_alert'});
                } else {
                    $.jGrowl("Успешно сохранено", { group: 'flash_notice'});

                    // if new medcard assign medcard to current visit(order) and initialize tabs
                    if (!currentVisit.medCard) {
                        currentVisit.medCard = response.medCard;
                        currentVisit.medCard.tabs = [];
                        $(".js-open-med-exam[data-visit-id=" + currentVisit.id + "]").text("Открыть дневник");
                    }

                    currentVisit.medCard.tabs.push(response.tab);

                    // render tab
                    $('.medcard-tabs > tbody').append(renderTab(response.tab, false));
                    // add click edit button handler
                    addMedCardTabListeners('.medcard-tabs > tbody > tr:last');

                    // refresh saved teeth
                    savedTeeth = savedTeeth.concat(response.tab.teeth.map(tooth => tooth.teeth_num));

                    // close medcard modal window
                    closeMedCardTab();
                }
            }).fail(function() {
            $.jGrowl("Произошла ошибка при сохранении", { group: 'flash_alert'});
        });
    }

    function editMedCardTab(id) {
        $.post({
            url: '/med-card/default/update?tab_id=' + id,
            data: $('#medcard-tab-form').serialize(),
            dataType: 'json'
        })
            .done(function(response) {
                if (response.errors && response.errors.teeth) {
                    $.jGrowl(response.errors.teeth, { group: 'flash_alert'});
                } else {
                    $.jGrowl("Успешно сохранено", { group: 'flash_notice'});

                    let tab = currentVisit.medCard.tabs.find(tab => tab.id == id);

                    // refresh save teeth, first delete old tab teeth, then add new
                    let teeth = tab.teeth.map(tooth => tooth.teeth_num);
                    savedTeeth = savedTeeth.filter(toothNumber => !(teeth.indexOf(toothNumber) > -1));
                    savedTeeth = savedTeeth.concat(response.tab.teeth.map(tooth => tooth.teeth_num));

                    // refresh tab comments
                    tab.diagnosis_id = response.tab.diagnosis_id;
                    tab.diagnosis = response.tab.diagnosis;
                    tab.comments = response.tab.comments;
                    tab.teeth = response.tab.teeth;
                    tab.childTeeth = response.tab.childTeeth;
                    tab.services = response.tab.services;

                    // refresh tab title
                    $('.medcard-tabs > tbody tr[data-tab-id=' + tab.id + ']')
                        .find('td:first')
                        .text(tab.teeth.map(tooth => tooth.teeth_num).join(','));

                    closeMedCardTab();
                }
            }).fail(function() {
            $.jGrowl("Произошла ошибка при сохранении", { group: 'flash_alert'});
        });
    }

    function deleteMedCardTab(id) {
        $.post({
            url: '/med-card/default/delete?tab_id=' + id,
            dataType: 'json'
        })
            .done(function(response) {
                // refresh tabs
                let deletedTab = currentVisit.medCard.tabs.find(tab => tab.id == id);
                let tabs = currentVisit.medCard.tabs.filter(tab => tab.id != id);
                currentVisit.medCard.tabs = tabs;

                // refresh saved teeth
                let teeth = deletedTab.teeth.map(tooth => tooth.teeth_num);
                savedTeeth = savedTeeth.filter(toothNumber => !(teeth.indexOf(toothNumber) > -1));

                // refresh teeth card
                teeth.forEach(toothNumber => {
                    $('.order-tooth-img-' + toothNumber).html('<img src="/image/teeth.png" width="35" height="35"/>').css('background-color', '').data('company_customer_id', '');
                });

                // remove tab row
                $('.medcard-tabs > tbody tr[data-tab-id=' + id + ']').remove();
                $.jGrowl("Успешно удалено", { group: 'flash_notice'});
            }).fail(function() {
            $.jGrowl("Произошла ошибка при удалении", { group: 'flash_alert'});
        });
    }

    function setDiagnosis(tab) {
        let diagnosesList = $('#js-diagnoses-list');
        if (tab.diagnosis_id) {
            // Set the value, creating a new option if necessary
            if (!diagnosesList.find("option[value='" + tab.diagnosis_id + "']").length) {
                let text = "(" + tab.diagnosis.code + ") " + tab.diagnosis.name;
                let newOption = new Option(text, tab.diagnosis_id, true, true);
                diagnosesList.append(newOption);
            }
            diagnosesList.val(tab.diagnosis_id).trigger('change');
        } else {
            diagnosesList.val("").trigger('change.select2');
        }
    }

    function addMedCardTabListeners(selector) {
        $(selector).find(".js-edit-medcard-tab").click(function(e) {
            e.preventDefault();
            let tab_id = $(this).data('tab-id');

            $('.js-save-medcard-tab').data('tab-id', tab_id);

            if (currentVisit.medCard) {
                let tab = currentVisit.medCard.tabs.find(item => item.id == tab_id);
                let teethNumbers = tab.teeth.map(tooth => tooth.teeth_num);
                // set modal title
                $('#medcard-tab-modal').find('.modal-title').text('Область');

                setDiagnosis(tab);

                loadTeeth(tab.teeth);
                // load comments
                loadComments(tab.comments);

                if (tab.childTeeth.length > 0) {
                    enableChildTeeth('teeth-form');
                }

                renderMedCardServices(tab.services);

                // hide saved teeth except teeth in current tab
                hideFormTeeth(savedTeeth.filter(toothNumber => !(teethNumbers.indexOf(toothNumber) > -1) ));
            }

            showMedCardTab();
        });

        $(selector).find(".js-delete-medcard-tab").click(function(e) {
            let tab_id = $(this).data('tab-id');
            confirmMessage("Вы уверены, что хотите удалить данные?", function(result) {
                if (result) {
                    deleteMedCardTab(tab_id);
                }
            });
        });
    }

    function closeMedCardTab(e) {
        $("#medcard-tab-modal").hide("modal");
        $('#medcard-modal').show('modal');
        showFormTeeth();
        unselectFormTeeth();
        $('.comments-form .panel-collapse.collapse').collapse('hide');
        $('.order_comment_item').val('');
        $('#js-diagnoses-list').val("").trigger('change.select2');

        $('#med_card_services_table').html('');
        $('#med_card_services_tabbed_table tbody').html('');
        $('#med_card_services_tabbed_table').hide();
        $('#js-med-card-add-service').hide();
        $('.field-medcard-services .select2').show();
    }

    // Teeth
    function selectTeeth (data) {
        $.each(data, function (key, tooth) {
            setToothColor(tooth.diagnosis_id, tooth.teeth_num);
            let abbreviation = getAbbreviation(tooth.diagnosis_id);
            if (abbreviation) {
                setToothTitle(tooth.teeth_num, '<img src="/image/teeth.png" width="35" height="35"/><span class="tooth-number tooth-abbreviation">' + abbreviation + '</span>');
            }
            setToothTab(tooth.teeth_num);
        });
    }

    function unselectTeeth () {
        $('.order-tooth-img').html('<img src="/image/teeth.png" width="35" height="35"/>').css('background-color', '');
        disableChildTeeth('teeth-view');
        savedTeeth = [];
    }

    function unselectFormTeeth () {
        $('.teeth-form .order-tooth-img').html('<img src="/image/teeth.png" width="35" height="35"/>').css('background-color', '');
        disableChildTeeth('teeth-form');
        $('#medcard-tab-form')[0].reset();
        $('.order-mobility').hide();
        selectedTeeth = [];
    }

    function enableChildTeeth(container) {
        let button = $('.' + container + ' .order-tooth-controls a');
        button.text(button.data('title-off'));
        button.data('enabled', 1);
        $('.' + container + " .child-tooth-row").show();
    }

    function disableChildTeeth(container) {
        let button = $('.' + container + ' .order-tooth-controls a');
        button.text(button.data('title-on'));
        button.data('enabled', 0);
        $('.' + container + " .child-tooth-row").hide();
    }

    // set tooth color depending of diagnosis
    function setToothColor(diagnosis_id, toothNumber) {
        if (diagnosis_id) {
            $('.order-tooth-img-' + toothNumber).css('background-color', colors[diagnosis_id]).data('company_customer_id', currentVisit.company_customer_id);
        } else {
            $('.order-tooth-img-' + toothNumber).css('background-color', '').data('company_customer_id', currentVisit.company_customer_id);
        }
    }

    function getAbbreviation(diagnosis_id) {
        if (abbreviations.hasOwnProperty(diagnosis_id)) {
            return  abbreviations[diagnosis_id];
        } else if (diagnosis_id == 1) {
            return 'X';
        }
        return null;
    }

    function setToothTab(toothNumber) {
        $('.order-tooth-img-' + toothNumber).data('company_customer_id', currentVisit.company_customer_id);
    }

    function setToothTitle(toothNumber, title) {
        $('.order-tooth-img-' + toothNumber).html(title);
    }

    $('.js-select-tooth-diagnosis').change(function(e) {
        let toothNumber = parseInt(e.target.dataset.tooth);
        let abbreviation = getAbbreviation(e.target.value);

        // set abbrevations
        if (abbreviation) {
            setToothTitle(toothNumber, '<img src="/image/teeth.png" width="35" height="35"/><span class="tooth-number tooth-abbreviation">' + abbreviation + '</span>');
        } else {
            setToothTitle(toothNumber, '<img src="/image/teeth.png" width="35" height="35"/>');
        }

        // show or hide mobility input
        if (e.target.value == DIAGNOSIS_MOBILITY) {
            $('.tooth-mobility[name="MedCard[teeth][' + toothNumber + '][mobility]"]').show();
        } else {
            $('.tooth-mobility[name="MedCard[teeth][' + toothNumber + '][mobility]"]').hide();
            $(this).closest('.popover').popoverX('hide');
        }

        // set background image
        setToothColor(e.target.value, toothNumber);

        // set teeth list in comments

        let search = selectedTeeth.join(', ') + ': ';
        if (isNaN(parseInt(e.target.value))) {
            selectedTeeth = selectedTeeth.filter(number => number != toothNumber);
        } else {
            if (selectedTeeth.indexOf(toothNumber) == -1) {
                selectedTeeth.push(toothNumber);
            }
        }
        let replaceStr = selectedTeeth.join(', ') + ': ';

        $.each($('.order_comment_item'), function(ind, el) {
            let text = el.value;
            if (text.search(search) > -1) {
                text = text.replace(search, replaceStr);
            } else {
                text = replaceStr + text;
            }
            $(el).val(text);
        });

    });

    $('.tooth-mobility').change(function(e) {
        let toothNumber = e.target.dataset.tooth;
        if (!isNaN(e.target.value)) {
            setToothTitle(toothNumber, '<img src="/image/teeth.png" width="35" height="35"/><span class="tooth-number">' + e.target.value + '</span>');
            $(this).closest('.popover').popoverX('hide');
        }
    });

    $('.order-tooth-controls a').click(function (e) {
        e.preventDefault();
        let enabled = $(this).data('enabled');
        let container = $(this).data('container');
        if (!parseInt(enabled)) {
            enableChildTeeth(container);
        } else {
            disableChildTeeth(container);
        }
    });

    function loadTeeth(teeth) {
        teeth.forEach((tooth, index) => {
            $('.teeth-form .order-tooth-wrapper-' + tooth.teeth_num + ' select').val(tooth.diagnosis_id).change();
        });
    }

    function loadComments(comments) {
        comments.forEach((comment, index) => {
            if (comment.comment) {
                $('#comment-collapse-' + comment.category_id).collapse('show');
            } else {
                $('#comment-collapse-' + comment.category_id).collapse('hide');
            }
            $('#js-order_comment_' + comment.category_id).val(comment.comment);
        });
    }
});
