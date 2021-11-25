import Fiber from "./index";

export default function (path, options) {
  return async (ready) => {
    const dropdown = await Fiber.request("dropdowns/" + path, {
      ...options,
      method: "POST",
      type: "$dropdown"
    });

    // the request could not be parsed
    // the fatal view is taking over
    if (dropdown === false) {
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
        option.click = function () {
          const url =
            typeof option.dialog === "string"
              ? option.dialog
              : option.dialog.url;
          const options =
            typeof option.dialog === "object" ? option.dialog : {};
          this.$dialog(url, options);
        };
      }
      return option;
    });

    ready(dropdown.options);
  };
}
