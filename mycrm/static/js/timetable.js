$(() => {
  const addEventUrl = '/timetable/add-event?expand=documents,files,products,services,payments,staff,contactCustomers,title,customer';
  const updateEventUrl = '/timetable/update-event';
  const updateEventDurationUrl = '/timetable/update-event-duration';
  const updateEventDropUrl = '/timetable/update-event-drop';
  const deleteEventUrl = '/timetable/delete-event';
  const listEventsUrl = '/timetable/events?expand=documents,files,products,services,payments,staff,contactCustomers,title,customer';
  const listResourcesUrl = '/timetable/active-staff';
  const eventHistoryUrl = '/timetable/history';
  const eventCheckoutUrl = '/timetable/checkout-event';
  const eventReturnUrl = '/timetable/return-event';
  const searchUrl = '/timetable/search';
  const generateDocUrl = '/order/document/generate';
  const workingPeriod = '/timetable/working-period';
  const customerInfoUrl = '/customer/customer/info?expand=categories,debt,deposit,revenue,canceledOrders,finishedOrders,lastVisit,categories_title,source_id_title';
  const customerEditUrl = '/customer/customer/edit?expand=categories,debt,deposit,revenue,canceledOrders,finishedOrders,lastVisit,categories_title,source_id_title';
  const customerVisitsUrl = '/customer/customer/visits';

  const calendarEl = $('#calendar');
  const timetableCalendar = calendarEl.find('#timetable-calendar');

  if (timetableCalendar.length) {
    const STATUS_ENABLED = 1;
    const STATUS_FINISHED = 3;
    const STATUS_CANCELED = 4;
    const STATUS_WAITING = 5;
    let selectedUserEl = calendarEl.find('#timetable-selected-users');
    let selectedUserID = selectedUserEl.data('selected-user');
    const selectedUsersArr = selectedUserEl.data('selected-users');
    const duration_min = timetableCalendar.data('duration-min');
    const duration_max = timetableCalendar.data('duration-max');

    // initialize ui-datepicker-next
    calendarEl.find('#datepicker').datepicker({
      dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
      monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
      firstDay: 1,
      onSelect(dateText, inst) {
        var start = moment(dateText, 'DD.MM.YYYY');
        $.getJSON(workingPeriod, {
          start: start.format('YYYY-MM-DD'),
          end: start.format('YYYY-MM-DD'),
        }).done((data) => {
          timetableCalendar.fullCalendar('option', 'minTime', data['min']);
          timetableCalendar.fullCalendar('option', 'maxTime', data['max']);
          timetableCalendar.fullCalendar('gotoDate', start);
        });
      },
    });

    // remove title attribute for datepicker buttons because of incorrect tooltip rendering
    $('.ui-datepicker-next.ui-corner-all').removeAttr('title');
    $('.ui-datepicker-prev.ui-corner-all').removeAttr('title');

    // initialize fullcalendar
    const format = 'HH:mm:ss DD.MM.YYYY';
    const displayHeight = $(window).height();
    let curEvent = false;
    let resourceRefreshInterval;
    const slotInterval = localStorage.getItem('slotInterval') || '00:05:00';
    const currentTime = moment().subtract(0.5, 'hour').format('HH:mm:00');

    const fcOpts = {
      locale: 'ru',
      defaultView: 'agendaDay',
      timezone: 'Asia/Almaty',
      allDayText: 'весь день',
      allDaySlot: false,
      slotDuration: slotInterval,
      slotLabelInterval: '01:00:00',
      weekNumbers: true,
      displayEventTime: true,
      displayEventEnd: true,
      displayAnnotation: true,
      selectHelper: true,
      scrollTime: currentTime,
      minTime: duration_min,
      maxTime: duration_max,
      contentHeight: (displayHeight - 110),
      aspectRatio: 0.001,
      axisFormat: 'HH:mm',
      slotLabelFormat: 'HH:mm',
      selectable: true,
      editable: true,
      droppable: true, // this allows things to be dropped onto the calendar !!!
      dropAccept: '.draggable-order',
      eventDurationEditable: true,
      eventOverlap: true,
      events: {
        url: listEventsUrl,
        data() {
          const viewName = timetableCalendar.fullCalendar('getView').name;
          const data = {
            viewName,
            _csrf: $('meta[name=csrf-token]').attr('content'),
            division_id: $('#timetable-division_id').val(),
            position_id: $('#timetable-position_id').val(),
          };

          const selected = [];
          $.each($('input.checkboxuser:checked'), function () {
            selected.push($(this).data('entity-id'));
          });

          if (viewName != 'agendaDay') {
            data.staffs = selected;
          }

          return data;
        },
        type: 'POST',
        error() {
          clearInterval(resourceRefreshInterval);
          alertMessage('Проблемы с обновлением записей. Возможно, у вас проблемы с интернетом');
        },
        success(events) {
          let maxTime = timetableCalendar.fullCalendar('option', 'maxTime');
          if (typeof events != 'undefined' && events.length > 0 && events[0].id) {
            maxTime = events[0].maxTime;
            extendCalendar(maxTime, true);
          }
        },
      },
      refetchResourcesOnNavigate: true,
      resources(callback, start, end, timezone) {
        const viewName = timetableCalendar.fullCalendar('getView').name;
        const date = timetableCalendar.fullCalendar('getDate');
        $.getJSON(listResourcesUrl, {
          date: date.format('DD.MM.YYYY'),
          division_id: $('#timetable-division_id').val(),
          position_id: $('#timetable-position_id').val(),
        }).done((data) => {
          if (viewName == 'agendaDay') {
            setTimetableStaff(data, false);
          }
          callback(data);
        });
      },
      resourceOrder: 'staff_id',
      resourceRender(resourceObj, labelTds, bodyTds) {
        let title = 'Нет расписания';
        const position = $(`.checkboxuser[data-entity-id=${resourceObj.id}]`).data('entity-position');
        if (resourceObj.schedule) {
          const start = moment(resourceObj.schedule.start_at).format('HH:mm');
          let end = moment(resourceObj.schedule.end_at).format('HH:mm');
          if (end == '00:00') {
            end = '24:00';
          }
          title = `${start} - ${end}`;
        }
        return labelTds.append(`<div data-annotations-col="0" class="fc-annotation">${title}<br>${position}</div>`);
      },
      select(start, end, jsEvent, view, resource) {
        // open modal window with  form
        renderOrderButtons();
        $('#payments').data('balance', 0);
        $('#order-company_customer_id').val('');

        const color = resource ? resource.eventClassName : $('input.checkboxuser:checked').data('entity-color');
        $('#order-color').val(color).change();

        normalizeOrderTabs(false);
        setModalTitle('Запись на', start.format('DD MMMM YYYY HH:mm'));
        renderPayments(resource);
        showOrderModal();

        const staff_id = resource ? resource.id : $('input.checkboxuser:checked').data('entity-id');
        const division_id = resource ? resource.division_id : $('input.checkboxuser:checked').data('entity-division_id');

        $('#order-hours_before').val(division_notification[division_id]);

        $('#event-staff_id').val(staff_id);
        $('#order-division_id').val(division_id).change();
        // set date in form
        $('#order-datetime').val(start.format('YYYY-MM-DD HH:mm'));
        $('#order-datetimepicker').val(capitalize(start.format('DD ') + start.format('MMMM ') + start.format('YYYY HH:mm')));

        curEvent = false;
        timetableCalendar.fullCalendar('unselect');
      },
      eventClick(event, jsEvent) {
        renderOrderButtons(event);
        fetchOrder(event);
        normalizeOrderTabs(true);
        setModalTitle('Запись на ', capitalize(event.start.format('DD MMMM YYYY HH:mm')));
        loadDocsList(event.division_id);
        loadMedCards(event.company_customer_id);
        showOrderModal();

        curEvent = event;
      },
      eventDrop(event, delta, revertFunc, jsEvent, ui, view) {
        if (event.status == STATUS_FINISHED) {
          revertFunc();
        }
        dropCalendarEvent(event, revertFunc);
      },
      eventResize(event, delta, revertFunc) {
        if (event.status == STATUS_FINISHED) {
          revertFunc();
        }
        changeEventDuration(event, revertFunc);
      },
      eventRender(event, element) {
        // var color = $("input[type='radio']:checked").attr('data-entity-color');
        if (event.id) {
          element.addClass(event.color);
          let tip = "<div class='js-show-event-tip fc-event-tip'><i class='fa fa-info'></i></div>";
          if (event.customer && event.customer.balance < 0) {
            tip += "<div class='js-show-customer-debt-tip fc-event-tip fc-event-tip_red'><i class='fa fa-dollar-sign'></i></div>";
          }
          if (event.customer && event.customer.finishedOrders == 0) {
            tip += "<div class='js-show-customer-new-tip fc-event-tip fc-event-tip_green'>Н</div>";
          }
          element.find('.fc-time').append(tip);
        }
      },
      eventAfterRender(event, element, view) {
        if (event.id) {
          let title = '';
          $.each(event.services, (key, service) => {
            title += `${service.service_name}(${service.duration}мин, ${service.price}〒)<br>`;
          });
          let description = '';
          if (event.end != null) {
            description = `<div class='event-description-item'><b>${event.start.format('HH:mm')} - ${event.end.format('HH:mm')}</b></div>
                            <div class='event-description-item'>${event.customer.name}</div>
                            <div class='event-description-item'>${event.customer.phone}</div>
                            <div class='event-description-item'>${title}</div>
                            <div class='event-description-item'>${event.note}</div>
                        `;
          }
          const position = {
            my: 'left center',
            at: 'right center',
          };
          const resources = timetableCalendar.fullCalendar('getResources');
          const maxStaffId = Math.max(...resources.map(o => o.staff_id));
          if ((view.name != 'agendaDay' && event.start.isoWeekday() == 7) ||
              (view.name == 'agendaDay' && maxStaffId == event.staff_id)
          ) {
            position.my = 'right center';
            position.at = 'left center';
          }
          element.find('.js-show-event-tip').qtip({
            overwrite: true,
            content: description,
            style: { classes: 'qtip-light qtip-shadow qtip-calendar' },
            position,
          });
          element.find('.js-show-customer-new-tip').qtip({
            overwrite: true,
            content: 'Новый клиент',
            style: { classes: 'qtip-light qtip-shadow qtip-calendar' },
            position,
          });
          element.find('.js-show-customer-debt-tip').qtip({
            overwrite: true,
            content: 'У клиента есть непогашенный долг',
            style: { classes: 'qtip-light qtip-shadow qtip-calendar' },
            position,
          });
        }
      },
      defaultTimedEventDuration: '00:30:00',
      eventReceive(event) {
        event.documents = [];
        event.files = [];
      },
      drop(date, jsEvent, ui, resourceId) {
        const id = $(this).data('id');
        if (id) {
          $.post({
            url: `/timetable/enable-pending?id=${id}`,
            dataType: 'json',
          }, {
            start: date.format('YYYY-MM-DD HH:mm:ss'),
          })
            .done(response => $(this).remove())
            .fail((error) => {
              $.jGrowl('Произошла ошибка при добавлении записи в график.', { group: 'flash_alert' });
              timetableCalendar.fullCalendar('removeEvents', id);
            });
        }
      },
      customButtons: {
        print: {
          text: 'печать',
          click() {
            window.print();
          },
        },
      },
      header: {
        left: 'prev,next today print',
        center: 'title',
        right: 'month,agendaWeek,agendaDay',
      },
      buttonText: {
        today: 'сегодня',
        month: 'месяц',
        week: 'неделя',
        day: 'день',
      },
      weekNumberTitle: 'нед',
      views: {
        month: {
          columnFormat: 'ddd',
        },
        agenda: {
          columnFormat: 'ddd M/D',
        },
        week: {
          titleFormat: 'DD MMM YYYY',
        },
        day: {
          titleFormat: 'dddd, DD MMM YYYY',
        },
      },
      viewRender(view) {
        if (view.name != 'agendaDay') {
          $('.entity_list_item').removeClass('active');
          changeInputType('checkboxuser', 'radio');
          setRadioListener();
        } else {
          $('.entity_list_item').removeClass('active');
          changeInputType('checkboxuser', 'checkbox');
          setCheckboxListener();
        }
        add_hours_to_hovered_slots(view);
        setTimeline(view);
      },
      lazyFetching: false,
    };
    timetableCalendar.fullCalendar(fcOpts);

    setInterval(setTimeline, 30 * 1000);
    initColorPicker('select[name="Order[color]"]');
    // refreshResourcesByTimeout();

    function renderOrderButtons(event) {
      $('#formModal .modal-footer button').attr('disabled', false);
      $('#formModal .modal-footer button').not('.save-order-button').hide();

      if (!event) {
        $('.save-order-button').text('Создать запись');
      } else {
        $('.save-order-button').text('Сохранить');

        if (event.status == STATUS_FINISHED) {
          $('.save-order-button').attr('disabled', true);
          $('.return-order-button').show();
        } else if (event.status == STATUS_CANCELED) {
          $('.save-order-button').attr('disabled', true);
          $('.delete-order-button').show();
          $('.return-order-button').show();
        } else {
          $('.checkout-order-button').show();
          $('.disable-order-button').show();
        }

        // check if current division can print order
        const canPrintOrder = $('#timetable-division_id :selected').data('can-print');
        if (canPrintOrder) {
          $('.print-order-button').show();
        }
      }
    }

    $('#timetable-division_id').change(function () {
      const division_id = $(this).val();

      $(`.checkboxuser[data-entity-division_id!=${division_id}]`)
        .prop('checked', false)
        .prop('disabled', true)
        .closest('.entity_list_item')
        .removeClass('active');

      let selector = `.checkboxuser[data-entity-division_id=${division_id}]`;
      if (timetableCalendar.fullCalendar('getView').name != 'agendaDay') {
        selector = `.checkboxuser[data-entity-division_id=${division_id}]:first`;
      }

      $(selector).prop('checked', true)
        .prop('disabled', false)
        .closest('.entity_list_item')
        .addClass('active');

      timetableCalendar.fullCalendar('refetchResources');
      timetableCalendar.fullCalendar('refetchEvents');
    });

    $('#timetable-position_id').change(function () {
        $('#timetable-division_id').change();
    });

      setTimetableInterval(slotInterval);
    setIntervalListener();

      function setTimetableInterval(interval) {
      $('#timetable-interval').val(interval);
    }

    function setIntervalListener() {
      $('#timetable-interval').change(function (e) {
        const slotInterval = $(this).val();
        localStorage.setItem('slotInterval', slotInterval);
        $('#timetable-calendar').fullCalendar('destroy');
        $('#timetable-calendar').fullCalendar(
          $.extend(fcOpts, {
            slotDuration: slotInterval,
          }),
        );
      });
    }


    // initialize event handler for cancel button with confirmation to proceed
    $('.close-order-button').on('click', () => {
      let hasChanges = false;
      if (curEvent && (curEvent.status != STATUS_FINISHED)) {
        const form = $('#order-form');
        const inputs = form.serializeArray();
        hasChanges = checkChanges(inputs, curEvent);
      } else if (!curEvent) {
        hasChanges = ($('#order-customer_name').val() || $('#order-customer_phone').val());
      }

      // open modal with confirmation
      if (hasChanges) {
        bootbox.dialog({
          message: 'При закрытии данного окна все несохраненные данные будут утеряны. Продолжить?',
          title: 'Вы уверены?',
          buttons: {
            success: {
              label: 'Подтвердить',
              className: 'btn-primary pull-left',
              callback: () => hideAndResetForm(),
            },
            danger: {
              label: 'Отменить',
              className: 'btn-default pull-right',
            },
          },
        });
      } else {
        // close modal window and reset form
        hideAndResetForm();
      }
    });

    $('.delete-order-button').on('click', (e) => {
      bootbox.dialog({
        message: 'При изменении статуса запись исчезнет из таблицы.' +
                "<br/>Но вы сможете просмотреть во вкладке 'Обзор'." +
                '<br/>Продолжить?',
        title: 'Вы уверены?',
        buttons: {
          success: {
            label: 'Подтвердить',
            className: 'btn-danger pull-left',
            callback() {
              const id = $('#order-id').val();
              $.post(`${deleteEventUrl}?id=${id}`, { _csrf: $('meta[name=csrf-token]').attr('content') })
                .done((response) => {
                  timetableCalendar.fullCalendar('removeEvents', id);
                  hideAndResetForm();
                }).fail((error) => {
                  alertMessage('Что-то пошло не так. Проверьте подключение и попробуйте еще раз');
                });
            },
          },
          danger: {
            label: 'Отменить',
            className: 'btn-default pull-right',
            callback() {
            },
          },
        },
      });
    });

    $('.cancel-order-button').on('click', (e) => {
      const id = $('#order-id').val();
      const event = timetableCalendar.fullCalendar('clientEvents', id)[0];
      $.post(`${'/timetable/cancel-event' + '?id='}${id}`, { _csrf: $('meta[name=csrf-token]').attr('content') })
        .done((response) => {
          event.status = STATUS_CANCELED;
          event.className = 'canceled_event';
          timetableCalendar.fullCalendar('updateEvent', event); // stick? = true
          $.jGrowl('Запись отменена успешно.', { group: 'flash_notice' });
          hideAndResetForm();
        }).fail((error) => {
          alertMessage('Что-то пошло не так. Проверьте подключение и попробуйте еще раз');
        });
    });

    $('.print-order-button').on('click', (e) => {
      const id = $('#order-id').val();
      const event = timetableCalendar.fullCalendar('clientEvents', id)[0];
      const printedContent = $('#order-print').clone();
        const divisionOption = $(`#timetable-division_id option[value=${event.division_id}]`);
      const staffName = $(`.checkboxuser[data-entity-id=${event.staff_id}]`).data('entity-name');

      printedContent.find('.division-name').text(divisionOption.text());
      printedContent.find('.division-address').text(divisionOption.data('address'));
        if (divisionOption.data('logo')) {
            printedContent.find('.division-info > .logo > img').attr('src', divisionOption.data('logo'));
            printedContent.find('.division-info > .logo').show();
        } else {
            printedContent.find('.division-info > .logo').hide();
        }
      printedContent.find('.staff-name').text(staffName);

      printedContent.find('.order-key').text(event.number);
      printedContent.find('.order-datetime').text(event.start.format('DD.MM.YYYY HH:mm'));
      printedContent.find('.date-created').text(moment().format('DD.MM.YYYY HH:mm'));

      let fullname = [event.customer.name, event.customer.lastname, event.customer.patronymic].join(' ');
      printedContent.find('.customer-fullname').text(fullname);

      const services = $('#servicesTable').find('tr');
      $.each(services, (key, serviceRow) => {
        const row = $('<tr></tr>');
        const discount = $(serviceRow).find('input[data-attr=discount]').val();
        const price = $(serviceRow).find('input[data-attr=price]').val();
        const quantity = $(serviceRow).find('input[data-attr=quantity]').val();

        row.append(`<td>${$(serviceRow).find('.order-service-column_name').text()}</td>`);
        row.append(`<td>${price}тг` + '</td>');
        row.append(`<td>${quantity}</td>`);
        row.append(`<td>${discount}%` + '</td>');
        row.append(`<td>${Number(price * (100 - discount) / 100)}тг` + '</td>');

        printedContent.find('table.services tbody').append(row);
      });

      printedContent.find('.order-price').text(`${$('#order-price').val()}тг`);
      printedContent.find('.order-paid').text(`${$('#order-paid').val()}тг`);
      printedContent.find('.order-debt').text(`${$('.payment-debt').text()}тг`);

      printElem(printedContent);
    });

    function submitForm(e) {
      const form = $('#order-form');
      const data = form.data('yiiActiveForm');
      $.each(data.attributes, function () {
        this.status = 2;
      });
      form.yiiActiveForm('validate');

      if (form.find('.has-error').length == 0) {
        saveOrder(form);
      }
      return false;
    }

    $('.return-order-button').on('click', (e) => {
      e.stopImmediatePropagation();
      $('#formModal .modal-footer button').attr('disabled', true);
      returnOrder($('#order-id').val());
    });

    $('.checkout-order-button').on('click', (e) => {
      e.stopImmediatePropagation();
      $('#formModal .modal-footer button').attr('disabled', true);
      checkoutOrder($('#order-id').val());
    });

    $('.save-order-button').on('click', e => submitForm(e));

    function checkoutOrder(event_id) {
      $.ajax({
        url: `${eventCheckoutUrl}?id=${event_id}`,
        type: 'post',
        data: $('#order-form').serialize(),
        success(response) {
            if (!response.errors) {
                hideAndResetForm();
                timetableCalendar.fullCalendar('refetchEvents');
            } else if (response.errors && response.errors.ignore_stock) {
                let message = response.errors.ignore_stock;
                let buttons = {
                    success: {
                        label: "Изменить",
                        className: "btn-primary pull-right",
                        callback: function () {
                            $('#formModal .modal-footer button').attr('disabled', false);
                        }
                    },
                    danger: {
                        label: "Все равно сохранить",
                        className: "btn-default pull-right",
                        callback: function () {
                            $('#order-ignore_stock').val(1);
                            checkoutOrder(event_id);
                        }
                    }
                };
                showBootbox(message, buttons);
            } else {
                showErrors(response.errors);
            }
        },
        dataType: 'json',
      });
    }

    function returnOrder(event_id) {
      $.ajax({
        url: `${eventReturnUrl}?id=${event_id}`,
        type: 'get',
        success(response) {
          hideAndResetForm();
          const event = timetableCalendar.fullCalendar('clientEvents', event_id)[0];
          event.status = STATUS_ENABLED;
          event.editable = true;
          event.className = event.color || $(`input[data-entity-id=${event.staff_id}]`).attr('data-entity-color');
          timetableCalendar.fullCalendar('updateEvent', event); // stick? = true
        },
        error() {
          alertMessage('Произошла ошибка при возврате записи');
          $('#formModal .modal-footer button').attr('disabled', false);
        },
        dataType: 'json',
      });
    }

    // set listener for chekbox input change - add highlighting, resource to calendar and refetch events
    function setCheckboxListener() {
      $(':checkbox').change(function () {
        const element = $(this);
        const userID = parseInt(element.data('entity-id'));
        if (userID) {
          if (this.checked) {
            // add highlithing and resource to full calendar
            element.closest('.entity_list_item').addClass('active');
            addTimetableStaff(
              userID,
              element.data('entity-division_id'),
              element.data('entity-name'),
              element.data('entity-color'),
              true,
            );
          } else {
            if (selectedUsersArr.length == 1) {
              this.checked = true;
              alertMessage('Минимум один сотрудник должен быть выбран.');
              return false;
            }
            // remove element with userID from selected users
            element.closest('.entity_list_item').removeClass('active');
            removeTimetableStaff(userID, true);
          }
          timetableCalendar.fullCalendar('refetchEvents');
        }
      });
    }

    function addTimetableStaff(staffID, divisionID, name, color, renderResource) {
      selectedUsersArr.push(staffID);
      if (renderResource) {
        timetableCalendar.fullCalendar('addResource', {
          id: staffID,
          staff_id: staffID,
          division_id: divisionID,
          title: name,
          eventClassName: color,
        });
      }
    }

    function removeTimetableStaff(staffID, removeResource) {
      const index = selectedUsersArr.indexOf(staffID);
      selectedUsersArr.splice(index, 1);
      if (removeResource) {
        timetableCalendar.fullCalendar('removeResource', staffID);
      }
    }

    // set listener for radio input change - add highlighting and refetch events
    function setRadioListener() {
      $('input:radio[name="entities[]"]').change(function () {
        // remove highlighting for previous item
        $('.entity_list_item').removeClass('active');
        // add highlighting for current item
        const element = $(this);
        element.closest('.entity_list_item').addClass('active');
        timetableCalendar.fullCalendar('refetchEvents');
        // set selected user ID to global variable selectedUserID
        selectedUserID = element.attr('data-entity-id');
      });
    }

    function fetchOrder(event) {
      $('#event-staff_id').val(event.staff_id);
      $('#order-company_cash_id option').attr('selected', false);
      $.each(event, (attribute, value) => {
        if (attribute == 'start') {
          const date = moment(value);
          $('#order-datetime').val(moment(value).format('YYYY-MM-DD HH:mm'));
          $('#order-datetimepicker').val(
            capitalize(date.format('DD ') + date.format('MMMM ') + date.format('YYYY HH:mm')),
          );
        } else if (attribute == 'division_id') {
            $(`#order-${attribute}`).val(value).change();
        } else if (attribute == 'customer') {
            $(`#order-customer_source_id`).val(value.source_id).change();
            $(`#order-customer_name`).val(value.name);
            $(`#order-customer_phone`).val(value.phone);
        } else if (attribute != 'datetime') {
          $(`#order-${attribute}`).val(value);
        }
      });

      // $('#event-division_service_id').val(event.division_service_id);
      renderServices(event.services);
      renderProducts(event.products);
      renderContacts(event.contactCustomers);
      renderPayments(event);
      renderDocuments(event.documents);
      renderFiles(event.files);
      calcServiceTotal();
      calcProductsTotal();
      calcTotal();
      loadCustomer(event.company_customer_id);
      loadHistory(event.id);
      loadVisits(event.company_customer_id);
      $('#order-referrer_id').trigger('change');
    }

    function changeEventDuration(event, revertFunc) {
      $.ajax({
        url: `${updateEventDurationUrl}?id=${event.id}`,
        type: 'post',
        data: {
          end: event.end.format('YYYY-MM-DD HH:mm:ss'),
        },
        success(response) {
          if (response.error == 0) {
            event.services = response.services;
            extendCalendar(event.end);
            return true;
          }
          revertFunc();
        },
        error() {
          revertFunc();
        },
        dataType: 'json',
      });
    }

    function dropCalendarEvent(event, revertFunc) {
      let staff_id = event.resourceId;
      if (!staff_id) {
        staff_id = event.staff_id;
      }
      $.ajax({
        url: `${updateEventDropUrl}?id=${event.id}`,
        type: 'post',
        data: {
          staff: staff_id,
          start: event.start.format('YYYY-MM-DD HH:mm:ss'),
        },
        success(response) {
          if (response.error == 0) {
            event.staff_id = staff_id;
            event.datetime = event.start.format('YYYY-MM-DD HH:mm:ss');
            extendCalendar(event.end);
            return true;
          }
          const buttons = {
            success: {
              label: 'Продолжить',
              className: 'btn-primary pull-right',
              callback: revertFunc(),
            },
          };
          showBootbox(response.message, buttons);
        },
        error() {
          revertFunc();
        },
        dataType: 'json',
      });
    }

    function saveOrder(form) {
      const update = !!$('#order-id').val();
      const event = timetableCalendar.fullCalendar('clientEvents', $('#order-id').val())[0];
      const url = update ? (`${updateEventUrl}?id=${event.id}&expand=documents,files,products,services,payments,staff,contactCustomers,title,customer`) : addEventUrl;

      $.ajax({
        url,
        type: 'post',
        data: form.serialize(),
        success(response) {
          if (!response.errors) {
            if (update) {
              $.each(response, (ind, attribute) => {
                if (event.hasOwnProperty(ind)) {
                  event[ind] = attribute;
                }
              });
              if (event.status == STATUS_FINISHED) {
                event.className = 'past_event';
              }
              timetableCalendar.fullCalendar('updateEvent', event); // stick? = true
              $.jGrowl('Запись сохранена успешно', { group: 'flash_notice' });
            } else {
              timetableCalendar.fullCalendar('renderEvent', response, false); // stick? = true
              $.jGrowl('Запись создана успешно', { group: 'flash_notice' });
            }
            hideAndResetForm();

            const eventFinish = moment(response.end);
            extendCalendar(eventFinish);
          } else if (response.errors.customer_name) {
            var message = response.errors.customer_name;
            var buttons = {
              success: {
                label: 'Изменить',
                className: 'btn-primary pull-right',
                callback() {
                },
              },
              danger: {
                label: 'Все равно сохранить',
                className: 'btn-default pull-right',
                callback() {
                  $('#order-ignorenamewarning').val(1);
                  saveOrder(form);
                },
              },
            };
            showBootbox(message, buttons);
          } else {
              showErrors(response.errors);
          }
        },
        dataType: 'json',
      });
    }

    function extendCalendar(finish, alreadyInRange) {
      alreadyInRange = alreadyInRange || false;
      const view = timetableCalendar.fullCalendar('getView');
      if (view.name != 'month' && finish) {
        const calendarStartDate = view.start;
        const calendarEndDate = view.end;
        const maxTime = timetableCalendar.fullCalendar('option', 'maxTime');
        const defaultDate = timetableCalendar.fullCalendar('getDate');

        let newMaxTime = finish;
        if (!alreadyInRange) {
          newMaxTime = finish.format('HH:mm');
        }
        if (((finish > calendarStartDate && finish < calendarEndDate) || alreadyInRange) && newMaxTime > maxTime) {
          timetableCalendar.fullCalendar('destroy');
          timetableCalendar.fullCalendar(
            $.extend(fcOpts, {
              maxTime: newMaxTime,
              defaultView: view.name,
              defaultDate,
            }),
          );
        }
      }
    }

    function refreshSelectedUsers(date) {
      $.getJSON(listResourcesUrl, { date }).done((data) => {
        setTimetableStaff(data, true);
        timetableCalendar.fullCalendar('gotoDate', moment(date, 'DD.MM.YYYY'));
      });
    }

    function setTimetableStaff(data, renderResource) {
      // Remove all selected staffs
      $.each($("input[name='entities[]']"), function () {
        $(this).prop('checked', false).removeClass('active');
        removeTimetableStaff($(this).data('entity-id'), renderResource);
      });

      if (data.length == 0) {
        const divisionName = $('#timetable-division_id :selected').text();
        alertMessage(`У филиала "${divisionName}" не настроен график сотрудников. <a href="../schedule/index">Настроить график работы</a>`);
        return;
      }

      // Select staffs
      $.each(data, (index, staff) => {
        const staff_element = $(`.checkboxuser[data-entity-id=${staff.id}]`);
        addTimetableStaff(
          staff.id,
          staff_element.attr('data-entity-division_id'),
          staff_element.attr('data-entity-name'),
          staff_element.attr('data-entity-color'),
          renderResource,
        );
        staff_element.closest('.entity_list_item').addClass('active');
        staff_element.prop('checked', true);
      });
    }

    function hideAndResetForm() {
      $('#formModal').modal('hide');
      $('#order-id').val('');
      // $('#order-division_id').val('').change();
      $('#order-ignorenamewarning').val(0);
      $('#order-form')[0].reset();

      $('#servicesTable tr').remove();
      $('#payments table').remove();
      $('#tabbedTable').hide();
      $('#js-add-service').hide();
      $("#order-division_service_id option").remove();
      $('.field-order-division_service_id .select2').show();
      $('#order-contacts').empty();
      clearDocuments();
      clearFiles();
        $('#products').find('.products-table > table > tbody').empty();
      hideDatetimeControls();
      clearMedCards();
      disableCustomerForm();
    }

    function loadPayments(payments) {
      payments.forEach((index, payment) => { $(`#order_payment_${payment.payment_id}`).val(payment.value); });
    }

    function changeInputType(className, type) {
      const division_id = $('#timetable-division_id').val();
      selectedUserID = $(`.checkboxuser[data-entity-division_id=${division_id}]:first`).data('entity-id');
      $(`.${className}`).each((idx, el) => {
        const element = $(el);
        let checked = false;
        const userID = parseInt(element.attr('data-entity-id'));
        const disabled = element.data('entity-division_id') != division_id;

        switch (type) {
          case 'radio':
            if (userID == selectedUserID) {
              checked = true;
              element.closest('.entity_list_item').addClass('active');
            }
            break;
          case 'checkbox':
            if ($.inArray(userID, selectedUsersArr) >= 0) {
              checked = true;
              element.closest('.entity_list_item').addClass('active');
            }
            break;
        }

        element.replaceWith($('<input>', {
          type,
          class: 'checkboxuser',
          name: element.attr('name'),
          value: element.attr('value'),
          checked,
          disabled,
          'data-entity-id': element.data('entity-id'),
          'data-entity-division_id': element.data('entity-division_id'),
          'data-entity-name': element.data('entity-name'),
          'data-entity-color': element.data('entity-color'),
          'data-entity-position': element.data('entity-position'),
        }));
      });
    }

    function loadHistory(order_id) {
      $.getJSON(eventHistoryUrl, { id: order_id })
        .done((response) => {
          let rows = '';

          response.forEach((log) => {
            rows += `${'<tr>' +
                            '<td>'}${log.created_at}</td>` +
                            `<td>${log.action}</td>` +
                            `<td>${log.datetime}</td>` +
                            `<td>${log.status}</td>` +
                            `<td>${log.user}</td>` +
                            '</tr>';
          });

          $('#history table > tbody').html(rows);
        });
    }

    function loadVisits(customer_id) {
      $('#visits_tab').show();
      $.get(customerVisitsUrl, { id: customer_id }, (data) => {
        $('#visits').html(data);
      });
    }

    function initializeAutocomplete(id, callback) {
      const element = $(`#${id}`);
      if (element.length) {
        $(`#${id}`).autocomplete({
          source: searchUrl,
          minLength: 1,
          select(event, ui) {
            callback(ui);
            return false;
          },
        }).autocomplete('instance')._renderItem = function (ul, item) {
          return $('<li>')
            .append(`<p style='padding: 6px 12px'>${item.name} ${item.lastname
            } ${item.phone} ` /* + item.customer_email */ + '</p>')
            .appendTo(ul);
        };
      }
    }

    function selectCustomer(ui) {
      $('#order-company_customer_id').val(ui.item.id);
      $('#order-customer_name').val(ui.item.name);
      $('#order-customer_phone').val(ui.item.phone);
      $('input[data-attr=discount]').val(ui.item.discount).change();
      orderDiscount = ui.item.discount;
      // $("#order-customer_email").val(ui.item.email);

      loadCustomer(ui.item.id);
      loadVisits(ui.item.id);
    }


    function selectCustomerForPending(ui) {
      $('#pendingorder-customer_name').val(ui.item.name);
      $('#pendingorder-customer_phone').val(ui.item.phone);
      $('#pendingorder-company_customer_id').val(ui.item.id);
    }

    initializeAutocomplete('order-customer_name', selectCustomer);
    initializeAutocomplete('order-customer_phone', selectCustomer);

    initializeAutocomplete('pendingorder-customer_name', selectCustomerForPending);
    initializeAutocomplete('pendingorder-customer_phone', selectCustomerForPending);

    $('#order-customer_name').on('keyup', function () {
      $(this).capitalize();
    });

    $('#order-price').on('change paste keyup', () => {
      calcTotal();
      $('.order_payment_item').change();
    });

    function normalizeOrderTabs(show) {
      if (!$('#info_tab').hasClass('active')) {
        $('#info_tab').addClass('active');
      }

      if (show) {
        $('.tab').removeClass('active').show();
        $('li.divider').show();

        // check if current division can print order
        const canPrintOrder = $('#timetable-division_id :selected').data('can-print');
        if (canPrintOrder) {
          $('#tooth_tab').show();
        } else {
          $('#tooth_tab').hide();
          $('#docs_tab').hide();
        }
      } else {
        $('.tab').removeClass('active').hide();
        $('li.divider').hide();
      }

      if (!$('#info').hasClass('active')) {
        $('.tab-pane').removeClass('active');
        $('#info').addClass('active');
      }
    }

    function checkChanges(inputs, event) {
      let hasChanges = false;
      $.each(inputs, (ind, field) => {
        const fieldName = field.name.replace(/Order|[[\]]/gi, '');

        if (event.hasOwnProperty(fieldName)) {
          let oldValue = event[fieldName] === '' ? null : event[fieldName];
          const curValue = field.value === '' ? null : field.value;
          if (fieldName == 'datetime') {
            oldValue = event.start.format('YYYY-MM-DD HH:mm');
          }

          if (oldValue != curValue) {
              hasChanges = true;
          }
        }
      });

      return hasChanges;
    }

      function showErrors(errors) {
          let message = '';
          $.each(errors, (ind, error) => {
              message += `${error}<br>`;
          });
          let buttons = {
              success: {
                  label: 'Продолжить',
                  className: 'btn-primary pull-right',
                  callback() {
                  },
              },
          };
          showBootbox(message, buttons);
      }

      function toggleCashbackButton(balance) {
          balance > 0 ? $('#js-order-use-cashback-button').show() : $('#js-order-use-cashback-button').hide();
      }

      function hideCashbackPayment(balance) {
          if (balance <= 0) {
              $("#payments").find(".payment_cashback_input").hide();
              $("#payments").find(".payment-cashback").text(balance);
              $("#payments").find(".payment-cashback").show();
          }
      }

    function refreshResourcesByTimeout() {
      resourceRefreshInterval = setTimeout(() => {
        timetableCalendar.fullCalendar('refetchEvents');
        refreshResourcesByTimeout();
      }, 60000); // each 60 seconds
    }

    function capitalize(str) {
      const result = str.replace(/(?:^|\s)\S/g, a => a.toUpperCase());
      return result;
    }

    function showBootbox(message, buttons, title, className) {
      title = title || 'Внимание';
      bootbox.dialog({
        message,
        title,
        buttons,
        className: className || null,
      });
    }

    function setModalTitle(title, datetime) {
      // $('.modal-title').html(title);
      $('.modal-datetime-title').text(`${datetime}\n`);
    }

    $('.change-datetime').click(() => {
      showDatetimeControls();
    });

    function showDatetimeControls() {
      $('.modal-datetime-controls').hide();
      $('.modal-datetime-title').hide();
      $('.modal-datetime').show();
    }

    function hideDatetimeControls() {
      $('.modal-datetime-controls').show();
      $('.modal-datetime-title').show();
      $('.modal-datetime').hide();
    }

    function showOrderModal() {
      $('#formModal').modal('show');
    }

    function showToothTab() {
      $('#tooth_tab').show();
    }


    // ---------- CUSTOMER BEGIN -----------//
    let isCustomerFormActive = false;

    window.loadCustomer = function (customer_id) {
      $('li.divider').show();
      $('#customer_tab').show();
      // $("#tooth_tab").show();

      // set customer_id
      $('input[name=company_customer_id]').val(customer_id);

      // load customer info
      $.get(customerInfoUrl, { id: customer_id }).done((response) => {
        setCustomerInfo(response);
      }).fail(() => {
        alertMessage('Произошла ошибка при загрузке данных клиента');
      });

      $('.js-pay-debt-button').data('company-customer', customer_id);
    };

    function setCustomerInfo(data) {
      $.each(data, (key, value) => {
        let text = '';
        let tag = 'input';
        switch (key) {
          case 'categories':
          case 'source_id':
          case 'gender':
            tag = 'select';
            text = data[`${key}_title`];
            break;
          case 'sms_birthday':
          case 'sms_exclude':
            text = data[`${key}_title`];
            break;
          default:
            text = value;
        }

        if (!$.isNumeric(text) && (text == null || !text.length)) { text = 'Не задано'; }
        $(`.field-${key}`).find('.info-block').text(text);

        if (key == 'birth_date' || key == 'insurance_expire_date') {
          value = moment(value).format('DD/MM/YYYY');
        }

        if (key == 'debt') {
          $('.js-pay-debt-button').data('debt', value);
        }

        if (key == 'sms_exclude' || key == 'sms_birthday') {
          $(`.field-${key}`).find(tag).prop('checked', value);
        } else if (key == 'image') {
          $('.customer-form .avatar img').prop('src', value);
        } else if (key == 'categories') {
          let categories = value ? value.map(function(category) {
            return category.id;
          }) : [];
          $(`.field-${key}`).find(tag).val(categories).change();
        } else {
          $(`.field-${key}`).find(tag).val(value).change();
        }

        if (key == 'balance') {
          $('#payments').data('balance', value);
          toggleBalanceButton(value);
          paymentList.calculateBalance(value);
        }

          if (key == 'cashback_balance') {
              $("#payments").data('cashback', value);
              toggleCashbackButton(value);
              hideCashbackPayment(value);$("#payments").find(".payment-cashback").text(value);
          }

          if (key == 'insurance_company_id' && curEvent == false) {
            $('#order-insurance_company_id').val(value).change();
          }
      });
    }

    $('.js-show-customer-form').click((e) => {
      e.preventDefault();
      if (!isCustomerFormActive) {
        $(e.target).text('Сохранить данные клиента');
        $('.customer-form .info-block').hide();
        $('.customer-form .field-block').show();
        isCustomerFormActive = true;
      } else {
        saveCustomer();
      }
    });

    function saveCustomer() {
      const data = new FormData(document.getElementById('customer-form'));
      $.post({
        url: customerEditUrl,
        data,
        processData: false,
        contentType: false,
      }).done((response) => {
        if (response.status == 'error') {
          $.each(response.errors, (key, error) => {
            $(`.customer-form .field-${key}`).find('.help-block').html(`<span class='text-danger'>${error}</span>`);
          });
        } else if (response.status == 'success') {
          setCustomerInfo(response.data);
          disableCustomerForm();
        }
      }).fail((error) => {
        alertMessage('Произошла ошибка при cохранении данных клиента');
      });
    }

    function disableCustomerForm() {
      $('.js-show-customer-form').text('Изменить данные клиента');
      isCustomerFormActive = false;
      $('.customer-form .info-block').show();
      $('.customer-form .field-block').hide();
      $('.customer-form .help-block').text('');
      $('#imagefile').val(null);
    }

    // ---------- CUSTOMER END -----------//

    // ---------- MED CARE BEGIN ---------//
    // ---------- MED CARE END -----------//

    // ---------- ORDER DOCUMENT BEGIN ----------//
    $('.js-generate-doc').click((e) => {
      const order_id = $('#order-id').val();
      const template_id = $('select[name=template_id]').val();

      $.get(generateDocUrl, { order_id, template_id }).done((doc) => {
        addDocument(order_id, doc);
        renderDocument(doc);
      }).fail(() => {
        alertMessage('Произошла ошибка при формировании документа');
      });
    });

    function addDocument(order_id, doc) {
      const event = timetableCalendar.fullCalendar('clientEvents', order_id)[0];
      event.documents.push(doc);
    }

    function renderDocument(doc) {
      const docRow = `<tr>
                <td>${doc.date}</td>
                <td>${doc.userName}</td>
                <td>${doc.templateName}</td>
                <td><a href="${doc.link}">Открыть</a></td>
                </tr>
            `;
      $('.order-documents table > tbody').append(docRow);
    }

    function renderDocuments(documents) {
      documents.forEach(renderDocument);
    }

    function clearDocuments() {
      $('.order-documents table > tbody').html('');
    }

    function loadDocsList(division_id) {
      $('select[name=template_id]').empty();
      $.get('/order/document/templates', { division_id }).done((templates) => {
        $.each(templates, (key, template) => {
          $('select[name=template_id]')
            .append($('<option>', { value: template.id })
              .text(template.name));
        });
      }).fail(() => {
        alertMessage('Произошла ошибка при загрузке списка документов');
      });
    }
    // ----------- ORDER DOCUMENT END ----------------//

    function dateFromString(d) {
      return moment.utc(d, 'YYYY-MM/-DD HH:mm');
    }

    function dateToString(d) {
      return moment(d).format('YYYY-MM-DD');
    }

    function getCurrentMoment() {
      return dateFromString(moment().format('YYYY-MM-DD HH:mm'));
    }

    function setTimeline(view) {
      view = view || timetableCalendar.fullCalendar('getView');

      const fcslats = $('.fc-time-grid:visible .fc-slats').parent();
      let timeline = fcslats.children('.timeline');
      let timelineArrow = fcslats.children('.timeline-arrow');

      const currentMoment = getCurrentMoment();
      const duration = moment.duration(duration_min);

      const f = moment.utc(dateToString(moment())).add(duration);
      let rowHeight = fcslats.find('.fc-slats tr:first').height();

        // if (rowHeight < 18) {
        //   rowHeight = 18;
        // }
        // if (rowHeight > 18) {
        //   rowHeight = 20;
        // }

      if (timeline.length == 0) {
        timeline = $('<hr>').addClass('timeline');
        timelineArrow = $('<b></b>').addClass('timeline-arrow caret');
        fcslats.prepend(timeline);
        fcslats.prepend(timelineArrow);
      }

      if (view.start < currentMoment && view.end > currentMoment) {
        timeline.show();
        timelineArrow.show();
      } else {
        timeline.hide();
        timelineArrow.hide();
        return;
      }

        const slot_length_in_minutes = +$('#timetable-interval').val().toString().substr(3, 2);
        const top = Math.floor((Math.floor(currentMoment.diff(f) / 1000 / 60 / slot_length_in_minutes) * rowHeight) + rowHeight - 1);
      timeline.css('top', `${top}px`);
      timelineArrow.css('top', `${top - 4}px`);

      if (view.name == 'agendaWeek') {
        const today = $('.fc-today:visible');
        const todayLeft = today.position().left;
        const todayWidth = today.outerWidth();
        timeline.css({ left: `${todayLeft}px`, width: `${todayWidth}px` });
      } else {
        const axisWidth = $('.fc-widget-header:visible .fc-axis').outerWidth();
        timeline.css('left', `${axisWidth}px`);
        timeline.css('width', `${$('.fc-agenda-view:visible').outerWidth() - axisWidth - 1}px`);
      }
    }
  }

  $('.js-add-pending-order').click((e) => {
    $('#pendingorder-id').val(null);
    $('#pending-order-form')[0].reset();
      $('#pending-order-modal').find('.js-delete-pending-order').hide();
    $('#pending-order-modal').modal('show');
  });

  $('#pending-order-form').on('beforeSubmit', (e) => {
    const id = $('#pendingorder-id').val();
    let url = '/timetable/add-pending';
    if (id) {
      url = `/timetable/update-pending?id=${id}`;
    }

    $.post({
      url,
      data: $('#pending-order-form').serialize(),
      dataType: 'json',
    }).done((response) => {
      if (id) {
        $(`#waiting_list > table > tbody > tr[data-id=${id}]`).html(renderPendingOrderCells(response));

        const event = $(`#waiting_list > table > tbody > tr[data-id=${id}]`).data('event');
        $.each(response, (ind, attribute) => {
          if (event.hasOwnProperty(ind)) {
            event[ind] = attribute;
          }
        });
      } else {
        $('#waiting_list > table > tbody').prepend(renderPendingOrder(response));
        $(`#waiting_list > table > tbody > tr[data-id=${response.id}]`).click(showPendingOrder);
        $(`#waiting_list > table > tbody > tr[data-id=${response.id}]`).draggable({
          helper: 'clone',
          start(event, ui) {
            $(ui.helper).css('width', `${$(event.target).width()}px`);
          },
          zIndex: 999,
          revert: true, // will cause the event to go back to its
          revertDuration: 0, //  original position after the drag
        });
      }

      $('#pendingorder-id').val(null);
      $('#pending-order-form')[0].reset();
      $('#pending-order-modal').modal('hide');
    }).fail((error) => {
      alertMessage('Произошла ошибка при cохранении данных клиента');
    });

    return false;
  });

    $('.js-delete-pending-order').click((e) => {
        confirmMessage("Вы уверены, что хотите удалить эту запись из листа ожидания?", (result) => {
            if (result) {
                let id = $('#pendingorder-id').val();
                $.post({
                    url: `/timetable/delete-pending?id=${id}`,
                    dataType: 'json',
                }).done((response) => {
                    if (!response.error) {
                        closePendingModalWindow();
                        $(`#waiting_list > table > tbody > tr[data-id=${id}]`);
                        $.jGrowl(response.message, {group: 'flash_notice'});
                    } else {
                        $.jGrowl(response.message, {group: 'flash_alert'});
                    }
                });
            }
        });
    });

    function closePendingModalWindow() {
        $('#pendingorder-id').val(null);
        $('#pending-order-form')[0].reset();
        $('#pending-order-modal').modal('hide');
    }

    $('.draggable-order').click(showPendingOrder);

    function showPendingOrder(e) {
        const id = $(this).data('id');
        const event = $(this).data('event');
        const date = moment(event.datetime).format('YYYY-MM-DD');

        $('#pendingorder-id').val(id);
        $('#pendingorder-date').val(date);
        $('#pendingorder-company_customer_id').val(event.company_customer_id);
        $('#pendingorder-customer_name').val(event.customer_name);
        $('#pendingorder-customer_phone').val(event.customer_phone);
        $('#pendingorder-staff_id').val(event.staff_id);
        $('#pendingorder-note').val(event.note);
        $('#pendingorder-division_id').val(event.division_id);

        $('#pending-order-modal').find('.js-delete-pending-order').show();
        $('#pending-order-modal').modal('show');
    }

  $('.draggable-order').draggable({
    helper: 'clone',
    start(event, ui) {
      $(ui.helper).css('width', `${$(event.target).width()}px`);
    },
    zIndex: 999,
    revert: true, // will cause the event to go back to its
    revertDuration: 0, //  original position after the drag
  });

  function renderPendingOrder(model) {
    return $(`
            <tr class="draggable-order" data-id="${model.id}">${renderPendingOrderCells(model)}</tr>
        `).data('event', model);
  }

  function renderPendingOrderCells(model) {
    const date = moment(model.datetime).format('YYYY-MM-DD');
    const staffName = $(`.checkboxuser[data-entity-id=${model.staff_id}]`).data('entity-name');
    return `
            <td>${model.customer_name}<br>${model.customer_phone}</td>
            <td>${date}<br>${staffName}</td>
        `;
  }

  window.servicesSelectEvent = function (e) {
    const tabbedTable = $('#tabbedTable');
    const servicesTable = $('#servicesTable');
    tabbedTable.show();

    const key = document.getElementById('servicesTable').rows.length;
    const service = {
      id: e.params.data.id,
      duration: e.params.data.element.dataset.duration,
      name: e.params.data.text,
      price: e.params.data.element.dataset.price,
      quantity: 1,
      service_name: e.params.data.element.dataset.service_name,
      service_price: e.params.data.element.dataset.price,
    };
    const row = renderService(key, service);
    servicesTable.append(row);

    $('.field-order-division_service_id .select2').hide();
    $('#js-add-service').show();
    $('#order-division_service_id').val('').change();

      loadProducts(e.params.data.id);

    calcServiceTotal();
  };

    $('.js-reset-customer').click(e => {
        e.preventDefault();

        $('#order-company_customer_id').val(null);
        $('#order-customer_name').val(null);
        $('#order-customer_phone').val(null);

        return false;
    });

  window.servicesCloseEvent = function (e) {
    const rowsCount = document.getElementById('servicesTable').rows.length;
    if (rowsCount >= 1) {
      $('.field-order-division_service_id .select2').hide();
      $('#js-add-service').show();
    }
  };
});
