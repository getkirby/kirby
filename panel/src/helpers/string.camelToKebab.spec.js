import string from "./string.js";

describe("$helper.string.camelToKebab", () => {

  it("should convert camelCase", () => {
    const result = string.camelToKebab("helloWorld");
    expect(result).to.equal("hello-world");
  });

});
