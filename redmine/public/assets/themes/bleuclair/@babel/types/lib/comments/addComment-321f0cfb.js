"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = addComment;
var _addComments = require("./addComments.js");
function addComment(node, type, content, line) {
  return (0, _addComments.default)(node, type, [{
    type: line ? "CommentLine" : "CommentBlock",
    value: content
  }]);
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/comments/addComment-87661fa9.js.map
