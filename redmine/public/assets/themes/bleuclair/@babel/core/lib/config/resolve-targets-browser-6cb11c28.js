"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.resolveBrowserslistConfigFile = resolveBrowserslistConfigFile;
exports.resolveTargets = resolveTargets;
function _helperCompilationTargets() {
  const data = require("@babel/helper-compilation-targets");
  _helperCompilationTargets = function () {
    return data;
  };
  return data;
}
function resolveBrowserslistConfigFile(browserslistConfigFile, configFilePath) {
  return undefined;
}
function resolveTargets(options, root) {
  const optTargets = options.targets;
  let targets;
  if (typeof optTargets === "string" || Array.isArray(optTargets)) {
    targets = {
      browsers: optTargets
    };
  } else if (optTargets) {
    if ("esmodules" in optTargets) {
      targets = Object.assign({}, optTargets, {
        esmodules: "intersect"
      });
    } else {
      targets = optTargets;
    }
  }
  return (0, _helperCompilationTargets().default)(targets, {
    ignoreBrowserslistConfig: true,
    browserslistEnv: options.browserslistEnv
  });
}
0 && 0;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/core/lib/config/resolve-targets-browser-429fb080.js.map
