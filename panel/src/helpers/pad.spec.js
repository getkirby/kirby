import pad from "./pad.js";

describe("$helper.pad()", () => {

  it("should add default padding", () => {
    expect(pad(1)).to.equal("01");
  });

  it("should not add padding", () => {
    expect(pad(10)).to.equal("10");
    expect(pad(120)).to.equal("120");
  });

  it("should add padding to zero", () => {
    expect(pad(0)).to.equal("00");
  });

  it("should apply custom padding", () => {
    expect(pad(1, 3)).to.equal("001");
    expect(pad(0, 3)).to.equal("000");
  });

  it("should not apply custom padding", () => {
    expect(pad(123, 3)).to.equal("123");
    expect(pad(1234, 3)).to.equal("1234");
  });
});
