import clone from "@/helpers/clone.js";
import debounce from "@/helpers/debounce.js";
import isComponent from "@/helpers/isComponent.js";
import isUploadEvent from "@/helpers/isUploadEvent.js";
import pad from "@/helpers/pad.js";
import ratio from "@/helpers/ratio.js";
import slug from "@/helpers/slug.js";
import sort from "@/helpers/sort.js";
import string from "@/helpers/string.js";
import upload from "@/helpers/upload.js";

export default {
  install(Vue) {

    /**
     * Array.sortBy()
     */
    Array.prototype.sortBy = function (sortBy) {
      const sort = Vue.prototype.$helper.sort();
      const options = sortBy.split(" ");
      const field = options[0];
      const direction = options[1] || "asc";

      return this.sort((a, b) => {
        const valueA = String(a[field]).toLowerCase();
        const valueB = String(b[field]).toLowerCase();

        if (direction === "desc") {
          return sort(valueB, valueA);
        } else {
          return sort(valueA, valueB);
        }
      });
    };

    /**
     * RegExp.escape(string)
     */
    RegExp.escape = function (string) {
      return string.replace(new RegExp("[-/\\\\^$*+?.()[\\]{}]", "gu"), '\\$&');
    };

    Vue.prototype.$helper = {
      clone: clone,
      isComponent: isComponent,
      isUploadEvent: isUploadEvent,
      debounce: debounce,
      pad: pad,
      ratio: ratio,
      slug: slug,
      sort: sort,
      string: string,
      upload: upload
    };

  }

};
