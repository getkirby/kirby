import ratio from "@/helpers/ratio.js";

describe("ratio Helper", () => {

  it("default ratio", () => {
    const result = ratio();
    expect(result).toBe("66.66666666666667%");
  });

  it("16/9", () => {
    const result = ratio("16/9");
    expect(result).toBe("56.25%");
  });

  it("invalid fraction 1", () => {
    const result = ratio("0/16");
    expect(result).toBe("100%");
  });

  it("invalid fraction 2", () => {
    const result = ratio("16/0");
    expect(result).toBe("100%");
  });

  it("invalid input 1", () => {
    const result = ratio(1);
    expect(result).toBe("100%");
  });

  it("invalid input 2", () => {
    const result = ratio({});
    expect(result).toBe("100%");
  });

});
