import slug from "./slug.js";

describe("$helper.slug()", () => {

  it("should replace spaces", () => {
    const result = slug("a b c");
    expect(result).to.equal("a-b-c");
  });

  it("should replace slashes with dashes", () => {
    const result = slug("a/b/c");
    expect(result).to.equal("a-b-c");
  });

  it("should replace non-allowed characters", () => {
    const result = slug("æöß");
    expect(result).to.equal("");
  });

  it("should replace non-word characters", () => {
    const result = slug("@a.b*c!");
    expect(result).to.equal("a-b-c");
  });

  it("should trim correctly", () => {
    const result = slug(" abc ");
    expect(result).to.equal("abc");
  });

  it("should remove non-asci characters", () => {
    const result = slug("❤️");
    expect(result).to.equal("");
  });

  it("should remove double seperators", () => {
    const result = slug("a--b  c");
    expect(result).to.equal("a-b-c");
  });

  it("should apply rules", () => {
    const rules = [
      { "å": "a" },
      { "á": "a" },
      { "ö": "oe" },
      { "ß": "ss" },
      { "İ": "i" }
    ];

    const result = slug("åöß", rules);
    expect(result).to.equal("aoess");
  });

  it("should handle plus signs", () => {
    const rules = [
      { "+": "-plus-" }
    ];

    const result = slug("1+1", rules);
    expect(result).to.equal("1-plus-1");
  });

  it("handles asterisks", () => {
    const resultA = slug("***");
    expect(resultA).to.equal("");

    const resultB = slug("***a***b***");
    expect(resultB).to.equal("a-b");
  });

});
