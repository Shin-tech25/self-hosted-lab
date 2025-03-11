"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const anyRequired_1 = __importDefault(require("../definitions/anyRequired"));
const anyRequired = (ajv) => ajv.addKeyword((0, anyRequired_1.default)());
exports.default = anyRequired;
module.exports = anyRequired;
//# sourceMappingURL=/assets/themes/bleuclair/ajv-keywords/dist/keywords/anyRequired-3e5bb476.js.map