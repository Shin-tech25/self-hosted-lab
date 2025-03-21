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
    name: "transform-reserved-words",
    visitor: {
      "BindingIdentifier|ReferencedIdentifier"(path) {
        if (!_core.types.isValidES3Identifier(path.node.name)) {
          path.scope.rename(path.node.name);
        }
      }
    }
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-reserved-words/lib/index-a56fcda1.js.map
