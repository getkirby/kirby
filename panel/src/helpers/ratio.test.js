/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import ratio from "./ratio.js";

describe.concurrent("$helper.ratio()", () => {
  const data = {
    "should return default ratio": [[undefined, "66.67%"]],
    "should return padding for 16/9": [["16/9", "56.25%"]],
    "should return 100% on invalid fractions": [
      ["0/16", "100%"],
      ["16/0", "100%"]
    ],
    "should return 100% on invalid input": [
      [1, "100%"],
      [{}, "100%"]
    ]
  };

  for (const test in data) {
    it(test, () => {
      for (const exp of data[test]) {
        expect(ratio(exp[0])).toBe(exp[1]);
      }
    });
  }
});
