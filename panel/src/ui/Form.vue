<template>
  <k-ui class="k-ui-sandbox">

    <k-textarea-field
      ref="field"
      :required="true"
      :validate="true"
      v-bind="$options.fields.text"
      v-model="form.text"
      @invalid="console.log($event)"
    />
    {{ form.text }}

    <br>
    <k-button @click="$refs.field.focus()">Focus</k-button>

  </k-ui>
</template>

<script>
import Ui from "./components/Ui.vue";

export default {
  components: {
    "k-ui": Ui
  },
  data() {
    return {
      console: console,
      form: {
        title: "",
        radio: null,
        checkboxes: ["a"],
        checkbox: true,
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
        { icon: 'tag', text: 'Typography', value: 'Typography' },
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
      required: true,
      time: true,
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
    }
  },
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
</style>
