import { describe, expect, it } from "vitest";
import string from "./string.js";

describe.concurrent("$helper.string.unescapeHTML", () => {
	it("should unescape HTML entities", () => {
		const result = string.unescapeHTML(
			"&lt;div class&#x3D;&quot;button&quot;&gt;This text includes &#x60;&amp;&lt;&gt;&quot;&#039;&#x2F;&#x3D;&#x60; characters&lt;&#x2F;div&gt;"
		);
		expect(result).toBe(
			'<div class="button">This text includes `&<>"\'/=` characters</div>'
		);
	});
});
