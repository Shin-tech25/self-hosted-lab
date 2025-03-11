"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = isLet;
var _index = require("./generated/index.js");
var _index2 = require("../constants/index.js");
function isLet(node) {
  return (0, _index.isVariableDeclaration)(node) && (node.kind !== "var" || node[_index2.BLOCK_SCOPED_SYMBOL]);
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/validators/isLet-13296f37.js.map
