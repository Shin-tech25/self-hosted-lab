"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _index = require("../validators/generated/index.js");
var _default = toExpression;
exports.default = _default;
function toExpression(node) {
  if ((0, _index.isExpressionStatement)(node)) {
    node = node.expression;
  }
  if ((0, _index.isExpression)(node)) {
    return node;
  }
  if ((0, _index.isClass)(node)) {
    node.type = "ClassExpression";
  } else if ((0, _index.isFunction)(node)) {
    node.type = "FunctionExpression";
  }
  if (!(0, _index.isExpression)(node)) {
    throw new Error(`cannot turn ${node.type} to an expression`);
  }
  return node;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/converters/toExpression-538bec01.js.map
