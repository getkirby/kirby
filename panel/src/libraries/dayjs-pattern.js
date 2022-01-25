export default (option, Dayjs, dayjs) => {
  /**
   * Returns instance of dayjs pattern class
   *
   * Public properties:
   * - pattern: {string} pattern/format
   * - parts:   {array}  info on each part of the pattern
   *
   * Methods
   * - at(start, end): returns part for cursor position/selection
   * - format(dt):     formats a dayjs object according to the pattern
   *
   * @param {string} pattern
   * @returns {Object}
   */
  dayjs.pattern = (pattern) =>
    new (class {
      constructor(dayjs, pattern) {
        this.dayjs = dayjs;
        this.pattern = pattern;

        // unit-tokens map
        const units = {
          year: ["YY", "YYYY"],
          month: ["M", "MM", "MMM", "MMMM"],
          day: ["D", "DD"],
          hour: ["h", "hh", "H", "HH"],
          minute: ["m", "mm"],
          second: ["s", "ss"],
          meridiem: ["a"]
        };

        // get array of parts
        this.parts = this.pattern.split(/\W/).map((part, index) => {
          const start = this.pattern.indexOf(part);
          return {
            index,
            unit: Object.keys(units)[
              Object.values(units).findIndex((tokens) => tokens.includes(part))
            ],
            start,
            end: start + (part.length - 1)
          };
        });
      }

      /**
       * Returns information about part at
       * provided selection/indexes
       * @param {number} start
       * @param {number} end
       * @returns {Object}
       */
      at(start, end = start) {
        const matches = this.parts.filter(
          (part) => part.start <= start && part.end >= end - 1
        );

        // exact selection found
        if (matches[0]) {
          return matches[0];
        }

        // fallback to part where selection starts
        return this.parts.filter((part) => part.start <= start).pop();
      }

      /**
       * Returns a string for the dayjs object
       * in the format of the pattern.
       * @param {Object} dt
       * @returns {string|null}
       */
      format(dt) {
        if (!dt || !dt.isValid()) {
          return null;
        }

        return dt.format(this.pattern);
      }
    })(dayjs, pattern);
};
