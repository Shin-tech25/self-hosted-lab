"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const oneRequired_1 = __importDefault(require("../definitions/oneRequired"));
const oneRequired = (ajv) => ajv.addKeyword((0, oneRequired_1.default)());
exports.default = oneRequired;
module.exports = oneRequired;
//# sourceMappingURL=/assets/themes/bleuclair/ajv-keywords/dist/keywords/oneRequired-f86335d8.js.map