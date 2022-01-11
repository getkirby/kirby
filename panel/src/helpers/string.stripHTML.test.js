import string from "./string.js";

describe("$helper.string.stripHTML", () => {
  it("should strip HTML elements", () => {
    const html = `<p>This is <strong>a</strong> test <i><span>text</span></i> with some html</p>`;
    const expected = `This is a test text with some html`;

    expect(string.stripHTML(html)).toBe(expected);
  });
});
