"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = applyDecs2305;
var _checkInRHS = require("checkInRHS");
function createAddInitializerMethod(initializers, decoratorFinishedRef) {
  return function addInitializer(initializer) {
    assertNotFinished(decoratorFinishedRef, "addInitializer");
    assertCallable(initializer, "An initializer");
    initializers.push(initializer);
  };
}
function assertInstanceIfPrivate(has, target) {
  if (!has(target)) {
    throw new TypeError("Attempted to access private element on non-instance");
  }
}
function memberDec(dec, thisArg, name, desc, initializers, kind, isStatic, isPrivate, value, hasPrivateBrand, metadata) {
  var kindStr;
  switch (kind) {
    case 1:
      kindStr = "accessor";
      break;
    case 2:
      kindStr = "method";
      break;
    case 3:
      kindStr = "getter";
      break;
    case 4:
      kindStr = "setter";
      break;
    default:
      kindStr = "field";
  }
  var ctx = {
    kind: kindStr,
    name: isPrivate ? "#" + name : name,
    static: isStatic,
    private: isPrivate,
    metadata: metadata
  };
  var decoratorFinishedRef = {
    v: false
  };
  if (kind !== 0) {
    ctx.addInitializer = createAddInitializerMethod(initializers, decoratorFinishedRef);
  }
  var get, set;
  if (!isPrivate && (kind === 0 || kind === 2)) {
    get = function (target) {
      return target[name];
    };
    if (kind === 0) {
      set = function (target, v) {
        target[name] = v;
      };
    }
  } else if (kind === 2) {
    get = function (target) {
      assertInstanceIfPrivate(hasPrivateBrand, target);
      return desc.value;
    };
  } else {
    var t = kind === 0 || kind === 1;
    if (t || kind === 3) {
      if (isPrivate) {
        get = function (target) {
          assertInstanceIfPrivate(hasPrivateBrand, target);
          return desc.get.call(target);
        };
      } else {
        get = function (target) {
          return desc.get.call(target);
        };
      }
    }
    if (t || kind === 4) {
      if (isPrivate) {
        set = function (target, value) {
          assertInstanceIfPrivate(hasPrivateBrand, target);
          desc.set.call(target, value);
        };
      } else {
        set = function (target, value) {
          desc.set.call(target, value);
        };
      }
    }
  }
  var has = isPrivate ? hasPrivateBrand.bind() : function (target) {
    return name in target;
  };
  ctx.access = get && set ? {
    get: get,
    set: set,
    has: has
  } : get ? {
    get: get,
    has: has
  } : {
    set: set,
    has: has
  };
  try {
    return dec.call(thisArg, value, ctx);
  } finally {
    decoratorFinishedRef.v = true;
  }
}
function assertNotFinished(decoratorFinishedRef, fnName) {
  if (decoratorFinishedRef.v) {
    throw new Error("attempted to call " + fnName + " after decoration was finished");
  }
}
function assertCallable(fn, hint) {
  if (typeof fn !== "function") {
    throw new TypeError(hint + " must be a function");
  }
}
function assertValidReturnValue(kind, value) {
  var type = typeof value;
  if (kind === 1) {
    if (type !== "object" || value === null) {
      throw new TypeError("accessor decorators must return an object with get, set, or init properties or void 0");
    }
    if (value.get !== undefined) {
      assertCallable(value.get, "accessor.get");
    }
    if (value.set !== undefined) {
      assertCallable(value.set, "accessor.set");
    }
    if (value.init !== undefined) {
      assertCallable(value.init, "accessor.init");
    }
  } else if (type !== "function") {
    var hint;
    if (kind === 0) {
      hint = "field";
    } else if (kind === 5) {
      hint = "class";
    } else {
      hint = "method";
    }
    throw new TypeError(hint + " decorators must return a function or void 0");
  }
}
function curryThis1(fn) {
  return function () {
    return fn(this);
  };
}
function curryThis2(fn) {
  return function (value) {
    fn(this, value);
  };
}
function applyMemberDec(ret, base, decInfo, decoratorsHaveThis, name, kind, isStatic, isPrivate, initializers, hasPrivateBrand, metadata) {
  var decs = decInfo[0];
  if (!decoratorsHaveThis && !Array.isArray(decs)) {
    decs = [decs];
  }
  var desc, init, value;
  if (isPrivate) {
    if (kind === 0 || kind === 1) {
      desc = {
        get: curryThis1(decInfo[3]),
        set: curryThis2(decInfo[4])
      };
    } else {
      if (kind === 3) {
        desc = {
          get: decInfo[3]
        };
      } else if (kind === 4) {
        desc = {
          set: decInfo[3]
        };
      } else {
        desc = {
          value: decInfo[3]
        };
      }
    }
  } else if (kind !== 0) {
    desc = Object.getOwnPropertyDescriptor(base, name);
  }
  if (kind === 1) {
    value = {
      get: desc.get,
      set: desc.set
    };
  } else if (kind === 2) {
    value = desc.value;
  } else if (kind === 3) {
    value = desc.get;
  } else if (kind === 4) {
    value = desc.set;
  }
  var newValue, get, set;
  var inc = decoratorsHaveThis ? 2 : 1;
  for (var i = decs.length - 1; i >= 0; i -= inc) {
    var dec = decs[i];
    newValue = memberDec(dec, decoratorsHaveThis ? decs[i - 1] : undefined, name, desc, initializers, kind, isStatic, isPrivate, value, hasPrivateBrand, metadata);
    if (newValue !== void 0) {
      assertValidReturnValue(kind, newValue);
      var newInit;
      if (kind === 0) {
        newInit = newValue;
      } else if (kind === 1) {
        newInit = newValue.init;
        get = newValue.get || value.get;
        set = newValue.set || value.set;
        value = {
          get: get,
          set: set
        };
      } else {
        value = newValue;
      }
      if (newInit !== void 0) {
        if (init === void 0) {
          init = newInit;
        } else if (typeof init === "function") {
          init = [init, newInit];
        } else {
          init.push(newInit);
        }
      }
    }
  }
  if (kind === 0 || kind === 1) {
    if (init === void 0) {
      init = function (instance, init) {
        return init;
      };
    } else if (typeof init !== "function") {
      var ownInitializers = init;
      init = function (instance, init) {
        var value = init;
        for (var i = ownInitializers.length - 1; i >= 0; i--) {
          value = ownInitializers[i].call(instance, value);
        }
        return value;
      };
    } else {
      var originalInitializer = init;
      init = function (instance, init) {
        return originalInitializer.call(instance, init);
      };
    }
    ret.push(init);
  }
  if (kind !== 0) {
    if (kind === 1) {
      desc.get = value.get;
      desc.set = value.set;
    } else if (kind === 2) {
      desc.value = value;
    } else if (kind === 3) {
      desc.get = value;
    } else if (kind === 4) {
      desc.set = value;
    }
    if (isPrivate) {
      if (kind === 1) {
        ret.push(function (instance, args) {
          return value.get.call(instance, args);
        });
        ret.push(function (instance, args) {
          return value.set.call(instance, args);
        });
      } else if (kind === 2) {
        ret.push(value);
      } else {
        ret.push(function (instance, args) {
          return value.call(instance, args);
        });
      }
    } else {
      Object.defineProperty(base, name, desc);
    }
  }
}
function applyMemberDecs(Class, decInfos, instanceBrand, metadata) {
  var ret = [];
  var protoInitializers;
  var staticInitializers;
  var staticBrand;
  var existingProtoNonFields = new Map();
  var existingStaticNonFields = new Map();
  for (var i = 0; i < decInfos.length; i++) {
    var decInfo = decInfos[i];
    if (!Array.isArray(decInfo)) continue;
    var kind = decInfo[1];
    var name = decInfo[2];
    var isPrivate = decInfo.length > 3;
    var decoratorsHaveThis = kind & 16;
    var isStatic = !!(kind & 8);
    var base;
    var initializers;
    var hasPrivateBrand = instanceBrand;
    kind &= 7;
    if (isStatic) {
      base = Class;
      if (kind !== 0) {
        staticInitializers = staticInitializers || [];
        initializers = staticInitializers;
      }
      if (isPrivate && !staticBrand) {
        staticBrand = function (_) {
          return _checkInRHS(_) === Class;
        };
      }
      hasPrivateBrand = staticBrand;
    } else {
      base = Class.prototype;
      if (kind !== 0) {
        protoInitializers = protoInitializers || [];
        initializers = protoInitializers;
      }
    }
    if (kind !== 0 && !isPrivate) {
      var existingNonFields = isStatic ? existingStaticNonFields : existingProtoNonFields;
      var existingKind = existingNonFields.get(name) || 0;
      if (existingKind === true || existingKind === 3 && kind !== 4 || existingKind === 4 && kind !== 3) {
        throw new Error("Attempted to decorate a public method/accessor that has the same name as a previously decorated public method/accessor. This is not currently supported by the decorators plugin. Property name was: " + name);
      }
      existingNonFields.set(name, !existingKind && kind > 2 ? kind : true);
    }
    applyMemberDec(ret, base, decInfo, decoratorsHaveThis, name, kind, isStatic, isPrivate, initializers, hasPrivateBrand, metadata);
  }
  pushInitializers(ret, protoInitializers);
  pushInitializers(ret, staticInitializers);
  return ret;
}
function pushInitializers(ret, initializers) {
  if (initializers) {
    ret.push(function (instance) {
      for (var i = 0; i < initializers.length; i++) {
        initializers[i].call(instance);
      }
      return instance;
    });
  }
}
function applyClassDecs(targetClass, classDecs, decoratorsHaveThis, metadata) {
  if (classDecs.length) {
    var initializers = [];
    var newClass = targetClass;
    var name = targetClass.name;
    var inc = decoratorsHaveThis ? 2 : 1;
    for (var i = classDecs.length - 1; i >= 0; i -= inc) {
      var decoratorFinishedRef = {
        v: false
      };
      try {
        var nextNewClass = classDecs[i].call(decoratorsHaveThis ? classDecs[i - 1] : undefined, newClass, {
          kind: "class",
          name: name,
          addInitializer: createAddInitializerMethod(initializers, decoratorFinishedRef),
          metadata
        });
      } finally {
        decoratorFinishedRef.v = true;
      }
      if (nextNewClass !== undefined) {
        assertValidReturnValue(5, nextNewClass);
        newClass = nextNewClass;
      }
    }
    return [defineMetadata(newClass, metadata), function () {
      for (var i = 0; i < initializers.length; i++) {
        initializers[i].call(newClass);
      }
    }];
  }
}
function defineMetadata(Class, metadata) {
  return Object.defineProperty(Class, Symbol.metadata || Symbol.for("Symbol.metadata"), {
    configurable: true,
    enumerable: true,
    value: metadata
  });
}
function applyDecs2305(targetClass, memberDecs, classDecs, classDecsHaveThis, instanceBrand, parentClass) {
  if (arguments.length >= 6) {
    var parentMetadata = parentClass[Symbol.metadata || Symbol.for("Symbol.metadata")];
  }
  var metadata = Object.create(parentMetadata === void 0 ? null : parentMetadata);
  var e = applyMemberDecs(targetClass, memberDecs, instanceBrand, metadata);
  if (!classDecs.length) defineMetadata(targetClass, metadata);
  return {
    e: e,
    get c() {
      return applyClassDecs(targetClass, classDecs, classDecsHaveThis, metadata);
    }
  };
}

//# sourceMappingURL=/assets/themes/bleuclair/@babel/helpers/lib/helpers/applyDecs2305-d61aad29.js.map
