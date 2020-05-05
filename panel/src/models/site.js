
export default function (Vue) {
  return {
    async options() {
      const site    = await Vue.prototype.$api.get("site", {select: "options"});
      const options = site.options;
      let result    = [];

      result.push({
        click: "rename",
        icon: "title",
        text: Vue.i18n.translate("rename"),
        disabled: !options.changeTitle
      });

      return result;
    }
  };
}
