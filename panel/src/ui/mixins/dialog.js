export default {
  methods: {
    open() {
      this.$refs.dialog.open();
      this.$emit("open");
    },
    close() {
      this.$refs.dialog.close();
      this.$emit("close");
    }
  }
};
