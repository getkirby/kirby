import "./regex";

export default (string, rules = []) => {

  const separator = "-";
  const allowed = "a-z0-9";

  string = string.trim().toLowerCase();

  // replace according to language and ascii rules
  rules.forEach(ruleset => {
    if (ruleset) {
      Object.keys(ruleset).forEach(rule => {
        string = string.replace(new RegExp(rule, "g"), ruleset[rule]);
      });
    }
  });

  // remove all other non-ASCII characters
  string = string.replace("/[^\x09\x0A\x0D\x20-\x7E]/", "");

  // replace spaces with simple dashes
  string = string.replace(new RegExp("[^" + allowed + "]", "ig"), separator);

  // remove double separators
  string = string.replace(new RegExp("[" + RegExp.escape(separator) + "]{2,}", "g"), separator);

  // replace slashes with dashes
  string = string.replace("/", separator);

  // trim leading and trailing non-word-chars
  string = string.replace(new RegExp("^[^a-z0-9]+", "g"), "");
  string = string.replace(new RegExp("[^a-z0-9]+$", "g"), "");

  return string;
};
