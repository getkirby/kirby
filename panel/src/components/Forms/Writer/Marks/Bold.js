export default {
  toolbar: {
    icon: "bold",
    label: "Bold",
    action: "toggleMark",
    args: ["bold"]
  },
  parseDOM: [
    { tag: "strong" },
    { tag: "b", getAttrs: node => node.style.fontWeight != "normal" && null },
    { style: "font-weight", getAttrs: value => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null }
  ],
  toDOM() {
    return ["strong", 0]
  },
};
