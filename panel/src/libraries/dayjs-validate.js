export default (option, Dayjs, dayjs) => {
  /**
   * Validates datetime against an
   * upper or lower (min/max) boundary
   *
   * @param {string} boundary
   * @param {string} type
   * @param {string} unit
   * @returns {bool}
   */
  Dayjs.prototype.validate = function (boundary, type, unit = "day") {
    if (!this.isValid()) {
      return false;
    }

    // if no boundary is provide, return true
    // since we already know dayjs is valid
    if (!boundary) {
      return true;
    }

    // generate dayjs object for value
    boundary = dayjs.iso(boundary);

    const condition = {
      min: "isAfter",
      max: "isBefore"
    }[type];

    // whether input is the reference or within the condition (upper/lower)
    // compared against the unit
    return this.isSame(boundary, unit) || this[condition](boundary, unit);
  };
};
