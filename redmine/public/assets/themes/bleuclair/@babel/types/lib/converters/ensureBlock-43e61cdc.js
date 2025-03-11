"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = ensureBlock;
var _toBlock = require("./toBlock.js");
function ensureBlock(node, key = "body") {
  const result = (0, _toBlock.default)(node[key], node);
  node[key] = result;
  return result;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/converters/ensureBlock-48670c9a.js.map
