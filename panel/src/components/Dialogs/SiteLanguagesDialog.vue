<template>
  <k-dialog class="k-site-languages-dialog" ref="dialog" size="large" button="Done">

    <template v-if="!add">
      <k-bar class="k-site-languages-header">
        <k-headline slot="left">Languages</k-headline>
        <k-button icon="add" slot="right" @click="add = true">Add language</k-button>
      </k-bar>

      <k-list v-if="languages.length">
        <k-list-item v-for="language in languages" :text="language.name" :info="language.code" :icon="{ type: 'globe', back: 'black' }">
          <k-button slot="options" icon="trash" @click="remove(language.code)" />
        </k-list-item>
      </k-list>
      <k-empty v-else icon="globe">
        No languages yet
      </k-empty>

      <footer slot="footer" class="k-dialog-footer">
        <k-button-group>
          <k-button icon="cancel" @click="$refs.dialog.close()">Close</k-button>
        </k-button-group>
      </footer>

    </template>

    <template v-else>
      <k-form ref="form" v-model="language" :fields="{
        name: {
          label: 'Name',
          type: 'text',
          required: true,
          width: '3/4'
        },
        code: {
          label: 'Code',
          type: 'text',
          required: true,
          width: '1/4'
        }
      }" @submit="create" />

      <footer slot="footer" class="k-dialog-footer">
        <k-button-group>
          <k-button icon="angle-left" @click="add = false">Back</k-button>
          <k-button icon="add" @click="$refs.form.submit()">Create</k-button>
        </k-button-group>
      </footer>

    </template>

  </k-dialog>
</template>
<script>

export default {
  data() {
    return {
      add: false,
      language: {
        name: null,
        code: null
      },
      languages: []
    }
  },
  methods: {
    open() {
      this.fetch()
        .then(() => {
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    fetch() {
      return this.$api.get('languages')
        .then(response => {
          this.languages = response.data;
        });
    },
    remove(code) {
      this.$api.delete('languages/' + code).then(this.fetch);
    },
    create() {

      this.$api.post('languages', this.language)
        .then(() => {
          this.fetch().then(() => {
            this.add = false;
            this.language = {
              code: null,
              name: null
            };
          });
        });

    }
  }
};
</script>

<style lang="scss">
.k-site-languages-header {
  margin-bottom: .75rem;
}
</style>
