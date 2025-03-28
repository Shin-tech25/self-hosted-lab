"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = removePropertiesDeep;
var _traverseFast = require("../traverse/traverseFast.js");
var _removeProperties = require("./removeProperties.js");
function removePropertiesDeep(tree, opts) {
  (0, _traverseFast.default)(tree, _removeProperties.default, opts);
  return tree;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/modifications/removePropertiesDeep-5bd8e76f.js.map
