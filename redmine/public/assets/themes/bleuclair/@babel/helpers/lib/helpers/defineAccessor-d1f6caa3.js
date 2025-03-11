"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = _defineAccessor;
function _defineAccessor(type, obj, key, fn) {
  var desc = {
    configurable: true,
    enumerable: true
  };
  desc[type] = fn;
  return Object.defineProperty(obj, key, desc);
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/helpers/lib/helpers/defineAccessor-1d11737c.js.map
