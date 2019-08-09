import Vue from "vue";

import "@/helpers/regex.js";

import clone from "@/helpers/clone.js";
import debounce from "@/helpers/debounce.js";
import pad from "@/helpers/pad.js";
import ratio from "@/helpers/ratio.js";
import slug from "@/helpers/slug.js";
import sort from "@/helpers/sort.js";
import string from "@/helpers/string.js";
import upload from "@/helpers/upload.js";

Vue.prototype.$helper = {
  clone: clone,
  debounce: debounce,
  pad: pad,
  ratio: ratio,
  slug: slug,
  sort: sort,
  string: string,
  upload: upload
};
