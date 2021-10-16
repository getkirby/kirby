/**
 * RegExp.escape(string)
 */
RegExp.escape = function (string) {
  return string.replace(new RegExp("[-/\\\\^$*+?.()[\\]{}]", "gu"), "\\$&");
};
