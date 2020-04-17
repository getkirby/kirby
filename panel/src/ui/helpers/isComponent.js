import Vue from "vue";

export default (name) => {
  if (Vue.options.components["k-" + name] !== undefined) {
    return true;
  }

  return false;
};
