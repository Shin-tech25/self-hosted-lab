"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const dynamicDefaults_1 = __importDefault(require("../definitions/dynamicDefaults"));
const dynamicDefaults = (ajv) => ajv.addKeyword((0, dynamicDefaults_1.default)());
exports.default = dynamicDefaults;
module.exports = dynamicDefaults;
//# sourceMappingURL=/assets/themes/bleuclair/ajv-keywords/dist/keywords/dynamicDefaults-a8426d1a.js.map