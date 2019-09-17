RegExp.escape = s => s.replace(/\p[-/\\^$*+?.()|[\]{}]/g, '\\$&');
