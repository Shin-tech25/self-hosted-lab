"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _helperPluginUtils = require("@babel/helper-plugin-utils");
var _helperBuilderBinaryAssignmentOperatorVisitor = require("@babel/helper-builder-binary-assignment-operator-visitor");
var _core = require("@babel/core");
var _default = (0, _helperPluginUtils.declare)(api => {
  api.assertVersion(7);
  return {
    name: "transform-exponentiation-operator",
    visitor: (0, _helperBuilderBinaryAssignmentOperatorVisitor.default)({
      operator: "**",
      build(left, right) {
        return _core.types.callExpression(_core.types.memberExpression(_core.types.identifier("Math"), _core.types.identifier("pow")), [left, right]);
      }
    })
  };
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-exponentiation-operator/lib/index-5060d7f9.js.map
