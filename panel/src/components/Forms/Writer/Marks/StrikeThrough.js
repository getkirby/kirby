export default {
  toolbar: {
    icon: "strikethrough",
    label: "Strike-through",
    action: "toggleMark",
    args: ["strikeThrough"]
  },
  parseDOM: [
    { tag: "del" },
    { style: "text-decoration=line-through" }
  ],
  toDOM() {
    return ["del", 0];
  }
};
