import { describe, expect, it } from "vitest";
import string from "./string.js";

describe("$helper.string.ucwords", () => {
  it("should convert first character in all words to uppercase", () => {
    const result = string.ucwords("hello world this is a test");
    expect(result).toBe("Hello World This Is A Test");
  });
});
