(function ($) {
    var ePrivacyClass = function (options) {
        var root = this;
        this.vars = {
            accepted: false,
            displaytype: 'message',
            policyurl: '',
            media: '',
            autoopen: true,
            modalclass: '',
            modalwidth: '600',
            modalheight: '400',
            loginlinks: [],
            lawlink: '',
            version: 0,
            root: '',
            cookie: {
                domain: null,
                path: null
            },
            npstorage: null
        };
        var construct = function (options) {
            if (!$('div.plg_system_eprivacy_module').length && ['cookieblocker', 'events'].indexOf(options.displaytype) === -1) {
                console.log('The EU e-Privacy Directive extension REQUIRES the eprivacy module to be published.');
                return;
            }
            Object.assign(root.vars, options);
            root.vars.npstorage = new npstorage();
            var decline = parseInt(root.getDataValue());
            if (decline === 1 || decline === 2 || !root.vars.autoopen) {
                root.hideMessage();
            } else {
                root.showMessage();
            }
            $.ajaxSetup({'cache': 'false'});
            initElements();
            root.vars.accepted = (root.getDataValue() === 2) ? true : options.accepted;
            if (root.vars.accepted === false) {
                root.initLoginLinks();
            }
            if (root.get_cookie('plg_system_eprivacy_show')) {
                root.showMessage();
            }
        };
        this.translate = function (constant) {
            return Joomla.JText._('PLG_SYS_EPRIVACY_' + constant);
        };
        var initElements = function () {
            $('button.plg_system_eprivacy_agreed').click(function (e) {
                root.acceptCookies(e);
            });
            $('button.plg_system_eprivacy_accepted').click(function (e) {
                root.unacceptCookies(e);
            });
            $('button.plg_system_eprivacy_declined').click(function (e) {
                if (root.vars.accepted) {
                    root.unacceptCookies(e);
                } else {
                    root.declineCookies(e);
                }
            });
            $('button.plg_system_eprivacy_reconsider').click(function (e) {
                root.undeclineCookies(e);
            });
            $('.activebar-container .accept').click(function (e) {
                root.acceptCookies(e);
            });
            $('.activebar-container .decline').click(function (e) {
                if (root.vars.accepted) {
                    root.unacceptCookies(e);
                } else {
                    root.declineCookies(e);
                }
            });
            $('input.cookiesAll').change(function (e) {
                if (this.checked) {
                    $('.cookietable input.acl').each(function (i, el) {
                        el.checked = true;
                    });
                } else {
                    $('.cookietable input.acl').each(function (i, el) {
                        if (!el.disabled) {
                            el.checked = false;
                        }
                    });
                }
            });
            var value;
            var showall = true;
            $('div.cookietable input.acl').each(function (i, el) {
                value = parseInt($(el).val());
                if (value === 1 || root.vars.cookies.accepted.indexOf(value) !== -1) {
                    el.checked = true;
                } else {
                    showall = false;
                }
            });
            if (showall) {
                $('input.cookiesAll').prop('checked',true);
            }
        };
        this.initLoginLinks = function () {
            if (root.vars.cookies.accepted) {
                return;
            }
            var url, match, re, selectarea, message;
            var div = document.createElement("div");
            div.innerHTML = root.translate('MESSAGE') + ' ' + root.translate('JSMESSAGE');
            message = div.textContent || div.innerText || "";
            $(root.vars.loginlinks).each(function (i, uri) {
                if (typeof uri === 'object') {
                    url = uri.uri;
                    selectarea = uri.selectarea;
                } else {
                    url = uri;
                    selectarea = "=";
                }
                switch (selectarea) {
                    case '=':
                        if (url == window.location.href) {
                            window.location = root.vars.root;
                        }
                        break;
                    case '^=':
                    case '$=':
                        re = new RegExp(root.escapeRegExp(url));
                        match = window.location.href.match(re);
                        if (Array.isArray(match) && match.length) {
                            window.location = root.vars.root;
                        }
                        break;
                }
                $('[href' + selectarea + '"' + url + '"]').click(function (e) {
                    var r = confirm(message);
                    if (r === true) {
                        root.undeclineCookies(e);
                    }
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
        };
        this.escapeRegExp = function (text) {
            return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
        };
        this.checkAll = function () {
            $('.cookietable input.acl').prop('checked',true);
        };
        this.getCheckboxConsent = function (event) {
            var target, consent = [];
            if (event !== undefined) {
                var parentTypes = {
                    'module': 'div.plg_system_eprivacy_message',
                    'modal': '#eprivacyModal'
                };
                var agreebutton = $(event.currentTarget);
                var parent = agreebutton.parents(parentTypes[root.vars.displaytype]);
                var target = $(parent[0]).find('.cookietable input.acl');
            } else {
                target = $('.cookietable input.acl');
            }
            target.each(function (i, el) {
                if (el.checked) {
                    consent.push(parseInt($(el).val()));
                }
            });
            var unique_array = root.unique_array(consent);
            return unique_array;
        };
        this.unique_array = function (arr) {
            return arr.filter(function (elem, index, self) {
                return index === self.indexOf(elem);
            });
        };
        this.removeConsentCookies = function (consent) {
            $.each(root.vars.cookies.accepted, function (id, level) {
                if (consent.indexOf(level) === -1) {
                    if (root.vars.cookieregex.hasOwnProperty(level)) {
                        $.each(root.vars.cookieregex[level], function (i, cookie) {
                            switch (cookie.type) {
                                case 'string':
                                    root.delete_cookie(cookie.name, cookie.domain, cookie.path);
                                    break;
                                case 'regex':
                                    var re = new RegExp(cookie.name);
                                    var cookieNames = document.cookie.split(/=[^;]*(?:;\s*|$)/);
                                    $.each(cookieNames, function (ic, name) {
                                        if (re.test(name)) {
                                            root.delete_cookie(name, cookie.domain, cookie.path);
                                        }
                                    });
                                    break;
                            }
                        });
                    }
                }
            });
        };
        this.acceptCookies = function (event, reload) {
            var consent = root.getCheckboxConsent(event);
            document.dispatchEvent(new CustomEvent('ePrivacyEvent', {detail: {'type': 'accept', 'consent': consent, 'destination': reload ? reload : window.location.href}}));
            root.removeConsentCookies(consent);
            root.setDataValue(2);
            $.getJSON(root.vars.root, {
                option: 'com_ajax',
                plugin: 'eprivacy',
                format: 'raw',
                method: 'accept',
                consent: consent.join('.'),
                country: root.vars.country
            }).done(function (response) {
                if (response) {
                    switch (reload) {
                        case undefined:
                        case true:
                            window.location.reload();
                            break;
                        default:
                            window.location = reload;
                            break;
                    }
                }
            });
        };
        this.unacceptCookies = function (event) {
            var r = confirm(root.translate('CONFIRMUNACCEPT'));
            if (r === true) {
                document.dispatchEvent(new CustomEvent('ePrivacyEvent', {detail: {'type': 'unaccept'}}));
                root.removeConsentCookies([]);
                root.delete_cookie('plg_system_eprivacy');
                root.setDataValue(1);
                $.getJSON(root.vars.root, {
                    option: 'com_ajax',
                    plugin: 'eprivacy',
                    format: 'raw',
                    method: 'decline'
                }).done(function (response) {
                    if (response) {
                        window.location.reload();
                    }
                });
            }
        };
//        this.buildTable = function() {
//            var def;
//            var buildTR = function() {
//                var tr = document.createElement('tr');
//                return tr;
//            };
//            var buildTH = function(content) {
//                var th = document.createElement('th');
//                th.appendChild(content);
//                return th;
//            };
//            var buildTD = function(content) {
//                var td = document.createElement('td');
//                td.appendChild(content);
//                return td;
//            };
//            var buildScrollTD = function(content) {
//                var td = buildTD(content);
//                td.className = 'scroll';
//                return td;
//            };
//            var buildCheckbox = function(value,className,checked,disabled) {
//                var checkbox = document.createElement('input');
//                checkbox.type = 'checkbox';
//                checkbox.value = value;
//                checkbox.className = className;
//                checkbox.disabled = disabled;
//                if(checked) {
//                    checkbox.checked = true;
//                }
//                return checkbox;
//            };
//            var ctn = function(text) {
//                return document.createTextNode(text);
//            };
//            var table = document.createElement('table');
//            var thead = document.createElement('thead');
//            var tbody = document.createElement('tbody');
//            table.appendChild(thead);
//            table.appendChild(tbody);
//            // heading row
//            var row = buildTR();
//            row.appendChild(buildTH(buildCheckbox(0,'cookiesAll',false,false)));
//            row.appendChild(buildTH(ctn(root.translate('TH_COOKIENAME'))));
//            row.appendChild(buildTH(ctn(root.translate('TH_COOKIEDOMAIN'))));
//            row.appendChild(buildTH(ctn(root.translate('TH_COOKIEDESCRIPTION'))));
//            thead.appendChild(row);
//            // session cookie row
//            if(!root.vars.cookies.sessioncookie) {
//                var row = buildTR();
//                row.appendChild(buildTD(buildCheckbox(1,'acl',true,true)));
//                row.appendChild(buildTD(ctn(root.translate('TD_SESSIONCOOKIE'))));
//                row.appendChild(buildTD(ctn(root.vars.cookie.domain)));
//                row.appendChild(buildTD(ctn(root.translate('TD_SESSIONCOOKIE_DESC'))));
//                tbody.appendChild(row);
//            }
//            for (let i=0; i<root.vars.cookies.definitions.length; i++) {
//                def = root.vars.cookies.definitions[i];
//                var row = buildTR();
//                row.appendChild(buildTD(buildCheckbox(def[4],'acl',false,def[3])));
//                row.appendChild(buildTD(ctn(def[1])));
//                row.appendChild(buildTD(ctn(def[0])));
//                row.appendChild(buildTD(ctn(def[2])));
//                tbody.appendChild(row);
//            }
//            return table;
//        };
        this.delete_cookie = function (name, domain, path) {
            domain = domain !== undefined ? domain : root.vars.cookie.domain;
            path = path !== undefined ? path : root.vars.cookie.path;
            var cookievalue = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT;'
                    + ((path && path.length) ? 'path=' + path + ';' : '')
                    + ((domain && domain.length) ? 'domain=' + domain + ';' : '');
            document.cookie = cookievalue;
        };
        this.get_cookie = function (name) {
            var dc = document.cookie;
            var prefix = name + "=";
            var begin = dc.indexOf("; " + prefix);
            if (begin == -1) {
                begin = dc.indexOf(prefix);
                if (begin != 0)
                    return null;
            } else
            {
                begin += 2;
                var end = document.cookie.indexOf(";", begin);
                if (end == -1) {
                    end = dc.length;
                }
            }
            // because unescape has been deprecated, replaced with decodeURI
            //return unescape(dc.substring(begin + prefix.length, end));
            return decodeURI(dc.substring(begin + prefix.length, end));
        }
        this.declineCookies = function (event) {
            document.dispatchEvent(new CustomEvent('ePrivacyEvent', {detail: {'type': 'decline'}}));
            root.setDataValue(1);
            root.hideMessage();
        };
        this.undeclineCookies = function (event) {
            document.dispatchEvent(new CustomEvent('ePrivacyEvent', {detail: {'type': 'undecline'}}));
            root.setDataValue(0);
            root.showMessage();
        };
        this.showMessage = function (displaytype) {
            $('div.plg_system_eprivacy_declined').each(function (index) {
                $(this).hide();
            });
            $('div.plg_system_eprivacy_accepted').each(function (index) {
                $(this).hide();
            });
            switch (root.vars.displaytype) {
                case 'message':
                    if (root.get_cookie('plg_system_eprivacy_show') || $('div.plg_system_eprivacy_message').length) {
                        root.delete_cookie('plg_system_eprivacy_show');
                        $('div.plg_system_eprivacy_message').each(function (index) {
                            $(this).show();
                        });
                    } else {
                        document.cookie = 'plg_system_eprivacy_show=1; expires=0; path='+root.vars.cookie.path+'; domain='+root.vars.cookie.domain+';';
                        window.location.reload();
                    }
                    break;
                case 'module':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).show();
                    });
                    break;
                case 'confirm':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    displayConfirm();
                    break;
                case 'modal':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    displayModal();
                    break;
                case 'ribbon':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    displayRibbon();
                    break;
                case 'cookieblocker':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    break;
            }
        };
        this.hideMessage = function () {
            if (parseInt(root.getDataValue()) === 1) {
                $('div.plg_system_eprivacy_declined').show();
                $('div.plg_system_eprivacy_accepted').hide();
            } else {
                $('div.plg_system_eprivacy_declined').hide();
                $('div.plg_system_eprivacy_accepted').show();
            }
            switch (root.vars.displaytype) {
                case 'message':
                case 'confirm':
                case 'module':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    break;
                case 'modal':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    $('#eprivacyModal').modal('hide');
                    break;
                case 'ribbon':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    $('div.activebar-container').each(function (index) {
                        $(this).hide();
                    });
                    break;
                case 'cookieblocker':
                    $('div.plg_system_eprivacy_message').each(function (index) {
                        $(this).hide();
                    });
                    $('div.plg_system_eprivacy_declined').each(function (index) {
                        $(this).hide();
                    });
                    $('div.plg_system_eprivacy_accepted').each(function (index) {
                        $(this).hide();
                    });
                    break;
            }
        };
        this.setDataValue = function (value) {
            root.vars.npstorage.set(btoa(window.location.hostname + '.plg_system_eprivacy_decline'), value);
        };
        this.getDataValue = function () {
            return root.vars.npstorage.get(btoa(window.location.hostname + '.plg_system_eprivacy_decline'), 0);
        };
        var displayRibbon = function () {
            $('.activebar-container').show();
        };
        var displayConfirm = function () {
            if (parseInt(root.getDataValue()) !== 1) {
                var r = confirm(root.translate('MESSAGE') + ' ' + root.translate('JSMESSAGE'));
                if (r === true) {
                    root.acceptCookies();
                } else {
                    root.declineCookies();
                }
            }
        };
        var displayModal = function () {
            if (parseInt(root.getDataValue()) !== 1) {
                if (!document.getElementById('eprivacyModal')) {
                    $(root.vars.modalmarkup).appendTo(document.body);
                    $('#eprivacyModal button.plg_system_eprivacy_agreed').click(function (e) {
                        root.acceptCookies(e);
                    });
                    $('#eprivacyModal button.plg_system_eprivacy_declined').click(function (e) {
                        root.declineCookies(e);
                    });
                }
                $('#eprivacyModal').modal('show');
            }
            ;
        };
        construct(options);
    };
    var npstorage = function () {
        var cache = (window.name[0] === '{' && window.name.substr(-1) === '}') ? JSON.parse(window.name) : {};
        this.get = function (key, dflt) {
            return cache.hasOwnProperty(key) ? cache[key] : dflt;
        };
        this.set = function (key, value) {
            if (typeof key === undefined && typeof value === undefined) {
                return;
            }
            cache[key] = value;
            window.name = JSON.stringify(cache);
        };
        this.unset = function (key) {
            if (typeof key === undefined) {
                return;
            }
            delete cache[key];
            window.name = JSON.stringify(cache);
        };
    };
    $(document).ready(function () {
        var optionsElement = document.getElementsByClassName('joomla-script-options')[0];
        var options = document.getElementsByClassName('joomla-script-options').length ? JSON.parse(optionsElement.textContent || optionsElement.innerText).plg_system_eprivacy : Joomla.optionsStorage.plg_system_eprivacy;
        window.eprivacy = new ePrivacyClass(options);
    });
})(jQuery);
(function () {
    var optionsElement = document.getElementsByClassName('joomla-script-options')[0];
    var options = optionsElement !== undefined ? JSON.parse(optionsElement.textContent || optionsElement.innerText) : Joomla.optionsStorage.plg_system_eprivacy;
    var cookie = document.cookie.split(';').filter(function (item) {
        return item.indexOf('plg_system_eprivacy=') >= 0;
    }).length;
    if (!options.plg_system_eprivacy.accepted || !cookie) {
        if (!document.__defineGetter__) {
            if (navigator.appVersion.indexOf("MSIE 6.") === -1 || navigator.appVersion.indexOf("MSIE 7.") === -1) { // javascript cookies blocked only in IE8 and up
                Object.defineProperty(document, 'cookie', {
                    get: function () {
                        return '';
                    },
                    set: function () {
                        return true;
                    }
                });
            }
        } else { // non IE browsers use this method to block javascript cookies
            document.__defineGetter__("cookie", function () {
                return '';
            });
            document.__defineSetter__("cookie", function () {});
        }
        window.localStorage.clear();
        window.localStorage.__proto__ = Object.create(window.Storage.prototype);
        window.localStorage.__proto__.setItem = function () {
            return undefined;
        };
        window.sessionStorage.clear();
        window.sessionStorage.__proto__ = Object.create(window.Storage.prototype);
        window.sessionStorage.__proto__.setItem = function () {
            return undefined;
        };
    }
})();
// Polyfill for Object.assign in IE
if (typeof Object.assign !== 'function') {
    // Must be writable: true, enumerable: false, configurable: true
    Object.defineProperty(Object, "assign", {
        value: function assign(target, varArgs) { // .length of function is 2
            'use strict';
            if (target === null) { // TypeError if undefined or null
                throw new TypeError('Cannot convert undefined or null to object');
            }

            var to = Object(target);

            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];

                if (nextSource !== null) { // Skip over if undefined or null
                    for (var nextKey in nextSource) {
                        // Avoid bugs when hasOwnProperty is shadowed
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}
;
(function () {
    if (typeof window.CustomEvent === "function")
        return false;

    function CustomEvent(event, params) {
        params = params || {bubbles: false, cancelable: false, detail: undefined};
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;

    window.CustomEvent = CustomEvent;
})();
