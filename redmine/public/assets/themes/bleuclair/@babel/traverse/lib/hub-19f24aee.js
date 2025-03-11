"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
class Hub {
  getCode() {}
  getScope() {}
  addHelper() {
    throw new Error("Helpers are not supported by the default hub.");
  }
  buildError(node, msg, Error = TypeError) {
    return new Error(msg);
  }
}
exports.default = Hub;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/traverse/lib/hub-d04c0d93.js.map
