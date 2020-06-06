<script>
import ModelsPicker from "./ModelsPicker.vue";

export default {
  extends: ModelsPicker,
  methods: {
    map(response) {
      response.data = response.data.map(item => {
        if (response.model) {
          item.options = [
            {
              option: "enter",
              icon: "angle-right",
              tooltip: this.$t("open"),
              disabled: !item.hasChildren
            }
          ];
        }

        return item;
      });

      return response;
    }
  }
}
</script>
