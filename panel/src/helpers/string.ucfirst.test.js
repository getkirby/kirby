/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import string from "./string.js";

describe.concurrent("$helper.string.ucfirst", () => {
  it("should convert first character to uppercase", () => {
    const result = string.ucfirst("hello");
    expect(result).toBe("Hello");
  });

  it("should convert single character to uppercase", () => {
    const result = string.ucfirst("h");
    expect(result).toBe("H");
  });

  it("should ignore invalid input", () => {
    const result = string.ucfirst(0);
    expect(result).toBe("0");
  });
});
