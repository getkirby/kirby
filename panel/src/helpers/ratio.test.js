import ratio from "./ratio.js";

describe("$helper.ratio()", () => {
  it("should return default ratio", () => {
    const result = ratio();
    expect(result).toBe("66.67%");
  });

  it("should return padding for 16/9", () => {
    const result = ratio("16/9");
    expect(result).toBe("56.25%");
  });

  it("should return 100% on invalid fractions", () => {
    expect(ratio("0/16")).toBe("100%");
    expect(ratio("16/0")).toBe("100%");
  });

  it("should return 100% on invalid input", () => {
    expect(ratio(1)).toBe("100%");
    expect(ratio({})).toBe("100%");
  });
});
