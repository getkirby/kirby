import ratio from "./ratio.js";

describe("$helper.ratio()", () => {
  it("should return default ratio", () => {
    const result = ratio();
    expect(result).to.equal("66.66666666666667%");
  });

  it("should return padding for 16/9", () => {
    const result = ratio("16/9");
    expect(result).to.equal("56.25%");
  });

  it("should return 100% on invalid fractions", () => {
    expect(ratio("0/16")).to.equal("100%");
    expect(ratio("16/0")).to.equal("100%");
  });

  it("should return 100% on invalid input", () => {
    expect(ratio(1)).to.equal("100%");
    expect(ratio({})).to.equal("100%");
  });
});
