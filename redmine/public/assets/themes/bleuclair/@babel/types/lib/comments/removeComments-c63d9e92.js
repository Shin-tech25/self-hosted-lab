"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = removeComments;
var _index = require("../constants/index.js");
function removeComments(node) {
  _index.COMMENT_KEYS.forEach(key => {
    node[key] = null;
  });
  return node;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/comments/removeComments-7df2b143.js.map
