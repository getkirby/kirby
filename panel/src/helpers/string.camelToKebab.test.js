/**
 * @vitest-environment node
 */

import string from "./string.js";

describe("$helper.string.camelToKebab", () => {
  it("should convert camelCase", () => {
    const result = string.camelToKebab("helloWorld");
    expect(result).toBe("hello-world");
  });
});
