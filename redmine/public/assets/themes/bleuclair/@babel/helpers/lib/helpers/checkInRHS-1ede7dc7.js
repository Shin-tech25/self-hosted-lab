"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = _checkInRHS;
function _checkInRHS(value) {
  if (Object(value) !== value) {
    throw TypeError("right-hand side of 'in' should be an object, got " + (value !== null ? typeof value : "null"));
  }
  return value;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/helpers/lib/helpers/checkInRHS-ae2b1938.js.map
