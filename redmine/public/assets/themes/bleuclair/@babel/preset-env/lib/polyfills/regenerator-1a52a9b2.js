"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = _default;
var _utils = require("./utils.js");
function isRegeneratorSource(source) {
  return source === "regenerator-runtime/runtime" || source === "regenerator-runtime/runtime.js";
}
function _default() {
  const visitor = {
    ImportDeclaration(path) {
      if (isRegeneratorSource((0, _utils.getImportSource)(path))) {
        this.regeneratorImportExcluded = true;
        path.remove();
      }
    },
    Program(path) {
      path.get("body").forEach(bodyPath => {
        if (isRegeneratorSource((0, _utils.getRequireSource)(bodyPath))) {
          this.regeneratorImportExcluded = true;
          bodyPath.remove();
        }
      });
    }
  };
  return {
    name: "preset-env/remove-regenerator",
    visitor,
    pre() {
      this.regeneratorImportExcluded = false;
    },
    post() {
      if (this.opts.debug && this.regeneratorImportExcluded) {
        let filename = this.file.opts.filename;
        if (process.env.BABEL_ENV === "test") {
          filename = filename.replace(/\\/g, "/");
        }
        console.log(`\n[${filename}] Based on your targets, regenerator-runtime import excluded.`);
      }
    }
  };
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/preset-env/lib/polyfills/regenerator-c36fb34f.js.map
