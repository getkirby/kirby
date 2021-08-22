
/**
 * This file exports various helper classes to work with
 * datetime objects based on the dayjs library:
 *
 * - default class:
 *   Helps to parse, manipulate, validate, format ... a datetime
 *   based on a provided format pattern
 *
 * - `Pattern` class:
 *   Used to analyse and evaluate a provided format pattern
 *
 * - `Input` class:
 *   Helps for interactions with a DOM input element
 *   that is based on a format pattern
 *
 * - `TOKENS` constant:
 *   Provides a mapping from datetime unit to dayjs format tokens
 *
 * - `cartesian` and `combinations` functions:
 *   Used to generate all possible combinations of tokens as format patterns
 */

/**
 * Mapping units and dayjs tokens
 */
export const TOKENS = {
  year:     ["YY", "YYYY"],
  month:    ["M", "MM"],
  day:      ["D", "DD"],
  hour:     (is12h) => is12h ? ["h", "hh"] : ["H", "HH"],
  minute:   ["m", "mm"],
  second:   ["s", "ss"],
  meridiem: (is12h) => is12h ? ["a"] : [],
};

/**
 * Generates all combinations for each
 * segment variation, always including all segments
 * @param {string[][]} segments
 * @return {string[][]}
 */
 export function cartesian(segments) {
  let results = segments.reverse().reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())));

  // for single elements, make sure to be wrapped in arrays
  if (Array.isArray(results[0]) === false) {
    results = results.map(a => [a]);
  }

  // Remove null from results and adjust order
  return results.map(a => a.filter(b => b).reverse());
}

/**
 * Generates all combinations for each
 * segment variation, also allowing for trailing
 * segments to be dropped
 * @param {string[][]} segments
 * @return {string[][]}
 */
 export function combinations(segments) {
   return segments.reduce((a, b, i) => a.concat(cartesian(segments.slice(0, i + 1))), []);
 }

/**
 * Disects a format pattern and generates
 * pattern variants based on all supported
 * tokens for each unit
 */
export class Pattern {

  constructor(pattern) {
    this.pattern    = pattern;
    this.parts      = pattern.split(/\W/);
    this.separators = pattern.match(/[\W]/g);
    this.is12h      = this.parts.includes("h") || this.parts.includes("hh");
    this.tokens     = Object.values(TOKENS).map(token => token instanceof Function ? token(this.is12h) : token)
  }

  /**
   * Whether the pattern only features time units
   * @returns {bool}
   */
  isTimeOnly() {
    const units = this.units();
    return !units.includes("year") &&
           !units.includes("month") &&
           !units.includes("day");
  }

  /**
   * Returns an array of all tokens
   * for the unit of the provided token
   * @param {string} token
   * @returns {string[]}
   */
  token(token) {
    return this.tokens[this.tokens.findIndex(a => a.includes(token))];
  }

  /**
   * Return the unit for the provided token
   * @param {string} token
   * @returns {string}
   */
  unit(token) {
    return Object.keys(TOKENS)[this.tokens.indexOf(this.token(token))];
  }

  /**
   * Returns array of units featured in the pattern
   * @returns {string[]}
   */
  units() {
    return this.parts.map(part => this.unit(part));
  }

  /**
   * Returns array of variants of the pattern
   * by combining the variants of all tokens
   * included in the pattern
   * @returns {string[]}
   */
  variants() {
    // retrieve token variants for each token part
    const segments = this.parts.map(part => this.token(part));
    // generate all possible combination of tokens
    const variants = combinations(segments).reverse();
    // join combinations to pattern strings by adding in
    // separators from original pattern
    return variants.map(variant => variant.reduce((a, b, i) => a + this.separators[i - 1] + b));
  }

}

/**
 * Supports structured interactions with an
 * input DOM element selection according to a
 * provided pattern.
 */
export class Input {

  constructor(input, pattern) {
    this.input    = input;
    this.pattern  = new Pattern(pattern);
    this.segments = pattern.split(/\W/).map((segment, index) => {
      const start = pattern.indexOf(segment);
      return {
        index: index,
        value: segment,
        unit:  this.pattern.unit(segment),
        start: start,
        end:   start + segment.length - 1
      }
    });
  }

  /**
   * Returns the segment of the current cursor position
   * @returns {Object|null}
   */
  current() {
    // if whole input is selected, return
    if (
      this.input.selectionStart === 0 &&
      this.input.selectionEnd === this.input.value.length
    ) {
      return null;
    }

    // based on the current cursor position,
    // return the matching part's index
    return this.segments.filter(segment =>
      segment.start <= this.input.selectionStart &&
      segment.end >= (this.input.selectionEnd - 1)
    )[0];
  }

  /**
   * Returns proceeding segment of the current cursor position
   * @returns {Object|null}
   */
  next() {
    const current = this.current();
    const next    = this.segments[this.segments.indexOf(current) + 1] || null;

    if (
      this.input.selectionStart === this.input.selectionEnd &&
      this.input.selectionEnd <= (current.end + 1)
    ) {
      return current;
    }

    return next;
  }

  /**
   * Sets the cursor select in the input element
   * that includes the provided segment
   * @param {Object} segment
   */
  select(segment) {
    if (segment) {
      this.input.setSelectionRange(segment.start, segment.end + 1);
    }
  }

}

/**
 * Parsing, rounding, validating etc. of a
 * datetime object according to a provided
 * pattern and step configuration
 */
export default class {

  constructor(dayjs, pattern, step = {}) {
    this.dayjs    = dayjs;
    this.pattern  = new Pattern(pattern);
    this.patterns = this.pattern.variants();
    this.step     = { unit: "day", size: 1, ...step };
  }

  /**
   * Parses the value to dayjs object, if needed,
   * rounds to nearest step and returns as formatted string
   * @param {string} value
   * @param {string} format uses the pattern as default
   * @returns {string}
   */
  format(value, format = this.pattern.pattern) {
    if (!value) {
      return null;
    }

    // parse value as datetime object if string,
    // otherwise expect dayjs object was provided as value
    if (typeof value == "string") {
      value = this.toObject(value)
    }

    if (value.isValid() === false) {
      return null;
    }

    // formats datetime according to the pattern
    return this.nearest(value).format(format);
  }

  /**
   * Returns an iso string for the dayjs object.
   * If the pattern only uses time units, a shorter format
   * with just time units is used. If the parameter is
   * not a valid dayjs object or cannot be converted to one,
   * an empty string is returned.
   * @param {Object|string} dt
   * @returns {string}
   */
  iso(dt) {
    const onlyTime = this.pattern.isTimeOnly();
    const pattern  = onlyTime ? "HH:mm:ss" : "YYYY-MM-DD HH:mm:ss";
    return this.format(dt, pattern) || "";
  }

  /**
   * Adds or substract a step to the provided segment
   * on the provided value. If the value is not parseable
   * as datetime  object, the current datetime is used.
   * @param {string} value
   * @param {Object} segment
   * @param {string} operator `add` or `substract`
   * @returns {Object}
   */
  manipulate(value, segment, operator) {
    let dt = this.parseToNearest(value);

    // if no parsed result exist, fill with current datetime
    if (dt === null) {
      return this.nearest(this.dayjs());
    }

    // as default use the step unit and size
    let unit = this.step.unit;
    let size = this.step.size;

    // if a segment in the input is selected
    if (segment !== null) {
      // handle manipulation of am/pm meridiem
      if (segment === "meridiem") {
        operator = dt.format("a") === "pm" ? "subtract" : "add";
        unit     = "hour";
        size     = 12;

      // handle manipulation of all other units
      } else {
        unit = segment;

        // only use step size for step unit,
        // otherwise use size of 1
        if (unit !== this.step.unit) {
          size = 1;
        }
      }
    }

    // manipulate datetime by size and unit
    return dt[operator](size, unit);
  }

  /**
   * Rounds the provided dayjs object to
   * the nearest unit step
   * @param {Object} dt
   * @returns
   */
  nearest(dt) {
    // define base reference for step unit
    let base;
    switch (this.step.unit) {
      case "second":
        base = dt.startOf("minute");
        break;
      case "minute":
        base = dt.startOf("hour");
        break;
      case "hour":
        base = dt.startOf("day");
        break;
      case "day":
        base = dt.startOf("month");
        break;
      case "month":
        base = dt.startOf("year");
        break;
      case "year":
        // get current century
        base = this.dayjs((Math.floor(parseInt(dt.format("YYYY")) / 100) * 100) + "-01-01 00:00:00");
        break;
    }

    // create range with all possible step options
    // by adding a step to the base reference.
    let range   = [];
    let current = base.clone();
    let max     = dt.add(this.step.size, this.step.unit);
    let step    = base.add(this.step.size, this.step.unit).unix() - base.unix();

    while (current.unix() < max.unix()) {
      range.unshift(current);
      current = current.add(this.step.size, this.step.unit);
    }

    // loop through range of options until we have found a
    // datetime that is less than half a step size away from the
    // provided datetime (and thus its nearest step)
    for (let i = 0; i < range.length; i++) {
      if (Math.abs(dt.unix() - range[i].unix()) <= (step/2)) {
        return range[i];
      }
    }
  }

  /**
   * Returns a dayjs object for the provided input, if possible,
   * by matching it against all variants of the pattern.
   * @param {string} input
   * @returns {Object|null}
   */
  parse(input) {
    if (typeof input === "string" && input !== "") {
      // loop through parsing patterns to find
      // first result where input is a valid date
      for (let i = 0; i < this.patterns.length; i++) {
        const dt = this.dayjs.utc(input, this.patterns[i]);

        if (dt.isValid() === true) {
          return dt;
        }
      }
    }

    return null;
  }

  /**
   * Parse provided input and round
   * to nearest unit step
   * @param {string} input
   * @returns {Object|null}
   */
  parseToNearest(input) {
    const dt = this.parse(input);
    return dt ? this.nearest(dt) : null;
  }

  /**
   * Generates dayjs object from string
   * @param {string} string
   * @returns {Object}
   */
  toObject(string) {
    if (this.pattern.isTimeOnly() === true) {
      return this.dayjs.utc(string, "HH:mm:ss");
    }

    return this.dayjs.utc(string);
  }

  /**
   * Validates input as datetime against an
   * upper or lower (min/max) limit
   * @param {Object|string} input
   * @param {string} limit
   * @param {string} condition
   * @param {string} base
   * @returns {bool}
   */
  validate(input, limit, condition, base = "day") {
    let dt = this.toObject(input);

    // if no limit is provided, just make sure
    // the dayjs object is valid
    if (!limit) {
      return !!input && dt.isValid();
    }

    // if the input is no valid dayjs object,
    // let validation pass
    // @todo is this right?
    if (!input || !dt.isValid()) {
      return true;
    }

    // generate dayjs object for limit
    limit = this.toObject(limit);

    // whether input is the limit or within the condition (upper/lower)
    // compared against the base unit
    return dt.isSame(limit, base) || dt[condition](limit, base);
  }
}