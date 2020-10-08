import previewThumb from "./previewThumb.js";

describe("$helper.previewThumb()", () => {

  it("should return false when empty", () => {
    const result = previewThumb();
    expect(result).to.equal(false);
  });

  it("should return false when there's no url", () => {
    const result = previewThumb({
      back: "Something"
    });

    expect(result).to.equal(false);
  });

  it("should return default thumb settings", () => {
    const result = previewThumb({
      url: "/my/image.jpg"
    });

    expect(result).to.deep.equal({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: undefined,
    });
  });

  it("should work with nested settings", () => {
    const result = previewThumb({
      list: {
        url: "/my/image.jpg"
      }
    });

    expect(result).to.deep.equal({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: undefined,
    });
  });

  it("should support srcset", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      srcset: "srcset"
    });

    expect(result).to.deep.equal({
      src: "/my/image.jpg",
      srcset: "srcset",
      back: "black",
      cover: undefined,
    });
  });

  it("should support custom backgrounds", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      back: "pattern"
    });

    expect(result).to.deep.equal({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "pattern",
      cover: undefined,
    });
  });

  it("should support the cover option", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      cover: true
    });

    expect(result).to.deep.equal({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: true,
    });
  });

});
