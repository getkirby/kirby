import autosize from "autosize";
import dayjs from "./dayjs";

export default {
  install(app) {
    app.prototype.$library = {
      autosize: autosize,
      dayjs: dayjs
    };
  }
};
