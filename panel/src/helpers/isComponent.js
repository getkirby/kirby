import Vue from "vue";

export default (name) => {
  if (Vue.options.components[name] !== undefined) {
    return true;
  }

  return false;
};
