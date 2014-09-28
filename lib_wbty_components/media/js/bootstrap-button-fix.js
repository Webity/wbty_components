/* ============================================================
 * bootstrap-button-fix.js v2.1.0-1.0
 * Fixes conflicts between jquery-ui buttons and bootstrap buttons
 * To use, first load bootstrap THEN load the jquery-ui-button module
 * in order to redefine the button function namespace.
 * Lastly, load this file in order to re-implement the functionality
 * using a different namespace.
 * ============================================================


 /* ============================================================
 * bootstrap-button.js v2.1.0
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function ($) {

    "use strict"; // jshint ;_;


    /* BUTTON PUBLIC CLASS DEFINITION
     * ============================== */

    var CraftyBootstrapButton = function (element, options) {
        this.$element = $(element)
        this.options = $.extend({}, $.fn.craftyBootstrapButton.defaults, options)
    }

    CraftyBootstrapButton.prototype.setState = function (state) {
        var d = 'disabled'
            , $el = this.$element
            , data = $el.data()
            , val = $el.is('input') ? 'val' : 'html'

        state = state + 'Text'
        data.resetText || $el.data('resetText', $el[val]())

        $el[val](data[state] || this.options[state])

        // push to event loop to allow forms to submit
        setTimeout(function () {
            state == 'loadingText' ?
                $el.addClass(d).attr(d, d) :
                $el.removeClass(d).removeAttr(d)
        }, 0)
    }

    CraftyBootstrapButton.prototype.toggle = function () {
        var $parent = this.$element.parent('[data-toggle="buttons-radio"]')

        $parent && $parent
            .find('.active')
            .removeClass('active')

        this.$element.toggleClass('active')
    }


    /* BUTTON PLUGIN DEFINITION
     * ======================== */

    $.fn.craftyBootstrapButton = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('craftyBootstrapButton')
                , options = typeof option == 'object' && option

            if (!data) $this.data('craftyBootstrapButton', (data = new CraftyBootstrapButton(this, options)))
            if (option == 'toggle') data.toggle()
            else if (option) data.setState(option)
        })
    }

    $.fn.craftyBootstrapButton.defaults = {
        loadingText: 'loading...'
    }

    $.fn.craftyBootstrapButton.Constructor = CraftyBootstrapButton


    /* BUTTON DATA-API
     * =============== */

    $(function () {
        $('body').on('click.button.data-api', '[data-toggle^=button]', function ( e ) {
            var $btn = $(e.target)
            if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
            $btn.craftyBootstrapButton('toggle')
        })
    })

}(window.jQuery);
