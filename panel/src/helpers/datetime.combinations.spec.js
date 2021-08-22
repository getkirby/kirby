
import { cartesian, combinations } from "./datetime";

describe("cartesian", () => {
  it("creates cartesian combinations for 1 segment", () => {
    const result = cartesian([
      ["A", "AA"]
    ]);

    expect(result).to.deep.equal([
      ["A"],
      ["AA"]
    ]);
  });

  it("creates cartesian combinations for multiple segments", () => {
    const result = cartesian([
      ["A", "AA"],
      ["B", "BB"],
      ["C", "CC"]
    ]);

    expect(result).to.deep.equal([
      ["A", "B", "C"],
      ["AA", "B", "C"],
      ["A", "BB", "C"],
      ["AA", "BB", "C"],
      ["A", "B", "CC"],
      ["AA", "B", "CC"],
      ["A", "BB", "CC"],
      ["AA", "BB", "CC"]
    ]);
  });
})

describe("combinations", () => {
  it("creates variable combinations for multiple segments", () => {
    const result = combinations([
      ["A", "AA"],
      ["B", "BB"],
      ["C", "CC"]
    ]);

    expect(result).to.deep.equal([
      ["A"],
      ["AA"],
      ["A", "B"],
      ["AA", "B"],
      ["A", "BB"],
      ["AA", "BB"],
      ["A", "B", "C"],
      ["AA", "B", "C"],
      ["A", "BB", "C"],
      ["AA", "BB", "C"],
      ["A", "B", "CC"],
      ["AA", "B", "CC"],
      ["A", "BB", "CC"],
      ["AA", "BB", "CC"]
    ]);
  });

  it("creates variable combinations for date tokens", () => {
    const result = combinations([
      ["YY", "YYYY"],
      ["M", "MM"],
      ["D", "DD"]
    ]);

    expect(result).to.deep.equal([
      ["YY"],
      ["YYYY"],
      ["YY", "M"],
      ["YYYY", "M"],
      ["YY", "MM"],
      ["YYYY", "MM"],
      ["YY", "M", "D"],
      ["YYYY", "M", "D"],
      ["YY", "MM", "D"],
      ["YYYY", "MM", "D"],
      ["YY", "M", "DD"],
      ["YYYY", "M", "DD"],
      ["YY", "MM", "DD"],
      ["YYYY", "MM", "DD"]
    ]);
  });

  it("creates variable combinations for 0 segments", () => {
    const result = combinations([]);
    expect(result).to.deep.equal([]);
  });
});