export default (option, Dayjs, dayjs) => {
  /**
   * Converts ISO format aliases to
   * dayjs format strings
   * @param {string} format
   * @returns {string}
   */
  const dayjsISOformat = (format) => {
    if (format === "date") {
      return "YYYY-MM-DD";
    }

    if (format === "time") {
      return "HH:mm:ss";
    }

    return "YYYY-MM-DD HH:mm:ss";
  };

  /**
   * Formats dayjs as ISO string
   * @param {string} format
   * @returns {string}
   */
  Dayjs.prototype.toISO = function (format = "datetime") {
    return this.format(dayjsISOformat(format));
  };

  /**
   * Converts ISO string to dayjs object
   * @param {string} string
   * @param {string} format
   * @returns {Object|null}
   */
  dayjs.iso = function (string, format = "datetime") {
    const dt = dayjs(string, dayjsISOformat(format));

    if (!dt || !dt.isValid()) {
      return null;
    }
    return dt;
  };
};
