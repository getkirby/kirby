/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import sort from "./sort.js";

describe.concurrent("$helper.sort()", () => {
  it("should sort", () => {
    let users = [
      { username: "Homer" },
      { username: "Marge" },
      { username: "Bart" },
      { username: "Lisa" },
      { username: "Maggie" }
    ];

    const expected = [
      { username: "Bart" },
      { username: "Homer" },
      { username: "Lisa" },
      { username: "Maggie" },
      { username: "Marge" }
    ];

    const sorter = sort();

    users.sort((a, b) => {
      return sorter(a.username, b.username);
    });

    expect(users).toEqual(expected);
  });
});
