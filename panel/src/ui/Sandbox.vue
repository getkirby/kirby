<template>
  <!-- eslint-disable -->
  <kirby-ui class="kirby-ui-sandbox">
    <kirby-view>
      <kirby-header :tabs="headerTabs" :tab="headerTabs[0]">
        Kirby UI Sandbox
        <kirby-button-group slot="left">
          <kirby-button icon="open" target="_blank" link="https://getkirby.com">Open</kirby-button>
          <kirby-dropdown>
            <kirby-button icon="cog" @click="$refs.dropdown.toggle()">Settings</kirby-button>
            <kirby-dropdown-content ref="dropdown">
              <kirby-dropdown-item icon="settings" @click="$refs.settings.open()">Dialog Demo</kirby-dropdown-item>
              <kirby-dropdown-item icon="url" link="https://getkirby.com" target="_blank">Link Demo</kirby-dropdown-item>
              <kirby-dropdown-item :disabled="true" icon="cancel">Disabled Item Demo</kirby-dropdown-item>
            </kirby-dropdown-content>
          </kirby-dropdown>
          <kirby-button :disabled="true" icon="trash">Remove</kirby-button>
        </kirby-button-group>
        <kirby-button-group slot="right">
          <kirby-button :disabled="true" icon="angle-left"></kirby-button>
          <kirby-button icon="angle-right"></kirby-button>
        </kirby-button-group>
      </kirby-header>

      <kirby-header :tabs="headerTabsMany" :tab="headerTabsMany[0]">
        Too Many Tabs
      </kirby-header>

      <kirby-dialog ref="settings">
        This is a demo dialog
      </kirby-dialog>

      <section class="demo-section">
        <kirby-headline class="demo-headline" size="large">Forms</kirby-headline>

        <kirby-form
          :fields="$options.fields"
          :validate="true"
          :disabled="false"
          v-model="form"
          @submit="$refs.formOutput.open()"
        />

        <kirby-dialog ref="formOutput" size="large" @submit="$refs.formOutput.close()">
          <kirby-headline>Form Result</kirby-headline>
          <kirby-box theme="code">
            {{ form }}
          </kirby-box>
        </kirby-dialog>
      </section>

      <section class="demo-section">
        <kirby-bar class="demo-headline">
          <kirby-headline slot="left" size="large">Collections</kirby-headline>
          <kirby-button-group slot="right">
            <kirby-button icon="add" @click="addItem">Add Item</kirby-button>
            <kirby-button icon="trash" @click="removeItem">Remove Item</kirby-button>
            <kirby-button icon="cog" @click="$refs.itemsSettings.open()">Settings</kirby-button>
          </kirby-button-group>
        </kirby-bar>

        <kirby-collection
          :items="collection"
          :sortable="items.sortable"
          :layout="items.layout"
        />

        <kirby-dialog ref="itemsSettings" @submit="$refs.itemsSettings.close()">
          <kirby-fieldset
            :fields="{
              count: {
                label: 'Number of cards',
                type: 'number',
                min: 1
              },
              ratio: {
                label: 'Image Ratio',
                options: items.ratios,
                type: 'select'
              },
              layout: {
                label: 'Layout',
                options: [
                  { value: 'cards', text: 'Cards' },
                  { value: 'list', text: 'List' }
                ],
                type: 'radio'
              },
              sortable: {
                label: 'Sortable',
                type: 'toggle'
              },
            }"
            v-model="items"
          >
          </kirby-fieldset>
        </kirby-dialog>
      </section>

      <section class="demo-section">
        <kirby-headline class="demo-headline" size="large">Boxes</kirby-headline>

        <kirby-grid gutter="medium" style="--columns: 3">
          <kirby-box text="This is a regular box" />
          <kirby-box text="This is a positive box" theme="positive" />
          <kirby-box text="This is a negative box" theme="negative" />
        </kirby-grid>

      </section>

      <section class="demo-section">
        <kirby-headline class="demo-headline" size="large">Uploads</kirby-headline>
        <kirby-upload ref="upload" />

        <kirby-button icon="upload" @click="$refs.upload.open()">Upload</kirby-button>

      </section>

      <section class="demo-section">
        <kirby-headline class="demo-headline" size="large">Icons</kirby-headline>

        <kirby-grid style="--columns: 8">
          <kirby-icon class="demo-icon" type="url" />
          <kirby-icon class="demo-icon" type="url" back="black" />
          <kirby-icon class="demo-icon" type="url" back="white" />
          <kirby-icon class="demo-icon" type="url" back="pattern" />
          <kirby-icon class="demo-icon" type="💩" :emoji="true" />
          <kirby-icon class="demo-icon" type="💩" :emoji="true" back="black" />
          <kirby-icon class="demo-icon" type="💩" :emoji="true" back="white" />
          <kirby-icon class="demo-icon" type="💩" :emoji="true" back="pattern" />
        </kirby-grid>

      </section>

    </kirby-view>
  </kirby-ui>
</template>

<script>
import Ui from "./components/Ui.vue";

export default {
  components: {
    "kirby-ui": Ui
  },
  data() {
    return {
      console: window.console,
      headerTabs: this.createTabs(3),
      headerTabsMany: this.createTabs(7),
      items: {
        count: 4,
        ratio: "3/2",
        cover: true,
        back: "pattern",
        layout: "list",
        sortable: false,
        ratios: [
          { value: "1/1", text: "1/1" },
          { value: "3/2", text: "3/2" },
          { value: "3/1", text: "3/1" },
          { value: "2/3", text: "2/3" },
          { value: "16/9", text: "16/9" }
        ]
      },
      list: {
        count: 4
      },
      form: {
        title: "",
        radio: null,
        checkboxes: ["a"],
        email: "mail@example.com",
        url: "https://getkirby.com",
        phone: "+49 1234 5678",
        number: 12,
        select: "a",
        twitter: "getkirby",
        tags: ['testy mac test'],
        text: "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc,",
        time: "12:59",
        date: "2012-12-12T12:12:12",
        range: null,
        toggle: true,
        password: "",
      },
    }
  },
  fields: {
    title: {
      label: "Title",
      type: "text",
      required: true,
      minlength: 5,
      help: "Here's some help for the title field"
    },
    email: {
      label: "Email",
      type: "email",
      width: "1/2"
    },
    url: {
      label: "Url",
      type: "url",
      width: "1/2"
    },
    phone: {
      label: "Phone",
      type: "tel",
      width: "1/2"
    },
    number: {
      label: "Number",
      before: "$",
      max: 2,
      after: "budget",
      type: "number",
      width: "1/2"
    },
    select: {
      label: "Select",
      type: "select",
      width: "1/2",
      options: [
        { text: "Option A", value: "a" },
        { text: "Option B", value: "b" },
        { text: "Option C", value: "c" }
      ]
    },
    twitter: {
      label: "Twitter",
      type: "text",
      width: "1/2",
      before: "@",
      icon: "twitter"
    },
    radio: {
      label: "Radio",
      type: "radio",
      options: [
        { text: 'Option A', value: 'a', info: 'This is a brilliant option', icon: "url" },
        { text: 'Option B with some ridiculous long text', value: 'b', info: 'You could actually choose this too', icon: "clock" },
        { text: 'Option C', value: 'c', info: 'Well, if you think this makes sense, go ahead', icon: "twitter" },
        { text: 'Option D', value: 'd', info: 'Nah, I would not take that one, to be honest', icon: "tag" }
      ]
    },
    checkboxes: {
      label: "Checkboxes",
      type: "checkboxes",
      min: null,
      max: 3,
      options: [
        { text: 'Option A', value: 'a' },
        { text: 'Option B with some ridiculous long text', value: 'b' },
        { text: 'Option C', value: 'c' },
        { text: 'Option D', value: 'd' }
      ]
    },
    tags: {
      label: "Tags",
      type: "tags",
      min: 2,
      options: [
        { icon: 'tag', text: 'Architecture', value: 'Architecture' },
        { icon: 'tag', text: 'Photography', value: 'Photography' },
        { icon: 'tag', text: 'Design', value: 'Design' },
        { icon: 'tag', text: 'Culture', value: 'Culture' }
      ]
    },
    line: {
      type: "line"
    },
    text: {
      label: "Text",
      type: "textarea",
      required: true,
      buttons: true,
      size: "large"
    },
    headline: {
      label: "Custom Headline",
      type: "headline"
    },
    info: {
      label: "Help",
      type: "info",
      text: "This is a really useful info box"
    },
    time: {
      label: "Time",
      type: "time",
      required: true,
      width: "1/2"
    },
    date: {
      label: "Date",
      type: "date",
      time: {
        notation: 12
      },
      required: true,
      width: "1/2"
    },
    range: {
      label: "Range",
      type: "range",
      required: true
    },
    toggle: {
      label: "Toggle",
      type: "toggle",
      text: ["nope", "yes"]
    },
    password: {
      label: "Password",
      type: "password"
    },
    structure: {
      label: "Structure",
      type: "structure",
      fields: {
        title: {
          label: "Title",
          type: "text",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "text",
          width: "1/2"
        },
        links: {
          label: "Links",
          type: "structure",
          fields: {
            url: {
              label: "URL",
              type: "url"
            }
          }
        }
      }
    },
    user: {
      label: "User",
      type: "user",
    }
  },
  computed: {
    collection() {
      let items = [];

      for (let x = 0; x < this.items.count; x++) {
        items.push({
          image: {
            url: 'https://picsum.photos/400/400?random&t=' + x,
            cover: true,
            ratio: this.items.ratio
          },
          text: `Some image from unsplash ${x+1}`,
          info: 'random-unsplash.jpg',
          link: 'https://unsplash.com/',
          target: '_blank',
          options: [
            { icon: 'edit', text: 'Edit' },
            { icon: 'trash', text: 'Delete' }
          ]
        });
      }

      return items;
    }
  },
  methods: {
    addItem() {
      this.items.count++;
    },
    createTabs (count) {
      return [...Array(count)].map((el, i) => ({
        label: `Tab #${i}`,
        name: `#${i}`,
        icon: 'trash'
      }))
    },
    removeItem() {
      if (this.items.count > 1) {
        this.items.count--;
      }
    },
    onSort(items) {
      window.console.log(items);
    },
  }
}
</script>

<style lang="scss">

html {
  background: $color-background;
}

body {
  margin: 0;
  padding: 0;
}

.demo-section {
  padding-bottom: 9rem;
}
.demo-headline {
  margin-bottom: 1.5rem;
  border-bottom: 1px dashed $color-border;
  padding-bottom: .75rem;
}

.demo-icon {
  width: 100%;
  height: 2rem;
}
</style>
