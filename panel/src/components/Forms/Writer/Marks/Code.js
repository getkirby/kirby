export default {
  toolbar: {
    icon: "code",
    label: "Code",
    action: "toggleMark",
    args: ["code"]
  },
  parseDOM: [
    { tag: "code" }
  ],
  toDOM() {
    return ["code", 0];
  }
};
