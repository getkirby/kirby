import en from "../../../i18n/translations/en.json";

export default function(string, replace = {}) {
  let message = en[string] || "$t('" + string + "')";

  Object.keys(replace).forEach(key => {
    const regex = new RegExp("{" + key + "}", "g");
    message = message.replace(regex, replace[key]);
  });

  return message;
};
