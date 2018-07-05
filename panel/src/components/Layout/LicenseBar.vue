<template>
  <div v-if="false">
    <div class="kirby-license-bar">
      <p>This is an unregistered demo of Kirby.
        <a href="#register" @click.prevent="dropzone = true">Register</a>&nbsp;or&nbsp;<a target="blank" href="https://getkirby.com/buy">Buy a license</a>
      </p>
    </div>
    <kirby-dropzone v-if="dropzone" class="kirby-license-dropzone" @drop="drop">
      <div class="kirby-license-dropzone-text">
        <kirby-icon class="kirby-license-dropzone-icon" type="upload" />
        <p>Drag your license file here to register your Kirby installation</p>

        <kirby-button icon="cancel" @click="dropzone = false">Cancel</kirby-button>
      </div>
    </kirby-dropzone>
    <kirby-upload ref="upload" @success="uploaded" @error="dropzone = false" />
  </div>
</template>

<script>
import config from "@/config/config.js";

export default {
  data() {
    return {
      dropzone: false
    };
  },
  computed: {
    isVisible() {

      if (this.$route.meta.outside) {
        return false;
      }

      if (!this.$store.state.system.info.isOk) {
        return false;
      }

      if (!this.$store.state.system.info.license) {
        return true;
      }

      return false;

    }
  },
  methods: {
    drop(files) {
      this.$refs.upload.drop(files, {
        url: config.api + "/system/register",
        multiple: false
      });
    },
    uploaded() {
      this.$store.dispatch(
        "notification/success",
        "Thank you for registering Kirby!"
      );

      // reload the system information to gather the license info
      this.$store.dispatch("system/load", true);

      this.dropzone = false;
    }
  }
};
</script>

<style lang="scss">
.kirby-license-bar {
  position: relative;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: $color-notice-on-dark;
}
.kirby-license-bar p {
  text-align: center;
  padding: .625rem 1rem;
}
.kirby-license-bar a {
  font-weight: 500;
  border-bottom: 2px solid $color-dark;
  margin: 0 0.25rem;
}

.kirby-dropzone.kirby-license-dropzone {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: z-index("dialog");
  background: rgba($color-dark, 0.9);
  color: $color-white;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kirby-license-dropzone-text {
  max-width: 20rem;
  text-align: center;
}
.kirby-license-dropzone-text > p {
  margin-bottom: 1.5rem;
  line-height: 1.5em;
}
.kirby-license-dropzone-icon {
  display: block;
  margin-bottom: 1rem;
}
</style>
