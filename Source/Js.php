<?php

namespace PureMashiro\BundleJs\Source;

class Js
{
    public const DEFER_OUTER_JS = <<< JS
var runOuterJs = false,
    mouseoverSupported = 'onmouseover' in document.documentElement,
    touchSupported = 'ontouchstart' in document.documentElement;

function deferOuterJsHandler() {
    var defers = document.querySelectorAll('[type="deferOuter/javascript"]');
    if (defers.length) {
        var head = document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0];
        defers.forEach(function (defer) {
           var script = document.createElement("script");
           defer.getAttributeNames().forEach(function (attributeName) {
               script.setAttribute(attributeName, defer.getAttribute(attributeName));
               }, this);
           script.setAttribute('type', 'text/javascript');

           head.appendChild(script);
        });
    }
}

function runDeferOuterJsHandler() {
    if (!runOuterJs) {
        runOuterJs = true;
        deferOuterJsHandler();
        document.removeEventListener('mouseover', runDeferOuterJsHandler);
        document.removeEventListener('touchstart', runDeferOuterJsHandler);
    }
}

function hasCart() {
    var storage = window.localStorage.getItem('mage-cache-storage');
    try {
        storage = storage && JSON.parse(storage);
        return storage.cart && storage.cart.items && storage.cart.items.length;
    } catch (e) {
        return false;
    }
}

var getCookie = function (name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) return match[2];
}

if (getCookie('mashiro_auto_collect_phase') === 'phase_1'
    || (getCookie('mashiro_auto_collect_phase') !== 'phase_2' && (hasCart() || getCookie('mage-messages')))
) {
    runDeferOuterJsHandler();
} else if (!getCookie('mashiro_auto_collect_phase')) {
    document.addEventListener('mouseover', runDeferOuterJsHandler);
    document.addEventListener('touchstart', runDeferOuterJsHandler);
}
JS;

    public const DEFER_INNER_JS = <<< JS
var runInnerJs = false,
    mouseoverSupported = 'onmouseover' in document.documentElement,
    touchSupported = 'ontouchstart' in document.documentElement;

function deferInnerJsHandler() {
    var defers = document.querySelectorAll('[type="deferInner/javascript"]');
    if (defers.length) {
        var head = document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0];
        defers.forEach(function (defer) {
             var script = document.createElement("script");
             script.innerHTML = defer.innerHTML;
             head.appendChild(script);
        });
    }
}

function runDeferInnerJsHandler() {
    if (!runInnerJs) {
        runInnerJs = true;
        deferInnerJsHandler();
        document.removeEventListener('mouseover', runDeferInnerJsHandler);
        document.removeEventListener('touchstart', runDeferInnerJsHandler);
    }
}

var getCookie = function(name) {
  var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  if (match) return match[2];
}

if (getCookie('mashiro_auto_collect_phase') === 'phase_1') {
    runDeferInnerJsHandler();
} else if (!getCookie('mashiro_auto_collect_phase')) {
    document.addEventListener('mouseover', runDeferInnerJsHandler);
    document.addEventListener('touchstart', runDeferInnerJsHandler);
}
JS;
}
