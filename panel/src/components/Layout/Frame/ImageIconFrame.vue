<template>
	<k-image-frame v-if="src" v-bind="$props" />
	<k-icon-frame v-else v-bind="$props" />
</template>

<script>
import { props as ImageFrameProps } from "./ImageFrame.vue";
import { props as IconFrameProps } from "./IconFrame.vue";

export const props = {
	mixins: [ImageFrameProps, IconFrameProps]
};

export default {
	mixins: [props]
};
</script>
