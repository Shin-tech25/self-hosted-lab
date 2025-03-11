"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;
var _helperPluginUtils = require("@babel/helper-plugin-utils");
var _helperCreateClassFeaturesPlugin = require("@babel/helper-create-class-features-plugin");
var _default = (0, _helperPluginUtils.declare)((api, options) => {
  api.assertVersion(7);
  return (0, _helperCreateClassFeaturesPlugin.createClassFeaturePlugin)({
    name: "transform-private-methods",
    api,
    feature: _helperCreateClassFeaturesPlugin.FEATURES.privateMethods,
    loose: options.loose,
    manipulateOptions(opts, parserOpts) {
      parserOpts.plugins.push("classPrivateMethods");
    }
  });
});
exports.default = _default;

//# sourceMappingURL=/assets/themes/bleuclair/@babel/plugin-transform-private-methods/lib/index-ba558f1f.js.map
