const languages = {
  de: {
    code: "de",
    name: "Deutsch",
    locale: "de_DE",
    direction: "ltr",
  },
  en: {
    code: "en",
    default: true,
    name: "English",
    locale: "en_US",
    direction: "ltr",
  }
};

export default {
  get: {
    "languages": () => (languages),
    "languages/de": () => (languages.de),
    "languages/en": () => (languages.en)
  }
};
