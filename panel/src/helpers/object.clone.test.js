/**
 * @vitest-environment node
 */

import { clone } from "./object.js";

describe("$helper.object.clone()", () => {
  it("should clone the object", () => {
    const object = {
      a: "A",
      b: "B"
    };

    expect(clone(object)).toStrictEqual(object);
  });

  it("should return nothing when provided nothing", () => {
    expect(clone()).toEqual(undefined);
  });
});
