import pad from "@/helpers/pad.js";

describe("pad Helper", () => {

  it("default padding", () => {
    const result = pad(1);
    expect(result).toBe("01");
  });

  it("default padding with unpadded number", () => {
    const result = pad(10);
    expect(result).toBe("10");
  });

  it("default padding with higher number", () => {
    const result = pad(120);
    expect(result).toBe("120");
  });

  it("default padding with zero", () => {
    const result = pad(0);
    expect(result).toBe("00");
  });

  it("custom padding", () => {
    const result = pad(1, 3);
    expect(result).toBe("001");
  });

  it("custom padding with zero", () => {
    const result = pad(0, 3);
    expect(result).toBe("000");
  });

  it("custom padding with unpadded number", () => {
    const result = pad(123, 3);
    expect(result).toBe("123");
  });

  it("custom padding with higher number", () => {
    const result = pad(1234, 3);
    expect(result).toBe("1234");
  });

});
