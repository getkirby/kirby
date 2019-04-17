import Api from "@getkirby/api-js";

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
          name: language.name,
          default: language.default,
          direction: language.direction
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
          context.commit("SET_CURRENT", currentLanguage);
          return;
        }
      }

      context.commit("SET_CURRENT", defaultLanguage || languages[0]);

    },
    load(context) {
      return Api.get("languages").then(response => {
        context.dispatch("install", response.data);
      });
    }
  }
};
