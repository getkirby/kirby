export default (string, rules = [], allowed = "") => {

  const separator = "-";
  allowed = "a-z0-9" + allowed;
  string  = string.trim().toLowerCase();

  // replace according to language and ascii rules
  rules.forEach(ruleset => {
    if (ruleset) {
      Object.keys(ruleset).forEach(rule => {
        const isTrimmed = rule.substr(0,1) !== "/";
        const trimmed   = rule.substring(1, rule.length - 1);
        const regex     = isTrimmed ? rule : trimmed;
        string = string.replace(new RegExp(RegExp.escape(regex), "g"), ruleset[rule]);
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
  string = string.replace(new RegExp("^[^" + allowed + "]+", "g"), "");
  string = string.replace(new RegExp("[^" + allowed + "]+$", "g"), "");

  return string;
};
