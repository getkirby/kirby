export default {
  toolbar: {
    icon: "italic",
    label: "Italic",
    action: "toggleMark",
    args: ["italic"]
  },
  parseDOM: [
    { tag: "i" },
    { tag: "em" },
    { style: "font-style=italic" }
  ],
  toDOM() {
    return ["em", 0];
  }
};
