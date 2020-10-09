import Vue from "vue";
const req = require.context("./components", true, /\.vue$/i);
let components = [];

req.keys().map((key) => {
  let name = key.match(/\w+/)[0];
  components.push({
    key: key,
    name: name,
    html: require("!!raw-loader!" + __dirname + "/components/" + key.replace("./", "")).default
  });
  Vue.component("Sandbox" + name, req(key).default);
});

export default components;
