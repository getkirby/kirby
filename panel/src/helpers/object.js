/**
 * Clone provided object or array
 *
 * @param {Object|array} array
 * @returns  {Object|array}
 */
export function clone(array) {
  if (array === undefined) {
    return undefined;
  }

  return JSON.parse(JSON.stringify(array));
}

/**
 * Merges two objects
 *
 * @param {Object} target
 * @param {Object} source
 * @returns {Object}
 */
export function merge(target, source) {
  // Iterate through `source` properties and if an `Object` set property to merge of `target` and `source` properties
  for (const key of Object.keys(source)) {
    if (source[key] instanceof Object) {
      Object.assign(source[key], merge(target[key] || {}, source[key]));
    }
  }

  // Join `target` and modified `source`
  Object.assign(target || {}, source);
  return target;
}

export default {
  clone,
  merge
};
