export default (option, Dayjs) => {
  /**
   * Merges the current datetime with a part (date or time)
   * of another dayjs object
   *
   * @param {Object} dt  dayjs object to merge into current object
   * @param {string|array} units array of units or alias (`date` or `time`)
   * @returns
   */
  Dayjs.prototype.merge = function (dt, units = "date") {
    let result = this.clone();

    // if string alias has been provided,
    // transform to array of units
    if (typeof units === "string") {
      const map = {
        date: ["year", "month", "date"],
        time: ["hour", "minute", "second"]
      };

      if (map.hasOwnProperty(units) === false) {
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
