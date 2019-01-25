<script>
import UrlFieldPreview from "./UrlFieldPreview.vue";

export default {
  extends: UrlFieldPreview,
  computed: {
    link() {
      return "mailto:" + this.value;
    }
  }
}
</script>
