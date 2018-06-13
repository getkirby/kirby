export default {
  methods: {
    open() {
      this.$refs.dialog.open();
      this.$emit("open");
    },
    close() {
      this.$refs.dialog.close();
      this.$emit("close");
    },
    success(payload) {
      this.$refs.dialog.close();

      if (payload.route) {
        this.$router.push(payload.route);
      }

      if (payload.message) {
        this.$store.dispatch("notification/success", payload.message);
      }

      if (payload.event) {
        this.$events.$emit(payload.event);
      }

      this.$emit("success");
    }
  }
};
