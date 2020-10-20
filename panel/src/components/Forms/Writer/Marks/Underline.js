export default {
  toolbar: {
    icon: "underline",
    label: "Underline",
    action: "toggleMark",
    args: ["underline"]
  },
  parseDOM: [
    { tag: "u" },
    { style: "text-decoration=underline" }
  ],
  toDOM() {
    return ["u", 0];
  }
};
