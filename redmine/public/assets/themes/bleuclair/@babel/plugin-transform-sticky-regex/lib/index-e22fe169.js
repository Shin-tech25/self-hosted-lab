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
    name: "transform-sticky-regex",
    visitor: {
      RegExpLiteral(path) {
        const {
          node
        } = path;
        if (!node.flags.includes("y")) return;
        path.replaceWith(_core.types.newExpression(_core.types.identifier("RegExp"), [_core.types.stringLiteral(node.pattern), _core.types.stringLiteral(node.flags)]));
      }
    }
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-sticky-regex/lib/index-af86208c.js.map
