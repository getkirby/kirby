export default (option, Dayjs) => {
  /**
   * Merges the current datetime with a part (date or time)
   * of another dayjs object
   *
   * @param {Object} dt  dayjs object to merge into current object
   * @param {string|array} units array of units or alias (`date` or `time`)
   * @returns {Object}
   */
  Dayjs.prototype.merge = function (dt, units = "date") {
    let result = this.clone();

    // if provided object is not valid,
    // return unaltered
    if (!dt || !dt.isValid()) {
      return this;
    }

    // if string alias has been provided,
    // transform to array of units
    if (typeof units === "string") {
      const map = {
        date: ["year", "month", "date"],
        time: ["hour", "minute", "second"]
      };

      if (Object.prototype.hasOwnProperty.call(map, units) === false) {
        throw new Error("Invalid merge unit alias");
      }

      units = map[units];
    }

    for (const unit of units) {
      result = result.set(unit, dt.get(unit));
    }

    return result;
  };
};
