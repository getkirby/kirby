
export default function (image) {
  if (!image) {
    return false;
  }

  let src    = null;
  let srcset = null;

  if (image.list) {
    src    = image.list.url;
    srcset = image.list.srcset;
  } else {
    src    = image.url;
    srcset = image.srcset;
  }

  if (!src) {
    return false;
  }

  return {
    src: src,
    srcset: srcset,
    back: image.back || "black",
    cover: image.cover
  };
}
