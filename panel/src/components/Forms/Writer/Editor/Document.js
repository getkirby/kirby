import { DOMParser } from "prosemirror-model";

export default function (schema, html, code) {

  let dom = document.createElement("div");

  html = html || "";

  if (code) {
    dom.innerHTML = "<pre><code style='white-space: pre-wrap'></code></pre>";
    dom.querySelector("code").appendChild(document.createTextNode(html));
  } else {
    dom.innerHTML = html;
  }

  return DOMParser.fromSchema(schema).parse(dom);

};
