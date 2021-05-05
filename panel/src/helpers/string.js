export default {
  camelToKebab(string) {
    return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase();
  },
  hasEmoji(string) {
    if (typeof string !== "string") {
      return false;
    }

    // Source: https://thekevinscott.com/emojis-in-javascript/
    // eslint-disable-next-line no-misleading-character-class
    const result = string.match(/(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|[\ud83c\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|[\ud83c\ude32-\ude3a]|[\ud83c\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/i);

    return result !== null && result.length !== null;
  },
  lcfirst(string) {
    const str = String(string);
    return str.charAt(0).toLowerCase() + str.substr(1);
  },
  random(length) {
    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
  },
  stripHTML(string) {
    const str = String(string);
    return str.replace(/(<([^>]+)>)/gi, "");
  },
  template(string, values = {}) {

    const opening = "[{]{1,2}[ ]{0,}";
    const closing = "[ ]{0,}[}]{1,2}";

    Object.keys(values).forEach(key => {
      // replace string template with value
      string = string.replace(
        new RegExp(`${opening}${key}${closing}`, "gi"),
        values[key] || "…"
      );

      // for arrays, allow string templates for length/count
      if (Array.isArray(values[key]) === true) {
        string = string.replace(
          new RegExp(`${opening}${key}.count${closing}|${opening}${key}.length${closing}`, "gi"),
          values[key].length || 0
        );
      }
    })

    return string.replace(/{{.*}}/gi, "…");
  },
  ucfirst(string) {
    const str = String(string);
    return str.charAt(0).toUpperCase() + str.substr(1);
  },
  ucwords(string) {
    const str = String(string);
    return str.split(/ /g).map(word => this.ucfirst(word)).join(" ");
  }
};
