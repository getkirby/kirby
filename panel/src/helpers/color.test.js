import color from "./color.js";

const data = {
  "should return HEX": [
    ["#fff", "#fff"],
    ["#bababa", "#bababa"]
  ],
  "should return CSS variables": [
    ["var(--color-white)", "var(--color-white)"],
    ["var(--color-blue-200)", "var(--color-blue-200)"]
  ],
  "should parse CSS colors": [
    ["white", "var(--color-white)"],
    ["blue-200", "var(--color-blue-200)"]
  ],
  "should convert pattern": [
    ["pattern", "var(--color-gray-800) var(--bg-pattern)"]
  ]
};

describe("$helper.css.color()", () => {
  for (const test in data) {
    it(test, () => {
      for (const exp of data[test]) {
        expect(color(exp[0])).toBe(exp[1]);
      }
    });
  }
});
