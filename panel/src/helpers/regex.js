RegExp.escape = s => s.replace(new RegExp("[-/\\\\^$*+?.()[\\]{}]", "gu"), '\\$&');
