<template>
  <div class="k-file-preview">
    <k-view class="k-file-preview-layout">
      <div class="k-file-preview-image">
        <k-link
          :to="url"
          :title="$t('open')"
          class="k-file-preview-image-link"
          target="_blank"
        >
          <k-item-image :image="image" layout="cards" />
        </k-link>
      </div>
      <div class="k-file-preview-details">
        <ul>
          <li v-for="detail in details" :key="detail.title">
            <h3>{{ detail.title }}</h3>
            <p>
              <k-link
                v-if="detail.link"
                :to="detail.link"
                tabindex="-1"
                target="_blank"
              >
                /{{ detail.text }}
              </k-link>
              <template v-else>
                {{ detail.text }}
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
    details: Array,
    image: Object,
    url: String
  }
};
</script>
<style>
.k-file-preview {
  background: var(--color-gray-800);
}
.k-file-preview-layout {
  display: grid;
  grid-template-columns: 50% auto;
}
.k-file-preview-layout > * {
  min-width: 0;
}

.k-file-preview-image {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-pattern);
}
.k-file-preview-image-link {
  display: block;
  width: 100%;
  padding: min(4vw, 3rem);
  outline: 0;
}
.k-file-preview-image-link[data-tabbed="true"] {
  box-shadow: none;
  outline: 2px solid var(--color-focus);
  outline-offset: -2px;
}

.k-file-preview-details {
  padding: 1.5rem;
  flex-grow: 1;
}
.k-file-preview-details ul {
  line-height: 1.5em;
  max-width: 50rem;
  display: grid;
  grid-gap: 1.5rem 3rem;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
}
.k-file-preview-details h3 {
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--color-gray-500);
}
.k-file-preview-details p,
.k-file-preview-details a {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: rgba(255, 255, 255, 0.75);
  font-size: var(--text-sm);
}

@media screen and (min-width: 30em) {
  .k-file-preview-details ul {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}
@media screen and (max-width: 65em) {
  .k-file-preview-layout {
    padding: 0 !important;
  }
}
@media screen and (min-width: 65em) {
  .k-file-preview-layout {
    grid-template-columns: 33.333% auto;
    align-items: center;
  }
  .k-file-preview-details {
    padding: 3rem;
  }
}
@media screen and (min-width: 90em) {
  .k-file-preview-layout {
    grid-template-columns: 25% auto;
  }
}
</style>
