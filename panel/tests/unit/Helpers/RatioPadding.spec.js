import ratioPadding from "@/helpers/ratioPadding.js";

describe("ratioPadding Helper", () => {

  it("default ratio", () => {
    const result = ratioPadding();
    expect(result).toBe("66.66666666666667%");
  });

  it("16/9", () => {
    const result = ratioPadding("16/9");
    expect(result).toBe("56.25%");
  });

  it("invalid fraction 1", () => {
    const result = ratioPadding("0/16");
    expect(result).toBe("100%");
  });

  it("invalid fraction 2", () => {
    const result = ratioPadding("16/0");
    expect(result).toBe("100%");
  });

  it("invalid input 1", () => {
    const result = ratioPadding(1);
    expect(result).toBe("100%");
  });

  it("invalid input 2", () => {
    const result = ratioPadding({});
    expect(result).toBe("100%");
  });

});
