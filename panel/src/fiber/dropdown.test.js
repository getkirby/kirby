/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dropdown from "./dropdown.js";

describe.concurrent("$dropdown()", () => {
  it("should load the dropdown", async () => {
    const expected = [{ text: "Option A" }, { text: "Option B" }];

    const vue = {
      $fiber: {
        async request(path, options) {
          expect(path).toBe("dropdowns/test");
          expect(options.type).toBe("$dropdown");

          return {
            options: expected
          };
        }
      }
    };

    await dropdown.call(
      vue,
      "test"
    )((options) => {
      expect(options).toEqual(expected);
    });
  });

  it("should return false if the response is invalid", async () => {
    const vue = {
      $fiber: {
        async request() {
          return false;
        }
      }
    };

    const result = await dropdown.call(vue, "test")();
    expect(result).toBe(false);
  });

  it("should throw an exception if the dropdown is empty", async () => {
    const vue = {
      $fiber: {
        async request() {
          return {
            options: []
          };
        }
      }
    };

    try {
      await dropdown.call(vue, "test")();
    } catch (e) {
      expect(e.message).toBe("The dropdown is empty");
    }
  });

  it("should throw an exception if the options are not an array", async () => {
    const vue = {
      $fiber: {
        async request() {
          return {
            options: "invalid"
          };
        }
      }
    };

    try {
      await dropdown.call(vue, "test")();
    } catch (e) {
      expect(e.message).toBe("The dropdown is empty");
    }
  });

  it("should handle dialogs in dropdown options", async () => {
    const vue = {
      $dialog(url, options) {
        return { url, options };
      },
      $fiber: {
        async request() {
          return {
            options: [
              {
                text: "Option A",
                dialog: "test-a"
              },
              {
                text: "Option B",
                dialog: {
                  url: "test-b",
                  query: {
                    foo: "bar"
                  }
                }
              }
            ]
          };
        }
      }
    };

    await dropdown.call(
      vue,
      "test"
    )((options) => {
      // option.dialog = simple string
      expect(options[0].click()).toEqual({
        options: {},
        url: "test-a"
      });

      // option.dialog = options object
      expect(options[1].click()).toEqual({
        options: {
          query: {
            foo: "bar"
          },
          url: "test-b"
        },
        url: "test-b"
      });
    });
  });
});
