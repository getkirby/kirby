/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { clone } from "./object.js";

describe.concurrent("$helper.object.clone()", () => {
  it("should clone the object", () => {
    const object = {
      a: "A",
      b: "B"
    };

    expect(clone(object)).toEqual(object);
  });

  it("should return nothing when provided nothing", () => {
    expect(clone()).toEqual(undefined);
  });
});
