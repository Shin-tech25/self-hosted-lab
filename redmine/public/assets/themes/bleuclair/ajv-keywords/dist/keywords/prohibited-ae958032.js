"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const prohibited_1 = __importDefault(require("../definitions/prohibited"));
const prohibited = (ajv) => ajv.addKeyword((0, prohibited_1.default)());
exports.default = prohibited;
module.exports = prohibited;
//# sourceMappingURL=/assets/themes/bleuclair/ajv-keywords/dist/keywords/prohibited-d23a7b0b.js.map