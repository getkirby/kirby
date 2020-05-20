<template>
  <div>
    <h1 class="text-lg mb-8">{{ method }}: {{ endpoint }}</h1>

    <h2 class="font-bold mb-3">Usage</h2>
    <k-code-block :code="call" class="mb-8" />

    <h2 class="font-bold mb-3">Example response</h2>
    <k-code-block :code="response" />
  </div>
</template>

<script>
export default {
  props: {
    method: {
      type: String,
      default: "GET"
    },
    endpoint: String,
    js: String,
    call: String
  },
  data() {
    return {
      response: null,
    }
  },
  created() {
    this.load();
  },
  methods: {
    async load() {
      try {
        this.response = await eval(this.call);
      } catch (error) {
        this.response = error;
      }
    }
  }
};
</script>
