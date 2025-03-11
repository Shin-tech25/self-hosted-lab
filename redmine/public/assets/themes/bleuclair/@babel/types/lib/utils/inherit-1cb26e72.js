"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = inherit;
function inherit(key, child, parent) {
  if (child && parent) {
    child[key] = Array.from(new Set([].concat(child[key], parent[key]).filter(Boolean)));
  }
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/utils/inherit-a6d2f4bd.js.map
