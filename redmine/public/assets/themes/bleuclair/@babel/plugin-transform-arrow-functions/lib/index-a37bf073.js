"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _helperPluginUtils = require("@babel/helper-plugin-utils");
var _default = (0, _helperPluginUtils.declare)((api, options) => {
  var _api$assumption;
  api.assertVersion(7);
  const noNewArrows = (_api$assumption = api.assumption("noNewArrows")) != null ? _api$assumption : !options.spec;
  return {
    name: "transform-arrow-functions",
    visitor: {
      ArrowFunctionExpression(path) {
        if (!path.isArrowFunctionExpression()) return;
        {
          path.arrowFunctionToExpression({
            allowInsertArrow: false,
            noNewArrows,
            specCompliant: !noNewArrows
          });
        }
      }
    }
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-arrow-functions/lib/index-3b018956.js.map
