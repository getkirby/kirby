import string from "@/helpers/string.js";

describe("String Case Helper", () => {

  it("ucfirst", () => {
    const result = string.ucfirst("hello");
    expect(result).toBe("Hello");
  });

  it("ucfirst with single-char word", () => {
    const result = string.ucfirst("h");
    expect(result).toBe("H");
  });

  it("ucfirst with wrong input", () => {
    const result = string.ucfirst(0);
    expect(result).toBe("0");
  });

  it("lcfirst", () => {
    const result = string.lcfirst("Hello");
    expect(result).toBe("hello");
  });

  it("lcfirst with single-char word", () => {
    const result = string.lcfirst("H");
    expect(result).toBe("h");
  });

  it("lcfirst with wrong input", () => {
    const result = string.lcfirst(0);
    expect(result).toBe("0");
  });

});
