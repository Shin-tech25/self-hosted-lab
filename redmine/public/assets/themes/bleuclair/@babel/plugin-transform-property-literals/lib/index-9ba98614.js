"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _helperPluginUtils = require("@babel/helper-plugin-utils");
var _core = require("@babel/core");
var _default = (0, _helperPluginUtils.declare)(api => {
  api.assertVersion(7);
  return {
    name: "transform-property-literals",
    visitor: {
      ObjectProperty: {
        exit({
          node
        }) {
          const key = node.key;
          if (!node.computed && _core.types.isIdentifier(key) && !_core.types.isValidES3Identifier(key.name)) {
            node.key = _core.types.stringLiteral(key.name);
          }
        }
      }
    }
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-property-literals/lib/index-00bd2149.js.map
