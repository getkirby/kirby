import Vue from "vue";

const files = import.meta.globEager('./components/*.vue');

let components = Object.keys(files).map(async (key) => {

  const name = key.match(/\/([a-zA-Z]*)\.vue/)[1].toLowerCase();
  const html = await import(key + "?raw");
 
  Vue.component("sandbox-" + name, files[key].default);

  return {
    key: key,
    name: name,
    html: html
  };
});

export default components;
