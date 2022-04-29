/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { isEmpty } from "./object.js";

describe.concurrent("$helper.object.isEmpty()", () => {
  it("should detect empty values as true", () => {
    expect(isEmpty(undefined)).toBeTruthy();
    expect(isEmpty(null)).toBeTruthy();
    expect(isEmpty("")).toBeTruthy();
  });

  it("should detect empty object as true", () => {
    expect(isEmpty({})).toBeTruthy();
  });

  it("should detect empty countables as true", () => {
    expect(isEmpty([])).toBeTruthy();
  });

  it("should detect non-empty as false", () => {
    expect(isEmpty([1, 2, 3])).toBeFalsy();
    expect(isEmpty(["a", "b", "c"])).toBeFalsy();
    expect(isEmpty("abc")).toBeFalsy();
    expect(isEmpty(7)).toBeFalsy();
    expect(isEmpty({ foo: null })).toBeFalsy();
  });
});
