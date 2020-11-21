import autosize from "autosize";

import dayjs from "dayjs";
import customParseFormat from 'dayjs/plugin/customParseFormat'
import localeData from 'dayjs/plugin/localeData';
import utc from 'dayjs/plugin/utc';
import weekday from 'dayjs/plugin/weekday';

export default {
  install(Vue) {
    dayjs.extend(customParseFormat);
    dayjs.extend(localeData)
    dayjs.extend(utc);
    dayjs.extend(weekday)

    Vue.$library = Vue.prototype.$library = {
      autosize: autosize,
      dayjs: dayjs
    };
  }
};
