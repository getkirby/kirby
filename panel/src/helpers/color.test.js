import { describe, expect, it } from "vitest";
import color from "./color.js";

describe.concurrent("$helper.css.color()", () => {
  document.documentElement.style.setProperty("--color-white", "white");
  document.documentElement.style.setProperty("--color-blue-200", "blue-200");
  document.documentElement.style.setProperty(
    "--color-background",
    "background"
  );

  const data = {
    "should return nothing for non-strings": [
      [1, undefined],
      [null, undefined]
    ],
    "should convert pattern": [
      ["pattern", "var(--color-gray-800) var(--bg-pattern)"]
    ],
    "should resolve aliases to CSS colors": [
      ["white", "var(--color-white)"],
      ["blue-200", "var(--color-blue-200)"]
    ],
    "should return CSS variables": [
      ["var(--color-white)", "var(--color-white)"],
      ["var(--color-blue-200)", "var(--color-blue-200)"]
    ],
    "should return HEX": [
      ["#fff", "#fff"],
      ["#bababa", "#bababa"]
    ],
    "should return self with lowercase": [
      ["LightSeaGreen", "lightseagreen"],
      ["DarkSalmon", "darksalmon"],
      ["MediumSlateBlue", "mediumslateblue"],
      ["Background", "var(--color-background)"],
      ["#E2E2E2", "#e2e2e2"]
    ]
  };

  for (const test in data) {
    it(test, () => {
      for (const exp of data[test]) {
        expect(color(exp[0])).toBe(exp[1]);
      }
    });
  }
});
