"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = buildMatchMemberExpression;
var _matchesPattern = require("./matchesPattern.js");
function buildMatchMemberExpression(match, allowPartial) {
  const parts = match.split(".");
  return member => (0, _matchesPattern.default)(member, parts, allowPartial);
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/validators/buildMatchMemberExpression-ad40e82a.js.map
