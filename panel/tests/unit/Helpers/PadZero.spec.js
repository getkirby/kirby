import { mount } from "@vue/test-utils";
import padZero from "@/helpers/padZero.js";

describe("padZero Helper", () => {

  it("default padding", () => {
    const result = padZero(1);
    expect(result).toBe("01");
  });

  it("default padding with unpadded number", () => {
    const result = padZero(10);
    expect(result).toBe("10");
  });

  it("default padding with higher number", () => {
    const result = padZero(120);
    expect(result).toBe("120");
  });

  it("default padding with zero", () => {
    const result = padZero(0);
    expect(result).toBe("00");
  });

  it("custom padding", () => {
    const result = padZero(1, 3);
    expect(result).toBe("001");
  });

  it("custom padding with zero", () => {
    const result = padZero(0, 3);
    expect(result).toBe("000");
  });

  it("custom padding with unpadded number", () => {
    const result = padZero(123, 3);
    expect(result).toBe("123");
  });

  it("custom padding with higher number", () => {
    const result = padZero(1234, 3);
    expect(result).toBe("1234");
  });

});
