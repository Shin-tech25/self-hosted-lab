"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = getBindingIdentifiers;
var _index = require("../validators/generated/index.js");
function getBindingIdentifiers(node, duplicates, outerOnly) {
  const search = [].concat(node);
  const ids = Object.create(null);
  while (search.length) {
    const id = search.shift();
    if (!id) continue;
    const keys = getBindingIdentifiers.keys[id.type];
    if ((0, _index.isIdentifier)(id)) {
      if (duplicates) {
        const _ids = ids[id.name] = ids[id.name] || [];
        _ids.push(id);
      } else {
        ids[id.name] = id;
      }
      continue;
    }
    if ((0, _index.isExportDeclaration)(id) && !(0, _index.isExportAllDeclaration)(id)) {
      if ((0, _index.isDeclaration)(id.declaration)) {
        search.push(id.declaration);
      }
      continue;
    }
    if (outerOnly) {
      if ((0, _index.isFunctionDeclaration)(id)) {
        search.push(id.id);
        continue;
      }
      if ((0, _index.isFunctionExpression)(id)) {
        continue;
      }
    }
    if (keys) {
      for (let i = 0; i < keys.length; i++) {
        const key = keys[i];
        const nodes = id[key];
        if (nodes) {
          Array.isArray(nodes) ? search.push(...nodes) : search.push(nodes);
        }
      }
    }
  }
  return ids;
}
getBindingIdentifiers.keys = {
  DeclareClass: ["id"],
  DeclareFunction: ["id"],
  DeclareModule: ["id"],
  DeclareVariable: ["id"],
  DeclareInterface: ["id"],
  DeclareTypeAlias: ["id"],
  DeclareOpaqueType: ["id"],
  InterfaceDeclaration: ["id"],
  TypeAlias: ["id"],
  OpaqueType: ["id"],
  CatchClause: ["param"],
  LabeledStatement: ["label"],
  UnaryExpression: ["argument"],
  AssignmentExpression: ["left"],
  ImportSpecifier: ["local"],
  ImportNamespaceSpecifier: ["local"],
  ImportDefaultSpecifier: ["local"],
  ImportDeclaration: ["specifiers"],
  ExportSpecifier: ["exported"],
  ExportNamespaceSpecifier: ["exported"],
  ExportDefaultSpecifier: ["exported"],
  FunctionDeclaration: ["id", "params"],
  FunctionExpression: ["id", "params"],
  ArrowFunctionExpression: ["params"],
  ObjectMethod: ["params"],
  ClassMethod: ["params"],
  ClassPrivateMethod: ["params"],
  ForInStatement: ["left"],
  ForOfStatement: ["left"],
  ClassDeclaration: ["id"],
  ClassExpression: ["id"],
  RestElement: ["argument"],
  UpdateExpression: ["argument"],
  ObjectProperty: ["value"],
  AssignmentPattern: ["left"],
  ArrayPattern: ["elements"],
  ObjectPattern: ["properties"],
  VariableDeclaration: ["declarations"],
  VariableDeclarator: ["id"]
};

//# sourceMappingURL=/assets/themes/bleuclair/@babel/types/lib/retrievers/getBindingIdentifiers-cbe970cd.js.map
