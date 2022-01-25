export default (option, Dayjs, dayjs) => {
  /**
   * Returns a dayjs object for the provided input, if possible,
   * by matching it against various format variations
   * @param {string} input
   * @param {string} format (date|time)
   * @returns {Object|null}
   */
  dayjs.interpret = (input, format = "date") => {
    const variations = {
      date: {
        "YYYY-MM-DD": true,
        "YYYY-MM-D": true,
        "YYYY-MM-": true,
        "YYYY-MM": true,
        "YYYY-M-DD": true,
        "YYYY-M-D": true,
        "YYYY-M-": true,
        "YYYY-M": true,
        "YYYY-": true,
        YYYYMMDD: true,

        "MMM DD YYYY": false,
        "MMM D YYYY": false,
        "MMM DD YY": false,
        "MMM D YY": false,
        "MMM DD": false,
        "MMM D": false,

        "DD MMMM YYYY": false,
        "DD MMMM YY": false,
        "DD MMMM": false,
        "D MMMM YYYY": false,
        "D MMMM YY": false,
        "D MMMM": false,

        "DD MMM YYYY": false,
        "D MMM YYYY": false,
        "DD MMM YY": false,
        "D MMM YY": false,
        "DD MMM": false,
        "D MMM": false,

        "DD MM YYYY": false,
        "DD M YYYY": false,
        "D MM YYYY": false,
        "D M YYYY": false,
        "DD MM YY": false,
        "D MM YY": false,
        "DD M YY": false,
        "D M YY": false,

        YYYY: true,
        MMMM: true,
        MMM: true,
        "DD MM": false,
        "DD M": false,
        "D MM": false,
        "D M": false,
        DD: false,
        D: false
      },
      time: {
        "HH:mm:ss a": false,
        "HH:mm:ss": false,
        "HH:mm a": false,
        "HH:mm": false,
        "HH a": false,
        HH: false
      }
    };

    if (typeof input === "string" && input !== "") {
      for (const variation in variations[format]) {
        const dt = dayjs(input, variation, variations[format][variation]);

        if (dt.isValid() === true) {
          return dt;
        }
      }
    }

    return null;
  };
};
