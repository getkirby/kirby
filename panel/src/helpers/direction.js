
export default function (app) {
  const defaultLanguage = app.$languages.default || null;
  const language = app.$languages.current || null;
  const multilang = app.$system.multilang || false;
  const userLanguage = app.$user ? app.$user.language : null;
  const direction = language ? language.direction : null;

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
      app.disabled === false &&
      (
          language.direction !== defaultLanguage.direction ||
          userLanguage !== language.code
      )
  ) {
    return direction;
  }
}
