/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { slug } from "./string.js";

describe.concurrent("$helper.string.slug()", () => {
  it("should replace spaces", () => {
    const result = slug("a b c");
    expect(result).toBe("a-b-c");
  });

  it("should replace slashes with dashes", () => {
    const result = slug("a/b/c");
    expect(result).toBe("a-b-c");
  });

  it("should replace slashes with custom separator", () => {
    const result = slug("a/b/c", [], [], "%");
    expect(result).toBe("a%b%c");
  });

  it("should replace non-allowed characters", () => {
    const result = slug("æöß");
    expect(result).toBe("");
  });

  it("should replace non-word characters", () => {
    const result = slug("@a.b*c!");
    expect(result).toBe("a-b-c");
  });

  it("should trim correctly", () => {
    const result = slug(" abc ");
    expect(result).toBe("abc");
  });

  it("should remove non-asci characters", () => {
    const result = slug("❤️");
    expect(result).toBe("");
  });

  it("should remove double seperators", () => {
    const result = slug("a--b  c");
    expect(result).toBe("a-b-c");
  });

  it("should apply rules", () => {
    const rules = [
      { å: "a" },
      { á: "a" },
      { ö: "oe" },
      { ß: "ss" },
      { İ: "i" }
    ];

    const result = slug("åöß", rules);
    expect(result).toBe("aoess");
  });

  it("should handle plus signs", () => {
    const rules = [{ "+": "-plus-" }];

    const result = slug("1+1", rules);
    expect(result).toBe("1-plus-1");
  });

  it("handles asterisks", () => {
    const resultA = slug("***");
    expect(resultA).toBe("");

    const resultB = slug("***a***b***");
    expect(resultB).toBe("a-b");
  });

  it("should return empty string when no param sent", () => {
    const result = slug();
    expect(result).toBe("");
  });

  it("should return empty string when null param sent", () => {
    const result = slug(null);
    expect(result).toBe("");
  });

  it("should return empty string when undefined param sent", () => {
    const result = slug(undefined);
    expect(result).toBe("");
  });
});
