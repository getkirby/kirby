<template>
  <div class="k-file-preview bg-black-light text-white">
    <k-view class="k-file-preview-layout items-center">
      <k-link
        :to="link"
        :title="$t('open')"
        class="flex items-center justify-center"
        target="_blank"
      >
        <k-item-figure
          layout="card"
          :preview="preview"
        />
      </k-link>
      <div class="k-file-preview-details text-sm">
        <ul>
          <li>
            <h3>{{ $t("template") }}</h3>
            <p>{{ template }}</p>
          </li>
          <li>
            <h3>{{ $t("mime") }}</h3>
            <p>{{ mime }}</p>
          </li>
          <li>
            <h3>{{ $t("url") }}</h3>
            <p>
              <k-link
                v-if="link"
                :to="link"
                tabindex="-1"
                target="_blank"
              >
                {{ linkText || link }}
              </k-link>
            </p>
          </li>
          <li>
            <h3>{{ $t("size") }}</h3>
            <p>{{ size }}</p>
          </li>
          <li>
            <h3>{{ $t("dimensions") }}</h3>
            <p>
              <template v-if="width || height">
                {{ width }} &times; {{ height }} {{ $t("pixel") }}
              </template>
            </p>
          </li>
          <li>
            <h3>{{ $t("orientation") }}</h3>
            <p>
              <template v-if="orientation">
                {{ $t("orientation." + orientation) }}
              </template>
            </p>
          </li>
        </ul>
      </div>
    </k-view>
  </div>
</template>

<script>
export default {
  props: {
    height: [Number, String],
    icon: Object,
    image: String,
    link: String,
    linkText: String,
    mime: String,
    orientation: String,
    size: String,
    template: String,
    width: [Number, String]
  },
  computed: {
    preview() {
      let preview = {
        image: this.image,
        back: 'pattern',
        ratio: '1/1'
      };

      if (this.icon) {
        preview.icon  = this.icon.type;
        preview.color = this.icon.color;
        preview.size  = this.icon.size || 'large';
      }

      return preview;
    }
  }
};
</script>
<style lang="scss">
/** Layout **/
.k-file-preview-layout {
  display: grid;
  @media screen and (max-width: $breakpoint-md) {
    padding: 0 !important;
  }
  @media screen and (min-width: $breakpoint-sm) {
    grid-template-columns: 50% auto;
  }
  @media screen and (min-width: $breakpoint-md) {
    grid-template-columns: 1fr auto;
  }
  @media screen and (min-width: $breakpoint-md) {
    grid-template-columns: 33.33% auto;
  }
  @media screen and (min-width: $breakpoint-lg) {
    grid-template-columns: 25% auto;
  }
}
.k-file-preview-layout > * {
  min-width: 0;
}

/** Image/icon **/
.k-file-preview .k-item-figure {
  width: 100%;
}

/** Details Table **/
.k-file-preview-details {
  padding: 1.5rem;

  @media screen and (min-width: $breakpoint-md) {
    padding: 3rem;
  }
}
.k-file-preview-details ul {
  line-height: 1.5em;
  max-width: 50rem;
  display: grid;
  grid-gap: 1.5rem 3rem;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));

  @media screen and (min-width: $breakpoint-sm) {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}
.k-file-preview-details h3 {
  color: $color-gray-400;
}
.k-file-preview-details p {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-file-preview-details p:empty::after {
  content: "—";
}
.k-file-preview-details p a {
  display: block;
  width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
