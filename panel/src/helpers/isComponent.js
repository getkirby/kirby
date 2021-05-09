import Vue from "vue";

export default (name) => {
  if (Vue.component(name) !== undefined) {
    return true;
  }

  return false;
};
