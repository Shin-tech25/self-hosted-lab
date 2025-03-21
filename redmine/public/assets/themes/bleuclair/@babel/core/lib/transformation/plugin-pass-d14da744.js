"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
class PluginPass {
  constructor(file, key, options) {
    this._map = new Map();
    this.key = void 0;
    this.file = void 0;
    this.opts = void 0;
    this.cwd = void 0;
    this.filename = void 0;
    this.key = key;
    this.file = file;
    this.opts = options || {};
    this.cwd = file.opts.cwd;
    this.filename = file.opts.filename;
  }
  set(key, val) {
    this._map.set(key, val);
  }
  get(key) {
    return this._map.get(key);
  }
  availableHelper(name, versionRange) {
    return this.file.availableHelper(name, versionRange);
  }
  addHelper(name) {
    return this.file.addHelper(name);
  }
  buildCodeFrameError(node, msg, _Error) {
    return this.file.buildCodeFrameError(node, msg, _Error);
  }
}
exports.default = PluginPass;
{
  PluginPass.prototype.getModuleName = function getModuleName() {
    return this.file.getModuleName();
  };
  PluginPass.prototype.addImport = function addImport() {
    this.file.addImport();
  };
}
0 && 0;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/core/lib/transformation/plugin-pass-7e7232c4.js.map
