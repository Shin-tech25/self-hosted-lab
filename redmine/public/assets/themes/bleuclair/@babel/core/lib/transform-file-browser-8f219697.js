"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.transformFile = void 0;
exports.transformFileAsync = transformFileAsync;
exports.transformFileSync = transformFileSync;
const transformFile = function transformFile(filename, opts, callback) {
  if (typeof opts === "function") {
    callback = opts;
  }
  callback(new Error("Transforming files is not supported in browsers"), null);
};
exports.transformFile = transformFile;
function transformFileSync() {
  throw new Error("Transforming files is not supported in browsers");
}
function transformFileAsync() {
  return Promise.reject(new Error("Transforming files is not supported in browsers"));
}
0 && 0;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/core/lib/transform-file-browser-e5068f55.js.map
