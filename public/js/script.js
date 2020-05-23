const ALERT_INFO = 0,
    ALERT_SUCCESS = 1,
    ALERT_WARNING = 2,
    ALERT_ERROR = 3;

function alertBS(selector, text, type, replace = true) {
    var classes = [
        'alert-info',
        'alert-success',
        'alert-warning',
        'alert-danger'
    ];

    var titles = [
        'Informacja.',
        'Sukces!',
        'Ostrzeżenie!',
        'Błąd!',
    ];

    if (type < 0 || type > classes.length) {
        return;
    }

    var alertBody = '<div class="alert alert-dismissable ' + classes[type] + '"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong>' + titles[type] + '</strong> ' + text + '</div>';

    if (replace) {
        $(selector).html(alertBody);
    } else {
        $(selector).append(alertBody);
    }
}

function copyAlerts(srcSelector, destSelector, override = false) {
    var sourceHTML = $(srcSelector).html();
    $(srcSelector).html('');

    if (override) {
        $(destSelector).html(sourceHTML);
    } else {
        $(destSelector).append(sourceHTML);
    }
}

function removeFormErrors(formSelector) {
    var formGroup = $(formSelector).find('.has-error');
    formGroup.removeClass('has-error');
    $(formSelector).find('.help-block, .alert').remove();
}

function bindSearchForm(searchFormSelector, tableWrapperSelector = '#TableWrapper') {
    $('body').on('submit', searchFormSelector, function (e) {
        e.preventDefault();
        $(this).ajaxSubmit(
            {
                success: function (resp) {
                    if (typeof resp === 'object') {
                        switch (resp.type) {
                            case 'alert': {
                                if (resp.alert_type != ALERT_INFO && resp.alert_type != ALERT_SUCCESS) {
                                    removeFormErrors(searchFormSelector);
                                }

                                alertBS('#alertContainer', resp.message, resp.alert_type);
                                break;
                            }
                        }
                    } else {
                        $html = $(resp);
                        if ($html.find('table')) {
                            removeFormErrors(searchFormSelector);
                            $(tableWrapperSelector).html(resp);
                        }
                    }
                },
                error: function () {
                    alertBS('#alertContainer', 'Wystąpił błąd podczas wyszukiwania', ALERT_ERROR);
                },
            });
    });
}

function bindAjaxKnpPaginator(tableWrapper) {
    //Paginacja Ajaxowa i sortowanie
    $('body').on('click', tableWrapper + ' .navigation a, ' + tableWrapper + ' .custom-sort a', function (e) {

        e.preventDefault();
        $.ajax({
            type: 'GET',
            url: $(this).attr('href'),
            success: function (resp) {
                if (typeof resp === 'object') {
                    switch (resp.type) {
                        case 'alert': {
                            alertBS('#alertContainer', resp.message, resp.alert_type);
                            break;
                        }
                    }
                } else {
                    $(tableWrapper).html(resp);
                    $('[data-toggle="tooltip"]').tooltip();
                }
            },
            error: function () {
                alertBS('#alertContainer', 'Wystąpił błąd podczas wyszukiwania', 3);
            },
        });
    });

}

function bindDeleteModal(modalSelector = '#FormModal', confirmBtnSelector = '#confirmBtn', cancelButtonSelector='#cancelBtn', tableWrapperSelector = '#TableWrapper')
{
    //Kliknięcie tak
    $('body').on('click', modalSelector + ' ' + confirmBtnSelector, function (e) {
        e.preventDefault();
        var actionUrl = $(this).data('action');
        if (!actionUrl) {
            return;
        }

        $.ajax({
            url: actionUrl,
            success: function (data) {
                if (typeof data === 'object') { //W przypadku zwróconego jsona
                    if (data.type == 'alert') {
                        alertBS('#alertContainer', data.message, data.alert_type);
                    }
                } else {
                    $(tableWrapperSelector).html(data);
                    copyAlerts('#hiddenAlerts', '#alertContainer', true);
                }
            },
            error: function () {
                alertBS('#alertContainer', 'Błąd podczas łączenia z serwerem.', 3);
            }
        });
    });

    //Kliknięcie nie
    $('body').on('click', modalSelector + ' ' + cancelButtonSelector, function (e) {
        $(modalSelector).modal('hide');
        $(modalSelector).modal('dispose');
        $(modalSelector).remove();
    });
}

function bindAjaxModalForm(btnSelector, modalFormSelector = "#FormModal", successHandler = null, hideHandler=null) {
    $('body').on('click', btnSelector, function (e) {
        e.preventDefault();
        var actionUrl = $(this).data('action');
        if (!actionUrl) {
            return;
        }

        $.ajax({
            url: actionUrl,
            success: function (data) {
                if (typeof data === 'object') { //W przypadku zwróconego jsona
                    if (data.type == 'alert') {
                        alertBS('#alertContainer', data.message, data.alert_type);
                    }
                } else {
                    $('#ModalContainer').html(data);
                    $('#ModalContainer ' + modalFormSelector).on('shown.bs.modal', function (e) {
                        if (successHandler) {
                            successHandler();
                        }
                        $('.selectpicker').selectpicker();
                        hideSpoilers();
                    });

                    $('#ModalContainer ' + modalFormSelector).on('hide.bs.modal', function () {
                        if (hideHandler) {
                            hideHandler();
                        }
                    });

                    $('#ModalContainer ' + modalFormSelector).modal();
                }
            },
            error: function () {
                alertBS('#alertContainer', 'Błąd podczas łączenia z serwerem.', 3);
            }
        });
    });
}

function fixDateTimePicker() {
    $.fn.datetimepicker.Constructor.Default = $.extend({},
        $.fn.datetimepicker.Constructor.Default,
        { icons:
                { time: 'fas fa-clock',
                    date: 'fas fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-arrow-circle-left',
                    next: 'fas fa-arrow-circle-right',
                    today: 'far fa-calendar-check-o',
                    clear: 'fas fa-trash',
                    close: 'far fa-times' } });
}

function bindIpInfoGetter()
{
    $('body').on('mouseover', '.ipV4Address, .ipV6Address', function (e) {
        var ipAddress = $(this).text();
        var $this = $(this);

        $('.ipV4Address, .ipV6Address').tooltip('hide');
        $('.ipV4Address, .ipV6Address').attr('title', '');

        $.ajax({
            url: '/getIpInfo/' + ipAddress,
            success: function (data) {
                $this.attr('title', data);
                $this.tooltip('show');
            },
            error: function () {
                //alertBS('#alertContainer', 'Błąd podczas łączenia z serwerem.', 3);
            }
        });


    });

    /*$('body').on('mouseout', '.ipV4Address, .ipV6Address', function (e) {
        $(this).tooltip('hide');
    });*/
}


$(document).ready(function () {

    //DateTime Picker
    fixDateTimePicker();

    $('.datetimepicker').datetimepicker({
        locale: 'pl'
    });

    $('.datetimepicker').on('focusout', function(ev){
        $(this).datetimepicker('hide');
    });

    bindIpInfoGetter();

    bindAjaxKnpPaginator('#MyPastesTableWrapper');
    bindSearchForm('#MyPastesSearchForm','#MyPastesTableWrapper');

    bindAjaxModalForm('.btnMyPasteDelete', "#MyPasteDeleteModal");
    bindDeleteModal("#MyPasteDeleteModal", "#confirmBtn", "#cancelBtn", "#MyPastesTableWrapper");

    bindAjaxKnpPaginator('#PastesTableWrapper');
    bindSearchForm('#PastesSearchForm','#PastesTableWrapper');

    bindAjaxModalForm('.btnPasteDelete', "#PasteDeleteModal");
    bindDeleteModal("#PasteDeleteModal", "#confirmBtn", "#cancelBtn", "#PastesTableWrapper");
});

//$('.ipV4Address').tooltip();
//$('.ipV6Address').tooltip();