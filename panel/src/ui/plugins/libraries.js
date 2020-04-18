import autosize from "autosize";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

export default {
  install(Vue) {

    dayjs.extend(customParseFormat);

    Vue.prototype.$library = {
      autosize: autosize,
      dayjs: dayjs
    };
  }
};
