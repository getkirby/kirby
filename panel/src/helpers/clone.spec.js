import clone from "./clone.js";

describe("$helper.clone()", () => {

  it("should clone the object", () => {

    const object = {
      a: "A",
      b: "B"
    };

    expect(clone(object)).to.deep.equal(object);
  });

});
