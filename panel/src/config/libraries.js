import Vue from "vue";

import autosize from "autosize";

import dayjs from "dayjs";
import customParseFormat from 'dayjs/plugin/customParseFormat'
dayjs.extend(customParseFormat);

Vue.prototype.$library = {
  autosize: autosize,
  dayjs: dayjs
};
