/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { pad } from "./string.js";

describe.concurrent("$helper.string.pad()", () => {
  it("should add default padding", () => {
    expect(pad(1)).toBe("01");
  });

  it("should not add padding", () => {
    expect(pad(10)).toBe("10");
    expect(pad(120)).toBe("120");
  });

  it("should add padding to zero", () => {
    expect(pad(0)).toBe("00");
  });

  it("should apply custom padding", () => {
    expect(pad(1, 3)).toBe("001");
    expect(pad(0, 3)).toBe("000");
  });

  it("should not apply custom padding", () => {
    expect(pad(123, 3)).toBe("123");
    expect(pad(1234, 3)).toBe("1234");
  });
});
