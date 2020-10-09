import string from "./string.js";

describe("$helper.string.template", () => {

  it("should insert values", () => {
    const values = { a: "Berlin", b: "Tegucigalpa", cc: "Tokyo" };
    let result = string.template( "From {{a}} to {{ b}} to {{ cC }}", values);
    expect(result).to.equal("From Berlin to Tegucigalpa to Tokyo");

    result = string.template( "Counting: {{a.length}} / {{a.count}}", { a: [1, 2, 3]});
    expect(result).to.equal("Counting: 3 / 3");
  });

  it("should insert default", () => {
    const values = { a: null, b: null, cc: null };
    let result = string.template( "From {{a}} to {{ b}} to {{ cC }}", values);
    expect(result).to.equal("From … to … to …");

    result = string.template( "Counting: {{a.length}} / {{a.count}}", { a: []});
    expect(result).to.equal("Counting: 0 / 0");
  });

});
