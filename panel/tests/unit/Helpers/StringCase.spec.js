import { ucfirst, lcfirst } from '@/helpers/StringCase.js'

describe("String Case Helper", () => {

  it("ucfirst", () => {
    const result = ucfirst("hello");
    expect(result).toBe("Hello");
  });

  it("ucfirst with single-char word", () => {
    const result = ucfirst("h");
    expect(result).toBe("H");
  });

  it("ucfirst with wrong input", () => {
    const result = ucfirst(0);
    expect(result).toBe("0");
  });

  it("lcfirst", () => {
    const result = lcfirst("Hello");
    expect(result).toBe("hello");
  });

  it("lcfirst with single-char word", () => {
    const result = lcfirst("H");
    expect(result).toBe("h");
  });

  it("lcfirst with wrong input", () => {
    const result = lcfirst(0);
    expect(result).toBe("0");
  });

});
