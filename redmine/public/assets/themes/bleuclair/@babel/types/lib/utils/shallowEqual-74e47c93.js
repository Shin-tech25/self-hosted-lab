"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = shallowEqual;
function shallowEqual(actual, expected) {
  const keys = Object.keys(expected);
  for (const key of keys) {
    if (actual[key] !== expected[key]) {
      return false;
    }
  }
  return true;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/utils/shallowEqual-5f65f570.js.map
