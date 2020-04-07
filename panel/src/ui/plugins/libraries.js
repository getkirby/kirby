import autosize from "autosize";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

dayjs.extend(customParseFormat);

export default {
  install(Vue) {
    Vue.prototype.$library = {
      autosize: autosize,
      dayjs: dayjs
    };
  }
};
