RegExp.escape = s => s.replace(new RegExp("[\\p{L}]|[-/\\\\^$*+?.()[\\]{}]", "gu"), '\\$&');
