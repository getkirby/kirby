// Range field
panel.field('range', {
  props: {
    min: {
      type: Number,
      default: 0
    },
    max: {
      type: Number
    },
    step: {
      type: Number,
      default: 1
    },
    before: {
      type: String
    },
    after: {
      type: String
    },

    // for the kirby-field component
    label: {
      default: 'Range'
    },
    name: {
      default: 'range'
    }
  },
  computed: {
    // either show value or a placeholder in tooltip
    display: function () {
      return this.value || '–';
    }
  },
  template: `
    <kirby-field :label="label" :required="required" :readonly="readonly" :name="name">
      <div class="kirby-range-input">
        <input type="range" :id="name" :name="name" :min="min" :max="max" :step="step" :required="required" v-model.number="data">
        <span>{{ before }} {{ display }} {{ after }}</span>
      </div>
    </kirby-field>
  `,
});
