<template>
  <div class="kirby-file-preview">
    <kirby-view class="kirby-file-preview-layout">
      <div class="kirby-file-preview-image">
        <a :href="file.url" target="_blank">
          <kirby-image
            v-if="preview.image"
            :src="preview.image"
            back="none"
          />
          <kirby-icon
            v-else
            :type="preview.icon || 'document'"
            :style="{ color: preview.color }"
            class="kirby-file-preview-icon"
          />
        </a>
      </div>
      <div class="kirby-file-preview-details">
        <ul>
          <li>
            <h3>{{ $t("file.template") }}</h3>
            <p>{{ file.template || "—" }}</p>
          </li>
          <li>
            <h3>{{ $t("file.mime") }}</h3>
            <p>{{ file.mime }}</p>
          </li>
          <li>
            <h3>{{ $t("file.url") }}</h3>
            <p>
              <a :href="file.url" target="__blank">/{{ file.id }}</a>
            </p>
          </li>
          <li>
            <h3>{{ $t("file.size") }}</h3>
            <p>{{ file.niceSize }}</p>
          </li>
          <li>
            <h3>{{ $t("file.dimensions") }}</h3>
            <p v-if="file.dimensions">{{ file.dimensions.width }}&times;{{ file.dimensions.height }} {{ $t("file.dimensions.pixel") }}</p>
            <p v-else>—</p>
          </li>
          <li>
            <h3>{{ $t("file.orientation") }}</h3>
            <p v-if="file.dimensions">{{ $t("file.orientation." + file.dimensions.orientation) }}</p>
            <p v-else>—</p>
          </li>
        </ul>
      </div>
    </kirby-view>
  </div>
</template>

<script>
export default {
  props: {
    file: Object
  },
  computed: {
    preview() {
      return this.$api.files.preview(this.file);
    }
  }
};
</script>
<style lang="scss">
.kirby-file-preview {
  background: lighten($color-dark, 10%);
}
.kirby-file-preview-layout {
  display: grid;

  @media screen and (max-width: $breakpoint-medium) {
    padding: 0 !important;
  }

  @media screen and (min-width: $breakpoint-small) {
    grid-template-columns: 50% auto;
  }
  @media screen and (min-width: $breakpoint-medium) {
    display: flex;
    align-items: center;
  }
}
.kirby-file-preview-layout > * {
  min-width: 0;
}
.kirby-file-preview-image {
  position: relative;
  background: url($pattern);

  @media screen and (min-width: $breakpoint-medium) {
    width: 33.33%;
  }
  @media screen and (min-width: $breakpoint-large) {
    width: 25%;
  }
}
.kirby-file-preview-image .kirby-image span {
  overflow: hidden;
  padding-bottom: 66.66%;

  @media screen and (min-width: $breakpoint-small) and (max-width: $breakpoint-medium) {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    padding-bottom: 0 !important;
  }

  @media screen and (min-width: $breakpoint-medium) {
    padding-bottom: 100%;
  }
}
.kirby-file-preview-image img {
  padding: 3rem;
}
.kirby-file-preview-icon {
  position: relative;
  display: block;
  padding-bottom: 100%;
  overflow: hidden;
  color: rgba($color-white, 0.5);
}
.kirby-file-preview-icon svg {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(4);
}
.kirby-file-preview-details {
  padding: 1.5rem;
  flex-grow: 1;

  @media screen and (min-width: $breakpoint-medium) {
    padding: 3rem;
  }
}
.kirby-file-preview-details ul {
  line-height: 1.5em;
  max-width: 50rem;
  display: grid;
  grid-gap: 1.5rem 3rem;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));

  @media screen and (min-width: $breakpoint-small) {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}
.kirby-file-preview-details h3 {
  font-size: $font-size-small;
  font-weight: 500;
  color: $color-light-grey;
}
.kirby-file-preview-details p {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: rgba($color-white, 0.75);
  font-size: $font-size-small;
}
.kirby-file-preview-details p a {
  display: block;
  width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
