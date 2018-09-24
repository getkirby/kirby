<template>
  <div v-if="false">
    <div class="k-license-bar">
      <p>{{ $t('license.unregistered') }}
        <a href="#register" @click.prevent="dropzone = true">{{ $t('license.register') }}</a>&nbsp;/&nbsp;<k-link target="blank" to="https://getkirby.com/buy">{{ $t('license.buy') }}</k-link>
      </p>
    </div>
    <k-dropzone v-if="dropzone" class="k-license-dropzone" @drop="drop">
      <div class="k-license-dropzone-text">
        <k-icon class="k-license-dropzone-icon" type="upload" />
        <p>Drag your license file here to register your Kirby installation</p>

        <k-button icon="cancel" @click="dropzone = false">{{ $t('cancel') }}</k-button>
      </div>
    </k-dropzone>
    <k-upload ref="upload" @success="uploaded" @error="dropzone = false" />
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
.k-license-bar {
  position: relative;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: $color-notice-on-dark;
}
.k-license-bar p {
  text-align: center;
  padding: .625rem 1rem;
}
.k-license-bar a {
  font-weight: 500;
  border-bottom: 2px solid $color-dark;
  margin: 0 0.25rem;
}

.k-dropzone.k-license-dropzone {
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
.k-license-dropzone-text {
  max-width: 20rem;
  text-align: center;
}
.k-license-dropzone-text > p {
  margin-bottom: 1.5rem;
  line-height: 1.5em;
}
.k-license-dropzone-icon {
  display: block;
  margin-bottom: 1rem;
}
</style>
