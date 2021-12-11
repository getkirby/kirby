export default (option, Dayjs, dayjs) => {
  dayjs.units = (is12h) => ({
    year: ["YY", "YYYY"],
    month: ["M", "MM"],
    day: ["D", "DD"],
    hour: is12h ? ["h", "hh"] : ["H", "HH"],
    minute: ["m", "mm"],
    second: ["s", "ss"],
    meridiem: is12h ? ["a"] : []
  });
};
