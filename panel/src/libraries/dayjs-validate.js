export default (option, Dayjs) => {
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

    const condition = {
      min: "isAfter",
      max: "isBefore"
    }[type];

    // generate dayjs object for value
    boundary = this.parse(boundary);

    // whether input is the reference or within the condition (upper/lower)
    // compared against the unit
    return this.isSame(boundary, unit) || this[condition](boundary, unit);
  };
};
