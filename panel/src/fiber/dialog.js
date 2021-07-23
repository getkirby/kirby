
import Fiber from "./index";

export default async function (path, options = {}) {
  try {
    const data = await Fiber.request("dialogs/" + path, options);

    // the GET request for the dialog is failing
    if (!data.$dialog) {
      throw `The dialog could not be loaded`;
    }

    // the dialog sends a backend error
    if (data.$dialog.error) {
      throw data.$dialog.error;
    }

    // open the dialog and keep the dialog props in the store
    this.$store.dispatch("dialog", data.$dialog);

    // return the dialog object if needed
    return data.$dialog;
  } catch (e) {
    console.error(e);
    this.$store.dispatch("notification/error", e);
  }
}