import autosize from "autosize";

import dayjs from "dayjs";
import customParseFormat from 'dayjs/plugin/customParseFormat'
import utc from 'dayjs/plugin/utc';

export default {
  install(app) {
    dayjs.extend(customParseFormat);
    dayjs.extend(utc);

    app.config.globalProperties.$library = {
      autosize: autosize,
      dayjs: dayjs
    };
  }
};
