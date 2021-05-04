import Vue from "vue";
let components = [];

const files = import.meta.globEager('./components/*.vue');

Object.keys(files).map((key) => {
  const name = key.match(/\/([a-zA-Z]*)\.vue/)[1].toLowerCase();
 
  components.push({
    key: key,
    name: name,
    // html: import('!raw-loader!' + key).default
  });

  Vue.component("Sandbox" + name, files[key].default);
});

export default components;
