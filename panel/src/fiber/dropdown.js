/**
 * Loads dropdown options from the server
 *
 * @example
 * <k-dropdown-content :options="$dropdown('some/dropdown')" />
 *
 * @param {String} path
 * @param {Object} options
 * @return {Function}
 */
export default function (path, options = {}) {
  return async (ready) => {
    const dropdown = await this.$fiber.request("dropdowns/" + path, {
      ...options,
      type: "$dropdown"
    });

    // the request could not be parsed
    // the fatal view is taking over
    if (!dropdown) {
      return false;
    }

    if (
      Array.isArray(dropdown.options) === false ||
      dropdown.options.length === 0
    ) {
      throw Error(`The dropdown is empty`);
    }

    dropdown.options.map((option) => {
      if (option.dialog) {
        option.click = () => {
          const url =
            typeof option.dialog === "string"
              ? option.dialog
              : option.dialog.url;
          const options =
            typeof option.dialog === "object" ? option.dialog : {};
          return this.$dialog(url, options);
        };
      }
      return option;
    });

    ready(dropdown.options);
  };
}
