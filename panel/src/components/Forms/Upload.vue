<template>
  <div class="k-upload">
    <input
      ref="input"
      :accept="options.accept"
      :multiple="options.multiple"
      aria-hidden="true"
      type="file"
      tabindex="-1"
      @change="select"
      @click.stop
    >

    <k-dialog
      ref="dialog"
      :cancel-button="false"
      :submit-button="false"
      size="medium"
    >
      <template v-if="errors.length > 0">
        <k-headline>{{ $t("upload.errors") }}</k-headline>
        <ul class="k-upload-error-list">
          <li v-for="(error, index) in errors" :key="'error-' + index">
            <p class="k-upload-error-filename">{{ error.file.name }}</p>
            <p class="k-upload-error-message">{{ error.message }}</p>
          </li>
        </ul>
      </template>
      <template v-else>
        <k-headline>{{ $t("upload.progress") }}</k-headline>
        <ul class="k-upload-list">
          <li v-for="(file, index) in files" :key="'file-' + index">
            <k-progress :ref="file.name"/>
            <p class="k-upload-list-filename">{{ file.name }}</p>
            <p>{{ errors[file.name] }}</p>
          </li>
        </ul>
      </template>
      <template slot="footer">
        <template v-if="errors.length > 0">
          <k-button-group>
            <k-button icon="check" @click="$refs.dialog.close()">
              {{ $t("confirm") }}
            </k-button>
          </k-button-group>
        </template>
      </template>
    </k-dialog>

  </div>
</template>

<script>
export default {
  props: {
    url: {
      type: String
    },
    accept: {
      type: String,
      default: "*"
    },
    attributes: {
      type: Object
    },
    multiple: {
      type: Boolean,
      default: true
    },
    max: {
      type: Number
    }
  },
  data() {
    return {
      options: this.$props,
      completed: {},
      errors: [],
      files: [],
      total: 0
    };
  },
  methods: {
    open(params) {
      this.params(params);

      setTimeout(() => {
        this.$refs.input.click();
      }, 1);
    },
    params(params) {
      this.options = Object.assign({}, this.$props, params);
    },
    select(e) {
      this.upload(e.target.files);
    },
    drop(files, params) {
      this.params(params);
      this.upload(files);
    },
    upload(files) {
      this.$refs.dialog.open();

      this.files = [...files];
      this.completed = {};
      this.errors = [];
      this.hasErrors = false;

      if (this.options.max) {
        this.files = this.files.slice(0, this.options.max);
      }

      this.total = this.files.length;
      this.files.forEach(file => {
        this.$helper.upload(file, {
          url: this.options.url,
          attributes: this.options.attributes,
          headers: {
            "X-CSRF": window.panel.csrf
          },
          progress: (xhr, file, progress) => {
            if (this.$refs[file.name] && this.$refs[file.name][0]) {
              this.$refs[file.name][0].set(progress);
            }
          },
          success: (xhr, file, response) => {
            this.complete(file, response.data);
          },
          error: (xhr, file, response) => {
            this.errors.push({ file: file, message: response.message });
            this.complete(file, response.data);
          }
        });
      });
    },
    complete(file, data) {
      this.completed[file.name] = data;

      if (Object.keys(this.completed).length == this.total) {
        // remove the selected file
        this.$refs.input.value = "";

        if (this.errors.length > 0) {
          this.$forceUpdate();
          this.$emit("error", this.files);
          return;
        }

        setTimeout(() => {
          this.$refs.dialog.close();
          this.$emit("success", this.files, Object.values(this.completed));
        }, 250);
      }
    }
  }
};
</script>

<style lang="scss">
.k-upload input {
  position: absolute;
  top: 0;

  [dir="ltr"] & {
    left: -3000px;
  }

  [dir="rtl"] & {
    right: -3000px;
  }
}

.k-upload .k-headline {
  margin-bottom: .75rem;
}

.k-upload-list,
.k-upload-error-list {
  line-height: 1.5em;
  font-size: $font-size-small;
}
.k-upload-list-filename {
  color: $color-dark-grey;
}

.k-upload-error-list li {
  padding: 0.75rem;
  background: $color-white;
  border-radius: $border-radius;
}
.k-upload-error-list li:not(:last-child) {
  margin-bottom: 2px;
}
.k-upload-error-filename {
  color: $color-negative;
  font-weight: $font-weight-bold;
}
.k-upload-error-message {
  color: $color-dark-grey;
}
</style>
