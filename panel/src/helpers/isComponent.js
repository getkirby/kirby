import Vue from "vue";

/**
 * Checks if the coponent is registered globally
 * @param {string} name component name
 * @returns {bool}
 */
export default (name) => {
  if (Vue.options.components[name] !== undefined) {
    return true;
  }

  return false;
};
