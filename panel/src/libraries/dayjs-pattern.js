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
       * @returns {Object|null}
       */
      interpret(input) {
        if (typeof input === "string" && input !== "") {
          // loop through all pattern variations to find
          // first result where input is a valid date
          const variations = this.variations();

          for (let i = 0; i < variations.length; i++) {
            const dt = this.dayjs(input, variations[i]);

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
