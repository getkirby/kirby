
import { TOKENS } from "./datetime";

it("returns proper hour tokens", () => {
  expect(TOKENS.hour(true)).to.deep.equal(["h", "hh"]);
  expect(TOKENS.hour(false)).to.deep.equal(["H", "HH"]);

  expect(TOKENS.meridiem(true)).to.deep.equal(["a"]);
  expect(TOKENS.meridiem(false)).to.deep.equal([]);
});

