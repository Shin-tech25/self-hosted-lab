"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = isSpecifierDefault;
var _index = require("./generated/index.js");
function isSpecifierDefault(specifier) {
  return (0, _index.isImportDefaultSpecifier)(specifier) || (0, _index.isIdentifier)(specifier.imported || specifier.exported, {
    name: "default"
  });
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/validators/isSpecifierDefault-8eb3c431.js.map
