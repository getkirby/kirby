// Custom Section
panel.section('test', {
  props: {
    config: Object
  },
  data () {
    return {
      message: this.config.message
    }
  },
  methods: {
    submit () {
      this.$refs.dialog.close();
    }
  },
  computed: {
    fields () {
      return [
        {
          label: 'Message',
          name: 'message',
          type: 'text'
        }
      ];
    }
  },
  template: `
    <section>
      <kirby-headline>
        {{ config.headline }}

        <kirby-button-group slot="options">
          <kirby-button @click="$refs.dialog.open()" icon="cog"></kirby-button>
        </kirby-button-group>
      </kirby-headline>
      <kirby-box @click="$refs.dialog.open()">
        {{ message ? message : 'Please change me …' }}
      </kirby-box>

      <kirby-dialog ref="dialog" button="Ok" @submit="submit">
        <kirby-form :fields="fields" :values="$data"></kirby-form>
      <kirby-dialog>
    </section>
  `
});
