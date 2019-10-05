RegExp.escape = s => s.replace(/[\p{L}]|[-\/\\^$*+?.()|[\]{}]+/u, '\\$&');
