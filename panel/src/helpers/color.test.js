import color from "./color.js";

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
