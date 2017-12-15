// Range field
panel.field('range', {
  props: {
    min: {
      type: Number,
      default: 0
    },
    max: Number,
    step: {
      type: Number,
      default: 1
    },
    before: String,
    after: String,
    name: {
      default: 'range'
    },

    // for the kirby-field component
    label: {
      default: 'Range'
    }
  },
  computed: {
    // either show value or a placeholder in tooltip
    display () {
      return this.format(this.state) || '–';
    },
    track () {
      return `background: linear-gradient(to right, #282c34 ${this.position}%, #efefef ${this.position}%)`;
    },
    position () {
      return (this.state - this.min) / (this.max - this.min) * 100;
    }
  },
  methods: {
    // format number according to current language
    format (value) {
      // get language locale from store
      const locale = this.$store.state.language.locale;
      // get decimals of step
      let decimals = this.step.toString().split('.');
      decimals = decimals.length > 1 ? decimals[1].length : 0;
      return new Intl.NumberFormat(locale, {
        minimumFractionDigits: decimals
      }).format(value)
    }
  },
  template: `
    <kirby-field v-bind="fieldProps">
      <div class="kirby-range-input">
        <input
          type="range"
          :id="name"
          :name="name"
          :min="min"
          :max="max"
          :step="step"
          :required="required"
          v-model.number="state"
          :style="track"
        />
        <span>{{ before }} {{ display }} {{ after }}</span>
      </div>
    </kirby-field>
  `,
});


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
