/* eslint-disable */
/**
 * @param {Object} options
 * JS implementation of
 * https://github.com/bubkoo/natsort
 */
export default (options) => {
  options = options || {};

  var GREATER = options.desc ? -1 : 1;
  var SMALLER = -GREATER;

  var ore = /^0/;
  var sre = /\s+/g;
  var tre = /^\s+|\s+$/g;
  // unicode
  var ure = /[^\x00-\x80]/;
  // hex
  var hre = /^0x[0-9a-f]+$/i;
  // numeric
  // var nre = /(^([+\-]?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?(?=\D|\s|$))|^0x[\da-fA-F]+$|\d+)/g;
  var nre =
    /(0x[\da-fA-F]+|(^[\+\-]?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?(?=\D|\s|$))|\d+)/g;
  // datetime
  var dre =
    /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/;

  var normalize = options.insensitive
    ? function (s) {
        return lowerCase("" + s).replace(tre, "");
      }
    : function (s) {
        return ("" + s).replace(tre, "");
      };

  function lowerCase(s) {
    if (s.toLocaleLowerCase) {
      return s.toLocaleLowerCase();
    }
    return s.toLowerCase();
  }

  function tokenize(s) {
    return s
      .replace(nre, "\0$1\0")
      .replace(/\0$/, "")
      .replace(/^\0/, "")
      .split("\0");
  }

  function parse(s, l) {
    // normalize spaces; find floats not starting with '0',
    // string or 0 if not defined (Clint Priest)
    return (
      ((!s.match(ore) || l === 1) && parseFloat(s)) ||
      s.replace(sre, " ").replace(tre, "") ||
      0
    );
  }

  return function (a, b) {
    // trim pre-post whitespace
    var x = normalize(a);
    var y = normalize(b);

    // return immediately if at least one of the values is empty.
    // - empty string < any others
    if (!x && !y) {
      return 0;
    }

    if (!x && y) {
      return SMALLER;
    }

    if (x && !y) {
      return GREATER;
    }

    // tokenize: split numeric strings and default strings
    var xArr = tokenize(x);
    var yArr = tokenize(y);

    // hex or date detection
    var xD = parseInt(x.match(hre), 16) || (xArr.length !== 1 && Date.parse(x));
    var yD =
      parseInt(y.match(hre), 16) ||
      (xD && y.match(dre) && Date.parse(y)) ||
      null;

    // try and sort Hex codes or Dates
    if (yD) {
      if (xD < yD) {
        return SMALLER;
      } else if (xD > yD) {
        return GREATER;
      }
    }

    //console.log('x: ' + xArr.join('@'));
    //console.log('y: ' + yArr.join('@'));

    var xL = xArr.length;
    var yL = yArr.length;

    // handle numeric strings and default strings
    for (var i = 0, l = Math.max(xL, yL); i < l; i++) {
      var xF = parse(xArr[i] || "", xL);
      var yF = parse(yArr[i] || "", yL);

      //console.log('xF: ' + xF);
      //console.log('yF: ' + yF);

      // handle numeric vs string comparison
      // - numeric < string - (Kyle Adams)
      if (isNaN(xF) !== isNaN(yF)) {
        return isNaN(xF) ? GREATER : SMALLER;
      }

      // if unicode use locale comparison
      if (ure.test(xF + yF) && xF.localeCompare) {
        var comp = xF.localeCompare(yF);

        if (comp > 0) {
          return GREATER;
        } else if (comp < 0) {
          return SMALLER;
        } else {
          if (i === l - 1) {
            return 0;
          } else {
            continue;
          }
        }
      }

      if (xF < yF) {
        return SMALLER;
      } else if (xF > yF) {
        return GREATER;
      }
    }

    return 0;
  };
};
