import string from "./string.js";

describe("$helper.string.lcfirst", () => {

  it("should convert first character to lowercase", () => {
    const result = string.lcfirst("Hello");
    expect(result).to.equal("hello");
  });

  it("should convert single character to lowercase", () => {
    const result = string.lcfirst("H");
    expect(result).to.equal("h");
  });

  it("should ignore invalid input", () => {
    const result = string.lcfirst(0);
    expect(result).to.equal("0");
  });

});
