import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Upload",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <div>
      <k-button
        icon="upload"
        @click="$refs.upload.open()"
      >
        Upload
      </k-button>
      <k-upload ref="upload" />
    </div>
  `,
});

export const acceptMIME = () => ({
  template: `
    <div>
      <k-button
        icon="upload"
        @click="$refs.upload.open({
          accept: 'image/*'
        })"
      >
        Upload Image
      </k-button>
      <k-upload ref="upload" />
    </div>
  `,
});

export const singleUpload = () => ({
  template: `
    <div>
      <k-button
        icon="upload"
        @click="$refs.upload.open({
          accept: 'image/*',
          multiple: false
        })"
      >
        Upload Image
      </k-button>
      <k-upload ref="upload" />
    </div>
  `,
});

export const imageUpload = () => ({
  template: `
    <div>
      <k-button-native
        @click="$refs.upload.open({ accept: 'image/*' })">
        <k-image
          :cover="true"
          back="black"
          style="display: block; width: 6rem"
          src="https://source.unsplash.com/user/erondu/1600x900"
        />
      </k-button-native>
      <k-upload ref="upload" />
    </div>
  `,
});

export const withDropzone = () => ({
  computed: {
    styles() {
      return {
        padding: "2rem",
        border: "1px dashed #ddd",
        width: "100%"
      };
    },
    params() {
      return {
        accept: "image/*"
      }
    }
  },
  template: `
    <div>
      <k-dropzone @drop="$refs.upload.drop($event, params)">
        <k-button
          icon="upload"
          :style="styles"
          @click="$refs.upload.open(params)"
        >
          Click or Drop to upload
        </k-button>
      </k-dropzone>
      <k-upload ref="upload" />
    </div>
  `
});
