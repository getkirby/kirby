<script>
import UserRoute from "./UserRoute.vue";
import Vue from "vue";

const load = async () => {
  return await Vue.$api.auth.user({ view: "panel" });
};

export default {
  ...UserRoute,
  async beforeRouteEnter(to, from, next) {
    const model = await load();
    next(vm => vm.load(model));
  },
  async beforeRouteUpdate(to, from, next) {
    this.model = null;
    const model = await load();
    this.load(model);
    next();
  },
  computed: {
    ...UserRoute.computed,
    account() {
      return true;
    }
  }
}
</script>
