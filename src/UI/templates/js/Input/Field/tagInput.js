/**
 * Wraps the BootstrapTagsInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Daniel Weise <dweise@concepts-and-training.de>
 */
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($) {
    il.UI.Input.tagInput = (function ($) {
        /**
         *
         * @param raw_id
         * @param config
         */
        var init = function (raw_id, config) {
            var _DEBUG = false;
            var _CONFIG = {};

            var _log = function (key, data) {
                if (!_DEBUG) {
                    return;
                }
                console.log("***********************");
                console.log(key + ":");
                console.log(data);
            };

            var _getSettings = function() {
                return {
                    whitelist: _CONFIG.options,
                    enforceWhitelist: _CONFIG.extendable,
                    duplicates: _CONFIG.allowDuplicates,
                    maxTags: _CONFIG.maxItems,
                    originalInputValueFormat: valuesArr => valuesArr.map(item => item.value),
                    dropdown: {
                        enabled: _CONFIG.dropdownSuggestionsStartAfter,
                        maxItems: _CONFIG.dropdownMaxItems,
                        closeOnSelect: _CONFIG.dropdownCloseOnSelect,
                        highlightFirst: _CONFIG.highlight
                    }
                }
            }

            // Initialize ID and Configuration
            _CONFIG = $.extend(_CONFIG, config);
            _DEBUG = _CONFIG.debug;
            _CONFIG.id = raw_id;
            _log("config", _CONFIG);

            var input = document.getElementById(_CONFIG.id);
            new Tagify(input, _getSettings());


        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
