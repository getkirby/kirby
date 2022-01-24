/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import string from "./string.js";

describe.concurrent("$helper.string.escapeHTML", () => {
  it("should escape HTML", () => {
    const result = string.escapeHTML(
      '<div class="button">This text includes `&<>"\'/=` characters</div>'
    );
    expect(result).toBe(
      "&lt;div class&#x3D;&quot;button&quot;&gt;This text includes &#x60;&amp;&lt;&gt;&quot;&#39;&#x2F;&#x3D;&#x60; characters&lt;&#x2F;div&gt;"
    );
  });
});
