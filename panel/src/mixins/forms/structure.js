import { props as Field } from "@/components/Forms/Field.vue";

export default {
  mixins: [Field],
  methods: {
    columnIsEmpty(value) {
      if (value === undefined || value === null || value === "") {
        return true;
      }

      if (
        typeof value === "object" &&
        Object.keys(value).length === 0 &&
        value.constructor === Object
      ) {
        return true;
      }

      if (value.length !== undefined && value.length === 0) {
        return true;
      }

      return false;
    },
    displayText(field, value) {
      switch (field.type) {
        case "user": {
          return value.email;
        }
        case "date": {
          const date = this.$library.dayjs(value);
          const format =
            field.time === true ? "YYYY-MM-DD HH:mm" : "YYYY-MM-DD";
          return date.isValid() ? date.format(format) : "";
        }
        case "tags":
        case "multiselect":
          return value
            .map((item) => {
              return item.text;
            })
            .join(", ");
        case "checkboxes": {
          return value
            .map((item) => {
              let text = item;

              field.options.forEach((option) => {
                if (option.value === item) {
                  text = option.text;
                }
              });

              return text;
            })
            .join(", ");
        }
        case "radio":
        case "select": {
          const option = field.options.filter(
            (item) => item.value === value
          )[0];
          return option ? option.text : null;
        }
      }

      if (typeof value === "object" && value !== null) {
        return "…";
      }

      return value?.toString();
    },
    previewExists(type) {
      return this.$helper.isComponent(`k-${type}-field-preview`);
    },
    width(fraction) {
      return fraction ? this.$helper.ratio(fraction, "auto", false) : "auto";
    }
  }
};
