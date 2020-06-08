export default (Vue, store) => ({
  async install(values) {
    const user = await Vue.$api.system.install(values);
    await store.dispatch("user/current", user);
    return user;
  },
  async load(reload = false) {
    // reuse cached system info
    if (
      reload === false &&
      store.state.system.status.isReady &&
      store.state.user.current
    ) {
      return store.state.system;
    }

    // reload the system info
    try {
      const response = await Vue.$api.system.get();

      // set system info
      store.dispatch("system/set", {
        kirbytext:    response.kirbytext,
        license:      response.license,
        multilang:    response.multilang,
        requirements: response.requirements,
        site:         response.site,
        title:        response.title,
        version:      response.version
      });

      // set system update info
      store.dispatch("system/update", response.updateStatus)

      // set system status
      store.dispatch("system/status", {
        isInstallable: response.isInstallable,
        isInstalled:   response.isInstalled,
        isLocale:      response.isLocal,
        isOk:          response.isOk,
      });

      // set content languages
      if (response.languages) {
        store.dispatch("languages/install", response.languages);
      }

      // set rules for languages
      if (response.ascii || response.slugs) {
        store.dispatch("languages/rules", {
          ascii: response.ascii,
          slugs: response.slugs
        });
      }

      // set UI translations
      store.dispatch("translation/default", response.defaultTranslation);
      store.dispatch("translation/install", response.translation);
      store.dispatch("translation/activate", response.translation.id);

      // set user
      if (response.user) {
        store.dispatch("user/current", response.user);
      }

    } catch (error) {
      store.dispatch("system/status", {
        ...store.state.system.status,
        isOk: false,
        error: error.message
      });

    } finally {
      store.dispatch("system/status", {
        ...store.state.system.status,
        isReady: store.state.system.status.isInstalled &&
                 store.state.system.status.isOk,
      });

      return store.state.system;
    }
  },
  async register(registration) {
    await Vue.$api.system.register(registration);
    await store.dispatch("system/register", registration.license);
    store.dispatch("notification/success", Vue.$t("license.register.success"));
  },
  title(title) {
    store.dispatch("title", title);
    let site = store.state.system.title;

    if (title) {
      site = title + " | " + site;
    }

    document.title = site;
  },
  async update() {
    const response = await Vue.$api.system.update();
    store.dispatch("system/update", response);
    return response;
  }
});
