<script>
import UrlFieldPreview from "./UrlFieldPreview.vue";

export default {
  extends: UrlFieldPreview,
  computed: {
    link() {
      return this.value !== null && this.value.length > 0 ? 'mailto:' + this.value : null;
    }
  }
}
</script>
