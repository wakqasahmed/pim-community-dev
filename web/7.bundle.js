webpackJsonp([7],{

/***/ 149:
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/common/property.js ***!
  \****************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Property accessor extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = function () {
    return {
        /**
         * Access a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {mixed}  defaultValue
         *
         * @return {mixed}
         */
        accessProperty: function (data, path, defaultValue) {
            defaultValue = defaultValue || null;
            var pathPart = path.split('.');

            if (undefined === data[pathPart[0]]) {
                return defaultValue;
            }

            return 1 === pathPart.length ?
                data[pathPart[0]] :
                this.accessProperty(data[pathPart[0]], pathPart.slice(1).join('.'), defaultValue);
        },

        /**
         * Update a property in an object
         *
         * @param {object} data
         * @param {string} path
         * @param {mixed}  value
         *
         * @return {mixed}
         */
        updateProperty: function (data, path, value) {
            var pathPart = path.split('.');

            data[pathPart[0]] = 1 === pathPart.length ?
                value :
                this.updateProperty(data[pathPart[0]], pathPart.slice(1).join('.'), value);

            return data;
        }
    };
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),

/***/ 202:
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/meta/status.html ***!
  \******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<span title=\"<%- label %>: <%- value %>\" id=\"status\">\n    <%- label %>: <%- value %>\n</span>\n"

/***/ }),

/***/ 234:
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/meta/status.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Updated at extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 4),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! text-loader!pim/template/form/meta/status */ 202),
        __webpack_require__(/*! pim/common/property */ 149)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, __, BaseForm, mediator, formTemplate, propertyAccessor) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'AknTitleContainer-metaItem',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;
                this.label   = __(this.config.label);
                this.value   = __(this.config.value);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var status = this.getFormData();
                var value = this.config.valuePath ?
                    propertyAccessor.accessProperty(status, this.config.valuePath) : '';

                this.$el.html(this.template({
                    label: this.label,
                    value: value
                }));

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});