import { mount } from '@vue/test-utils'
import slug from '@/helpers/slug.js'

describe("Slug Helper", () => {

  it("replaces spaces", () => {
    const result = slug("a b c");
    expect(result).toBe("a-b-c");
  });

  it("replaces slashes with dashes", () => {
    const result = slug("a/b/c");
    expect(result).toBe("a-b-c");
  });

  it("replaces non-allowed characters", () => {
    const result = slug("æöß");
    expect(result).toBe("");
  });

  it("replaces non-word characters", () => {
    const result = slug("@a.b*c!");
    expect(result).toBe("a-b-c");
  });

  it("trims correctly", () => {
    const result = slug(" abc ");
    expect(result).toBe("abc");
  });

  it("removes non-asci characters", () => {
    const result = slug("❤️");
    expect(result).toBe("");
  });

  it("removes double seperators", () => {
    const result = slug("a--b  c");
    expect(result).toBe("a-b-c");
  });

  it("applies rules", () => {
    const rules = [
      {"å": "a"},
      {"ö": "oe"},
      {"ß": "ss"},
      {"İ": "i"}
    ];

    const result = slug("åöß", rules);
    expect(result).toBe("aoess");
  });

});
