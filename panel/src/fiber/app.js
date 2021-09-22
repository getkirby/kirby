
import Fiber from "./index";

export default {
  name: "Fiber",
  data() {
    return {
      component: null,
      page: window.fiber,
      key: null
    };
  },
  created() {
    Fiber.init({
      page: window.fiber,
      csrf: window.fiber.$system.csrf,
      swap: async ({ component, page, preserveState }) => {
        this.component = component;
        this.page = page;
        this.key = preserveState ? this.key : Date.now();
        this.$store.dispatch("navigate");
        document.documentElement.style.overflow = "visible";
      }
    });
  },
  render(h) {
    if (this.component) {
      return h(this.component, {
        key: this.key,
        props: this.page.$view.props
      });
    }
  }
}