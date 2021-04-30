import Vue from "vue";

const files = import.meta.globEager('./components/*.vue');
const paths = Object.keys(files);

export async function meta() {
  const components = paths.map(async (key) => {

    const name = key.match(/\/([a-zA-Z]*)\.vue/)[1].toLowerCase();
    const html = await import(key + "?raw");
  
    return { key: key, name: name, html: html };
  });

  return Promise.all(components);
}

export async function register() {
  const components = paths.map(async (key) => {
    const name = key.match(/\/([a-zA-Z]*)\.vue/)[1].toLowerCase();
    Vue.component("sandbox-" + name, files[key].default);
  });

  Promise.all(components);
}