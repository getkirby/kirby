import { color } from "./css.js";

describe("$helper.css.color()", () => {
  it("should return HEX", () => {
    expect(color("#fff")).to.deep.equal("#fff");
    expect(color("#bababa")).to.deep.equal("#bababa");
  });

  it("should return CSS variables", () => {
    expect(color("var(--color-white)")).to.deep.equal("var(--color-white)");
    expect(color("var(--color-blue-200)")).to.deep.equal(
      "var(--color-blue-200)"
    );
  });

  it("should parse CSS colors", () => {
    expect(color("white")).to.deep.equal("var(--color-white)");
    expect(color("blue-200")).to.deep.equal("var(--color-blue-200)");
  });

  it("should convert pattern", () => {
    expect(color("pattern")).to.deep.equal(
      "var(--color-gray-800) var(--bg-pattern)"
    );
  });
});
