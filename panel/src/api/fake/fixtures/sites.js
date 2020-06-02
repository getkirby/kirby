import { blueprint } from "./blueprints.js";

export default [
  {
    blueprint: blueprint("site"),
    childIds: [
      "photography"
    ],
    id: "site",
    options: {
      changeTitle: true
    },
    title: "Maegazine",
  },
];
