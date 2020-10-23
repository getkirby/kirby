<template>
  <k-view style="padding-top: 3rem">

    <k-headline style="margin-bottom: .75rem">Layout</k-headline>
    <template v-if="layouts.length">
      <k-draggable class="k-layouts" :handle="true">
        <section
          v-for="(layout, layoutIndex) in layouts"
          :key="layout.id"
          class="k-layout"
        >
          <k-sort-handle class="k-layout-handle" />
          <k-grid class="k-layout-columns">
            <k-column
              v-for="(column, columnIndex) in layout.columns"
              :key="columnIndex"
              :width="column.width"
              class="k-layout-column"
            >
              <k-builder-blocks
                :fieldsets="fieldsets"
                :value="column.blocks"
                :endpoints='{"field":"pages/builder/fields/builder","section":"pages/builder/sections/main-fields","model":"pages/builder"}'
                group="layout"
              />
            </k-column>
          </k-grid>
          <k-dropdown class="k-layout-options">
            <k-button icon="angle-down" @click="$refs['layout-' + layout.id][0].toggle()" />
            <k-dropdown-content :ref="'layout-' + layout.id" align="right">
              <k-dropdown-item icon="angle-up" @click="selectLayout(layoutIndex)">Insert before</k-dropdown-item>
              <k-dropdown-item icon="angle-down" @click="selectLayout(layoutIndex + 1)">Insert after</k-dropdown-item>
              <hr>
              <k-dropdown-item icon="trash" @click="removeLayout(layout)">Delete layout</k-dropdown-item>
            </k-dropdown-content>
          </k-dropdown>
        </section>
      </k-draggable>
    </template>
    <template v-else>
      <k-empty
        icon="dashboard"
        class="k-layout-empty"
        @click="selectLayout(0)"
      >
        No layouts yet
      </k-empty>
    </template>


    <k-dialog
      ref="selector"
      :cancel-button="false"
      :submit-button="false"
      size="medium"
      class="k-layout-selector"
    >
      <k-headline>Select a layout</k-headline>
      <ul>
        <li
          v-for="(layoutOption, layoutOptionIndex) in layoutOptions"
          :key="layoutOptionIndex"
          class="k-layout-selector-option"
        >
          <k-grid @click.native="addLayout(layoutOption)">
            <k-column
              v-for="(column, columnIndex) in layoutOption"
              :key="columnIndex"
              :width="column"
            >
            </k-column>
          </k-grid>
        </li>
      </ul>
    </k-dialog>

    <k-overlay ref="editor" :dimmed="false" class="k-builder-editor">
      <div class="k-builder-editor-box" @mousedown.stop>
        <header class="k-builder-editor-header">
          <k-headline>Blocks</k-headline>
          <k-button icon="check" @click="$refs.editor.close()">Done</k-button>
        </header>
        <div class="k-builder-editor-body">
          <k-builder-blocks
            ref="editorBlocks"
            :compact="false"
            :endpoints="endpoints"
            :fieldsets="fieldsets"
            :max="max"
            :value="blocks"
            v-on="$listeners"
          />
        </div>
      </div>
    </k-overlay>

  </k-view>
</template>

<script>

import Blocks from "@/components/Blocks/Blocks.vue";

export default {
  components: {
    "k-blocks": Blocks
  },
  data() {
    return {
      fieldsets: {
        heading: {
          icon: "title",
          name: "heading",
          label: "Heading",
          type: "heading",
          tabs: {
            main: {
              fields: {
                text: {
                  name: "text",
                  label: "Text",
                  type: "text"
                }
              }
            }
          }
        },
        bodytext: {
          icon: "text",
          name: "Text",
          label: "Text",
          type: "bodytext",
          tabs: {
            main: {
              fields: {
                text: {
                  name: "text",
                  label: "Text",
                  type: "textarea"
                }
              }
            }
          }
        },
        gallery: {
          icon: "image",
          name: "Images",
          label: "Images",
          type: "gallery",
          tabs: {
            images: {
              label: "Images",
              fields: {
                images: {
                  name: "images",
                  type: "files",
                  layout: "cards",
                  label: "Images",
                  multiple: true,
                  image: {
                    ratio: "1/1",
                  }
                }
              },
            },
            style: {
              label: "Style",
              fields: {
                margin: {
                  label: "Margin",
                  type: "text"
                }
              }
            }
          }
        },
        cta: {
          icon: "bolt",
          name: "CTA",
          label: "CTA",
          type: "cta",
          tabs: {
            main: {
              fields: {
                text: {
                  name: "text",
                  label: "Button Text",
                  type: "text"
                }
              }
            }
          }
        },
      },
      layoutOptions: [
        ["1/1"],
        ["1/2", "1/2"],
        ["1/3", "1/3", "1/3"],
        ["1/3", "2/3"],
        ["2/3", "1/3"],
        ["1/4", "1/4", "1/4", "1/4"],
        ["1/1", "1/4", "1/4", "1/4", "1/4"],
      ],
      layouts: [
        {
          id: "layout-a",
          columns: [
            { id: "a", width: "1/2", blocks: [] },
            { id: "b", width: "1/2", blocks: [] }
          ]
        }
      ],
      nextIndex: null,
    }
  },
  methods: {
    addLayout(columns) {

      let layout = {
        id: +new Date(),
        columns: []
      };

      columns.forEach(width => {
        layout.columns.push({ width: width, blocks: [] });
      });

      this.layouts.splice(this.nextIndex, 0, layout);
      this.$refs.selector.close();
    },
    prependLayout(layout) {
      this.$refs.selector.open();
    },
    removeLayout(layout) {
      const index = this.layouts.findIndex(element => element.id === layout.id);

      if (index !== -1) {
        this.$delete(this.layouts, index);
      }
    },
    selectLayout(index) {
      this.nextIndex = index;
      this.$refs.selector.open();
    }
  }
};
</script>

<style lang="scss">
$layout-color-border: $color-blue-300;
$layout-padding: .5rem;

.k-layout {
  position: relative;
  background: $layout-color-border;
  margin: 0 1.5rem;
  border: 1px solid $layout-color-border;
}
.k-layout:not(:last-child) {
  margin-bottom: -1px;
}
.k-layout-handle,
.k-layout-options {
  position: absolute;
  top: -1px;
  bottom: -1px;
  height: calc(100% + 2px);
  width: 1.5rem;
  left: -1.5rem;
  border: 1px solid $layout-color-border;
  color: $color-blue-300;
}
.k-layout-options {
  left: auto;
  right: -1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-layout-options > .k-button {
  height: 100%;
  width: 100%;
}
.k-layout:hover .k-layout-options,
.k-layout:hover .k-layout-handle {
  color: $color-blue-400;
}
.k-layout-columns {
  grid-gap: 1px;
}
.k-layout-column {
  background: $color-background;
}
.k-layout-column > div {
  padding: $layout-padding;
  height: 100%;
}
.k-layout-column:hover > div {
  background: rgba($color-blue-200, .5);
}


.k-layout-selector.k-dialog {
  background: #313740;
  color: $color-white;
}

.k-layout-selector .k-headline {
  margin-bottom: 1.5rem;
  line-height: 1;
  margin-top: -.25rem;
}

.k-layout-selector ul {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 1.5rem;
}
.k-layout-selector-option .k-grid {
  height: 5rem;
  grid-gap: 2px;
  box-shadow: $shadow;
  cursor: pointer;
}
.k-layout-selector-option:hover {
  outline: 2px solid $color-green-300;
  outline-offset: 2px;
}
.k-layout-selector-option:last-child {
  margin-bottom: 0;
}
.k-layout-selector-option .k-column {
  display: flex;
  background: rgba(#fff, .2);
  justify-content: center;
  font-size: $text-xs;
  align-items: center;
}

.k-layout-column .k-builder-field-empty {
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 38px;
  opacity: 0;
}
.k-layout-column .k-builder-field-empty .k-icon {
  border-right: 0;
  margin-right: 0;
}
.k-layout-column .k-builder-field-empty p {
  padding-left: .25rem;
}

.k-layout-add-button {
  margin: .75rem auto;
  display: block;
}


.k-builder-editor {
  display: flex;
  justify-content: flex-end;
}
.k-builder-editor-box {
  position: relative;
  background: #313740;
  flex-basis: 90%;
  flex-shrink: 0;
  max-width: 70rem;
  box-shadow: $shadow-xl;
  display: flex;
  flex-direction: column;
}
.k-builder-editor-box:before {
  content: "";
  position: absolute;
  top: 0;
  left: -20rem;
  bottom: 0;
  height: 100%;
  width: 20rem;
  pointer-events: none;
  background: linear-gradient(to left, rgba(#313740, 0), rgba(#313740, .3));
}
.k-builder-editor-body .k-builder-block:not([data-compact]) {
  background: $color-background;
  box-shadow: $shadow-md;
}
.k-builder-editor-header {
  height: 2.5rem;
  line-height: 1;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: $color-white;
  flex-shrink: 0;
}
.k-builder-editor-header .k-headline {
  font-weight: $font-normal;
  font-size: $text-sm;
  line-height: 1;
}
.k-builder-editor-header .k-button .k-icon {
  color: $color-green-400;
}
.k-builder-editor-body {
  padding: 1.5rem;
  padding-top: .75rem;
  overflow: auto;
  flex-grow: 1;
}

</style>
