export function read(e) {
  if (!e) {
    return null;
  }

  if (typeof e === "string") {
    return e;
  }

  if (e instanceof ClipboardEvent) {
    e.preventDefault();
    const html =
      e.clipboardData.getData("text/html") ||
      e.clipboardData.getData("text/plain") ||
      null;

    if (html) {
      return html.replace(/\u00a0/g, " ");
    }
  }

  return null;
}

export function write(value, e) {
  // create pretty json of objects and arrays
  if (typeof value !== "string") {
    value = JSON.stringify(value, null, 2);
  }

  // use the optional native clipboard event to copy
  if (e && e instanceof ClipboardEvent) {
    e.preventDefault();
    e.clipboardData.setData("text/plain", value);

    return true;
  }

  // fall back to little execCommand hack with a temporary textarea
  const input = document.createElement("textarea");
  input.value = value;
  document.body.append(input);

  // iOS
  if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
    input.contentEditable = true;
    input.readOnly = true;

    const range = document.createRange();
    range.selectNodeContents(input);

    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
    input.setSelectionRange(0, 999999);

    // everything else
  } else {
    input.select();
  }

  document.execCommand("copy");
  input.remove();

  return true;
}

export default {
  read,
  write
};
