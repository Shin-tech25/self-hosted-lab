"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = addComments;
function addComments(node, type, comments) {
  if (!comments || !node) return node;
  const key = `${type}Comments`;
  if (node[key]) {
    if (type === "leading") {
      node[key] = comments.concat(node[key]);
    } else {
      node[key].push(...comments);
    }
  } else {
    node[key] = comments;
  }
  return node;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/comments/addComments-95bc647c.js.map
