"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = isNode;
var _index = require("../definitions/index.js");
function isNode(node) {
  return !!(node && _index.VISITOR_KEYS[node.type]);
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/validators/isNode-0d4a9750.js.map
