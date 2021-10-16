import Fiber from "./index";

export default function (path, options) {
  return async (ready) => {
    try {
      const data = await Fiber.request("dropdowns/" + path, options);

      // the GET request for the dialog is failing
      if (!data.$dropdown) {
        throw "The dropdown could not be loaded";
      }

      // the dropdown sends a backend error
      if (data.$dropdown.error) {
        throw data.$dropdown.error;
      }

      if (
        Array.isArray(data.$dropdown.options) === false ||
        data.$dropdown.options.length === 0
      ) {
        throw "The dropdown is empty";
      }

      data.$dropdown.options.map((option) => {
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

      ready(data.$dropdown.options);
    } catch (e) {
      console.error(e);
      this.$store.dispatch("notification/error", e);
    }
  };
}
