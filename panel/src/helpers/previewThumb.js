
export default function (image, type = "list", column = "1/1") {
  if (!image || image.length === 0) {
    return false;
  }

  let src    = null;
  let srcset = null;

  if (image.list) {
    src    = image[type].url;
    srcset = image[type].srcset;
  } else {
    src    = image.url;
    srcset = image.srcset;
  }

  if (!src) {
    return false;
  }

  let result = {
    src: src,
    srcset: srcset,
    back: image.back || "black",
    cover: image.cover
  };

  if (type === "cards") {
    result.ratio = image.ratio || "3/2";
    result.sizes = getSizes(column);
  }

  return result;
}

function getSizes(width) {
  switch (width) {
    case '1/2':
    case '2/4':
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 44em, 27em';
    case '1/3':
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 29.333em, 27em';
    case '1/4':
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 22em, 27em';
    case '2/3':
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 27em, 27em';
    case '3/4':
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 66em, 27em';
    default:
      return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 88em, 27em';
  }
}
