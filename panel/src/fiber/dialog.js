import Fiber from "./index";

export default async function (path, options = {}) {
  const dialog = await Fiber.request("dialogs/" + path, {
    ...options,
    type: "$dialog"
  });

  // the request could not be parsed
  // the fatal view is taking over
  if (dialog === false) {
    return false;
  }

  // check for an existing dialog component
  if (
    !dialog.component ||
    this.$helper.isComponent(dialog.component) === false
  ) {
    throw Error(`The dialog component does not exist`);
  }

  // make sure the dialog always receives a props object
  dialog.props = dialog.props || {};

  // open the dialog and keep the dialog props in the store
  this.$store.dispatch("dialog", dialog);

  // return the dialog object if needed
  return dialog;
}
