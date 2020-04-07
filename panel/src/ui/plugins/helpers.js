import "../helpers/regex.js";
import clone from "../helpers/clone.js";
import debounce from "../helpers/debounce.js";
import pad from "../helpers/pad.js";
import previewThumb from "../helpers/previewThumb.js";
import ratio from "../helpers/ratio.js";
import slug from "../helpers/slug.js";
import sort from "../helpers/sort.js";
import string from "../helpers/string.js";
import upload from "../helpers/upload.js";
import isUploadEvent from "../helpers/isUploadEvent.js";

export default {
  install(Vue) {
    Vue.prototype.$helper = {
      clone: clone,
      isUploadEvent: isUploadEvent,
      debounce: debounce,
      pad: pad,
      previewThumb: previewThumb,
      ratio: ratio,
      slug: slug,
      sort: sort,
      string: string,
      upload: upload
    };
  }
};
