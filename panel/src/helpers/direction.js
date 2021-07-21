
export default (app) => {
  const defaultLanguage = app.$languages ? app.$languages.find(language => language.default === true) : null;
  const language        = app.$language || null;
  const multilang       = app.$multilang || false;
  const userLanguage    = app.$user ? app.$user.language : null;
  const direction       = language ? language.direction : null;

  /**
   * Return LTR/RTL direction only when;
   * - Multilang enabled
   * - Current editing language exists
   * - Input is not disabled
   * - Editing language direction not equal with default language direction or
   *   user language not equal with editing language
   *
   */
  if (
      multilang &&
      language &&
      defaultLanguage &&
      app.disabled === false &&
      (
        language.direction !== defaultLanguage.direction ||
        userLanguage !== language.code
      )
  ) {
    return direction;
  }
}
