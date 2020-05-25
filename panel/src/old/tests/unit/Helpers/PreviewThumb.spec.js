import previewThumb from "@/helpers/previewThumb.js";

describe("previewThumb Helper", () => {

  it("empty", () => {
    const result = previewThumb();
    expect(result).toBe(false);
  });

  it("no src", () => {
    const result = previewThumb({
      back: "Something"
    });

    expect(result).toBe(false);
  });

  it("simple", () => {
    const result = previewThumb({
      url: "/my/image.jpg"
    });

    expect(result).toEqual({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: undefined,
    });
  });

  it("nested", () => {
    const result = previewThumb({
      list: {
        url: "/my/image.jpg"
      }
    });

    expect(result).toEqual({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: undefined,
    });
  });

  it("srcset", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      srcset: "srcset"
    });

    expect(result).toEqual({
      src: "/my/image.jpg",
      srcset: "srcset",
      back: "black",
      cover: undefined,
    });
  });

  it("back", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      back: "pattern"
    });

    expect(result).toEqual({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "pattern",
      cover: undefined,
    });
  });

  it("cover", () => {
    const result = previewThumb({
      url: "/my/image.jpg",
      cover: true
    });

    expect(result).toEqual({
      src: "/my/image.jpg",
      srcset: undefined,
      back: "black",
      cover: true,
    });
  });

});
