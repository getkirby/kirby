import { describe, expect, it } from "vitest";
import string from "./string.js";

describe("$helper.string.uuid", () => {
  it("creates a unique id", () => {
    // i.e. 619e59d3-d4a5-461a-a7bf-580136956726
    expect(string.uuid()).toMatch(
      /[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/
    );
  });
});
