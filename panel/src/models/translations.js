import Api from "@/api/api.js";

export default {
  async options() {
    const translations = await Api.translations.list();
    return translations.data.map(translation => ({
      value: translation.id,
      text: translation.name
    }));
  }
}
