"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _helperPluginUtils = require("@babel/helper-plugin-utils");
var _default = (0, _helperPluginUtils.declare)(api => {
  api.assertVersion(7);
  const regex = /(\\*)([\u2028\u2029])/g;
  function replace(match, escapes, separator) {
    const isEscaped = escapes.length % 2 === 1;
    if (isEscaped) return match;
    return `${escapes}\\u${separator.charCodeAt(0).toString(16)}`;
  }
  return {
    name: "transform-json-strings",
    inherits: require("@babel/plugin-syntax-json-strings").default,
    visitor: {
      "DirectiveLiteral|StringLiteral"({
        node
      }) {
        const {
          extra
        } = node;
        if (!(extra != null && extra.raw)) return;
        extra.raw = extra.raw.replace(regex, replace);
      }
    }
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-json-strings/lib/index-8a164596.js.map
