"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.toGetWrapperPayload = toGetWrapperPayload;
exports.wrapReference = wrapReference;
var _core = require("@babel/core");
var _normalizeAndLoadMetadata = require("./normalize-and-load-metadata.js");
function toGetWrapperPayload(lazy) {
  return (source, metadata) => {
    if (lazy === false) return null;
    if ((0, _normalizeAndLoadMetadata.isSideEffectImport)(metadata) || metadata.reexportAll) return null;
    if (lazy === true) {
      return /\./.test(source) ? null : "lazy";
    }
    if (Array.isArray(lazy)) {
      return lazy.indexOf(source) === -1 ? null : "lazy";
    }
    if (typeof lazy === "function") {
      return lazy(source) ? "lazy" : null;
    }
    throw new Error(`.lazy must be a boolean, string array, or function`);
  };
}
function wrapReference(ref, payload) {
  if (payload === "lazy") return _core.types.callExpression(ref, []);
  return null;
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/helper-module-transforms/lib/lazy-modules-aa623886.js.map
