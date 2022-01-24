import { describe, expect, it } from "vitest";
import string from "./string.js";

describe("$helper.string.random", () => {
  it("should create a random string", () => {
    const result = string.random(8);
    expect(result).toMatch(/[a-z0-9]{8}/i);
  });
});
