import string from "./string.js";

describe("$helper.string.escapeHTML", () => {

  it("should escape HTML", () => {
    const result = string.escapeHTML("<div class=\"button\">This text includes `&<>\"'/=` characters</div>");
    expect(result).to.equal("&lt;div class&#x3D;&quot;button&quot;&gt;This text includes &#x60;&amp;&lt;&gt;&quot;&#39;&#x2F;&#x3D;&#x60; characters&lt;&#x2F;div&gt;");
  });

});
