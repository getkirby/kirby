<script>
import FormDialog from "../components/FormDialog.vue";

export default {
  extends: FormDialog,
  computed: {
    fields() {
      return {
        license: {
          label: this.$t("license.register.label"),
          type: "text",
          required: true,
          counter: false,
          placeholder: "K3-",
          help: this.$t("license.register.help")
        },
        email: {
          label: this.$t("email"),
          type: "email",
          required: true,
          counter: false
        }
      };
    }
  },
  methods: {
    async load() {
      return {
        license: null,
        email: null
      };
    },
    async submit(values) {
      await this.$api.system.register(values);
    }
  }
}
</script>
