/**
 * Defines dialogs via JS object
 *
 * @example
 * this.$dialog({
 *   component: 'k-remove-dialog',
 *   props: {
 *      text: 'Do you really want to delete this?'
 *   },
 *   submit: () => {},
 *   cancel: () => {}
 * });
 *
 * @param {Object} dialog
 * @return {Object}
 */
const syncDialog = async function (dialog) {
  return {
    cancel: null,
    submit: null,
    props: {},
    ...dialog
  };
};

/**
 * Loads the dialog setup from the server
 *
 * @example
 * this.$dialog('some/dialog');
 *
 * @example
 * this.$dialog('some/dialog', () => {
 *  // on submit
 * });
 *
 * @example
 * this.$dialog('some/dialog', {
 *   query: {
 *     template: 'some-template'
 *   },
 *   submit: () => {},
 *   cancel: () => {}
 * });
 *
 * @param {String} path
 * @param {Function|Object} options
 */
const asyncDialog = async function (path, options = {}) {
  let submit = null;
  let cancel = null;

  if (typeof options === "function") {
    submit = options;
    options = {};
  } else {
    submit = options.submit;
    cancel = options.cancel;
  }

  // load the dialog definition from the server
  let result = await this.$fiber.request("dialogs/" + path, {
    ...options,
    type: "$dialog"
  });

  // JSON parsing failed. The dialog is invalid
  if (typeof result !== "object") {
    return false;
  }

  // add the event handlers to the result
  // they will be stored in Vuex to be available
  // in the Fiber dialog component
  result.submit = submit || null;
  result.cancel = cancel || null;

  return result;
};

/**
 * Opens a dialog by either loading its
 * definition from the server in a Fiber request
 * or by the given object definition (first arg).
 *
 * @param {String|Object} path
 * @param {Function|Object} options
 * @returns
 */
export default async function (path, options = {}) {
  let dialog = null;

  // dialog is defined as object and will not
  // be loaded from the API. All options that normally
  // will be returned from the API request must be set in
  // the object (component, props, etc.)
  if (typeof path === "object") {
    dialog = await syncDialog.call(this, path);
  } else {
    dialog = await asyncDialog.call(this, path, options);
  }

  // the request could not be parsed
  // the fatal view is taking over
  if (!dialog) {
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
