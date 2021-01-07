import clone from "./clone.js";
import debounce from "./debounce.js";
import embed from "./embed.js";
import isComponent from "./isComponent.js";
import isUploadEvent from "./isUploadEvent.js";
import pad from "./pad.js";
import ratio from "./ratio.js";
import slug from "./slug.js";
import sort from "./sort.js";
import string from "./string.js";
import upload from "./upload.js";
import uuid from "./uuid.js";
import validate from "./validate.js";

import "./regex.js";

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


    Vue.prototype.$helper = {
      clone: clone,
      embed: embed,
      isComponent: isComponent,
      isUploadEvent: isUploadEvent,
      debounce: debounce,
      pad: pad,
      ratio: ratio,
      slug: slug,
      sort: sort,
      string: string,
      upload: upload,
      uuid: uuid,
      validate: validate,
    };

  }

};
