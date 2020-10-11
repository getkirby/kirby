import Vue from "vue";

import autosize from "autosize";

import dayjs from "dayjs";
import customParseFormat from 'dayjs/plugin/customParseFormat'
import utc from 'dayjs/plugin/utc';
dayjs.extend(customParseFormat);
dayjs.extend(utc);

Vue.prototype.$library = {
  autosize: autosize,
  dayjs: dayjs
};
