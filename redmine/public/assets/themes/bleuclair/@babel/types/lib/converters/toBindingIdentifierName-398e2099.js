"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = toBindingIdentifierName;
var _toIdentifier = require("./toIdentifier.js");
function toBindingIdentifierName(name) {
  name = (0, _toIdentifier.default)(name);
  if (name === "eval" || name === "arguments") name = "_" + name;
  return name;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/converters/toBindingIdentifierName-6399098b.js.map
