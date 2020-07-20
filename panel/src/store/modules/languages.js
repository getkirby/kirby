import Vue from "vue";

export default {
  namespaced: true,
  state: {
    all: [],
    current: null,
    default: null
  },
  mutations: {
    SET_ALL(state, languages) {
      state.all = languages.map(language => {
        return {
          code: language.code,
          default: language.default,
          direction: language.direction,
          locale: language.locale,
          name: language.name,
          rules: language.rules,
          url: language.url
        };
      });
    },
    SET_CURRENT(state, language) {
      state.current = language;
      if (language && language.code) {
        localStorage.setItem("kirby$language", language.code);
      }
    },
    SET_DEFAULT(state, language) {
      state.default = language;
    }
  },
  actions: {
    current(context, language) {
      context.commit("SET_CURRENT", language);
    },
    install(context, languages) {
      const defaultLanguage = languages.filter(language => language.default)[0];

      context.commit("SET_ALL", languages);
      context.commit("SET_DEFAULT", defaultLanguage);

      // get the current langauge from localstorage
      const currentLanguageCode = localStorage.getItem("kirby$language");

      // search for the current language
      if (currentLanguageCode) {
        const currentLanguage = languages.filter(language => {
          return language.code === currentLanguageCode
        })[0];


        if (currentLanguage) {
          context.dispatch("current", currentLanguage);
          return;
        }
      }

      context.dispatch("current", defaultLanguage || languages[0] || null);

    },
    async load(context) {
      const response = await Vue.$api.languages.list();
      context.dispatch("install", response.data);
    }
  }
};
