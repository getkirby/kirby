export default (option, Dayjs, dayjs) => {
  /**
   * Returns a map of units to dayjs tokens
   * based on 12h/24h clock
   *
   * @param {bool} is12h
   * @returns {Object}
   */
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
