import string from "./string.js";

describe("$helper.string.ucfirst", () => {

  it("should convert first character to uppercase", () => {
    const result = string.ucfirst("hello");
    expect(result).to.equal("Hello");
  });

  it("should convert single character to uppercase", () => {
    const result = string.ucfirst("h");
    expect(result).to.equal("H");
  });

  it("should ignore invalid input", () => {
    const result = string.ucfirst(0);
    expect(result).to.equal("0");
  });

});
