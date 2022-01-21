export default (option, Dayjs, dayjs) => {
  /**
   * Returns instance of dayjs pattern class
   *
   * Public properties:
   * - pattern: {string} pattern/format
   * - is12h: {bool} if 12 h or 24 h clock
   * - isTime: {bool} if pattern only includes time units
   * - parts: {array} info on each part of the pattern (incl. unit, tokens)
   * - units: {array} units present in pattern
   * - separators: {array} separators present in pattern
   *
   * Methods
   * - at(start, end):   returns part for cursor position/selection
   * - format(dt):       formats a dayjs object according to the pattern
   * - interpret(inpit): tries to parse input to dayjs object
   * - tokens(unit):     returns all tokens for a unit
   * - unit(token):      returns unit for a token
   * - variations():     returns variations for all tokens/units
   *                     for fuzzy interpretation of input
   *
   * @param {string} pattern
   * @returns {Object}
   */
  dayjs.pattern = (pattern) =>
    new (class {
      constructor(dayjs, pattern) {
        this.dayjs = dayjs;
        this.pattern = pattern;

        // set if 12/24 hours clock
        const parts = this.pattern.split(/\W/);
        this.is12h = parts.includes("h") || parts.includes("hh");

        // get unit-tokens map
        this.map = dayjs.units(this.is12h);

        // get array of parts
        this.parts = parts.map((part, index) => {
          const unit = this.unit(part);
          const start = this.pattern.indexOf(part);
          return {
            index,
            unit,
            tokens: this.tokens(unit),
            start,
            end: start + (part.length - 1)
          };
        });

        // set units that are present in pattern
        this.units = this.parts.map((x) => x.unit);

        // set array of separators present in pattern
        this.separators = this.pattern.match(/[\W]/g);

        // set if time-only pattern
        this.isTime =
          (this.units.includes("year") ||
            this.units.includes("month") ||
            this.units.includes("day")) === false;
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

      /**
       * Returns a dayjs object for the provided input, if possible,
       * by matching it against all variations of the pattern.
       * @param {string} input
       * @param {string} format (date|time)
       * @returns {Object|null}
       */
      interpret(input, format = "date") {
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
            const dt = this.dayjs(
              input,
              variation,
              variations[format][variation]
            );

            if (dt.isValid() === true) {
              return dt;
            }
          }
        }

        return null;
      }

      /**
       * Returns all tokens for a unit
       * @param {string} unit
       * @returns {array}
       */
      tokens(unit) {
        return this.map[unit];
      }

      /**
       * Returns unit for the provided token
       * @param {string} token
       * @returns {string}
       */
      unit(token) {
        const index = Object.values(this.map).findIndex((tokens) =>
          tokens.includes(token)
        );
        return Object.keys(this.map)[index];
      }

      /**
       * Returns an array of pattern variations
       * by using all tokens for each units present
       * and allowing incomplete patterns (dropping units
       * from the end)
       * @returns {array}
       */
      variations() {
        const segments = this.parts.map((x) => x.tokens);
        // generate all possible combination of tokens
        const variations = segments.reduce((a, b, i) => {
          const segment = segments.slice(0, i + 1);
          let results = segment
            .filter((item) => item)
            .reverse()
            .reduce((a, b) => a.flatMap((d) => b.map((e) => [d, e].flat())));

          // for single elements, make sure to be wrapped in arrays
          if (Array.isArray(results[0]) === false) {
            results = results.map((a) => [a]);
          }

          // Remove null from results and adjust order
          const variation = results.map((a) => a.filter((b) => b).reverse());

          return a.concat(variation);
        }, []);

        // join combinations to pattern strings by adding in
        // separators from original pattern
        return variations
          .reverse()
          .map((x) => x.reduce((a, b, i) => a + this.separators[i - 1] + b));
      }
    })(dayjs, pattern);
};
