/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import search from "./search.js";

describe.concurrent("$search()", () => {
  it("should search", async () => {
    const vue = {
      $fiber: {
        request(path, options) {
          expect(path).toBe("search/pages");
          expect(options.query.query).toBe("Blog");
          expect(options.type).toBe("$search");
          expect(options.headers).toEqual({ "x-test": "foo" });
        }
      }
    };

    await search.call(vue, "pages", "Blog", {
      headers: { "x-test": "foo" }
    });
  });
});
