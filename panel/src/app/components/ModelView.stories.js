import { action } from "@storybook/addon-actions";

export default {
  title: "App | Views / Model View"
};

export const basic = () => ({
  template: `
    <k-model-view />
  `,
});

export const renamable = () => ({
  data() {
    return {
      title: "My model"
    };
  },
  methods: {
    onRename() {
      let title = null;

      if (title = prompt("Enter a title", this.title)) {
        this.title = title;
      }
    }
  },
  template: `
    <k-model-view
      :rename="true"
      :title="title"
      @rename="onRename"
    />
  `,
});

export const emptyColumns = () => ({
  extends: renamable(),
  computed: {
    columns() {
      return [
        { width: "1/2" },
        { width: "1/2" }
      ];
    }
  },
  template: `
    <k-model-view
      :columns="columns"
      :rename="true"
      :title="title"
      @rename="onRename"
    />
  `,
});

export const columns = () => ({
  extends: emptyColumns(),
  computed: {
    columns() {
      return [
        {
          width: "2/3",
          sections: {
            content: {
              type: "fields",
              fields: {
                excerpt: {
                  type: "text",
                },
                text: {
                  type: "textarea",
                  size: "large"
                },
              },
            },
          },
        },
        {
          width: "1/3",
          sections: {
            meta: {
              type: "fields",
              fields: {
                date: {
                  type: "date",
                },
                tags: {
                  type: "tags"
                }
              },
            },
          },
        },
      ];
    }
  }
});

export const options = () => ({
  extends: columns(),
  computed: {
    options() {
      return [
        { icon: "edit", text: "Edit", option: "edit" },
        { icon: "trash", text: "Delete", option: "delete" }
      ];
    }
  },
  methods: {
    onOption: action("option")
  },
  template: `
    <k-model-view
      :columns="columns"
      :rename="true"
      :options="options"
      :title="title"
      @option="onOption"
      @rename="onRename"
    />
  `,
});

export const customOptions = () => ({
  extends: options(),
  methods: {
    onOpen: action("open")
  },
  template: `
    <k-model-view
      :columns="columns"
      :rename="true"
      :options="options"
      :title="title"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const tabs = () => ({
  extends: customOptions(),
  computed: {
    tabs() {
      return [
        { name: "content", icon: "title", label: "Content" },
        { name: "seo", icon: "search", label: "SEO" }
      ];
    },
    tab() {
      return "content";
    }
  },
  template: `
    <k-model-view
      :columns="columns"
      :rename="true"
      :options="options"
      :tab="tab"
      :tabs="tabs"
      :title="title"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const prevNext = () => ({
  extends: customOptions(),
  computed: {
    prev() {
      return {
        tooltip: "Animals",
        link: "/pages/photography+animals"
      }
    },
    next() {
      return {
        tooltip: "Landscapes",
        link: "/pages/photography+landscapes"
      }
    }
  },
  template: `
    <k-model-view
      :columns="columns"
      :next="next"
      :options="options"
      :prev="prev"
      :rename="true"
      :title="title"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const disabledPrevNext = () => ({
  extends: customOptions(),
  template: `
    <k-model-view
      :columns="columns"
      :options="options"
      :prevnext="false"
      :rename="true"
      :title="title"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const values = () => ({
  extends: customOptions(),
  data() {
    return {
      content: {
        text: "Lorem ipsum",
        date: "2020-05-27"
      }
    };
  },
  methods: {
    onInput: action("input"),
    onSubmit: action("submit"),
  },
  template: `
    <k-model-view
      :columns="columns"
      :options="options"
      :rename="true"
      :title="title"
      v-model="content"
      @input="onInput"
      @option="onOption"
      @rename="onRename"
      @submit="onSubmit"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const changes = () => ({
  extends: values(),
  data() {
    const empty = {
      excerpt: "",
      text: "",
      date: "",
      tags: []
    };

    return {
      original: this.$helper.clone(empty),
      saving: false,
      value: this.$helper.clone(empty),
    };
  },
  computed: {
    hasChanges() {
      return JSON.stringify(this.original) != JSON.stringify(this.value);
    }
  },
  methods: {
    onSave($event) {
      action("save", $event);
      this.saving = true;
      setTimeout(() => {
        this.saving   = false;
        this.original = this.$helper.clone(this.value);
      }, 2000);
    },
    onRevert($event) {
      action("revert")($event);
      this.value = this.$helper.clone(this.original);
    }
  },
  template: `
    <div>
      <k-model-view
        :changes="hasChanges"
        :columns="columns"
        :options="options"
        :rename="true"
        :saving="saving"
        :title="title"
        v-model="value"
        @save="onSave"
        @revert="onRevert"
        @option="onOption"
        @rename="onRename"
      >
        <template slot="options">
          <k-button icon="open" text="Open" @click="onOpen" />
        </template>

        <template slot="footer">
          <k-grid gutter="medium">
            <k-column width="1/2">
              <k-headline class="mb-3">Original</k-headline>
              <k-code-block :code="original" />
            </k-column>
            <k-column width="1/2">
              <k-headline class="mb-3">Modified</k-headline>
              <k-code-block :code="value" />
            </k-column>
          </k-grid>
        </template>

      </k-model-view>

    </div>
  `,
});

export const lock = () => ({
  extends: customOptions(),
  computed: {
    lock() {
      return {
        email: "ada@getkirby.com",
        unlockable: true
      };
    }
  },
  methods: {
    onUnlock: action("unlock")
  },
  template: `
    <k-model-view
      :columns="columns"
      :lock="lock"
      :options="options"
      :rename="true"
      :title="title"
      @unlock="onUnlock"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});

export const notUnlockable = () => ({
  extends: lock(),
  computed: {
    lock() {
      return {
        email: "ada@getkirby.com",
        unlockable: false
      };
    }
  },
});

export const unlocked = () => ({
  extends: customOptions(),
  methods: {
    onDownload: action("download"),
    onResolve: action("resolve"),
  },
  template: `
    <k-model-view
      :columns="columns"
      :options="options"
      :rename="true"
      :title="title"
      :unlocked="true"
      @download="onDownload"
      @resolve="onResolve"
      @option="onOption"
      @rename="onRename"
    >
      <template slot="options">
        <k-button icon="open" text="Open" @click="onOpen" />
      </template>
    </k-model-view>
  `,
});
