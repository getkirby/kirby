
import autosize from "autosize";

import dayjs from "dayjs";
import customParseFormat from 'dayjs/plugin/customParseFormat'
import utc from 'dayjs/plugin/utc';
dayjs.extend(customParseFormat);
dayjs.extend(utc);

export default {
  install(app) {
    app.prototype.$library = {
      autosize: autosize,
      dayjs: dayjs
    };    
  }
}