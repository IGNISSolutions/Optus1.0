var CommonPlugins = function () {

    var handleDataTablesForEach = function () {
        ko.bindingHandlers.dataTablesForEach = {
            page: 0,
            tables: [],
            defaultOptions: {
                language: {
                    url: '/resources/datatables'
                },
                lengthMenu: [
                    [2, 5, 15, 30, 60, -1],
                    [2, 5, 15, 30, 60, 'Todos'] // change per page values here
                ],
                autoWidth: false,
                destroy: true,
                pageLength: 30,
                pagingType: 'bootstrap_full_number'
            },
            init: ko.bindingHandlers.foreach.init,
            update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var binding = ko.utils.unwrapObservable(valueAccessor()),
                    key = 'DataTablesForEach_Initialized';

                ko.unwrap(binding.data);

                var table_id = $(element).closest('table')[0].id;
                var stored_table = ko.bindingHandlers.dataTablesForEach.tables.find(t => t.id === table_id);
                var table = stored_table ? stored_table.data : null;

                var options = binding.options;
                options.scrollY = typeof options.scrollY !== 'undefined' ? options.scrollY : '';
                options.bStateSave = typeof options.bStateSave !== 'undefined' ? options.bStateSave : true; // save datatable state(pagination, sort, etc) in cookie.
                options.order = typeof options.order !== 'undefined' ? [options.order] : [
                    [1, "asc"]
                ];
                options.bPaginate = options.paging;

                // Text truncate Plugin
                if (typeof options.renderTruncate !== 'undefined') {
                    options.columnDefs = [{
                        targets: options.renderTruncate.targets,
                        render: $.fn.DataTable.render.ellipsis(
                            options.renderTruncate.cutOff,
                            typeof options.renderTruncate.wordBreak !== 'undefined' ? options.renderTruncate.wordBreak : false,
                            typeof options.renderTruncate.escapeHtml !== 'undefined' ? options.renderTruncate.escapeHtml : false
                        )
                    }];
                }

                // For initialization
                if (!table) {
                    // Create table
                    ko.bindingHandlers.foreach.update(element, valueAccessor, allBindings, viewModel, bindingContext);
                    table = $(element).closest('table').DataTable({ ...ko.bindingHandlers.dataTablesForEach.defaultOptions, ...options });
                    ko.bindingHandlers.dataTablesForEach.tables.push({
                        id: table_id,
                        data: table,
                        is_filtering: false
                    });
                    // For update
                } else {
                    // Clear table and add rows
                    table.clear().draw();
                    if (valueAccessor().data().length > 0) {
                        stored_table.is_filtering = true;
                        valueAccessor().data().forEach(item => {
                            table.rows.add(item);
                        });
                        table.draw();
                    }

                    // Avoid to reset on update second round
                    const index = ko.bindingHandlers.dataTablesForEach.tables.findIndex(t => t.id === table_id);
                    if (stored_table.is_filtering) {
                        table.destroy();
                        ko.bindingHandlers.foreach.update(element, valueAccessor, allBindings, viewModel, bindingContext);
                        table = $(element).closest('table').DataTable({ ...ko.bindingHandlers.dataTablesForEach.defaultOptions, ...options });
                        stored_table.data = table;
                    }
                    stored_table.is_filtering = false;
                    ko.bindingHandlers.dataTablesForEach.tables[index] = stored_table;
                }

                if (binding.options.paging) {
                    ko.bindingHandlers.dataTablesForEach.page = table.page();
                    if (table.page.info().pages - ko.bindingHandlers.dataTablesForEach.page === 0) {
                        table.page(--ko.bindingHandlers.dataTablesForEach.page).draw(false);
                    } else {
                        table.page(ko.bindingHandlers.dataTablesForEach.page).draw(false);
                    }
                }

                //if we have not previously marked this as initialized and there is currently items in the array, then cache on the element that it has been initialized
                if (!ko.utils.domData.get(element, key) && (binding.data || binding.length)) {
                    ko.utils.domData.set(element, key, true);
                }

                return {
                    controlsDescendantBindings: true
                };
            }
        };
    };

    var handleSelectPicker = function () {
        ko.bindingHandlers.select2 = {
            defaultOptions: {
                width: 'auto',
                responsive: true,
                language: 'es'
            },
            after: ['options', 'value', 'selectedOptions'],
            init: function (el, valueAccessor, allBindingsAccessor, viewModel) {
                $.fn.select2.defaults.set('theme', 'bootstrap');
                $.fn.select2.defaults.set('language', 'es');
                var allBindings = allBindingsAccessor();
                var options = ko.utils.unwrapObservable(allBindings.select2);

                ko.utils.domNodeDisposal.addDisposeCallback(el, function () {
                    $(el).select2('destroy');
                });

                $(el).select2({ ...ko.bindingHandlers.select2.defaultOptions, ...options });
            },
            update: function (el, valueAccessor, allBindingsAccessor, viewModel) {
                var allBindings = allBindingsAccessor();
                var options = ko.utils.unwrapObservable(allBindings.select2);

                if ('value' in allBindings) {
                    const value = ko.utils.unwrapObservable(allBindings.value());
                    if (!value) {
                        $(el).val(value).trigger('change');
                    }
                    if ($(el).hasClass('select2-hidden-accessible')) {
                        $(el).hide();
                        $(el).select2('destroy');
                        setTimeout(() => {
                            $(el).select2({ ...ko.bindingHandlers.select2.defaultOptions, ...options });
                            $(el).show();
                        }, 500);
                    }
                } else if ('selectedOptions' in allBindings) {
                    const value = ko.utils.unwrapObservable(allBindings.selectedOptions());
                    if (value && value.length === 0) {
                        $(el).val([value]).trigger('change');
                    }
                    if ($(el).hasClass('select2-hidden-accessible')) {
                        $(el).hide();
                        $(el).select2('destroy');
                        setTimeout(() => {
                            $(el).select2({ ...ko.bindingHandlers.select2.defaultOptions, ...options });
                            $(el).show();
                        }, 500);
                    }
                }
            }
        };
    };

    var handleDateTimePicker = function () {
        ko.bindingHandlers.dateTimePicker = {
            init: function (element, valueAccessor, allBindingsAccessor) {
                var defaultOptions = {
                    language: 'es',
                    autoclose: true,
                    keyboardNavigation: false,
                    todayHighlight: true,
                    minView: 1
                };
                //initialize datepicker with some optional options
                var options = allBindingsAccessor().dateTimePickerOptions;
                
                options = { ...defaultOptions, ...options };
                var el = $(element);
                

                el.datetimepicker(options);
                
                el.on('change', function (event) {
                    var value = valueAccessor();
                    
                    //console.log('el.datetimepicker("getDate")', el.datetimepicker("getDate"));
                    var vDate = el.datetimepicker("getDate");
                    if (vDate != undefined) {
                        var newDate = moment(vDate).format(options.momentFormat);
                        value(newDate);
                    }
                });

                /*
                el.on('changeDate', function(event) {
                    var value = valueAccessor();
                    console.log('On changeDate datetimepicker event', event);
                    console.log('On changeDate datetimepicker value.date', value.date);
                    var newDate = moment(event.date).format(options.momentFormat);
                    value(newDate);
                });
                */
            },
            update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var el = $(element);
                var date = ko.utils.unwrapObservable(valueAccessor());
                if (date) {
                    el.datetimepicker().val(date);
                    //el.datetimepicker('update');
                }
            }
        };
    };

    var handleICheck = function () {
        ko.bindingHandlers.iCheck = {
            init: function (element, valueAccessor) {
                var $el = $(element);
                var observable = valueAccessor();
                $el.iCheck({
                    radioClass: 'iradio_minimal-blue',
                    inheritClass: true
                });

                $el.on('ifClicked', function (e) {
                    var val = $(e.target).val();
                    observable(val);
                });
            },
            update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
                // This update handles both the reverting of values from cancelling edits, and the initial value setting.
                var $el = $(element);
                var value = ko.unwrap(valueAccessor());
                if (value == $el.val()) {
                    $el.iCheck('check');
                } else if (value == "" || value == null) { // Handle clearing the value on reverts.
                    $el.iCheck('uncheck');
                }
            }
        }
    };

    var handleInputMask = function () {
        ko.bindingHandlers.inputmask = {
            init: function (element, valueAccessor, allBindingsAccessor) {

                var mask = valueAccessor();

                var observable = mask.value;

                if (ko.isObservable(observable)) {

                    $(element).on('focusout change', function () {

                        if ($(element).inputmask('isComplete')) {
                            observable($(element).val());
                        } else {
                            observable(null);
                        }

                    });
                }

                $(element).inputmask(mask);


            },
            update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var mask = valueAccessor();

                var observable = mask.value;
                
                if (ko.isObservable(observable)) {

                    var valuetoWrite = observable();

                    $(element).val(valuetoWrite);
                }
            }
        }
    };

    var handleTagsInput = function () {
        ko.bindingHandlers.tagsinput = {
            init: function (element, valueAccessor, allBindings) {
                var defaultOptions = {
                    cancelConfirmKeysOnEmpty: true
                };
                var options = allBindings().tagsinputOptions || {};
                var value = valueAccessor();
                var valueUnwrapped = ko.unwrap(value);

                var el = $(element);

                options = { ...defaultOptions, ...options };

                el.tagsinput(options);

                for (var i = 0; i < valueUnwrapped.length; i++) {
                    el.tagsinput('add', valueUnwrapped[i]);
                }

                el.on('itemAdded', function (event) {
                    if (ko.unwrap(value).indexOf(event.item) === -1) {
                        valueAccessor().push(event.item);
                    }
                })

                el.on('itemRemoved', function (event) {
                    valueAccessor().remove(event.item);
                });
            },
            update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var value = valueAccessor();
                var valueUnwrapped = ko.unwrap(value);

                var el = $(element);
                var prev = el.tagsinput('items');

                var added = valueUnwrapped.filter(function (i) { return prev.indexOf(i) === -1; });
                var removed = prev.filter(function (i) { return valueUnwrapped.indexOf(i) === -1; });

                // Remove tags no longer in bound model
                for (var i = 0; i < removed.length; i++) {
                    el.tagsinput('remove', removed[i]);
                }

                // Refresh remaining tags
                el.tagsinput('refresh');

                // Add new items in model as tags
                for (i = 0; i < added.length; i++) {
                    el.tagsinput('add', added[i]);
                }
            }
        }
    };

    var handleFileInput = function () {
        ko.bindingHandlers.fileinput = {
            init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
                self = this
                var defaultOptions = {
                    language: 'es',
                    theme: 'fa',
                    uploadAsync: true,
                    maxFileSize: 100240,
                    maxTotalFileCount: 1,
                    dropZoneEnabled: false,
                    allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx'],
                    showPreview: false,
                    showUpload: false,
                    showCancel: false,
                    showPause: false,
                    showClose: false,
                    showDownload: true,
                    showCaption: true,
                    overwriteInitial: true,
                    initialPreviewAsData: true,
                    browseClass: 'btn default',
                    autoOrientImage: false,
                    previewFileIcon: '<i class="fa fa-file"></i>',
                    previewFileIconSettings: {
                        'doc': '<i class="fa fa-file-word-o text-primary"></i>',
                        'xls': '<i class="fa fa-file-excel-o text-success"></i>',
                        'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                        'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
                        'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
                        'txt': '<i class="fa fa-file-text-o text-info"></i>',
                    }
                };
                var options = allBindingsAccessor().fileinputOptions || {};
                var value = valueAccessor();
                var valueUnwrapped = ko.unwrap(value);
                var el = $(element);

                options = { ...defaultOptions, ...options };

                el.fileinput(options);

                el.on('filebatchselected', function (event, data) {
                    const fileStack = el.fileinput('getFileStack'),
                        fstack = [];
                    $.each(fileStack, function (fileId, fileObj) {
                        if (fileObj !== undefined) {
                            el.fileinput('upload');
                        }
                    });
                });

                el.on('fileuploaded', function (event, data) {
                    try {
                        value().filename(data.response.initialPreviewConfig[0].caption);
                        value().action('upload');
                    } catch (e) {
                        value.filename(data.response.initialPreviewConfig[0].caption);
                        value.action('upload');
                    }
                    var fileuploadedCallback = allBindingsAccessor().fileuploadedCallback;
                    if (fileuploadedCallback) {
                        fileuploadedCallback();
                    }
                });

                el.on('filedeleted', function (event, data) {
                    try {
                        value().filename(null);
                        value().action('delete');
                    } catch (e) {
                        value.filename(null);
                        value.action('delete');
                    }
                    var filedeletedCallback = allBindingsAccessor().filedeletedCallback;
                    if (filedeletedCallback) {
                        filedeletedCallback();
                    }
                });

                el.on('fileclear', function (event, data) {
                    try {
                        value().filename(null);
                        value().action('clear');
                    } catch (e) {
                        value.filename(null);
                        value.action('clear');
                    }
                    var fileclearCallback = allBindingsAccessor().fileclearCallback;
                    if (fileclearCallback) {
                        fileclearCallback();
                    }
                });

                el.on('fileuploaderror', function (event, data, msg) {
                    setTimeout(() => {
                        swal({
                            title: 'Error',
                            html: true,
                            text: msg,
                            type: 'error'
                        });
                        el.fileinput('clear');
                    }, 500);
                });
            }
        }
    };

    var handleFileInputExcel = function () {
        ko.bindingHandlers.fileUploadExcel = {
            init: function (element, valueAccessor) {
                $(element).change(function () {
                    valueAccessor()(element.files[0]);
                });
            },
            update: function (element, valueAccessor) {
                if (ko.unwrap(valueAccessor()) === null) {
                    $(element).wrap('<form>').closest('form').get(0).reset();
                    $(element).unwrap();
                }
            }
        };
    }

    var handleBootstrapSwitch = function () {
        ko.bindingHandlers.bootstrapSwitchOn = {
            init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
                $elem = $(element);
                $(element).bootstrapSwitch();
                $(element).bootstrapSwitch('state', ko.utils.unwrapObservable(valueAccessor())); // Set intial state
                $elem.on('switchChange.bootstrapSwitch', function (e, data) {
                    const oldValue = valueAccessor()();
                    valueAccessor()(data);
                    var onChangeCallback = allBindingsAccessor().onChangeCallback;
                    if (onChangeCallback) {
                        onChangeCallback(data, oldValue);
                    }
                }); // Update the model when changed.
            },
            update: function (element, valueAccessor, allBindingsAccessor, viewModel) {
                var vStatus = $(element).bootstrapSwitch('state');
                var vmStatus = ko.utils.unwrapObservable(valueAccessor());
                if (vStatus != vmStatus) {
                    $(element).bootstrapSwitch('state', vmStatus);
                }
            }
        };
    };

    var handlePulsate = function () {
        ko.bindingHandlers.pulsate = {
            init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var bindings = ko.utils.unwrapObservable(allBindings());
                $(element).hide();
                $(element).pulsate(bindings.pulsateOptions);
            },
            update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                var condition = ko.utils.unwrapObservable(valueAccessor());
                if (condition) {
                    $(element).show('pulsate');
                } else {
                    $(element).hide('pulsate');
                }
            }
        };
    }

    var handleNumber = function () {
        ko.bindingHandlers.number = {
            update: function (element, valueAccessor, allBindingsAccessor) {
                var defaults = ko.bindingHandlers.number.defaults,
                    aba = allBindingsAccessor,
                    unwrap = ko.utils.unwrapObservable,
                    value = unwrap(valueAccessor()) || valueAccessor(),
                    result = '',
                    numarray;

                var separator = unwrap(aba().separator) || defaults.separator,
                    decimal = unwrap(aba().decimal) || defaults.decimal,
                    precision = unwrap(aba().precision) || defaults.precision,
                    symbol = unwrap(aba().symbol) || defaults.symbol,
                    after = unwrap(aba().after) || defaults.after;

                value = parseFloat(value) || 0;

                if (precision > 0)
                    value = value.toFixed(precision)

                numarray = value.toString().split('.');

                for (var i = 0; i < numarray.length; i++) {
                    if (i == 0) {
                        result += numarray[i].replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1' + separator);
                    } else {
                        result += decimal + numarray[i];
                    }
                }

                result = (after) ? result += symbol : symbol + result;

                ko.bindingHandlers.text.update(element, function () { return result; });
            },
            defaults: {
                separator: '.',
                decimal: ',',
                precision: 0,
                symbol: '',
                after: false
            }
        };
    }

    return {
        //main function to initiate the module
        init: function () {
            handleDataTablesForEach();
            handleSelectPicker();
            handleDateTimePicker();
            handleICheck();
            handleInputMask();
            handleTagsInput();
            handleFileInput();
            handleFileInputExcel();
            handleBootstrapSwitch();
            handlePulsate();
            handleNumber();
        }
    };

}();

jQuery(document).ready(function () {
    $.ajaxSetup({
        data: {
            UserToken: typeof User != 'undefined' ? User.Token : null
        }
    });
    CommonPlugins.init();
    $.blockUI.defaults = {
        // message displayed when blocking (use null for no message) 
        message: '<img src="' + HOST + '/assets/global/img/loading-spinner-grey.gif" /> <div style="line-height: 22px; float: right;">Procesando...</div>',
        title: null, // title string; only used when theme == true 
        draggable: true, // only used when theme == true (requires jquery-ui.js to be loaded) 

        theme: false, // set to true to use with jQuery UI themes 

        // styles for the message when blocking; if you wish to disable 
        // these and use an external stylesheet then do this in your code: 
        // $.blockUI.defaults.css = {}; 
        css: {
            textAlign: 'center',
            color: '#000',
            backgroundColor: '#fff',
            cursor: 'wait'
        },
        // minimal style set used when themes are used 
        themedCSS: {
            width: '30%',
            top: '40%',
            left: '35%'
        },
        // styles for the overlay 
        overlayCSS: {
            backgroundColor: '#000',
            opacity: 0.6,
            cursor: 'wait'
        },
        // style to replace wait cursor before unblocking to correct issue 
        // of lingering wait cursor 
        cursorReset: 'default',
        // styles applied when using $.growlUI 
        growlCSS: {
            width: '350px',
            top: '10px',
            left: '',
            right: '10px',
            border: 'none',
            padding: '5px',
            opacity: 0.6,
            cursor: null,
            color: '#fff',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px'
        },
        // IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w 
        // (hat tip to Jorge H. N. de Vasconcelos) 
        iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',
        // force usage of iframe in non-IE browsers (handy for blocking applets) 
        forceIframe: false,
        // z-index for the blocking overlay 
        baseZ: 1000,
        // set these to true to have the message automatically centered 
        centerX: true, // <-- only effects element blocking (page block controlled via css above) 
        centerY: true,
        // allow body element to be stetched in ie6; this makes blocking look better 
        // on "short" pages.  disable if you wish to prevent changes to the body height 
        allowBodyStretch: true,
        // enable if you want key and mouse events to be disabled for content that is blocked 
        bindEvents: true,
        // be default blockUI will supress tab navigation from leaving blocking content 
        // (if bindEvents is true) 
        constrainTabKey: true,
        // fadeIn time in millis; set to 0 to disable fadeIn on block 
        fadeIn: 200,
        // fadeOut time in millis; set to 0 to disable fadeOut on unblock 
        fadeOut: 400,
        // time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock 
        timeout: 0,
        // disable if you don't want to show the overlay 
        showOverlay: true,
        // if true, focus will be placed in the first available input field when 
        // page blocking 
        focusInput: true,
        // suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity) 
        // no longer needed in 2012 
        // applyPlatformOpacityRules: true, 

        // callback method invoked when fadeIn has completed and blocking message is visible 
        onBlock: null,
        // callback method invoked when unblocking has completed; the callback is 
        // passed the element that has been unblocked (which is the window object for page 
        // blocks) and the options that were passed to the unblock call: 
        //   onUnblock(element, options) 
        onUnblock: null,
        // don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493 
        quirksmodeOffsetHack: 4,
        // class name of the message block 
        blockMsgClass: 'page-loading page-loading-boxed',
        // if it is already blocked, then ignore it (don't unblock and reblock) 
        ignoreIfBlocked: false
    };

    $('[data-toggle="tooltip"]').tooltip();
});