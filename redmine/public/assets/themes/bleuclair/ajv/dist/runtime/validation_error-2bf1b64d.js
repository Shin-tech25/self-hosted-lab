"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
class ValidationError extends Error {
    constructor(errors) {
        super("validation failed");
        this.errors = errors;
        this.ajv = this.validation = true;
    }
}
exports.default = ValidationError;
//# sourceMappingURL=/assets/themes/bleuclair/ajv/dist/runtime/validation_error-56bab951.js.map