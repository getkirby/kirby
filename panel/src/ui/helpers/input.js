export default {
  options(options) {
    return options.map(option => {
      if (typeof option !== "object") {
        return {
          value: option,
          text: option
        };
      }

      return option;
    });
  }
}
