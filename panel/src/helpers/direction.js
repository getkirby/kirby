
export default function ($this) {
  const defaultLanguage = $this.$store.state.languages.default || null;
  const language = $this.$store.state.languages.current || null;
  const multilang = $this.$store.state.system.info.multilang || false;
  const userLanguage = $this.$store.state.system.info.user ? $this.$store.state.system.info.user.language : null;
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
      $this.disabled === false &&
      (
          language.direction !== defaultLanguage.direction ||
          userLanguage !== language.code
      )
  ) {
    return direction;
  }
}
